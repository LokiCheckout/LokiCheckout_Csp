<?php declare(strict_types=1);

namespace LokiCheckout\Csp\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeInterface;

class EnableCspConfigPlugin
{
    private const MODE_PATH = 'csp/mode/storefront_checkout_index_index';
    private const POLICIES_PATH = 'csp/policies/storefront_checkout_index_index';

    /**
     * @var bool[]
     */
    private array $isEnabled = [];

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
        if (false === is_string($path) || false === str_starts_with($path, 'csp/')) {
            return $result;
        }

        if (false === in_array($path, [self::MODE_PATH, self::MODE_PATH . '/report_only', self::POLICIES_PATH])) {
            return $result;
        }

        if (false === $this->isEnabled($subject, $scopeType, $scopeCode)) {
            return $result;
        }

        if ($path === self::MODE_PATH . '/report_only') {
            return 0;
        }

        if ($path === self::MODE_PATH) {
            return array_replace(is_array($result) ? $result : [], ['report_only' => 0]);
        }

        $cspPolicy = $this->getCspPolicy();
        if (false === is_array($result)) {
            return $cspPolicy;
        }

        return array_replace_recursive($result, $cspPolicy);
    }

    private function isEnabled(
        ScopeConfigInterface $scopeConfig,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        $cacheKey = $this->getScopeCacheKey($scopeType, $scopeCode);
        if (false === isset($this->isEnabled[$cacheKey])) {
            $this->isEnabled[$cacheKey] = (bool)$scopeConfig->getValue(
                'loki_checkout/general/enable',
                $scopeType,
                $scopeCode
            );
        }

        return $this->isEnabled[$cacheKey];
    }

    private function getScopeCacheKey($scopeType, $scopeCode): string
    {
        if (is_object($scopeCode)) {
            $scopeCode = method_exists($scopeCode, 'getCode')
                ? $scopeCode->getCode()
                : spl_object_hash($scopeCode);
        }

        return (string)$scopeType . '|' . (is_scalar($scopeCode) ? (string)$scopeCode : '');
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
