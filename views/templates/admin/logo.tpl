{*
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2023-2024 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 * @website   https://www.stancer.com
 * @version   1.2.2
 *}

<div class="stancer-logo">
  {$choices = []}
  {$choices[] = 'none'}
  {$choices[] = 'stancer-logo'}
  {$choices[] = 'stancer'}
  {$choices[] = 'all-schemes'}
  {$choices[] = 'all-schemes-prefixed'}
  {$choices[] = 'all-schemes-suffixed'}
  {$choices[] = 'visa-mc'}
  {$choices[] = 'visa-mc-prefixed'}
  {$choices[] = 'visa-mc-suffixed'}

  <div class="stancer-logo__grid">
    {foreach $choices as $choice}
      <input
        class="stancer-logo__input"
        id="stancer-logo-{$choice}"
        type="radio"
        name="STANCER_CTA_LOGO"
        value="{$choice}"
        {if $STANCER_CTA_LOGO_VALUE == $choice}
          checked
        {/if}
      />
      <label class="stancer-logo__item stancer-logo__item--{$choice}" for="stancer-logo-{$choice}">
        {if $choice != 'none'}
          <img
            class="stancer-logo__preview stancer-logo__preview--{$choice}"
            src="{$stancer_module_img|cat:'/logo.svg#'|cat:$choice|escape:'htmlall':'UTF-8'}"
          />
        {/if}
        <span class="stancer-logo__text">
          {if $choice == 'none'}
            {l s='No displayed logo' mod='stancer'}
          {elseif $choice == 'stancer-logo'}
            {l s='Stancer logo without text' mod='stancer'}
          {elseif $choice == 'stancer'}
            {l s='Stancer logo with text' mod='stancer'}
          {elseif $choice == 'all-schemes'}
            {l s='Supported schemes' mod='stancer'}
          {elseif $choice == 'all-schemes-prefixed'}
            {l s='Supported schemes' mod='stancer'}
            <br />
            {l s='Prefixed with the Stancer logo' mod='stancer'}
          {elseif $choice == 'all-schemes-suffixed'}
            {l s='Supported schemes' mod='stancer'}
            <br />
            {l s='Suffixed by the Stancer logo' mod='stancer'}
          {elseif $choice == 'visa-mc'}
            {l s='Visa and Mastercard logos' mod='stancer'}
          {elseif $choice == 'visa-mc-prefixed'}
            {l s='Visa and Mastercard logos' mod='stancer'}
            <br />
            {l s='Prefixed with the Stancer logo' mod='stancer'}
          {elseif $choice == 'visa-mc-suffixed'}
            {l s='Visa and Mastercard logos' mod='stancer'}
            <br />
            {l s='Suffixed by the Stancer logo' mod='stancer'}
          {/if}
        </span>
      </label>
    {/foreach}
  </div>
</div>
