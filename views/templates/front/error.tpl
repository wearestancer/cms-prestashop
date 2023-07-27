{*
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2018-2023 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 * @website   https://www.stancer.com
 * @version   1.0.0
 *}

{extends file='page.tpl'}

{block name='page_content_container'}
  <div>
    <p>
      {l s='This error should be temporary, please try again later.' mod='stancer'}
    </p>
    <p>
      {l s='If this message persists, please contact the store to resolve this issue as soon as possible.' mod='stancer'}
    </p>
    <p class="lnk">
      <a class="alert-link" href="{$back|escape:'htmlall':'UTF-8'}" title="{l s='Back' mod='stancer'}">
        &laquo; {l s='Back' mod='stancer'}
      </a>
    </p>
  </div>
{/block}
