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
if (!defined('_PS_VERSION_')) {
    exit;
}

class InstallDataProvider
{
    // Right now i don't know how to save the strings properly in our database without this trick
    public static function getOrderStatusTranslation(): array
    {
        return [
            'fr' => 'Paiement autorisé',
            'en' => 'Authorized payment',
            'it' => 'Pagamento autorizzato',
        ];
    }
}
