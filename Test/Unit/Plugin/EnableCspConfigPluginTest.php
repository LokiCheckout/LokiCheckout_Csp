<?php declare(strict_types=1);

namespace LokiCheckout\Csp\Test\Unit\Plugin;

use LokiCheckout\Csp\Plugin\EnableCspConfigPlugin;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;

class EnableCspConfigPluginTest extends TestCase
{
    public function testIgnoresNonCspPaths(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|default' => true]);
        $plugin = new EnableCspConfigPlugin();

        $this->assertSame('foo', $plugin->afterGetValue($scopeConfig, 'foo', 'web/foo/bar', 'store', 'default'));
    }

    public function testForcesReportOnlyOffOnLeafPath(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|default' => true]);
        $plugin = new EnableCspConfigPlugin();

        $result = $plugin->afterGetValue(
            $scopeConfig,
            '1',
            'csp/mode/storefront_checkout_index_index/report_only',
            'store',
            'default'
        );

        $this->assertSame(0, $result);
    }

    public function testKeepsReportUriOnParentModePath(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|default' => true]);
        $plugin = new EnableCspConfigPlugin();

        $result = $plugin->afterGetValue(
            $scopeConfig,
            ['report_only' => 1, 'report_uri' => 'https://example.com/csp'],
            'csp/mode/storefront_checkout_index_index',
            'store',
            'default'
        );

        $this->assertSame(['report_only' => 0, 'report_uri' => 'https://example.com/csp'], $result);
    }

    public function testDoesNotChangeModeWhenDisabled(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|default' => false]);
        $plugin = new EnableCspConfigPlugin();

        $result = $plugin->afterGetValue(
            $scopeConfig,
            '1',
            'csp/mode/storefront_checkout_index_index/report_only',
            'store',
            'default'
        );

        $this->assertSame('1', $result);
    }

    public function testPolicyMergeKeepsCheckoutSpecificHosts(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|default' => true]);
        $plugin = new EnableCspConfigPlugin();

        $result = $plugin->afterGetValue(
            $scopeConfig,
            [
                'scripts' => [
                    'inline' => 1,
                    'eval' => 1,
                    'hosts' => ['psp' => 'https://psp.example.com'],
                ],
                'images' => ['policy_id' => 'img-src'],
            ],
            'csp/policies/storefront_checkout_index_index',
            'store',
            'default'
        );

        $this->assertSame(0, $result['scripts']['inline']);
        $this->assertSame(0, $result['scripts']['eval']);
        $this->assertSame(['psp' => 'https://psp.example.com'], $result['scripts']['hosts']);
        $this->assertSame(['policy_id' => 'img-src'], $result['images']);
    }

    public function testEnabledFlagIsResolvedPerScope(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|enabled_store' => true, 'store|disabled_store' => false]);
        $plugin = new EnableCspConfigPlugin();
        $path = 'csp/mode/storefront_checkout_index_index/report_only';

        $this->assertSame(0, $plugin->afterGetValue($scopeConfig, '1', $path, 'store', 'enabled_store'));
        $this->assertSame('1', $plugin->afterGetValue($scopeConfig, '1', $path, 'store', 'disabled_store'));
    }

    private function getScopeConfig(array $enabledPerScope): ScopeConfigInterface
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(
            function ($path, $scopeType = 'default', $scopeCode = null) use ($enabledPerScope) {
                if ($path !== 'loki_checkout/general/enable') {
                    return null;
                }

                return $enabledPerScope[$scopeType . '|' . $scopeCode] ?? false;
            }
        );

        return $scopeConfig;
    }
}
