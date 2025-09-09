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
 * Model for a card.
 */
class StancerApiCard extends ObjectModel
{
    /** @var int Customer id */
    public $id_customer;

    /** @var string Card id */
    public $card_id;

    /** @var bool Is a live mode object? */
    public $live_mode;

    /** @var string Card last4 */
    public $last4;

    /** @var string Card expiration date */
    public $expiration;

    /** @var string Card brand */
    public $brand;

    /** @var string Card brand name */
    public $brandname;

    /** @var string Card name */
    public $name;

    /** @var string Card creation date in Stancer Api */
    public $created;

    /** @var string Last date of use */
    public $last_used;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /** @var Stancer\Card|null The api object. */
    protected ?Stancer\Card $api = null;

    /**
     * @see ObjectModel::$definition
     *
     * @var array<string, string|mixed[]>
     */
    public static $definition = [
        'table' => 'stancer_card',
        'primary' => 'id_stancer_card',
        'fields' => [
            'id_customer' => [
                'required' => true,
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'card_id' => [
                'required' => true,
                'size' => 29,
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'live_mode' => [
                'required' => true,
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'last4' => [
                'required' => true,
                'size' => 4,
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'expiration' => [
                'required' => true,
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'brand' => [
                'size' => 10,
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'brandname' => [
                'size' => 20,
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'name' => [
                'size' => 64,
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'created' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'last_used' => [
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
     * Delete card
     *
     * @param Stancer\Card $card
     *
     * @return bool
     */
    public static function deleteFrom(Stancer\Card $card): bool
    {
        $card = static::findByApiCard($card);
        if ($card) {
            return $card->delete();
        }

        return false;
    }

    /**
     * Retrieves card depends on Stancer API card
     *
     * @param Stancer\Card $card
     *
     * @return StancerApiCard|null
     */
    public static function findByApiCard(Stancer\Card $card): ?StancerApiCard
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from(static::$definition['table']);
        $query->where('`card_id` = "' . pSQL($card->id) . '"');

        $row = Db::getInstance()->getRow($query);
        if (!$row) {
            return null;
        }
        // @phpstan-ignore new.static
        $card = new static();
        $card->hydrate((array) $row);

        return $card;
    }

    /**
     * Retrieve a card
     *
     * @param Customer $customer
     * @param int $id
     *
     * @return StancerApiCard|null
     */
    public static function getCustomerCard(Customer $customer, int $id): ?StancerApiCard
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from(static::$definition['table']);
        $query->where('`id_customer` = ' . (int) $customer->id);
        $query->where('`expiration` > CURDATE()');
        $query->where('`' . bqSQL(static::$definition['primary']) . '` = ' . (int) $id);

        $row = Db::getInstance()->getRow($query);
        if (!$row) {
            return null;
        }

        // @phpstan-ignore new.static
        $card = new static();
        $card->hydrate($row);

        return $card;
    }

    /**
     * Get Stancer API card object
     *
     * @return Stancer\Card
     */
    public function getApiObject(): Stancer\Card
    {
        if (!$this->api) {
            $this->api = new Stancer\Card($this->card_id);
        }

        return $this->api;
    }

    /**
     * Retrieves all valid card of customer
     *
     * @param Customer $customer
     *
     * @return StancerApiCard[]
     */
    public static function getCustomerCards(Customer $customer): array
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from(static::$definition['table']);
        $query->where('`id_customer` = ' . (int) $customer->id);
        $query->where('`expiration` > CURDATE()');
        $query->orderBy('`last_used` DESC');

        $list = Db::getInstance()->executeS($query);

        return ObjectModel::hydrateCollection('StancerApiCard', $list);
    }

    /**
     * Save object
     *
     * @param mixed $null_values
     * @param mixed $auto_date
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
            $this->card_id = $this->api->getId();
            $this->brand = $this->api->getBrand();
        }

        return parent::save($null_values, $auto_date);
    }

    /**
     * Create or update an Stancer card from Stancer API card object
     *
     * @param Stancer\Card $apiCard
     * @param Customer $customer
     *
     * @return StancerApiCard
     */
    public static function saveFrom(Stancer\Card $apiCard, Customer $customer): StancerApiCard
    {
        $card = static::findByApiCard($apiCard);

        if (!$card) {
            // @phpstan-ignore new.static
            $card = new static();
            $card->id_customer = $customer->id;
            $card->card_id = $apiCard->getId();
            $card->last4 = $apiCard->getLast4();
            $card->brand = $apiCard->getBrand();
            $card->brandname = $apiCard->getBrandName();
            $card->name = $apiCard->getName();

            $creation = $apiCard->getCreationDate();
            if ($creation) {
                $card->created = $creation->format('Y-m-d H:i:s');
            }

            $card->expiration = $apiCard->getExpirationDate()->format('Y-m-d');
        }

        $card->last_used = date('Y-m-d H:i:s');
        $card->save();

        $card->api = $apiCard;

        return $card;
    }
}
