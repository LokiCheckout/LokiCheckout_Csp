# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.14] - 06 November 2026
### Fixed
- Require Magento_Csp
- Drop crossorigin attribute from Alpine CSP script
- Enforce checkout CSP on Magento 2.4.6 and resolve enabled flag on every read

## [2.0.13] - 30 October 2026
### Fixed
- Enforce restrictive mode via correct config path
- Restrict inline script nonces to checkout allowlist
- Fix Hyva `x-html` issue

### Security
- Stop adding CSP nonces to the `messages` block, which could allow injected scripts to run
- Only add CSP nonces on the checkout page when Loki Checkout is enabled
- Fix config path used to force CSP restrictive mode in the checkout
- Resolve Loki Checkout enabled flag per scope instead of once per process
- Merge checkout script policy recursively so checkout-specific hosts are kept

## [2.0.12] - 11 August 2026
### Fixed
- Re-enable original Alpine CSP build for Hyva Themes

## [2.0.11] - 07 August 2026
### Fixed
- Drop custom Alpine CSP with Hyva CSP-theme
- Upgrade Alpine to 3.15.12

## [2.0.10] - 12 January 2026
### Fixed
- Check for AlpineJS loader block instead of module being enabled
- Copy generic CI/CD files

## [2.0.9] - 02 December 2025
### Fixed
- Enable CSP on-the-fly to allow for disabling under Luma Checkout

## [2.0.8] - 21 November 2025
### Fixed
- Allow compatibility with `MageOS_AlpineLoader`
- Replace `$block->getChildHtml()` with `$childRenderer->all()` including better sorting

## [2.0.7] - 14 November 2025
### Fixed
- Upgrade Alpine CSP to 3.15.1
- Update composer keywords
- Update composer keywords
- Update composer keywords
- Update README

## [2.0.6] - 16 September 2025
### Fixed
- Rename loki-components.alpinejs to loki.alpinejs

## [2.0.5] - 28 August 2025
### Fixed
- Add CI files
- Replace yireo/opensearch with yireo/opensearch-dummy in Gitlab CI

## [2.0.4] - 26 August 2025
### Fixed
- Rename Alpine CSP template
- Load Alpine Mask under Luma properly
- Add GitLab CI files

## [2.0.3] - 21 August 2025
### Fixed
- Add `defer` back to Alpine CSP loadig
- Fix newlines after comments
- Add escaping of template code
- Remove defer from Alpine to load things faster

## [2.0.2] - 19 August 2025
### Fixed
- Lower requirements to PHP 8.1

## [2.0.1] - 07 August 2025
### Fixed
- Lower PHP requirement to PHP 8.2+

## [2.0.0] - 22 July 2025
### Fixed
- Bump `LokiCheckout_Core` to 2.0.0
- Rename PHP namespace from `Yireo_Loki*` to `Loki*`
- Rename composer package from `yireo/magento2-loki*` to `loki/magento2*`

## [1.0.5] - 08 July 2025
### Fixed
- Generate new MODULE.json with simple test count
- Allow PHP 8.4 in CI

## [1.0.4] - 28 April 2025
### Fixed
- Properly add CSP to Luma-themes

## [1.0.3] - 25 April 2025
### Fixed
- Allow upgrading to LokiFieldComponents and LokiCheckout 1.0
- Update Alpine CSP built

## [1.0.2] - 08 April 2025
### Fixed
- Housekeeping

## [1.0.1] - 22 February 2025
- Change deps
- Add proper README
- Replace TODO.md with TODO.json

## [1.0.0] - 21 January 2025
- Add proper deps
- Initial release
