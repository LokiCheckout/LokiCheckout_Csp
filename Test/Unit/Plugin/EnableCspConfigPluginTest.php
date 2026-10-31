<?php declare(strict_types=1);

namespace LokiCheckout\Csp\Test\Unit\Plugin;

use LokiCheckout\Csp\Plugin\EnableCspConfigPlugin;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use PHPUnit\Framework\TestCase;

class EnableCspConfigPluginTest extends TestCase
{
    private const REPORT_ONLY_PATH = 'csp/mode/storefront_checkout_index_index/report_only';

    public function testIgnoresNonCspPaths(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|default' => true]);
        $plugin = $this->getPlugin();

        $this->assertSame('foo', $plugin->afterGetValue($scopeConfig, 'foo', 'web/foo/bar', 'store', 'default'));
    }

    public function testForcesReportOnlyOffOnLeafPath(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|default' => true]);
        $plugin = $this->getPlugin();

        $result = $plugin->afterGetValue($scopeConfig, '1', self::REPORT_ONLY_PATH, 'store', 'default');

        $this->assertSame(0, $result);
    }

    public function testKeepsReportUriOnParentModePath(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|default' => true]);
        $plugin = $this->getPlugin();

        $result = $plugin->afterGetValue(
            $scopeConfig,
            ['report_only' => 1, 'report_uri' => 'https://example.com/csp'],
            'csp/mode/storefront_checkout_index_index',
            'store',
            'default'
        );

        $this->assertSame(['report_only' => 0, 'report_uri' => 'https://example.com/csp'], $result);
    }

    public function testParentModePathWithoutValueForcesReportOnlyOff(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|default' => true]);
        $plugin = $this->getPlugin();

        $result = $plugin->afterGetValue(
            $scopeConfig,
            null,
            'csp/mode/storefront_checkout_index_index',
            'store',
            'default'
        );

        $this->assertSame(['report_only' => 0], $result);
    }

    public function testDoesNotChangeModeWhenDisabled(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|default' => false]);
        $plugin = $this->getPlugin();

        $result = $plugin->afterGetValue($scopeConfig, '1', self::REPORT_ONLY_PATH, 'store', 'default');

        $this->assertSame('1', $result);
    }

    public function testPolicyMergeKeepsCheckoutSpecificHosts(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|default' => true]);
        $plugin = $this->getPlugin();

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

    public function testPolicyWithoutValueIsAValidScriptSrcPolicy(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|default' => true]);
        $plugin = $this->getPlugin();

        $result = $plugin->afterGetValue(
            $scopeConfig,
            null,
            'csp/policies/storefront_checkout_index_index',
            'store',
            'default'
        );

        $this->assertSame('script-src', $result['scripts']['policy_id']);
        $this->assertSame(0, $result['scripts']['inline']);
        $this->assertSame(0, $result['scripts']['eval']);
    }

    public function testEnabledFlagIsResolvedPerScope(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|enabled_store' => true, 'store|disabled_store' => false]);
        $plugin = $this->getPlugin();

        $this->assertSame(0, $plugin->afterGetValue($scopeConfig, '1', self::REPORT_ONLY_PATH, 'store', 'enabled_store'));
        $this->assertSame('1', $plugin->afterGetValue($scopeConfig, '1', self::REPORT_ONLY_PATH, 'store', 'disabled_store'));
    }

    public function testEnabledFlagIsNotCachedForTheCurrentStore(): void
    {
        $enabled = true;
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(
            function ($path) use (&$enabled) {
                return $path === 'loki_checkout/general/enable' ? $enabled : null;
            }
        );
        $plugin = $this->getPlugin();

        $this->assertSame(0, $plugin->afterGetValue($scopeConfig, '1', self::REPORT_ONLY_PATH, 'store', null));

        $enabled = false;
        $this->assertSame('1', $plugin->afterGetValue($scopeConfig, '1', self::REPORT_ONLY_PATH, 'store', null));
    }

    public function testForcesGlobalReportOnlyOffOnCheckout(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|default' => true]);
        $plugin = $this->getPlugin('checkout_index_index');

        $result = $plugin->afterGetValue($scopeConfig, '1', 'csp/mode/storefront/report_only', 'store', 'default');

        $this->assertSame(0, $result);
    }

    public function testMergesScriptPolicyIntoGlobalPoliciesOnCheckout(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|default' => true]);
        $plugin = $this->getPlugin('checkout_index_index');

        $result = $plugin->afterGetValue(
            $scopeConfig,
            [
                'scripts' => ['policy_id' => 'script-src', 'self' => 1, 'inline' => 1, 'eval' => 1],
                'images' => ['policy_id' => 'img-src'],
            ],
            'csp/policies/storefront',
            'store',
            'default'
        );

        $this->assertSame(
            ['policy_id' => 'script-src', 'self' => 1, 'inline' => 0, 'eval' => 0, 'event_handlers' => 1],
            $result['scripts']
        );
        $this->assertSame(['policy_id' => 'img-src'], $result['images']);
    }

    public function testKeepsGlobalPathsOutsideCheckout(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|default' => true]);
        $plugin = $this->getPlugin('cms_index_index');
        $policies = ['scripts' => ['policy_id' => 'script-src', 'inline' => 1]];

        $this->assertSame('1', $plugin->afterGetValue($scopeConfig, '1', 'csp/mode/storefront/report_only', 'store', 'default'));
        $this->assertSame($policies, $plugin->afterGetValue($scopeConfig, $policies, 'csp/policies/storefront', 'store', 'default'));
    }

    public function testKeepsGlobalPathsOnCheckoutWhenDisabled(): void
    {
        $scopeConfig = $this->getScopeConfig(['store|default' => false]);
        $plugin = $this->getPlugin('checkout_index_index');

        $this->assertSame('1', $plugin->afterGetValue($scopeConfig, '1', 'csp/mode/storefront/report_only', 'store', 'default'));
    }

    public function testDoesNotReadRequestForUnrelatedCspPaths(): void
    {
        $request = $this->createMock(HttpRequest::class);
        $request->expects($this->never())->method('getFullActionName');
        $plugin = new EnableCspConfigPlugin($request);

        $scopeConfig = $this->getScopeConfig(['store|default' => true]);
        $this->assertSame('1', $plugin->afterGetValue($scopeConfig, '1', 'csp/mode/admin/report_only', 'store', 'default'));
    }

    private function getPlugin(string $fullActionName = 'checkout_index_index'): EnableCspConfigPlugin
    {
        $request = $this->createStub(HttpRequest::class);
        $request->method('getFullActionName')->willReturn($fullActionName);

        return new EnableCspConfigPlugin($request);
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
