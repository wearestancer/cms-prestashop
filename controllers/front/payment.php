<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2018-2025 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 *
 * @website   https://www.stancer.com
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
     * @var Stancer
     */
    public $module;

    /**
     * Process payment
     *
     * @return void
     */
    public function postProcess()
    {
        $context = $this->context;

        // phpcs:disable PSR2.ControlStructures.ControlStructureSpacing.SpacingAfterOpenBrace
        if (!Validate::isLoadedObject($context->cart)
            || !$context->cart->id_address_delivery
            || !$context->cart->id_address_invoice
            || !Validate::isLoadedObject($context->currency)
            || !Validate::isLoadedObject($context->customer)
            || $this->module->isNotAvailable()
        ) {
            // Redirect always terminate. No return needed.
            $this->redirect();
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

            $this->displayError($log);

            return;
        }

        // Redirect always terminate. No return needed.
        if (!empty($existingCard)) {
            $this->redirect(
                $context->link->getModuleLink(
                    $this->module->name,
                    'validation',
                    [],
                    true
                )
            );
        }

        // Redirect always terminate. No return needed.
        $this->redirect(
            $apiPayment->getPaymentPageUrl([
                'lang' => $context->language->language_code,
            ], true),
        );
    }
}
