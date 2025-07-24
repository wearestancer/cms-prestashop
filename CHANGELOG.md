# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

### [Unreleased]

### Added
- Compatibility with PrestaShop 9.0 (CMS-101)


## [1.2.4] - 2024-12-11

### Fixed
- Fix a typo in `upgrade.php` (CMS-274)


## [1.2.3] - 2024-11-19

### Fixed
- Handling of customer null edge cases. (CMS-255)
- Better iframe integration. (CMS-261)


## [1.2.2] - 2024-10-03

### Fixed
- Internal scripts for version publication


## [1.2.1] - 2024-10-02

### Added
- Prestashop 8.2 support (CMS-234)
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
- Change wordings #CMS-28
- Inline HTML in the module
- Some options in admin settings are impossible to deactivate #CMS-29 #CMS-30


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
