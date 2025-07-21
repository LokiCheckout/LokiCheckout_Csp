<?php declare(strict_types=1);

namespace LokiCheckout\Csp\Plugin;

use Magento\Framework\View\Element\Template;
use Yireo\CspUtilities\Util\ReplaceInlineScripts;

class AddCspInlineScripts
{
    private readonly ReplaceInlineScripts $replaceInlineScripts;

    public function __construct(
        ReplaceInlineScripts $replaceInlineScripts
    ) {
        $this->replaceInlineScripts = $replaceInlineScripts;
    }

    public function afterToHtml(Template $block, $html): string
    {
        if (false === in_array((string)$block->getNameInLayout(), $this->allowedBlocks())) {
            return (string)$html;
        }

        return $this->replaceInlineScripts->replace((string)$html);
    }

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
          'messages',
        ];
    }
}
