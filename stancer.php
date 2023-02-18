<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2023 Iliad 78
 * @license   https://opensource.org/licenses/MIT
 * @website   https://www.stancer.com
 * @version   1.0.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_ROOT_DIR_ . '/modules/stancer/vendor/autoload.php';

class Stancer extends PaymentModule
{
    protected $configurations = [];
    protected $languages = [];
    protected $hooks = [
        'paymentOptions',
        'header',
    ];

    /**
     * Constructor
     *
     * @param string $name Module unique name
     * @param Context $context
     */
    public function __construct($name = null, Context $context = null)
    {
        $this->name = 'stancer';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Stancer';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = ['min' => '1.7.0', 'max' => '8.0.999'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'Stancer';
        $this->description = $this->l('Simple payment solution at low prices.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->limited_currencies = ['EUR'];

        foreach (Language::getLanguages(false) as $lang) {
            if ($this->context->controller instanceof AdminController) {
                $default = $this->context->controller->default_form_language;
                $lang['is_default'] = $lang['id_lang'] == $default;
            }

            $this->languages[] = $lang;
        }
    }

    /**
     * Basic validation run at beginning of payment and paymentOptions hooks.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        if (!$this->active) {
            return false;
        }

        try {
            $apiConfig = new StancerApiConfig();
            return $apiConfig->isConfigured();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Return configuration for install or unistall module
     *
     * @return array
     */
    public function getConfigurationsList($group = null)
    {
        if (!$this->configurations) {
            $this->configurations = [];

            $mode = Configuration::get('STANCER_API_MODE') ?: Stancer\Config::TEST_MODE;
            $isLive = Stancer\Config::LIVE_MODE === $mode;

            $this->configurations['STANCER_API_LIVE_PUBLIC_KEY'] = [
                'default' => '',
                'desc' => sprintf($this->l('Starts with "%s"'), 'pprod_'),
                'group' => 'keys',
                'label' => $this->l('Public live API key'),
                'mode' => Stancer\Config::LIVE_MODE,
                'pattern' => 'pprod_',
                'required' => $isLive,
            ];

            $this->configurations['STANCER_API_LIVE_SECRET_KEY'] = [
                'default' => '',
                'desc' => sprintf($this->l('Starts with "%s"'), 'sprod_'),
                'group' => 'keys',
                'label' => $this->l('Secret live API key'),
                'mode' => Stancer\Config::LIVE_MODE,
                'pattern' => 'sprod_',
                'required' => $isLive,
            ];

            $this->configurations['STANCER_API_TEST_PUBLIC_KEY'] = [
                'default' => '',
                'desc' => sprintf($this->l('Starts with "%s"'), 'ptest_'),
                'group' => 'keys',
                'label' => $this->l('Public test API key'),
                'mode' => Stancer\Config::TEST_MODE,
                'pattern' => 'ptest_',
                'required' => false,
            ];

            $this->configurations['STANCER_API_TEST_SECRET_KEY'] = [
                'default' => '',
                'desc' => sprintf($this->l('Starts with "%s"'), 'stest_'),
                'group' => 'keys',
                'label' => $this->l('Secret test API key'),
                'mode' => Stancer\Config::TEST_MODE,
                'pattern' => 'stest_',
                'required' => false,
            ];

            $this->configurations['STANCER_API_MODE'] = [
                'default' => Stancer\Config::TEST_MODE,
                'group' => 'hidden',
            ];

            $this->configurations['STANCER_API_HOST'] = [
                'default' => '',
                'group' => 'settings',
                'type' => 'hidden',
            ];

            $this->configurations['STANCER_API_TIMEOUT'] = [
                'default' => 1,
                'group' => 'settings',
                'type' => 'hidden',
            ];

            $this->configurations['STANCER_PAGE_TYPE'] = [
                'default' => 'iframe',
                'group' => 'settings',
                'label' => $this->l('Page type'),
                'type' => 'radio',
                'values' => [
                    [
                        'id' => 'iframe',
                        'label' => $this->l('Inside the page'),
                        'value' => 'iframe',
                    ],
                    [
                        'id' => 'redirect',
                        'label' => $this->l('Redirect to an external page'),
                        'value' => 'redirect',
                    ],
                ],
            ];

            $this->configurations['STANCER_PAGE_URL'] = [
                'default' => 'https://payment.stancer.com',
                'group' => 'settings',
                'type' => 'hidden',
            ];

            $desc = [];
            $desc[] = $this->l(join(' ', [
                'Minimum amount to trigger an authenticated payment',
                '(3DS, Verified by Visa, Mastercard Secure Code...)',
            ]));
            $desc[] = $this->l(join(' ', [
                'Leave blank if you do not wish to authenticate,',
                'at zero all payments will be authenticated.',
            ]));

            $authLimit = 'STANCER_AUTH_LIMIT';
            $value = Tools::getValue($authLimit, Configuration::get($authLimit));
            $attr = [
                'class="input"',
                'name="' . $authLimit . '"',
                'type="number"',
                'value="' . $value . '"',
                'min="0"',
            ];

            $this->configurations[$authLimit] = [
                'default' => 0,
                'desc' => join('<br />', $desc),
                'group' => 'settings',
                'html_content' => '<input ' . join(' ', $attr) . ' />',
                'label' => $this->l('Authentication limit'),
                'type' => 'html',
            ];

            $defaultDescriptions = [];

            foreach ($this->languages as $lang) {
                $defaultDescriptions[$lang['id_lang']] = 'Your order SHOP_NAME.';

                if (strpos($lang['language_code'], 'fr') !== false) {
                    $defaultDescriptions[$lang['id_lang']] = 'Votre commande SHOP_NAME.';
                }
            }

            $desc = [];
            $vars = [
                'SHOP_NAME' => $this->l('Shop name configured in PrestaShop'),
                'TOTAL_AMOUNT' => $this->l('Total amount'),
                'CURRENCY' => $this->l('Currency of the cart'),
                'CART_ID' => $this->l('Cart identifier'),
            ];

            $desc[] = $this->l(implode(' ', [
                'Will be used as description for every payment made,',
                'and will be visible to your customer in redirect mode.',
            ]));

            $tmp = [];
            $tmp[] = '<details class="help-block">';
            $tmp[] = '<summary>' . $this->l('You may use simple variables, click to see.') . '</summary>';
            $tmp[] = '<dl>';

            foreach ($vars as $key => $val) {
                $tmp[] = '<dt>' . $key . '</dt><dd>' . $this->l($val) . '</dd>';
            }

            $tmp[] = '</dl>';
            $tmp[] = '</details>';
            $desc[] = implode('', $tmp);

            $this->configurations['STANCER_PAYMENT_DESCRIPTION'] = [
                'default' => $defaultDescriptions,
                'desc' => join('<br />', $desc),
                'group' => 'settings',
                'label' => $this->l('Payment description'),
                'lang' => true,
                'type' => 'text',
            ];
        }

        $newMode = Tools::getValue('STANCER_API_MODE');
        if ($newMode !== false) {
            $keys = [
                'STANCER_API_LIVE_PUBLIC_KEY',
                'STANCER_API_LIVE_SECRET_KEY',
            ];

            foreach ($keys as $key) {
                $this->configurations[$key]['required'] = !$newMode;
            }
        }

        if ($group) {
            return array_filter($this->configurations, function ($infos) use ($group) {
                return $infos['group'] === $group;
            });
        }

        return $this->configurations;
    }

    /**
     * Show configuration form
     *
     * @uses self::getHelperForm()
     * @return string
     */
    public function getContent()
    {
        $this->context->controller->addCss(_MODULE_DIR_ . $this->name . '/views/css/admin.css');
        $this->context->controller->addJs(_MODULE_DIR_ . $this->name . '/views/js/admin.js');

        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $hasError = false;
            $keysOk = true;

            foreach ($this->getConfigurationsList() as $name => $infos) {
                $value = Tools::getValue($name);

                if (array_key_exists('lang', $infos) && $infos['lang']) {
                    $value = [];

                    foreach ($this->languages as $lang) {
                        $value[$lang['id_lang']] = Tools::getValue($name . '_' . $lang['id_lang']);
                    }
                }

                if (is_array($value)) {
                    array_walk($value, 'trim');
                } else {
                    $value = trim($value);
                }

                if (array_key_exists('required', $infos) && !$infos['required'] && !$value) {
                    continue;
                }

                Configuration::updateValue($name, $value);
            }

            $apiMode = Stancer\Config::TEST_MODE;

            if ($keysOk) {
                $tmp = $this->l('You can not pass to live mode until an error occur with API keys.');
                $output .= $this->displayError($tmp);

                $apiMode = Tools::getValue('STANCER_API_MODE') ? Stancer\Config::TEST_MODE : Stancer\Config::LIVE_MODE;
            }

            Configuration::updateValue('STANCER_API_MODE', $apiMode);

            if (!$hasError) {
                $link = [
                    AdminController::$currentIndex,
                    'configure=' . $this->name,
                    'conf=4',
                    'token=' . Tools::getAdminTokenLite('AdminModules'),
                ];
                Tools::redirectAdmin(implode('&', $link));
            }
        }

        $helper = $this->getHelperForm();
        $form = [
            $this->getContentFormKeys($helper),
            $this->getContentFormSettings($helper),
            [
                'form' => [
                    'submit' => [
                        'class' => 'btn btn-default pull-right',
                        'title' => $this->l('Save'),
                    ],
                ],
            ],
        ];

        $output .= $this->display(__FILE__, 'infos.tpl');

        return $output . $helper->generateForm($form);
    }

    /**
     * Create admin form for keys.
     *
     * @param mixed $helper
     * @return array
     */
    public function getContentFormKeys(HelperForm $helper): array
    {
        $signup = $this->l('https://manage.stancer.com/en/sign-up');

        $keys = [
            'legend' => [
                'icon' => 'icon-key',
                'title' => $this->l('Keys'),
            ],
            'description' => implode(' ', [
                $this->l('You can create your API keys on'),
                '<a href="' . $signup . '" target="_blank">' . $signup . '</a>',
            ]),
            'input' => [],
        ];

        foreach ($this->getConfigurationsList('keys') as $name => $infos) {
            $class = '';

            if (array_key_exists('class', $infos)) {
                $class = $infos['class'];
            }

            $desc = null;
            if (array_key_exists('desc', $infos)) {
                $desc = $infos['desc'];
            }

            if (!empty($infos['mode']) === Stancer\Config::LIVE_MODE) {
                if ($desc) {
                    $desc .= ', ';
                } else {
                    $desc = '';
                }

                $desc .= '<em>' . $this->l('mandatory in live mode') . '</em>';
            }

            $keys['input'][] = [
                'class' => $class,
                'desc' => $desc,
                'label' => $infos['label'],
                'name' => $name,
                'required' => $infos['required'] ?? true,
                'size' => 20,
                'type' => 'text',
            ];

            $helper->fields_value[$name] = Configuration::get($name);
        }

        return ['form' => $keys];
    }

    /**
     * Create admin form for generals settings.
     *
     * @param HelperForm $helper
     * @return array
     */
    public function getContentFormSettings(HelperForm $helper): array
    {
        $settings = [
            'legend' => [
                'icon' => 'icon-gear',
                'title' => $this->l('Settings'),
            ],
            'input' => [],
        ];

        $link = implode('', [
            '<a href="' . $this->l('https://www.stancer.com/documentation/api/#test-cards') . '" target="_blank">',
            $this->l('test cards'),
            '</a>',
        ]);
        $settings['input'][] = [
            'desc' => implode('<br />', [
                $this->l('In test mode, no payment will really send to a bank, only test card can be used.'),
                sprintf($this->l('Check the documentation to find %s.'), $link),
            ]),
            'label' => $this->l('Test mode'),
            'name' => 'STANCER_API_MODE',
            'type' => 'switch',
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => 1,
                ],
                [
                    'id' => 'active_off',
                    'value' => 0,
                ],
            ],
        ];

        $mode = 'STANCER_API_MODE';
        $helper->fields_value[$mode] = Configuration::get($mode) !== Stancer\Config::LIVE_MODE;
        $excep = [
            'default' => 1,
            'group' => 1,
        ];

        foreach ($this->getConfigurationsList('settings') as $name => $infos) {
            $clean = array_diff_key($infos, $excep);
            $settings['input'][] = array_merge($clean, [
                'name' => $name,
            ]);

            if (array_key_exists('lang', $infos) && $infos['lang']) {
                foreach ($this->languages as $lang) {
                    $value = Configuration::get($name, $lang['id_lang']);
                    $helper->fields_value[$name][$lang['id_lang']] = $value;
                }
            } else {
                $helper->fields_value[$name] = Configuration::get($name);
            }
        }

        return ['form' => $settings];
    }

    /**
     * Return a form helper
     *
     * @return HelperForm
     */
    public function getHelperForm()
    {
        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $this->context->controller->default_form_language;
        $helper->allow_employee_form_lang = $this->context->controller->allow_employee_form_lang;
        $helper->languages = $this->languages;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;

        $backHref = [
            AdminController::$currentIndex,
            'token=' . Tools::getAdminTokenLite('AdminModules'),
        ];
        $saveHref = [
            AdminController::$currentIndex,
            'configure=' . $this->name,
            'save' . $this->name,
            'token=' . Tools::getAdminTokenLite('AdminModules'),
        ];

        $helper->toolbar_btn = [
            'back' => [
                'desc' => $this->l('Back to list'),
                'href' => implode('&', $backHref),
            ],
            'save' => [
                'desc' => $this->l('Save'),
                'href' => implode('&', $saveHref),
            ],
        ];

        return $helper;
    }

    /**
     * Hook called on page header generation
     *
     * @return string
     */
    public function hookHeader(): string
    {
        $this->registerStylesheet('global');

        if (Configuration::get('STANCER_PAGE_TYPE') === 'iframe') {
            $this->registerJavascript('iframe')->registerJavascript('message');
        }

        return '<script>var STANCER = {origin: "' . Configuration::get('STANCER_PAGE_URL') . '"};</script>';
    }

    /**
     * Hook called to display payment methods (PS1.7+).
     *
     * @param array $params
     * @return PrestaShop\PrestaShop\Core\Payment\PaymentOption[]
     */
    public function hookPaymentOptions(array $params): array
    {
        $list = [];
        if (!$this->isAvailable()) {
            return $list;
        }

        // @todo : uncomment when use existing card will be fixed
        // $cards = StancerApiCard::getCustomerCards($this->context->customer);
        $cards = [];
        foreach ($cards as $card) {
            $target = $this->context->link->getModuleLink(
                $this->name,
                'payment',
                [
                    'card' => $card->id,
                    'last-step' => Tools::getValue('step'),
                ],
                true
            );

            $cardOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $cardOption
                ->setModuleName($this->name)
                ->setCallToActionText(vsprintf($this->l('Pay with your %s finishing with %s'), [$card->brandname, $card->last4]))
                ->setAction($target)
            ;

            $list[] = $cardOption;
        }

        $target = $this->context->link->getModuleLink(
            $this->name,
            'payment',
            [
                'last-step' => Tools::getValue('step'),
            ],
            true
        );
        $tpl = 'module:' . $this->name . '/views/templates/front/';

        $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $paymentOption
            ->setModuleName($this->name)
            ->setCallToActionText($this->l('Pay by card'))
        ;

        switch (Configuration::get('STANCER_PAGE_TYPE')) {
            case 'iframe':
                $this->context->smarty->assign('target', $target);
                $this->context->smarty->assign('validation', $this->context->link->getModuleLink($this->name, 'validation', [], true));
                $paymentOption->setAdditionalInformation($this->context->smarty->fetch($tpl . 'iframe.tpl'));

                break;
            default:
                $paymentOption
                    ->setAction($target)
                    ->setAdditionalInformation($this->context->smarty->fetch($tpl . 'option.tpl'))
                ;

                break;
        }

        $list[] = $paymentOption;

        return $list;
    }

    /**
     * Module installation
     *
     * @uses self::installConfigurations()
     * @uses self::installDbRequirements()
     * @uses self::installHooks()
     * @return bool
     */
    public function install(): bool
    {
        return parent::install()
            && $this->installConfigurations()
            && $this->installDbRequirements()
            && $this->installHooks();
    }

    /**
     * Module configurations
     *
     * @return bool
     */
    public function installConfigurations(): bool
    {
        $return = true;

        foreach ($this->getConfigurationsList() as $name => $infos) {
            $return &= Configuration::updateValue($name, $infos['default']);
        }

        return $return;
    }

    /**
     * Module database configurations
     *
     * As `CHAR` is a case insensitive column type, we prefer using `BINARY`.
     *
     * @return bool
     */
    public function installDbRequirements(): bool
    {
        $db = Db::getInstance();
        $return = true;

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'stancer_card` (
                `id_stancer_card` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "Unique ID in this table",
                `id_customer` INT(10) NOT NULL COMMENT "ID of a customer (PS)",
                `card_id` BINARY(29) NOT NULL COMMENT "ID of a card, unique in this table",
                `last4` CHAR(4) NOT NULL COMMENT "Last 4 digits of the number",
                `expiration` DATE NOT NULL COMMENT "Expiration date",
                `brand` VARCHAR(10) COMMENT "Card brand (reference)",
                `brandname` VARCHAR(20) COMMENT "Card brand (visually correct)",
                `name` VARCHAR(64) COMMENT "Card holder\'s name",
                `created` DATETIME COMMENT "Creation date into the API",
                `last_used` DATETIME COMMENT "Last time this card was used",
                `extra_data` BLOB COMMENT "Extra data not referenced elsewhere",
                `date_add` DATETIME COMMENT "Creation date",
                `date_upd` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    COMMENT "Last modification date",
                PRIMARY KEY (`id_stancer_card`),
                UNIQUE KEY (`card_id`),
                KEY (`id_customer`, `expiration`)
            ) COMMENT "This table uses Stancer API names";';

        $return &= $db->execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'stancer_customer` (
                `id_stancer_customer` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "Unique ID in this table",
                `id_customer` INT(10) NOT NULL COMMENT "ID of a customer (PS)",
                `customer_id` BINARY(29) NOT NULL COMMENT "ID of a customer (API), unique in this table",
                `name` VARCHAR(64) COMMENT "Customer\'s name",
                `email` VARCHAR(64) COMMENT "Customer\'s email",
                `mobile` VARCHAR(16) COMMENT "Customer\'s mobile phone number",
                `deleted` TINYINT(1) UNSIGNED DEFAULT 0 COMMENT "Is this customer deleted ?",
                `extra_data` BLOB COMMENT "Extra data not referenced elsewhere",
                `created` DATETIME COMMENT "Creation date into the API",
                `date_add` DATETIME COMMENT "Creation date",
                `date_upd` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    COMMENT "Last modification date",
                PRIMARY KEY (`id_stancer_customer`),
                UNIQUE KEY (`customer_id`),
                KEY (`id_customer`)
            ) COMMENT "This table uses Stancer API names";';

        $return &= $db->execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'stancer_payment` (
                `id_stancer_payment` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "Unique ID in this table",
                `payment_id` BINARY(29) NOT NULL COMMENT "ID of a payment, unique in this table",
                `customer_id` BINARY(29) COMMENT "ID of the customer who paid (API)",
                `card_id` BINARY(29) COMMENT "ID of the card used (API)",
                `id_cart` INT(10) UNSIGNED NOT NULL COMMENT "PrestaShop cart ID",
                `id_order` INT(10) UNSIGNED COMMENT "PrestaShop order ID",
                `currency` CHAR(3) NOT NULL COMMENT "Currency used",
                `amount` INT(10) UNSIGNED NOT NULL COMMENT "Amount paid (in cents)",
                `status` VARCHAR(10) NOT NULL DEFAULT "pending" COMMENT "Payment\'s status (trust only API status)",
                `extra_data` BLOB COMMENT "Extra data not referenced elsewhere",
                `created` DATETIME COMMENT "Creation date into the API",
                `date_add` DATETIME COMMENT "Creation date",
                `date_upd` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    COMMENT "Last modification date",
                PRIMARY KEY (`id_stancer_payment`),
                UNIQUE KEY (`payment_id`),
                KEY `card_id` (`card_id`),
                KEY `customer_id` (`customer_id`),
                KEY `id_cart` (`id_cart`, `status`, `currency`, `amount`),
                KEY `id_order` (`id_order`)
            ) COMMENT "This table uses Stancer API names";';

        $return &= $db->execute($sql);

        return $return;
    }

    /**
     * Module hooks installation
     *
     * @return bool
     */
    public function installHooks(): bool
    {
        $add = true;
        foreach ($this->hooks as $hookName) {
            $add &= $this->registerHook($hookName);
        }

        if ($add) {
            $query = new DbQuery();
            $query->select('id_hook');
            $query->from('hook');
            $query->where('`name` = "paymentOptions"');

            $paymentHookId = (int) Db::getInstance()->getValue($query);

            $sql = 'UPDATE `' . _DB_PREFIX_ . 'hook_module`
                    SET `position` = if(`id_module` = ' . ((int) $this->id) . ', 1, `position` + 1)
                    WHERE TRUE
                    AND  `id_hook` = ' . ((int) $paymentHookId) . ';';

            return (bool) Db::getInstance()->execute($sql);
        }

        return false;
    }

    /**
     * Add a JS file to the current controller.
     *
     * @param string $name Name of the JS file to add.
     */
    protected function registerJavascript(string $name): self
    {
        $identifier = $this->name . '-' . $name;
        $path = 'modules/' . $this->name . '/views/js/' . $name . '.js';

        $this->context->controller->registerJavascript($identifier, $path);

        return $this;
    }

    /**
     * Add a CSS file to the current controller.
     *
     * @param string $name Name of the CSS file to add.
     */
    protected function registerStylesheet(string $name): self
    {
        $identifier = $this->name . '-' . $name;
        $path = 'modules/' . $this->name . '/views/css/' . $name . '.css';

        $this->context->controller->registerStylesheet($identifier, $path);

        return $this;
    }

    /**
     * updateConfigurationList
     *
     * @param string $name
     * @param array $params
     * @return void
     */
    public function updateConfigurationList(string $name, array $params): void
    {
        $this->configurations[$name] = array_merge($this->configurations[$name], $params);
    }

    /**
     * Module uninstallation
     *
     * @uses self::uninstallConfigurations()
     * @uses self::uninstallDbRequirements()
     * @uses self::uninstallHooks()
     * @return bool
     */
    public function uninstall(): bool
    {
        return
            parent::uninstall()
            && $this->uninstallConfigurations()
            && $this->uninstallDbRequirements()
            && $this->uninstallHooks();
    }

    /**
     * Module configuration suppresson
     *
     * @return bool
     */
    public function uninstallConfigurations(): bool
    {
        $return = true;

        foreach ($this->getConfigurationsList() as $name => $value) {
            $return &= Configuration::deleteByName($name);
        }

        return $return;
    }

    /**
     * Module database deconfiguration
     *
     * @return bool
     */
    public function uninstallDbRequirements(): bool
    {
        $db = Db::getInstance();
        $return = true;

        $return &= $db->execute('DROP TABLE IF EXISTS `' . bqSQL(_DB_PREFIX_ . 'stancer_card') . '`;');
        $return &= $db->execute('DROP TABLE IF EXISTS `' . bqSQL(_DB_PREFIX_ . 'stancer_customer') . '`;');
        $return &= $db->execute('DROP TABLE IF EXISTS `' . bqSQL(_DB_PREFIX_ . 'stancer_payment') . '`;');

        return $return;
    }

    /**
     * Module hooks uninstallation
     *
     * @return bool
     */
    public function uninstallHooks(): bool
    {
        $return = true;
        foreach ($this->hooks as $hookName) {
            $return &= $this->unregisterHook($hookName);
            $return &= $this->unregisterExceptions($hookName);
        }

        return $return;
    }
}
