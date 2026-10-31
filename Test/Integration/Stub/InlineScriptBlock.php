<?php declare(strict_types=1);

namespace LokiCheckout\Csp\Test\Integration\Stub;

use Magento\Framework\View\Element\Template;

class InlineScriptBlock extends Template
{
    protected function _toHtml()
    {
        return '<script>window.lokiCheckoutCspTest = 1;</script>';
    }
}
