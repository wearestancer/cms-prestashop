{*
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2018-2023 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 * @website   https://www.stancer.com
 * @version   1.0.0
 *}

{if !Configuration::get('PS_SSL_ENABLED')}
  <div class="alert alert-danger">
    <p>{l s='This module allows you to accept secure payments by card.' mod='stancer'}</p>
    <p>{l s='For security reason, you must enable SSL to use this module.' mod='stancer'}</p>
    <p>
      {l s='This can be done in general preferences available in the left menu or by following this' mod='stancer'}
      <a
        href="{Context::getContext()->link->getAdminLink('AdminPreferences')}"
        title="{l s='Open preferences to activate SSL' mod='stancer'}"
      >{l s='link' mod='stancer'}</a>.
    </p>
  </div>
{/if}
