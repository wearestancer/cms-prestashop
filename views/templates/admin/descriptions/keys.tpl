{*
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2023-2025 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 * @website   https://www.stancer.com
 *}

{l s='Starts with "%s"' mod='stancer' sprintf=[$prefix]}{if $is_prod},
  <em>{l s='mandatory in live mode' mod='stancer'}</em>
{/if}
