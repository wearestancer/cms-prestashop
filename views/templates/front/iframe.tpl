{*
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2018-2025 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 * @website   https://www.stancer.com
 *}

<iframe
  class="stancer-payment__iframe js-stancer-payment-iframe"
  allow="payment"
  sandbox="allow-forms allow-scripts allow-same-origin allow-top-navigation"
  data-inner-3ds="{$3ds|default:false|escape:'htmlall':'UTF-8'}"
  data-target="{$target|escape:'htmlall':'UTF-8'}"
  data-validation="{$validation|escape:'htmlall':'UTF-8'}"
></iframe>

<p class="js-stancer-confirm-terms">
  {l s='Please confirm terms and conditions before pursuing.' mod='stancer'}
</p>
