<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2023 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 * @website   https://www.stancer.com
 * @version   1.1.0
 */

function upgrade_module_1_1_0($module)
{
    return $module->installConfigurations();
}
