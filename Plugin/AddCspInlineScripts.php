<?php declare(strict_types=1);

namespace Yireo\LokiCheckoutCsp\Plugin;

use Magento\Framework\View\Element\Template;
use Yireo\CspUtilities\Util\ReplaceInlineScripts;

class AddCspInlineScripts
{
    private ReplaceInlineScripts $replaceInlineScripts;

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
          'head.magewire-init',
          'magewire.loader.notifications.bindings',
          'magewire.loader.bindings',
          'magewire.livewire',
          'magewire.livewire',
          'init.external.scripts',
          'product.info.options.configurable.js',
          'form-submit.set-uenc',
          'script-private-content',
          'magewire.plugin.error',
          'magewire.plugin.loader',
          'magewire.extend.plugin',
          'magewire.directive.select',
          'product.info.options.swatch.js',
          'script-alpine-js',
          'script-cookies',
          'magewire.initialize',
          'head.intersection-observer-polyfill',
          'mobile-safari-bug-workaround',
          'messages',
        ];
    }
}
