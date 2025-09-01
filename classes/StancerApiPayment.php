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
 * Model for a payment.
 */
class StancerApiPayment extends ObjectModel
{
    /**
     * @var string Payment id
     */
    public $payment_id;

    /**
     * @var string Customer id
     */
    public $customer_id;

    /**
     * @var string Card id
     */
    public $card_id;

    /**
     * @var int Cart id
     */
    public $id_cart;

    /**
     * @var int Order id
     */
    public $id_order;

    /**
     * @var bool Is a live mode object?
     */
    public $live_mode;

    /**
     * @var string Currency
     */
    public $currency;

    /**
     * @var string Payment amount
     */
    public $amount;

    /**
     * @var string Payment status
     */
    public $status = 'pending';

    /**
     * @var string Payment creation date in Stancer Api
     */
    public $created;

    /**
     * @var string Object creation date
     */
    public $date_add;

    /**
     * @var string Object last modification date
     */
    public $date_upd;

    protected $api;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'stancer_payment',
        'primary' => 'id_stancer_payment',
        'fields' => [
            'payment_id' => [
                'required' => true,
                'size' => 29,
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'customer_id' => [
                'size' => 29,
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'card_id' => [
                'size' => 29,
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'id_cart' => [
                'required' => true,
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'id_order' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'live_mode' => [
                'required' => true,
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'currency' => [
                'required' => true,
                'size' => 3,
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'amount' => [
                'required' => true,
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'status' => [
                'size' => 10,
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'created' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];

    /**
     * Retrieves Stancer Prestashop payment depends on Stancer API payment object
     *
     * @param mixed $apiPayment
     *
     * @return StancerApiPayment
     */
    public static function findByApiPayment(Stancer\Payment $apiPayment): ?StancerApiPayment
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from(static::$definition['table']);
        $query->where('`payment_id` = "' . pSQL($apiPayment->id) . '"');

        $row = Db::getInstance()->getRow($query);
        if (!$row) {
            return null;
        }

        $payment = new static();
        $payment->hydrate((array) $row);

        return static::ensureData($payment);
    }

    /**
     * Ensure object data with some cleanup
     *
     * @param StancerApiPayment $payment
     *
     * @return StancerApiPayment
     */
    public static function ensureData(StancerApiPayment $payment): StancerApiPayment
    {
        // Prevent from using 29 "\0" as an ID
        if (!trim($payment->card_id)) {
            $payment->card_id = null;
        }

        if (!trim($payment->customer_id)) {
            $payment->customer_id = null;
        }

        return $payment;
    }

    /**
     * Retrieves pending payment depends on cart and currency
     *
     * @param Cart $cart
     * @param Currency $currency
     *
     * @return StancerApiPayment
     */
    public static function find(Cart $cart, Currency $currency): ?StancerApiPayment
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from(static::$definition['table']);
        $query->where('`id_cart` = ' . (int) $cart->id);
        $query->where('`currency` = "' . pSQL($currency->iso_code) . '"');
        $query->where('`status` = "pending"');

        $row = Db::getInstance()->getRow($query);
        if (!$row) {
            return null;
        }

        $payment = new static();
        $payment->hydrate((array) $row);

        return static::ensureData($payment);
    }

    /**
     * Get Stancer API payment object
     *
     * @return Stancer\Payment
     */
    public function getApiObject(): Stancer\Payment
    {
        if (!$this->api) {
            $this->api = new Stancer\Payment($this->payment_id);
        }

        return $this->api;
    }

    /**
     * Get order state prestashop from stancer payment status
     *
     * @return int
     */
    public function getOrderState(): int
    {
        $statuses = [
            Stancer\Payment\Status::AUTHORIZED => 'PS_CHECKOUT_STATE_AUTHORIZED',
            Stancer\Payment\Status::CANCELED => 'PS_OS_CANCELED',
            Stancer\Payment\Status::CAPTURED => 'PS_OS_PAYMENT',
            Stancer\Payment\Status::DISPUTED => 'PS_OS_DISPUTED',
            Stancer\Payment\Status::EXPIRED => 'PS_OS_EXPIRED',
            Stancer\Payment\Status::FAILED => 'PS_OS_ERROR',
            Stancer\Payment\Status::TO_CAPTURE => 'PS_OS_PAYMENT',
        ];

        $key = 'PS_OS_ERROR';

        if (array_key_exists($this->api->getStatus(), $statuses)) {
            $key = $statuses[$this->api->getStatus()];
        }

        if ($key === Stancer\Payment\Status::CAPTURED && count($this->api->getRefunds())) {
            if ($this->api->getRefundableAmount()) {
                $key = 'PS_OS_PARTIAL_REFUND';
            } else {
                $key = 'PS_OS_REFUND';
            }
        }

        return Configuration::get($key);
    }

    /**
     * Save Stancer API payment
     *
     * @param bool $null_values
     * @param bool $auto_date
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function save($null_values = false, $auto_date = true): bool
    {
        $config = new StancerApiConfig();

        $this->live_mode = $config->isLiveMode();

        if ($this->api) {
            $this->payment_id = $this->api->id;

            if ($this->api->card) {
                $this->card_id = $this->api->card->id;
            }

            if ($this->api->customer) {
                $this->customer_id = $this->api->customer->id;
            }

            $status = $this->api->status;

            if (!$status && $this->api->auth) {
                if (in_array($this->api->auth->status, ['declined', 'expired', 'failed', 'unavailable'], true)) {
                    // We can not mark the payment failed in the API
                    $status = Stancer\Payment\Status::FAILED;
                }
            }

            $this->status = $status ?? 'pending';
        }

        return parent::save($null_values, $auto_date);
    }

    /**
     * Create or update an Stancer payment from Stancer API payment object
     *
     * @param Stancer\Payment $payment
     * @param Cart $cart
     *
     * @return StancerApiPayment
     */
    public static function saveFrom(Stancer\Payment $apiPayment, Cart $cart): StancerApiPayment
    {
        $payment = static::findByApiPayment($apiPayment);

        if (!$payment) {
            $payment = new static();
        }

        $card = $apiPayment->card;
        $creation = $apiPayment->creationDate;
        $customer = $apiPayment->customer;

        $payment->amount = $apiPayment->amount;
        $payment->api = $apiPayment;
        $payment->card_id = $card ? $card->id : null;
        $payment->created = $creation ? $creation->format('Y-m-d H:i:s') : null;
        $payment->currency = $apiPayment->currency;
        $payment->customer_id = $customer ? $customer->id : null;
        $payment->id_cart = $cart->id;
        $payment->payment_id = $apiPayment->id;

        $payment->save();

        return $payment;
    }
}
