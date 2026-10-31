<?php declare(strict_types=1);

namespace LokiCheckout\Csp\Test\Unit\Plugin;

use LokiCheckout\Csp\Plugin\AddCspInlineScripts;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\View\Element\Template;
use PHPUnit\Framework\TestCase;
use Yireo\CspUtilities\Util\ReplaceInlineScripts;

class AddCspInlineScriptsTest extends TestCase
{
    private const HTML = '<script>console.log(1)</script>';

    public function testAddsNoncesToAllowedBlockInCheckout(): void
    {
        $plugin = $this->getPlugin('checkout_index_index', true, true);
        $this->assertSame('replaced', $plugin->afterToHtml($this->getBlock('head.js'), self::HTML));
    }

    public function testSkipsMessagesBlock(): void
    {
        $plugin = $this->getPlugin('checkout_index_index', true, false);
        $this->assertSame(self::HTML, $plugin->afterToHtml($this->getBlock('messages'), self::HTML));
    }

    public function testSkipsNonCheckoutPages(): void
    {
        $plugin = $this->getPlugin('catalog_product_view', true, false);
        $this->assertSame(self::HTML, $plugin->afterToHtml($this->getBlock('head.js'), self::HTML));
    }

    public function testSkipsWhenLokiCheckoutIsDisabled(): void
    {
        $plugin = $this->getPlugin('checkout_index_index', false, false);
        $this->assertSame(self::HTML, $plugin->afterToHtml($this->getBlock('head.js'), self::HTML));
    }

    public function testReturnsStringForNullHtmlOfOtherBlocks(): void
    {
        $plugin = $this->getPlugin('checkout_index_index', true, false);
        $this->assertSame('', $plugin->afterToHtml($this->getBlock('messages'), null));
    }

    private function getPlugin(string $fullActionName, bool $enabled, bool $expectReplace): AddCspInlineScripts
    {
        $replaceInlineScripts = $this->createMock(ReplaceInlineScripts::class);
        $replaceInlineScripts->expects($expectReplace ? $this->once() : $this->never())
            ->method('replace')
            ->willReturn('replaced');

        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('isSetFlag')->willReturn($enabled);

        $request = $this->createStub(HttpRequest::class);
        $request->method('getFullActionName')->willReturn($fullActionName);

        return new AddCspInlineScripts($replaceInlineScripts, $scopeConfig, $request);
    }

    private function getBlock(string $name): Template
    {
        $block = $this->createStub(Template::class);
        $block->method('getNameInLayout')->willReturn($name);

        return $block;
    }
}
