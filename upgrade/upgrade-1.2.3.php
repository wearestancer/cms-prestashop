<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2023-2024 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 *
 * @website   https://www.stancer.com
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_2_3($module)
{
    if (!$module->installConfigurations()) {
        return false;
    }
    $module->unregisterHook('header');
    $module->unregisterExceptions('header');
    $module->registerHook('displayHeader');

    return true;
}
