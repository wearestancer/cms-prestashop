<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2023-2024 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 *
 * @website   https://www.stancer.com
 *
 * @version   1.2.1
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($module)
{
    if (!$module->installConfigurations()) {
        return false;
    }

    $mode = Configuration::get('STANCER_API_MODE');

    if (!in_array($mode, [Stancer\Config::TEST_MODE, Stancer\Config::LIVE_MODE], true)) {
        Configuration::updateValue('STANCER_API_MODE', $mode ? Stancer\Config::LIVE_MODE : Stancer\Config::TEST_MODE);
    }

    return true;
}
