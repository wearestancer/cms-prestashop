# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Payment reconciliation cron job (`controllers/front/cron.php`). Stancer does
  not support webhooks, so payments whose redirect never fires (browser closed,
  network error) stay permanently "pending" locally — no order is ever created
  even though the payment was captured. A new front controller, called every
  15 minutes, polls the Stancer API for such payments and creates the
  PrestaShop order when the status is `to_capture` or `captured`. Refused,
  failed, canceled and expired payments are logged as warnings.
  Cron URL: `https://example.com/module/stancer/cron?token=STANCER_CRON_TOKEN`
  Example crontab: `*/15 * * * * curl -s "https://…/module/stancer/cron?token=TOKEN"`
- `STANCER_CRON_TOKEN` configuration key, generated on module install via
  `random_bytes()`, used to authenticate requests to the cron endpoint.
- `hookActionCronJob()` in the main module class, for shops using the
  PrestaShop CronJobs module (ps_cronjobs) as an alternative to a server cron.


## [2.0.2] - 2025-12-11

### Added
- Italian translation for our module (CMS-442)

### Fixed
- Conflict with Stancer external libraries and PrestaShop external libraries (CMS-110)
- Stancer module being available for non supported amounts (CMS-410)


## [2.0.1] - 2025-09-18

### Fixed
- Multilingual form fields were not visible

## [2.0.0] - 2025-09-17

### Added
- Compatibility with PrestaShop 9.0 (CMS-101)

### Fixed
- Check test mode in UI to reflect the database state on module install (CMS-53)
- Fix smarty module unknown at install (CMS-275)


## [1.7.0] - 2025-09-12

### Changed
- Add compatibility with PrestaShop 1.7.1 and after (CMS-182)


## [1.2.4] - 2024-12-11

### Fixed
- Fix a typo in `upgrade.php` (CMS-274)


## [1.2.3] - 2024-11-19

### Fixed
- Handling of customer null edge cases (CMS-255)
- Better iframe integration (CMS-261)


## [1.2.2] - 2024-10-03

### Fixed
- Internal scripts for version publication


## [1.2.1] - 2024-10-02

### Added
- PrestaShop 8.2 support (CMS-234)
- Devcontainer to ease development process (CMS-81)
- Automatically create an archive for every merge request

### Changed
- Make 1.7.8 the minimum required PrestaShop version (CMS-94)
- New "GIE CB" logo (CMS-172)

### Fixed
- Failing a payment sometimes didn't allow you to retry paying for the same cart (CMS-27)
- Iframe secured with sandbox attributes (CMS-66)


## [1.2.0] - 2023-07-20

### Added
- PrestaShop 8.1 support
- PrestaShop Marketplace module key
- `.htaccess` file (to please PrestaShop validation team :) )
- `live_mode` in inner tables

### Fixed
- Admin settings may not be modifiable
- Change wordings (CMS-28)
- Inline HTML in the module
- Some options in admin settings are impossible to deactivate (CMS-29, CMS-30)


## [1.1.0] - 2023-04-19

### Added
- Admin form can be hidded
- Failed payments may not create an order [#1](https://gitlab.com/wearestancer/cms/prestashop/-/issues/1)
- `full-iframe` page type [#2](https://gitlab.com/wearestancer/cms/prestashop/-/issues/2) (not configurable for the moment)
- Module and PrestaShop versions added to API requests
- New devcontainer configuration, with PrestaShop included
- Payment option can be configured in admin

### Changed
- API keys wording
- Refact mode switch
- No default timeout
- Version requirements

### Fixed
- CI
- Error handling on keys and mode activation
- Licensing


## [1.0.0] - 2023-01-26
- First version
