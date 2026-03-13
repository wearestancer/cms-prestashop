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
require_once _PS_ROOT_DIR_ . '/modules/stancer/install/dbUpgrader.php';
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_0_3(Stancer $module): bool
{
    if (!$module->installConfigurations()) {
        return false;
    }
    $module->registerHook('displayAdminOrderSide');
    Configuration::deleteByName('STANCER_AUTH_LIMIT');

    return DbUpgrader::upgradeDbAuthorizeStatus($module);
}
