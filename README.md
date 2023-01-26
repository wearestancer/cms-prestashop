# Stancer Prestashop module

This official module allows you to accept credit card payments via the Stancer platform directly on Prestashop.

## Requirements
| PrestaShop version | PHP Version      |
|--------------------|------------------|
| 1.7.x - 8.0.1      | 7.1 or greater |

### Supported payment method
The module allows you to make payments by credit card.
Payments are 3D Secure compatible. The amount from which 3D Secure is triggered can be configured from the module in the Prestashop back office.
Customers have the option of registering a credit card when paying for the order so that they can quickly pay for their next purchases.

## Documentation
- [Module Prestashop](https://gitlab.com/wearestancer/cms/prestashop/-/wikis/home)
- [Stancer API](https://www.stancer.com/documentation/fr/api/)
- [Stancer Resource](https://www.stancer.com/documentation/fr/resources/)

## Generate API keys
In order to configure the Prestashop module, it's necessary to obtain information which you can find in your Stancer account via the link <a href="https://manage.stancer.com" target="_blank">https://manage.stancer.com</a>

Once logged in, go to the Developers tab
When creating your account, a private and public key is automatically generated for test mode.

## API Library
This module is using the Stancer API Library for PHP.
<a href="https://github.com/wearestancer/lib-php" target="_blank">This library can be found here</a>

## License
MIT license. For more information, see the LICENSE file.
