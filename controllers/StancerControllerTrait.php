<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2018-2024 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 *
 * @website   https://www.stancer.com
 *
 * @version   1.2.1
 */

/**
 * Controller helper.
 */
trait StancerControllerTrait
{
    /**
     * Display error and add log for Prestashop
     *
     * @param string $message
     * @param int $logLevel
     *
     * @return void
     */
    public function displayError(string $message, int $logLevel = 4)
    {
        $cart = $this->context->cart;

        PrestaShopLogger::addLog(
            'Stancer : ' . $message,
            $logLevel,
            null,
            'Cart',
            $cart->id,
            true
        );

        $this->context->smarty->assign('stancerErrors', $this->errors);
        $this->context->smarty->assign('back', $this->getRedirectLink());

        $this->setTemplate('module:' . $this->module->name . '/views/templates/front/error.tpl');

        return parent::postProcess();
    }

    /**
     * Get redirect link
     *
     * @return void
     */
    public function getRedirectLink()
    {
        return $this->context->link->getPageLink(
            'order',
            true,
            null,
            [
                'step' => (Tools::getValue('last-step') ?: 1),
            ]
        );
    }

    /**
     * Redirect an url or default redirection (last step of payment)
     *
     * @param mixed $url
     *
     * @return void
     */
    public function redirect($url = null)
    {
        Tools::redirect($url ?: $this->getRedirectLink());

        exit;
    }
}
