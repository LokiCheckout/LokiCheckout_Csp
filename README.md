# LokiCheckout_Csp

**This Magento 2 module is an add-on package for enforcing the CSP Restrictive Mode in the checkout, including the Alpine CSP Build and removing the `unsafe-inline` and `unsafe-eval` policies. This is required to be fully compliant with PCI DSS v4.**

*Note that we develop our core with this module enabled at all times, which ensures that the Loki Checkout is CSP compliant at all times.*

## Installation
Install this package via composer:
```bash
composer require loki-checkout/magento2-csp
```

Next, enable this module:
```bash
bin/magento module:enable LokiCheckout_Csp
```

## Usage notes
Note that this module only covers CSP rules for the LokiCheckout extensions. Your theme still requires work as well to work together with the no-`unsafe-inline` and no-`unsafe-eval` policies.

