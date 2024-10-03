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
 * @version   1.2.2
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Front controller receiving the payment from the gateway.
 */
class StancerValidationModuleFrontController extends ModuleFrontController
{
    use StancerControllerTrait;

    /**
     * Clones cart
     *
     * @param Cart $cart
     */
    public function cloneCart(Cart $cart)
    {
        $newCart = $cart->duplicate();

        if (!$newCart || !$newCart['success'] || !Validate::isLoadedObject($newCart['cart'])) {
            return;
        }

        $this->context->cookie->id_cart = $newCart['cart']->id;
        $this->context->cart = $newCart['cart'];
        $this->context->smarty->assign('cart_qties', $this->context->cart->nbProducts());

        CartRule::autoAddToCart($this->context);

        $sql = implode(' ', [
            'SELECT checkout_session_data',
            'FROM `' . _DB_PREFIX_ . 'cart`',
            'WHERE id_cart = ' . (int) $cart->id,
        ]);
        $checkoutSessionData = Db::getInstance()->getValue($sql);

        $sql = implode(' ', [
            'UPDATE `' . _DB_PREFIX_ . 'cart`',
            'SET checkout_session_data = "' . pSQL($checkoutSessionData) . '"',
            'WHERE id_cart = ' . (int) $this->context->cart->id,
        ]);
        Db::getInstance()->execute($sql);

        $this->context->cookie->write();
    }

    /**
     * Create a new order
     *
     * @param Cart $cart
     * @param Stancer\Payment $apiPayment
     *
     * @return Order
     */
    protected function createOrder(Cart $cart, Stancer\Payment $apiPayment, int $orderState): Order
    {
        $this->module->validateOrder(
            $cart->id,
            $orderState,
            $apiPayment->getAmount() / 100,
            $this->module->displayName,
            $this->getOrderMessage($apiPayment),
            ['transaction_id' => $apiPayment->getId()],
            (int) $cart->id_currency,
            false,
            $cart->secure_key,
        );

        $newOrder = new Order((int) $this->module->currentOrder);
        $orderPayments = OrderPayment::getByOrderReference($newOrder->reference);

        if (!empty($orderPayments)) {
            $apiCard = $apiPayment->getCard();

            foreach ($orderPayments as $orderPayment) {
                $orderPayment->card_number = $apiCard->getLast4();
                $orderPayment->card_brand = $apiCard->getBrandName();
                $orderPayment->card_expiration = $apiCard->getExpirationDate()->format('m/Y');
                $orderPayment->card_holder = $apiCard->getName();
                $orderPayment->save();
            }
        }

        return $newOrder;
    }

    /**
     * Generate order message
     *
     * @param Stancer\Payment $apiPayment
     *
     * @return array
     */
    protected function getOrderMessage(Stancer\Payment $apiPayment): string
    {
        $amount = vsprintf('%.02f %s', [
            $apiPayment->getAmount() / 100,
            strtoupper($apiPayment->getCurrency()),
        ]);

        if (class_exists('NumberFormatter')) {
            $formatter = new NumberFormatter('en_GB', NumberFormatter::CURRENCY);
            $amount = $formatter->formatCurrency($apiPayment->getAmount() / 100, $apiPayment->getCurrency());
        }

        $message[] = 'Transaction';
        $message[] = $apiPayment->getId();
        $message[] = '';
        $message[] = sprintf('Amount: %s', $amount);

        $state = $apiPayment->isSuccess() ? 'success' : 'failure';
        $message[] = sprintf('Response: %s (%s)', $apiPayment->getResponse(), $state);

        $message[] = '';

        switch ($apiPayment->getMethod()) {
            case 'card':
                $apiCard = $apiPayment->getCard();

                $message[] = 'Card';
                $message[] = $apiCard->getId();
                $message[] = '';
                $message[] = 'Brand: ' . $apiCard->getBrandName();
                $message[] = 'Last numbers: ' . $apiCard->getLast4();

                $date = $apiCard->getExpirationDate();
                $message[] = sprintf('Expiration: %02d/%04d', $date->format('m'), $date->format('Y'));
                break;
            case 'sepa':
                $apiSepa = $apiPayment->getSepa();

                $message[] = 'Sepa';
                $message[] = $apiSepa->getId();
                $message[] = '';
                $message[] = 'Country: ' . $apiSepa->getCountry();
                $message[] = 'Last numbers: ' . $apiSepa->getLast4();
                $message[] = 'Mandate: ' . $apiSepa->getMandate();
                break;
        }

        return trim(implode("\n", $message));
    }

    /**
     * Process validation
     *
     * @return void
     */
    public function postProcess()
    {
        $context = $this->context;
        $cart = $context->cart;
        $currency = $context->currency;
        $customer = $context->customer;

        // phpcs:disable PSR2.ControlStructures.ControlStructureSpacing.SpacingAfterOpenBrace
        if (
            !Validate::isLoadedObject($cart)
            || !$cart->id_address_delivery
            || !$cart->id_address_invoice
            || !Validate::isLoadedObject($currency)
            || !Validate::isLoadedObject($customer)
            || $this->module->isNotAvailable()
        ) {
            return $this->redirect();
        }

        $payment = StancerApiPayment::find($cart, $currency);

        if (!$payment) {
            $err = StancerErrors::getMessage(StancerErrors::NO_PAYMENT);
            $this->errors[] = $err;

            return $this->displayError($err); // We must have a payment
        }

        $apiPayment = $payment->getApiObject();
        $auth = $apiPayment->getAuth();
        $apiCard = $apiPayment->getCard();
        $status = $apiPayment->getStatus();

        $api = new StancerApi();

        if (!$status && $auth) {
            if ($auth->getStatus() === Stancer\Auth\Status::SUCCESS) {
                $api->markPaymentAsCaptured($apiPayment);
                $status = $apiPayment->getStatus();
            } else {
                // We can not mark the payment failed in the API
                $status = Stancer\Payment\Status::FAILED;
            }
        }

        if ($apiCard) {
            $payment->card_id = $apiCard->id;
        }

        $payment->status = $status;
        $payment->save();

        if ($status === Stancer\Payment\Status::AUTHORIZED) {
            $api->markPaymentAsCaptured($apiPayment);
            $status = $apiPayment->getStatus();
        }

        switch ($status) {
            case Stancer\Payment\Status::FAILED:
            case Stancer\Payment\Status::REFUSED:
                $err = StancerErrors::getMessage(StancerErrors::PAYMENT_FAILED);
                $this->errors[] = $err;

                if (Configuration::get('STANCER_ORDER_FOR_NOK_PAYMENTS')) {
                    $this->createOrder($cart, $apiPayment, $payment->getOrderState());
                    $this->cloneCart($cart);
                }

                return $this->displayError($err);
            case Stancer\Payment\Status::AUTHORIZED:
            case Stancer\Payment\Status::TO_CAPTURE:
            case Stancer\Payment\Status::CAPTURE:
                // @todo : remove check of property when property deleted will be added
                $deleted = property_exists($apiCard, 'deleted') && $apiCard->deleted ?? false;

                if ($deleted) {
                    StancerApiCard::deleteFrom($apiCard);
                } else {
                    StancerApiCard::saveFrom($apiCard, $customer);
                }

                $newOrder = $this->createOrder($cart, $apiPayment, $payment->getOrderState());

                $payment->id_order = $newOrder->id;
                $payment->save();

                $url = $context->link->getPageLink(
                    'order-confirmation',
                    true,
                    null,
                    [
                        'id_cart' => (int) $cart->id,
                        'id_module' => (int) $this->module->id,
                        'id_order' => (int) $newOrder->id,
                        'key' => $customer->secure_key,
                    ]
                );

                return Tools::redirect($url);
        }

        return $this->redirect($apiPayment->getPaymentPageUrl([
            'lang' => $this->context->language->language_code,
        ]));
    }
}
