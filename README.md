# Yireo_LokiCheckoutCsp

**This is an add-on package for enforcing the CSP Restrictive Mode in the checkout, including the Alpine CSP Build and removing the `unsafe-inline` and `unsafe-eval` policies. This is required to be fully compliant with PCI DSS v4.**

Current status: Stable

## Installation
Install this package via composer (assuming you have setup the `composer.yireo.com` repository correctly already):
```bash
composer require yireo/magento2-loki-checkout-csp
```

Next, enable this module:
```bash
bin/magento module:enable Yireo_LokiCheckoutCsp
```

## Usage notes
Note that this module only covers CSP rules for the LokiCheckout extensions. Your theme still requires work as well to work together with the no-`unsafe-inline` and no-`unsafe-eval` policies.

