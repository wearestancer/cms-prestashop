{*
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2023-2024 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 * @website   https://www.stancer.com
 * @version   1.2.3
 *}

{l s='Starts with "%s"' mod='stancer' sprintf=[$prefix]}{if $is_prod},
  <em>{l s='mandatory in live mode' mod='stancer'}</em>
{/if}
