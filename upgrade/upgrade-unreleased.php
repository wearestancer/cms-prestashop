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

function upgrade_module_unreleased(Stancer $module): bool
{
    Configuration::deleteByName('STANCER_AUTH_LIMIT');

    return true;
}
