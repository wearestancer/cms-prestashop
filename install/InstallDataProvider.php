<?php

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
