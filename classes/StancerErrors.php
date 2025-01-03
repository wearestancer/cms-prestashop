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
 * Error handler.
 */
class StancerErrors extends ObjectModel
{
    public const UNKNOWN_ERROR = 1;
    public const NOT_AVAILABLE = 2;
    public const BAD_MODE = 3;
    public const NOT_AUTHORIZED = 9;
    public const SERVER_ERROR = 10;
    public const CLIENT_ERROR = 11;
    public const NO_PAYMENT = 12;
    public const NO_PAYMENT_REINSURANCE = 13;
    public const PAYMENT_FAILED = 14;

    /**
     * Get error message
     *
     * @param mixed $key
     *
     * @return string
     */
    public static function getMessage(int $key): string
    {
        $module = Module::getInstanceByName('stancer');
        $errors = [];
        $message = [];

        if (_PS_MODE_DEV_) {
            $tmp = $module->l('Configuration error, unknown mode "%s".', 'StancerErrors');
            $message[] = sprintf($tmp, Configuration::get('STANCER_API_MODE'));

            $message[] = $module->l(
                'Please reconfigure the module or ask the site administrator to do it.',
                'StancerErrors',
            );
        } else {
            $message[] = $module->l('This payment method is currently unavailable.', 'StancerErrors');
            $message[] = $module->l('Please contact us to unlock this situation.', 'StancerErrors');
        }

        $errors[static::BAD_MODE] = implode(' ', $message);

        // API errors
        $errors[static::NOT_AUTHORIZED] = implode(' ', [
            $module->l('Impossible to connect to the payment platform.', 'StancerErrors'),
            $module->l('Please contact us to unlock this situation.', 'StancerErrors'),
        ]);
        $errors[static::SERVER_ERROR] = implode(' ', [
            $module->l('The payment platform is currently unavailable.', 'StancerErrors'),
            $module->l('Please wait a minute and try again.', 'StancerErrors'),
        ]);
        $errors[static::CLIENT_ERROR] = $module->l('The payment platform is currently unreacheable.', 'StancerErrors');
        $errors[static::NO_PAYMENT] = $module->l('No payment found for this cart.', 'StancerErrors');
        $errors[static::NO_PAYMENT_REINSURANCE] = $module->l('Your card has not been charged.', 'StancerErrors');
        $errors[static::PAYMENT_FAILED] = $module->l('The payment attempt failed.', 'StancerErrors');
        $errors[static::UNKNOWN_ERROR] = $module->l(
            'An unknown error occurred while connecting to the payment platform.',
            'StancerErrors',
        );

        if (array_key_exists($key, $errors)) {
            return $errors[$key];
        }

        return $errors[static::UNKNOWN_ERROR];
    }
}
