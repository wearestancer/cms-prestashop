<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2018-2024 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 *
 * @website   https://www.stancer.com
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_ROOT_DIR_ . '/modules/stancer/vendor/autoload.php';

/**
 * Stancer payment module.
 */
class Stancer extends PaymentModule
{
    public const VERSION = '1.2.3';

    protected $configurations = [];
    protected $languages = [];
    protected $hooks = [
        'paymentOptions',
        'displayHeader',
    ];

    /**
     * Constructor
     *
     * @param string $name Module unique name
     * @param Context $context
     */
    public function __construct($name = null, $context = null)
    {
        $this->name = 'stancer';
        $this->tab = 'payments_gateways';
        $this->version = '1.2.3';
        $this->author = 'Stancer';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = ['min' => '1.7.8', 'max' => '8.2.999'];
        $this->module_key = '405faa09756f808b77ad16948b321351';
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
     * Fetch a template.
     *
     * @param string $path path of the template to fetch
     *
     * @return string
     */
    protected function fetchTemplate(string $path)
    {
        return $this->context->smarty->fetch('module:' . $this->name . '/views/templates/' . $path);
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

            $mode = Tools::getValue('STANCER_API_MODE', Configuration::get('STANCER_API_MODE'));
            $isLive = Stancer\Config::LIVE_MODE === $mode;

            $this->configurations['STANCER_ADMIN_SHOW_DISPLAY'] = [
                'default' => true,
                'group' => 'settings',
                'type' => 'hidden',
            ];

            $this->configurations['STANCER_ADMIN_SHOW_KEYS'] = [
                'default' => true,
                'group' => 'settings',
                'type' => 'hidden',
            ];

            $this->configurations['STANCER_ADMIN_SHOW_SETTINGS'] = [
                'default' => true,
                'group' => 'settings',
                'type' => 'hidden',
            ];

            $this->configurations['STANCER_API_LIVE_PUBLIC_KEY'] = [
                'default' => '',
                'group' => 'keys',
                'label' => $this->l('Public live API key'),
                'mode' => Stancer\Config::LIVE_MODE,
                'pattern' => '/^pprod_\w{24}$/',
                'public' => true,
                'required' => $isLive,
            ];

            $this->configurations['STANCER_API_LIVE_SECRET_KEY'] = [
                'default' => '',
                'group' => 'keys',
                'label' => $this->l('Secret live API key'),
                'mode' => Stancer\Config::LIVE_MODE,
                'pattern' => '/^sprod_\w{24}$/',
                'public' => false,
                'required' => $isLive,
            ];

            $this->configurations['STANCER_API_TEST_PUBLIC_KEY'] = [
                'default' => '',
                'group' => 'keys',
                'label' => $this->l('Public test API key'),
                'mode' => Stancer\Config::TEST_MODE,
                'pattern' => '/^ptest_\w{24}$/',
                'public' => true,
                'required' => false,
            ];

            $this->configurations['STANCER_API_TEST_SECRET_KEY'] = [
                'default' => '',
                'group' => 'keys',
                'label' => $this->l('Secret test API key'),
                'mode' => Stancer\Config::TEST_MODE,
                'pattern' => '/^stest_\w{24}$/',
                'public' => false,
                'required' => false,
            ];

            $this->configurations['STANCER_API_MODE'] = [
                // By forcing the cast on Stance\Config Constant we make sure that it binds to values id and show the radio button checked in our form.
                'default' => (string) $mode,
                'desc' => $this->fetchTemplate('admin/descriptions/api_mode.tpl'),
                'group' => 'settings',
                'label' => $this->l('Mode'),
                'type' => 'radio',
                'values' => [
                    [
                        'id' => Stancer\Config::LIVE_MODE,
                        'label' => $this->l('Live'),
                        'value' => Stancer\Config::LIVE_MODE,
                    ],
                    [
                        'id' => Stancer\Config::TEST_MODE,
                        'label' => $this->l('Test'),
                        'value' => Stancer\Config::TEST_MODE,
                    ],
                ],
            ];

            $this->configurations['STANCER_API_HOST'] = [
                'default' => '',
                'group' => 'settings',
                'type' => 'hidden',
            ];

            $this->configurations['STANCER_API_TIMEOUT'] = [
                'default' => null,
                'group' => 'settings',
                'type' => 'hidden',
            ];

            $defaultValue = [];

            foreach ($this->languages as $lang) {
                $defaultValue[$lang['id_lang']] = 'Pay by card';

                if (strpos($lang['language_code'], 'fr') !== false) {
                    $defaultValue[$lang['id_lang']] = 'Payer par carte';
                }
            }

            $this->configurations['STANCER_CTA_TEXT'] = [
                'default' => $defaultValue,
                'group' => 'display',
                'label' => $this->l('Payment option text'),
                'lang' => true,
                'required' => true,
                'type' => 'text',
            ];

            $this->configurations['STANCER_CTA_LOGO'] = [
                'default' => 'none',
                'group' => 'display',
                'label' => $this->l('Payment option logo'),
                'template' => 'logo',
            ];

            $this->configurations['STANCER_FROM_MARKETPLACE'] = [
                'default' => false,
                'desc' => 'Internal flag, set to true if the module was installed from the marketplace',
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
                        'label' => $this->l('Inside the page (recommended)'),
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

            $this->configurations['STANCER_REUSE_CARD'] = [
                'default' => 0,
                'group' => 'settings',
                'label' => $this->l('Allow customers to reuse cards'),
                'type' => 'hidden',
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

            $this->configurations['STANCER_REUSED_CARD_LOGO'] = [
                'default' => 0,
                'group' => 'display',
                'label' => $this->l('Add scheme logo on reused card'),
                'type' => 'hidden',
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

            $this->configurations['STANCER_ORDER_FOR_NOK_PAYMENTS'] = [
                'default' => true,
                'desc' => $this->l('When active, an order will be created for failed payments.'),
                'group' => 'settings',
                'label' => $this->l('Create an order for every payment'),
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

            $authLimit = 'STANCER_AUTH_LIMIT';

            $this->context->smarty->assign('auth_limit', $authLimit);
            $this->context->smarty->assign('value', Tools::getValue($authLimit, Configuration::get($authLimit)));

            $this->configurations[$authLimit] = [
                'default' => 0,
                'desc' => $this->fetchTemplate('admin/descriptions/auth_limit.tpl'),
                'group' => 'settings',
                'html_content' => $this->fetchTemplate('admin/input/auth_limit.tpl'),
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

            $vars = [
                'SHOP_NAME' => $this->l('Shop name configured in PrestaShop'),
                'TOTAL_AMOUNT' => $this->l('Total amount'),
                'CURRENCY' => $this->l('Currency of the cart'),
                'CART_ID' => $this->l('Cart identifier'),
            ];
            $this->context->smarty->assign('variables', $vars);

            $this->configurations['STANCER_PAYMENT_DESCRIPTION'] = [
                'default' => $defaultDescriptions,
                'desc' => $this->fetchTemplate('admin/descriptions/payment_description.tpl'),
                'group' => 'settings',
                'label' => $this->l('Payment description'),
                'lang' => true,
                'type' => 'text',
            ];
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
     *
     * @return string
     */
    public function getContent()
    {
        $this->context->controller->addCss(_MODULE_DIR_ . $this->name . '/views/css/admin.css');
        $this->context->controller->addJs(_MODULE_DIR_ . $this->name . '/views/js/admin.js');

        $output = '';
        $publicProdKeyInError = false;
        $secretProdKeyInError = false;

        if (Tools::isSubmit('submit' . $this->name)) {
            $hasError = false;

            foreach ($this->getConfigurationsList() as $name => $infos) {
                $value = Tools::getValue($name);

                if (array_key_exists('lang', $infos) && $infos['lang']) {
                    $value = [];

                    foreach ($this->languages as $lang) {
                        $value[$lang['id_lang']] = Tools::getValue($name . '_' . $lang['id_lang']);
                    }
                } elseif (false === $value) {
                    continue;
                }

                if (is_array($value)) {
                    array_walk($value, 'trim');
                } else {
                    $value = trim($value);
                }

                if (array_key_exists('pattern', $infos) && '' !== $value) {
                    $check = preg_match($infos['pattern'], $value) === 1;

                    if (!$check) {
                        $hasError = true;
                        $error = $this->l('"%s" is invalid.');

                        if ($infos['group'] === 'keys') {
                            if ($infos['mode'] === Stancer\Config::LIVE_MODE) {
                                if ($infos['public']) {
                                    $publicProdKeyInError = true;
                                } else {
                                    $secretProdKeyInError = true;
                                }
                            }

                            $error = $this->l('"%s" is invalid, please provide a correct key.');
                        }

                        $output .= $this->displayError(sprintf($error, $infos['label']));

                        $this->updateConfigurationList($name, ['class' => 'js-show-error']);
                        continue;
                    }
                }

                Configuration::updateValue($name, $value);
            }

            $apiMode = Tools::getValue('STANCER_API_MODE') ?? Stancer\Config::TEST_MODE;

            if ($apiMode === Stancer\Config::LIVE_MODE) {
                $public = Configuration::get('STANCER_API_LIVE_PUBLIC_KEY');
                $secret = Configuration::get('STANCER_API_LIVE_SECRET_KEY');
                $keysOk = !$publicProdKeyInError && !$secretProdKeyInError;
                $error = $this->l('"%s" is invalid.');

                if (!$publicProdKeyInError) {
                    if (!$public) {
                        $label = $this->configurations['STANCER_API_LIVE_PUBLIC_KEY']['label'];
                        $output .= $this->displayError(sprintf($error, $label));
                        $keysOk = false;
                    }
                }

                if (!$secretProdKeyInError) {
                    if (!$secret) {
                        $label = $this->configurations['STANCER_API_LIVE_SECRET_KEY']['label'];
                        $output .= $this->displayError(sprintf($error, $label));
                        $keysOk = false;
                    }
                }

                if (!$keysOk) {
                    $tmp = $this->l('You cannot switch to live mode while an error is occurring with the API keys.');
                    $output .= $this->displayError($tmp);
                    $hasError = true;

                    $apiMode = Stancer\Config::TEST_MODE;
                }
            }

            Configuration::updateValue('STANCER_API_MODE', $apiMode);

            if (!$hasError) {
                $link = [
                    AdminController::$currentIndex,
                    'configure=' . $this->name,
                    'conf=4',
                    'token=' . Tools::getAdminTokenLite('AdminModules'),
                ];

                return Tools::redirectAdmin(implode('&', $link));
            }
        }

        $helper = $this->getHelperForm();
        $form = [];

        if (Configuration::get('STANCER_ADMIN_SHOW_KEYS')) {
            $form[] = $this->getContentFormKeys($helper);
        }

        if (Configuration::get('STANCER_ADMIN_SHOW_SETTINGS')) {
            $form[] = $this->getContentFormSettings($helper);
        }

        if (Configuration::get('STANCER_ADMIN_SHOW_DISPLAY')) {
            $form[] = $this->getContentFormDisplay($helper);
        }

        $form[] = [
            'form' => [
                'submit' => [
                    'class' => 'btn btn-default pull-right',
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $output .= $this->display(__FILE__, 'infos.tpl');

        return $output . $helper->generateForm($form);
    }

    /**
     * Create admin form for display settings.
     *
     * @param HelperForm $helper
     *
     * @return array
     */
    public function getContentFormDisplay(HelperForm $helper): array
    {
        $settings = [
            'legend' => [
                'icon' => 'icon-paint-brush',
                'title' => $this->l('Display'),
            ],
            'input' => [],
        ];

        $excep = [
            'default' => 1,
            'group' => 1,
            'template' => 1,
        ];

        foreach ($this->getConfigurationsList('display') as $name => $infos) {
            if (array_key_exists('template', $infos)) {
                $clean = array_diff_key($infos, $excep);
                $value = Configuration::get($name);

                $this->context->smarty->assign('stancer_module_img', _MODULE_DIR_ . $this->name . '/views/img');
                $this->context->smarty->assign($name . '_VALUE', $value);

                $settings['input'][] = array_merge($clean, [
                    'html_content' => $this->fetchTemplate('admin/' . $infos['template'] . '.tpl'),
                    'name' => $name,
                    'type' => 'html',
                ]);

                $helper->fields_value[$name] = $value;
            } else {
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
        }

        return ['form' => $settings];
    }

    /**
     * Create admin form for keys.
     *
     * @param mixed $helper
     *
     * @return array
     */
    public function getContentFormKeys(HelperForm $helper): array
    {
        $this->context->smarty->assign('signup', $this->l('https://manage.stancer.com/en/developers'));

        $keys = [
            'legend' => [
                'icon' => 'icon-key',
                'title' => $this->l('API keys'),
            ],
            'description' => $this->fetchTemplate('admin/descriptions/keys_settings.tpl'),
            'input' => [],
        ];

        foreach ($this->getConfigurationsList('keys') as $name => $infos) {
            $class = '';
            $prefix = '';

            if (array_key_exists('class', $infos)) {
                $class = $infos['class'];
            }

            $desc = null;
            if (array_key_exists('desc', $infos)) {
                $desc = $infos['desc'];
            }

            if (array_key_exists('public', $infos)) {
                $prefix = 's';

                if ($infos['public']) {
                    $prefix = 'p';
                }

                if ($infos['mode'] === Stancer\Config::LIVE_MODE) {
                    $prefix .= 'prod';
                } else {
                    $prefix .= 'test';
                }

                $this->context->smarty->assign('prefix', $prefix . '_');
                $this->context->smarty->assign('is_prod', $infos['mode'] === Stancer\Config::LIVE_MODE);

                $desc = $this->fetchTemplate('admin/descriptions/keys.tpl');
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
     *
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

        $mode = 'STANCER_API_MODE';
        $helper->fields_value[$mode] = Configuration::get($mode) === Stancer\Config::LIVE_MODE;
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
    public function hookDisplayHeader(): string
    {
        $this->registerStylesheet('global');

        if (in_array(Configuration::get('STANCER_PAGE_TYPE'), ['full-iframe', 'iframe'])) {
            $this->registerJavascript('iframe')->registerJavascript('message');
        }

        return $this->fetchTemplate('hook/header.tpl');
    }

    /**
     * Hook called to display payment methods (PS1.7+).
     *
     * @param array $params
     *
     * @return PrestaShop\PrestaShop\Core\Payment\PaymentOption[]
     */
    public function hookPaymentOptions(array $params): array
    {
        $list = [];

        if ($this->isNotAvailable()) {
            return $list;
        }

        if (Configuration::get('STANCER_REUSE_CARD')) {
            $cards = StancerApiCard::getCustomerCards($this->context->customer);

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

                $text = vsprintf($this->l('Pay with your %s finishing with %s'), [$card->brandname, $card->last4]);
                $cardOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
                $cardOption
                    ->setModuleName($this->name)
                    ->setCallToActionText($text)
                    ->setAction($target);

                if (Configuration::get('STANCER_REUSED_CARD_LOGO')) {
                    $url = _MODULE_DIR_ . $this->name . '/views/img/logo.svg#';

                    if (in_array($card->brand, ['amex', 'mastercard', 'visa'], true)) {
                        $url .= 'card-' . $card->brand;
                    } else {
                        $url .= $card->brand;
                    }

                    $cardOption->setLogo($url);
                }

                $list[] = $cardOption;
            }
        }

        $target = $this->context->link->getModuleLink(
            $this->name,
            'payment',
            [
                'last-step' => Tools::getValue('step'),
            ],
            true
        );

        $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $paymentOption
            ->setModuleName($this->name)
            ->setCallToActionText(Configuration::get('STANCER_CTA_TEXT', $this->context->language->id));

        $logo = Configuration::get('STANCER_CTA_LOGO');

        if ($logo !== 'none') {
            $paymentOption->setLogo(_MODULE_DIR_ . $this->name . '/views/img/logo.svg#' . $logo);
        }

        switch (Configuration::get('STANCER_PAGE_TYPE')) {
            case 'full-iframe':
                $this->context->smarty->assign('3ds', true);
                // no break
            case 'iframe':
                $link = $this->context->link->getModuleLink($this->name, 'validation', [], true);
                $this->context->smarty->assign('target', $target);
                $this->context->smarty->assign('validation', $link);
                $paymentOption->setAdditionalInformation($this->fetchTemplate('front/iframe.tpl'));

                break;
            default:
                $paymentOption
                    ->setAction($target)
                    ->setAdditionalInformation($this->fetchTemplate('front/option.tpl'));

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
     *
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
            if (array_key_exists('lang', $infos) && $infos['lang']) {
                $exists = false;

                foreach ($this->languages as $lang) {
                    $exists |= Configuration::hasKey($name, $lang['id_lang']);
                }
            } else {
                $exists = Configuration::hasKey($name);
            }

            if (!$exists) {
                $return &= Configuration::updateValue($name, $infos['default']);
            }
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
                `live_mode` TINYINT(1) UNSIGNED NOT NULL COMMENT "Is a live mode object?",
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
                `live_mode` TINYINT(1) UNSIGNED NOT NULL COMMENT "Is a live mode object?",
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
                `live_mode` TINYINT(1) UNSIGNED NOT NULL COMMENT "Is a live mode object?",
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
     * Basic validation is the module is available.
     *
     * @return bool
     */
    public function isNotAvailable(): bool
    {
        return !$this->isAvailable();
    }

    /**
     * Add a JS file to the current controller.
     *
     * @param string $name name of the JS file to add
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
     * @param string $name name of the CSS file to add
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
     *
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
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        return parent::uninstall()
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
