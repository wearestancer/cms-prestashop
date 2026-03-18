<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2026 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 *
 * @website   https://www.stancer.com
 *
 * @version   2.0.3
 */

require_once _PS_ROOT_DIR_ . '/modules/stancer/install/InstallDataProvider.php';

class DbUpgrader
{
    public static function upgradeDbAuthorizeStatus(Stancer $module): bool
    {
        $return = true;
        $db = Db::getInstance();
        $return &= $db->insert('order_state', ['invoice' => 1, 'send_email' => 1, 'module_name' => $module->name, 'color' => '#1450c5', 'unremovable' => true]);
        $orderStateId = $db->Insert_ID();
        $orderStatusTranslation = InstallDataProvider::getOrderStatusTranslation();
        foreach ($module->languages as $language) {
            $return &= $db->insert('order_state_lang',
                [
                    'id_order_state' => $orderStateId,
                    'id_lang' => $language['id_lang'],
                    'name' => $orderStatusTranslation[$language['iso_code']] ?? $orderStatusTranslation['en'],
                    'template' => 'payment',
                ]
            );
        }
        $return &= $db->insert('configuration', ['name' => 'PS_STANCER_AUTHORIZE', 'value' => $orderStateId]);

        return $return;
    }
}
