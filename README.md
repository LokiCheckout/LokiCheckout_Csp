# LokiCheckout_Csp

<!-- badges.specs.start -->
<div class="github-specs-badges">

![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/loki-checkout/magento2-csp)

</div>
<!-- badges.specs.end -->


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

When the Loki Checkout is enabled, this module enforces the CSP (no report-only mode) on the checkout page and removes `unsafe-inline` and `unsafe-eval` from `script-src`. This applies to every theme. Because of this, the following is required:

- **Hyvä**: Use the CSP theme `Hyva/default-csp` (or a child theme of it). The regular `Hyva/default` theme loads an Alpine.js build that requires `unsafe-eval` and stops working in the checkout.
- **Luma**: This module replaces Alpine.js with the Alpine CSP build in Luma-based themes (themes inheriting from `Magento/blank`). However, the Luma header still contains Knockout UI components (like the customer greeting), which Knockout and `mage/utils/template.js` parse with `eval`. These widgets break in the checkout, with errors like `EvalError: Unable to parse bindings`. Remove them from the checkout layout of your theme.
- **Other themes** (Breeze, themes not inheriting from `Magento/blank`) and **MageOS_AlpineLoader**: The Alpine CSP build of this module is not loaded there, so make sure that your theme loads an Alpine CSP build itself.

Magento 2.4.6 does not support CSP settings per page. With 2.4.6, this module therefore overrides the global storefront CSP settings, but only for the checkout page.


## Current status

<!-- badges.test.start -->
<div class="github-pipeline-badges">

![Static Tests](https://img.shields.io/github/actions/workflow/status/LokiCheckout/LokiCheckout_Csp/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/LokiCheckout/LokiCheckout_Csp/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/LokiCheckout/LokiCheckout_Csp/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/LokiCheckout/LokiCheckout_Csp/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/LokiCheckout/LokiCheckout_Csp/compile.yml?label=compile)

</div>
<!-- badges.test.end -->
