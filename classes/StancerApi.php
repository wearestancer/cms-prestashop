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
 * API helper.
 */
class StancerApi
{
    /** @var StancerApiConfig Stancer API configuration */
    public $apiConfig;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->apiConfig = new StancerApiConfig();
    }

    /**
     * Prepare payment data for send a payment to Stancer
     *
     * @param Cart $cart
     * @param Language $language
     * @param Currency $currency
     *
     * @return array<string, mixed>
     *
     * @phpstan-return PaymentData
     */
    public function buildPaymentData(
        Cart $cart,
        Language $language,
        Currency $currency
    ): array {
        $total = $cart->getOrderTotal(true, Cart::BOTH);
        $amount = (int) (string) ($total * 100);
        $authLimit = $this->apiConfig->authLimit;
        $auth = is_null($authLimit) || $authLimit === '' ? false : $total > $authLimit;
        $currencyCode = strtoupper($currency->iso_code);

        $message = Configuration::get('STANCER_PAYMENT_DESCRIPTION', $language->id);
        $params = [
            'SHOP_NAME' => Configuration::get('PS_SHOP_NAME', $language->id),
            'CART_ID' => (string) $cart->id,
            'TOTAL_AMOUNT' => (string) sprintf('%.02f', $total),
            'CURRENCY' => (string) $currencyCode,
        ];
        $description = $message ? str_replace(array_keys($params), $params, $message) : null;

        $uniqueId = null;

        if (Configuration::get('STANCER_FROM_MARKETPLACE')) {
            $now = new DateTime();
            $now->setTimezone(new DateTimeZone('UTC'));

            $uniqueId = implode('-', [
                'PS',
                'MP',
                $now->format('U'),
                str_pad((string) $cart->id, 6, '0', STR_PAD_LEFT),
            ]);
        }

        $paymentData = [
            'amount' => $amount,
            'auth' => $auth,
            'currency' => strtolower($currency->iso_code),
            'description' => $description,
            'orderId' => (string) $cart->id,
            'uniqueId' => $uniqueId,
        ];

        if ('iframe' !== Configuration::get('STANCER_PAGE_TYPE')) {
            $paymentData['returnUrl'] = Context::getContext()->link->getModuleLink('stancer', 'validation', [], true);
        }

        return $paymentData;
    }

    /**
     * Mark a Stancer payment as captured
     *
     * @param Stancer\Payment $apiPayment
     *
     * @return void
     */
    public function markPaymentAsCaptured(Stancer\Payment $apiPayment)
    {
        if (!$apiPayment->status || $apiPayment->status === Stancer\Payment\Status::AUTHORIZED) {
            $apiPayment->setStatus(Stancer\Payment\Status::CAPTURE);
            $this->sendToApi($apiPayment);
        }
    }

    /**
     * Pay with existing card
     *
     * @param Stancer\Payment $apiPayment
     *
     * @return array<mixed>
     */
    public function sendToApi(Stancer\Payment $apiPayment): array
    {
        $errors = [];
        $exception = null;
        $log = '';

        try {
            $apiPayment->send();
        } catch (Stancer\Exceptions\NotAuthorizedException $exception) {
            $errors[] = StancerErrors::getMessage(StancerErrors::NOT_AUTHORIZED);
            $log = $exception->getMessage();
        } catch (Stancer\Exceptions\ServerException $exception) {
            $errors[] = StancerErrors::getMessage(StancerErrors::SERVER_ERROR);
            $log = $exception->getMessage();
        } catch (Stancer\Exceptions\ClientException $exception) {
            $errors[] = StancerErrors::getMessage(StancerErrors::CLIENT_ERROR);
            $log = $exception->getMessage();
        } catch (Exception $exception) {
            $errors[] = StancerErrors::getMessage(StancerErrors::UNKNOWN_ERROR);
            $log = $exception->getMessage();
        }

        if ($exception && _PS_MODE_DEV_) {
            throw $exception;
        }

        return [
            'log' => $log,
            'errors' => $errors,
        ];
    }

    /**
     * Send payment to Stancer Api
     *
     * @param Cart $cart
     * @param Customer $customer
     * @param Language $language
     * @param Currency $currency
     * @param StancerApiCard|null $card
     * @param string[] $errors called by reference
     * @param string|null $log called by reference
     *
     * @return Stancer\Payment
     */
    public function sendPayment(
        Cart $cart,
        Customer $customer,
        Language $language,
        Currency $currency,
        $card = null,
        array &$errors = [],
        &$log = null
    ): Stancer\Payment {
        $paymentData = $this->buildPaymentData($cart, $language, $currency);
        $psApiCustomer = StancerApiCustomer::find($customer);

        $apiCustomer = $psApiCustomer->getApiObject();
        $currentPayment = StancerApiPayment::find($cart, $currency);

        $apiPayment = null;
        if ($currentPayment) {
            $apiPayment = $currentPayment->getApiObject();
        }

        if (!$apiPayment || $apiPayment->getStatus() === 'refused') {
            $apiPayment = new Stancer\Payment();
            $apiPayment
                ->setCustomer($apiCustomer)
                ->setOrderId($paymentData['orderId'])
                ->setCapture(false)
                ->setMethodsAllowed(['card']);
            if (isset($paymentData['returnUrl'])) {
                $apiPayment->setReturnUrl($paymentData['returnUrl']);
            }
        }

        if ($paymentData['auth'] && empty($apiPayment->getAuth())) {
            $apiPayment->setAuth(true);
        }

        if ($apiPayment->getAmount() != $paymentData['amount']) {
            $apiPayment->setAmount($paymentData['amount']);
        }

        if ($apiPayment->getCurrency() != $paymentData['currency']) {
            $apiPayment->setCurrency($paymentData['currency']);
        }

        if ($apiPayment->getDescription() != $paymentData['description']) {
            $apiPayment->setDescription($paymentData['description']);
        }

        if ($paymentData['uniqueId']) {
            $apiPayment->setUniqueId($paymentData['uniqueId']);
        }

        // Reuse an existing card
        if (!empty($card)) {
            $apiPayment->setCard($card->getApiObject());
        }

        // Send payment to Stancer
        if ($apiPayment->isModified()) {
            $result = $this->sendToApi($apiPayment);
            $log = $result['log'];
            $errors = $result['errors'];
        }

        if (empty($log)) {
            $apiCustomer = $apiPayment->getCustomer();

            if ($apiCustomer) {
                StancerApiCustomer::saveFrom($apiCustomer);
            }

            // Save payment in Prestashop
            StancerApiPayment::saveFrom($apiPayment, $cart);
        }

        return $apiPayment;
    }
}
