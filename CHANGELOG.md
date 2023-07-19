# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

### Fixed
- Change wordings #CMS-28


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
