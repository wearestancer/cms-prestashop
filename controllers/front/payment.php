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
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Front controller creating a payment.
 */
class StancerPaymentModuleFrontController extends ModuleFrontController
{
    use StancerControllerTrait;

    /**
     * Process payment
     *
     * @return void
     */
    public function postProcess()
    {
        $context = $this->context;

        // phpcs:disable PSR2.ControlStructures.ControlStructureSpacing.SpacingAfterOpenBrace
        if (
            !Validate::isLoadedObject($context->cart)
            || !$context->cart->id_address_delivery
            || !$context->cart->id_address_invoice
            || !Validate::isLoadedObject($context->currency)
            || !Validate::isLoadedObject($context->customer)
            || $this->module->isNotAvailable()
        ) {
            return $this->redirect();
        }

        $log = '';

        // Pay with an existing card
        $cardId = (int) Tools::getValue('card');
        $existingCard = null;

        if ($cardId) {
            $existingCard = StancerApiCard::getCustomerCard($context->customer, $cardId);
        }

        $errors = [];
        $log = '';

        $api = new StancerApi();
        $apiPayment = $api->sendPayment(
            $context->cart,
            $context->customer,
            $context->language,
            $context->currency,
            $existingCard,
            $errors,
            $log,
        );

        if ($log) {
            $this->errors = $errors;

            return $this->displayError($log);
        }

        if (!empty($existingCard)) {
            return $this->redirect($context->link->getModuleLink($this->module->name, 'validation', [], true));
        }

        return $this->redirect(
            $apiPayment->getPaymentPageUrl([
                'lang' => $context->language->language_code,
            ]),
        );
    }
}
