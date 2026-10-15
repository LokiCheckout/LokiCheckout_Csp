<?php declare(strict_types=1);

namespace LokiCheckout\Csp\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Yireo\CspUtilities\Util\ReplaceInlineScripts;

class AddCspInlineScripts
{
    private readonly ReplaceInlineScripts $replaceInlineScripts;
    private readonly ScopeConfigInterface $scopeConfig;
    private readonly HttpRequest $request;

    public function __construct(
        ReplaceInlineScripts $replaceInlineScripts,
        ScopeConfigInterface $scopeConfig,
        HttpRequest $request
    ) {
        $this->replaceInlineScripts = $replaceInlineScripts;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    public function afterToHtml(Template $block, $html): string
    {
        if (false === in_array((string)$block->getNameInLayout(), $this->allowedBlocks(), true)) {
            return (string)$html;
        }

        if (false === $this->isApplicable()) {
            return (string)$html;
        }

        return $this->replaceInlineScripts->replace((string)$html);
    }

    private function isApplicable(): bool
    {
        if ($this->request->getFullActionName() !== 'checkout_index_index') {
            return false;
        }

        return $this->scopeConfig->isSetFlag('loki_checkout/general/enable', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Only add blocks here that render static, developer-authored scripts. Never add blocks that may contain
     * request, quote, customer or admin data (like the "messages" block), because every script in their
     * output receives a valid CSP nonce.
     */
    private function allowedBlocks(): array
    {
        return [
          'head.js',
          'head.hyva-scripts',
          'init.external.scripts',
          'product.info.options.configurable.js',
          'form-submit.set-uenc',
          'script-private-content',
          'product.info.options.swatch.js',
          'script-alpine-js',
          'script-cookies',
          'head.intersection-observer-polyfill',
          'mobile-safari-bug-workaround',
        ];
    }
}
