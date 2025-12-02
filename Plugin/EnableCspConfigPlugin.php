<?php declare(strict_types=1);

namespace LokiCheckout\Csp\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeInterface;

class EnableCspConfigPlugin
{
    /**
     * @param ScopeConfigInterface           $subject
     * @param mixed                          $result
     * @param string                         $path
     * @param string                         $scopeType
     * @param int|ScopeInterface|string|null $scopeCode
     *
     * @return mixed
     */
    public function afterGetValue(
        ScopeConfigInterface $subject,
        mixed $result,
        $path,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        if (false === str_starts_with($path, 'csp/')) {
            return $result;
        }

        static $isEnabled = null;
        if (false === is_bool($isEnabled)) {
            $isEnabled = $this->isEnabled($subject, $scopeType, $scopeCode);
        }

        if (false === $isEnabled) {
            return $result;
        }

        if ($path == 'csp/mode/storefront_checkout_index_index') {
            return [
                'report_only' => 0,
            ];
        }

        if ($path == 'csp/policies/storefront_checkout_index_index') {
            $cspPolicy = $this->getCspPolicy();
            if (false === is_array($result)) {
                return $cspPolicy;
            }

            return array_merge($result, $cspPolicy);
        }


        return $result;
    }

    private function isEnabled(
        ScopeConfigInterface $scopeConfig,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        return (bool)$scopeConfig->getValue('loki_checkout/general/enable', $scopeType, $scopeCode);
    }

    private function getCspPolicy(): array
    {
        return [
            'scripts' => [
                'inline' => 0,
                'event_handlers' => 1,
                'eval' => 0,
            ]
        ];
    }
}
