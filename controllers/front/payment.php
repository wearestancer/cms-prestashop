<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2018-2025 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 *
 * @website https://www.stancer.com
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
     * @return null
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
            // @phpstan-ignore property.notFound
            || (method_exists($this->module, 'isNotAvailable') && $this->module->isNotAvailable())
        ) {
            // We return a void value, the return is here for lisibility
            // @phpstan-ignore method.void
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
            $log
        );

        if ($log) {
            $this->errors = $errors;

            return $this->displayError($log);
        }

        if (!empty($existingCard)) {
            // We return a void value, the return is here for lisibility
            // @phpstan-ignore method.void
            return $this->redirect($context->link->getModuleLink($this->module->name, 'validation', [], true));
        }

        // We return a void value, the return is here for lisibility
        // @phpstan-ignore method.void
        return $this->redirect(
            $apiPayment->getPaymentPageUrl(
                [
                    'lang' => $context->language->language_code,
                ],
                true
            )
        );
    }
}
