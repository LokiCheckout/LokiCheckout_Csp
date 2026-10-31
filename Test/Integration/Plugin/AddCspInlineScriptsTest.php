<?php declare(strict_types=1);

namespace LokiCheckout\Csp\Test\Integration\Plugin;

use LokiCheckout\Csp\Test\Integration\Stub\InlineScriptBlock;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class AddCspInlineScriptsTest extends TestCase
{
    #[ConfigFixture('loki_checkout/general/enable', 1, 'store', 'default')]
    public function testAddsNonceToAllowedBlockOnCheckout(): void
    {
        $this->setFullActionName('checkout_index_index');

        $this->assertMatchesRegularExpression('#<script nonce="[^"]+">#', $this->render('head.js'));
    }

    #[ConfigFixture('loki_checkout/general/enable', 1, 'store', 'default')]
    public function testSkipsBlockOutsideAllowlist(): void
    {
        $this->setFullActionName('checkout_index_index');

        $this->assertStringNotContainsString('nonce=', $this->render('messages'));
    }

    #[ConfigFixture('loki_checkout/general/enable', 1, 'store', 'default')]
    public function testSkipsOtherPages(): void
    {
        $this->setFullActionName('cms_index_index');

        $this->assertStringNotContainsString('nonce=', $this->render('head.js'));
    }

    #[ConfigFixture('loki_checkout/general/enable', 0, 'store', 'default')]
    public function testSkipsWhenLokiCheckoutIsDisabled(): void
    {
        $this->setFullActionName('checkout_index_index');

        $this->assertStringNotContainsString('nonce=', $this->render('head.js'));
    }

    private function setFullActionName(string $fullActionName): void
    {
        [$route, $controller, $action] = explode('_', $fullActionName);
        $request = ObjectManager::getInstance()->get(HttpRequest::class);
        $request->setRouteName($route);
        $request->setControllerName($controller);
        $request->setActionName($action);
    }

    private function render(string $name): string
    {
        $layout = ObjectManager::getInstance()->create(LayoutInterface::class);
        $block = $layout->createBlock(InlineScriptBlock::class, $name);
        $this->assertInstanceOf(InlineScriptBlock::class, $block);

        return (string)$block->toHtml();
    }
}
