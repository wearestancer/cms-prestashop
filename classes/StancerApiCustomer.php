<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2018-2023 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 *
 * @website   https://www.stancer.com
 *
 * @version   1.1.0
 */

/**
 * Model for a customer.
 */
class StancerApiCustomer extends ObjectModel
{
    /** @var int Customer id */
    public $id_customer;

    /** @var string Customer id */
    public $customer_id;

    /** @var string Customer name */
    public $name;

    /** @var string Customer email */
    public $email;

    /** @var string Customer mobile */
    public $mobile;

    /** @var bool Customer is deleted */
    public $deleted = false;

    /** @var string Customer creation date in Stancer Api */
    public $created;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    protected $api;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'stancer_customer',
        'primary' => 'id_stancer_customer',
        'fields' => [
            'id_customer' => [
                'required' => true,
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'customer_id' => [
                'required' => true,
                'size' => 29,
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'name' => [
                'size' => 64,
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'email' => [
                'size' => 64,
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'mobile' => [
                'size' => 16,
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
            ],
            'deleted' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
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
     * Find Stancer customer by Prestashop customer
     *
     * @param Customer $customer
     *
     * @return StancerApiCustomer
     */
    public static function find(Customer $customer): StancerApiCustomer
    {
        $sql = 'SELECT
                      `api`.*
                    , IFNULL(`api`.`name`, CONCAT(`ps`.`firstname`, " ", `ps`.`lastname`)) AS `name`
                    , IFNULL(`api`.`email`, `ps`.`email`) AS `email`
                    , `id_customer`
                FROM `' . bqSQL(_DB_PREFIX_ . 'customer') . '` AS `ps`
                LEFT JOIN `' . bqSQL(_DB_PREFIX_ . static::$definition['table']) . '` AS `api`
                USING (`id_customer`)
                WHERE TRUE
                AND `id_customer` = ' . ((int) $customer->id);

        $obj = new static();
        $obj->hydrate((array) Db::getInstance()->getRow($sql));

        // Prevent from using 29 "\0" as an ID
        if (!trim($obj->customer_id)) {
            $obj->customer_id = null;
        }

        return $obj;
    }

    /**
     * Get Stancer API customer object
     *
     * @return Stancer\Customer
     */
    public function getApiObject(): Stancer\Customer
    {
        if ($this->api) {
            return $this->api;
        }

        $this->api = new Stancer\Customer($this->customer_id);

        $params = [];

        if ($this->email && $this->email !== $this->api->getEmail()) {
            $params['email'] = $this->email;
        }

        if ($this->name && $this->name !== $this->api->getName()) {
            $params['name'] = $this->name;
        }

        if ($this->mobile && $this->mobile !== $this->api->getMobile()) {
            $params['mobile'] = $this->mobile;
        }

        if ($this->id_customer && $this->id_customer !== $this->api->getExternalId()) {
            $params['external_id'] = (string) $this->id_customer;
        }

        if ($params) {
            $this->api->hydrate($params);

            if ($this->api->getId()) {
                $this->api->send();
            }
        }

        return $this->api;
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
    public function save($null_values = false, $auto_date = true)
    {
        if ($this->api) {
            $this->customer_id = $this->api->getId();
            $this->name = $this->api->getName();
            $this->email = $this->api->getEmail();
            $this->mobile = $this->api->getMobile();
        }

        return parent::save($null_values, $auto_date);
    }

    /**
     * Create or update an Stancer customer from Stancer API customer object
     *
     * @param Stancer\Customer $apiCustomer
     *
     * @return StancerApiCustomer
     */
    public static function saveFrom(Stancer\Customer $apiCustomer): StancerApiCustomer
    {
        $query = new DbQuery();
        $query->select('id_stancer_customer');
        $query->from(static::$definition['table']);
        $query->where('`id_customer` = ' . (int) $apiCustomer->getExternalId());

        $existingCustomerId = Db::getInstance()->getValue($query);
        if ($existingCustomerId) {
            $customer = new static($existingCustomerId);
        } else {
            $customer = new static();
        }

        $customer->id_customer = $apiCustomer->getExternalId();
        $customer->customer_id = $apiCustomer->getId();
        $customer->name = $apiCustomer->getName();
        $customer->email = $apiCustomer->getEmail();
        $customer->mobile = $apiCustomer->getMobile();

        $creation = $apiCustomer->getCreationDate();

        if ($creation) {
            $customer->created = $creation->format('Y-m-d H:i:s');
        }

        $customer->api = $apiCustomer;
        $customer->save();

        return $customer;
    }
}
