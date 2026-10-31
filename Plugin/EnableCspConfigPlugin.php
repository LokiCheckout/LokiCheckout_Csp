<?php declare(strict_types=1);

namespace LokiCheckout\Csp\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\ScopeInterface;

class EnableCspConfigPlugin
{
    private const CHECKOUT_ACTION = 'checkout_index_index';
    private const MODE_PATH = 'csp/mode/storefront_checkout_index_index';
    private const POLICIES_PATH = 'csp/policies/storefront_checkout_index_index';
    private const GLOBAL_REPORT_ONLY_PATH = 'csp/mode/storefront/report_only';
    private const GLOBAL_POLICIES_PATH = 'csp/policies/storefront';

    private readonly HttpRequest $request;

    public function __construct(
        HttpRequest $request
    ) {
        $this->request = $request;
    }

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

        if (false === $this->isApplicablePath($path)) {
            return $result;
        }

        if (false === (bool)$subject->getValue('loki_checkout/general/enable', $scopeType, $scopeCode)) {
            return $result;
        }

        if ($path === self::MODE_PATH . '/report_only' || $path === self::GLOBAL_REPORT_ONLY_PATH) {
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

    private function isApplicablePath(string $path): bool
    {
        if (in_array($path, [self::MODE_PATH, self::MODE_PATH . '/report_only', self::POLICIES_PATH], true)) {
            return true;
        }

        if (false === in_array($path, [self::GLOBAL_REPORT_ONLY_PATH, self::GLOBAL_POLICIES_PATH], true)) {
            return false;
        }

        return $this->request->getFullActionName() === self::CHECKOUT_ACTION;
    }

    private function getCspPolicy(): array
    {
        return [
            'scripts' => [
                'policy_id' => 'script-src',
                'inline' => 0,
                'event_handlers' => 1,
                'eval' => 0,
            ]
        ];
    }
}
