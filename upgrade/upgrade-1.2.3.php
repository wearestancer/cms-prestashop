<?php

/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2023-2025 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 *
 * @website   https://www.stancer.com
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_2_3(Stancer $module): bool
{
    if (!$module->installConfigurations()) {
        return false;
    }
    $module->unregisterHook('header');
    $module->unregisterExceptions(Hook::getIdByName('header'));
    $module->registerHook('displayHeader');

    return true;
}
