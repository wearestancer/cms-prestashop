# Stancer PrestaShop module

This official module allows you to accept credit card payments via the Stancer platform directly on PrestaShop.

## Requirements

### Minimal versions

| PrestaShop version | PHP Version      |
|--------------------|------------------|
| 1.7.1 - 1.7.9      | 7.1 or greater   |

### API keys

In order to configure the PrestaShop module, you need Stancer API keys.
You can find your keys in the <q>Developers</q> tab on your [Stancer account](https://manage.stancer.com).

When creating your account, a private and public key is automatically generated for test mode.
Live mode keys will be created after account validation.

## Supported payment method

The module allows you to make payments by credit card.

Payments are 3D Secure compatible.
The amount from which 3D Secure is triggered can be configured from the module in the PrestaShop back office.

Customers have the option of registering a credit card when paying for the order so that they
can quickly pay for their next purchases.

## Documentation

- [PrestaShop module](https://gitlab.com/wearestancer/cms/prestashop/-/wikis/home) (french)
- [Stancer API](https://www.stancer.com/documentation/api)
- Stancer Resource:
  [english](https://www.stancer.com/documentation/resources/) /
  [french](https://www.stancer.com/documentation/fr/resources/)

## API Library

This module is using the [Stancer API Library for PHP](https://gitlab.com/wearestancer/library/lib-php).

## License

MIT license.

For more information, see the LICENSE file.
