{*
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2023-2024 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 * @website   https://www.stancer.com
 * @version   1.2.2
 *}

{l s='Will be used as description for every payment made.' mod='stancer'}

<details class="help-block">
  <summary>
    {l s='You may use simple variables, click here to see the list.' mod='stancer'}
  </summary>
  <dl>
    {foreach $variables as $key => $value}
      <dt>{$key|escape:'htmlall':'UTF-8'}</dt>
      <dd>{$value|escape:'htmlall':'UTF-8'}</dd>
    {/foreach}
  </dl>
</details>
