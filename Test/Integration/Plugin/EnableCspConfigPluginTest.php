<?php declare(strict_types=1);

namespace LokiCheckout\Csp\Test\Integration\Plugin;

use Magento\Csp\Api\CspRendererInterface;
use Magento\Csp\Api\ModeConfigManagerInterface;
use Magento\Csp\Helper\CspNonceProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class EnableCspConfigPluginTest extends TestCase
{
    #[ConfigFixture('loki_checkout/general/enable', 1, 'store', 'default')]
    public function testEnforcesCspOnCheckout(): void
    {
        $this->setFullActionName('checkout', 'index', 'index');

        $modeConfigManager = ObjectManager::getInstance()->get(ModeConfigManagerInterface::class);
        $this->assertFalse($modeConfigManager->getConfigured()->isReportOnly());
    }

    #[ConfigFixture('loki_checkout/general/enable', 1, 'store', 'default')]
    public function testForcesGlobalReportOnlyPathOffOnCheckout(): void
    {
        $this->setFullActionName('checkout', 'index', 'index');

        $this->assertSame(0, $this->getStoreValue('csp/mode/storefront/report_only'));
        $this->assertSame(0, (int)$this->getStoreValue('csp/policies/storefront')['scripts']['inline']);
        $this->assertSame(0, (int)$this->getStoreValue('csp/policies/storefront')['scripts']['eval']);
    }

    #[ConfigFixture('loki_checkout/general/enable', 1, 'store', 'default')]
    public function testRendersRestrictiveScriptSrcOnCheckout(): void
    {
        $this->setFullActionName('checkout', 'index', 'index');

        $nonce = ObjectManager::getInstance()->get(CspNonceProvider::class)->generateNonce();
        $response = ObjectManager::getInstance()->create(HttpResponse::class);
        ObjectManager::getInstance()->get(CspRendererInterface::class)->render($response);

        $this->assertFalse($response->getHeader('Content-Security-Policy-Report-Only'));
        $header = $response->getHeader('Content-Security-Policy');
        $this->assertNotFalse($header);

        $scriptSrc = $this->getDirective($header->getFieldValue(), 'script-src');
        $this->assertStringNotContainsString("'unsafe-inline'", $scriptSrc);
        $this->assertStringNotContainsString("'unsafe-eval'", $scriptSrc);
        $this->assertStringContainsString("'nonce-" . $nonce . "'", $scriptSrc);
    }

    #[ConfigFixture('loki_checkout/general/enable', 0, 'store', 'default')]
    public function testLeavesCspConfigAloneWhenLokiCheckoutIsDisabled(): void
    {
        $this->setFullActionName('checkout', 'index', 'index');

        $this->assertSame('1', (string)$this->getStoreValue('csp/mode/storefront/report_only'));
        $policies = $this->getStoreValue('csp/policies/storefront_checkout_index_index');
        $this->assertArrayNotHasKey('eval', $policies['scripts'] ?? []);
    }

    #[ConfigFixture('loki_checkout/general/enable', 1, 'store', 'default')]
    public function testKeepsReportOnlyOutsideCheckout(): void
    {
        $this->setFullActionName('cms', 'index', 'index');

        $modeConfigManager = ObjectManager::getInstance()->get(ModeConfigManagerInterface::class);
        $this->assertTrue($modeConfigManager->getConfigured()->isReportOnly());
        $this->assertSame('1', (string)$this->getStoreValue('csp/mode/storefront/report_only'));
    }

    private function setFullActionName(string $route, string $controller, string $action): void
    {
        $request = ObjectManager::getInstance()->get(HttpRequest::class);
        $request->setRouteName($route);
        $request->setControllerName($controller);
        $request->setActionName($action);
    }

    private function getStoreValue(string $path): mixed
    {
        $store = ObjectManager::getInstance()->get(StoreManagerInterface::class)->getStore();
        return ObjectManager::getInstance()->get(ScopeConfigInterface::class)
            ->getValue($path, ScopeInterface::SCOPE_STORE, $store);
    }

    private function getDirective(string $policy, string $name): string
    {
        foreach (explode(';', $policy) as $directive) {
            $directive = trim($directive, " \f\n\r\t\v\x00");
            if (str_starts_with($directive, $name . ' ')) {
                return $directive;
            }
        }

        $this->fail('Directive ' . $name . ' not found in ' . $policy);
    }
}
