{*
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2018-2023 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 * @website   https://www.stancer.com
 * @version   1.0.0
 *}

<iframe
  class="stancer-payment__iframe js-stancer-payment-iframe"
  data-inner-3ds="{$3ds|default:false}"
  data-target="{$target}"
  data-validation="{$validation}"
></iframe>

<p class="js-stancer-confirm-terms">
  {l s='Please confirm terms and conditions before pursuing.' mod='stancer'}
</p>
