<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2023 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 *
 * @website   https://www.stancer.com
 *
 * @version   1.2.0
 */
function upgrade_module_1_2_0($module)
{
    // Add new configurations
    if (!$module->installConfigurations()) {
        return false;
    }

    // Update tables schema
    $db = Db::getInstance();

    $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'stancer_card`
        ADD `live_mode` TINYINT(1) UNSIGNED NOT NULL COMMENT "Is a live mode object?" AFTER `card_id`;';

    if (!$db->execute($sql)) {
        return false;
    }

    $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'stancer_customer`
        ADD `live_mode` TINYINT(1) UNSIGNED NOT NULL COMMENT "Is a live mode object?" AFTER `customer_id`;';

    if (!$db->execute($sql)) {
        return false;
    }

    $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'stancer_payment`
        ADD `live_mode` TINYINT(1) UNSIGNED NOT NULL COMMENT "Is a live mode object?" AFTER `id_order`;';

    if (!$db->execute($sql)) {
        return false;
    }

    // Patch database

    $liveKeys = array_values(Configuration::getMultiple([
        'STANCER_API_LIVE_PUBLIC_KEY',
        'STANCER_API_LIVE_SECRET_KEY',
    ]));

    if ($liveKeys) {
        $config = Stancer\Config::init($liveKeys);
        $config->setMode(Stancer\Config::LIVE_MODE);

        upgrade_modes();
        fix_payments(true);
    }

    $testKeys = array_values(Configuration::getMultiple([
        'STANCER_API_TEST_PUBLIC_KEY',
        'STANCER_API_TEST_SECRET_KEY',
    ]));

    if ($testKeys) {
        $config = Stancer\Config::init($testKeys);
        $config->setMode(Stancer\Config::TEST_MODE);

        fix_payments(false);
    }

    return true;
}

function fix_payments(bool $isProd)
{
    // We use direct db update to prevent messing with the current configuration during the migration
    $db = Db::getInstance();

    $payments = (new PrestaShopCollection('StancerApiPayment'))->where('live_mode', '=', (int) $isProd);

    /** @var StancerApiPayment $payment */
    foreach ($payments as $payment) {
        try {
            $api = $payment->getApiObject();
            $status = $api->status;
            $updates = [];

            if (!$status && $api->auth && $api->auth->status !== Stancer\Auth\Status::SUCCESS) {
                $status = Stancer\Payment\Status::FAILED;
            }

            if ($status) {
                $updates[] = '`status` = "' . pSQL($status) . '"';
            }

            if (!trim($payment->card_id) && $api->card) {
                $updates[] = '`card_id` = "' . pSQL($api->card->id) . '"';
            }

            if ($updates) {
                $sql = 'UPDATE `' . _DB_PREFIX_ . 'stancer_payment`
                    SET ' . implode(', ', $updates) . '
                    WHERE `payment_id` = "' . pSQL($api->id) . '"';

                $db->execute($sql);
            }
        } catch (Stancer\Exceptions\Exception $exception) {
            // do nothing
        }
    }
}

function upgrade_modes()
{
    // We use direct db update to prevent messing with the current configuration during the migration
    $db = Db::getInstance();

    $payments = new PrestaShopCollection('StancerApiPayment');

    /** @var StancerApiPayment $payment */
    foreach ($payments as $payment) {
        try {
            $api = $payment->getApiObject();

            if ($api->card) {
                $sql = 'UPDATE `' . _DB_PREFIX_ . 'stancer_card`
                    SET `live_mode` = 1
                    WHERE `card_id` = "' . pSQL($api->card->id) . '"';

                $db->execute($sql);
            }

            if ($api->customer) {
                $sql = 'UPDATE `' . _DB_PREFIX_ . 'stancer_customer`
                    SET `live_mode` = 1
                    WHERE `customer_id` = "' . pSQL($api->customer->id) . '"';

                $db->execute($sql);
            }

            $sql = 'UPDATE `' . _DB_PREFIX_ . 'stancer_payment`
                SET `live_mode` = 1
                WHERE `payment_id` = "' . pSQL($api->id) . '"';

            $db->execute($sql);
        } catch (Stancer\Exceptions\Exception $exception) {
            // do nothing
        }
    }
}
