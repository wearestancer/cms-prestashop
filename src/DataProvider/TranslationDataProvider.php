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
declare(strict_types=1);

namespace Stancer\DataProvider;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationDataProvider
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    /**
     * Get all the translation used in a twig context.
     *
     * @return array
     */
    public function getOrderLabelTranslation(): array
    {
        return [
            'amount' => $this->translator->trans('Amount', [], 'Modules.Stancer.Translationdataprovider'),
            'amount_paid' => $this->translator->trans('Amount paid:', [], 'Modules.Stancer.Translationdataprovider'),
            'amount_refunded' => $this->translator->trans('Amount refunded:', [], 'Modules.Stancer.Translationdataprovider'),
            'amount_remaining' => $this->translator->trans('Amount remaining:', [], 'Modules.Stancer.Translationdataprovider'),
            'card_brand' => $this->translator->trans('Card brand:', [], 'Modules.Stancer.Translationdataprovider'),
            'card_details' => $this->translator->trans('Card details:', [], 'Modules.Stancer.Translationdataprovider'),
            'card_expirationdate' => $this->translator->trans('Expiration date:', [], 'Modules.Stancer.Translationdataprovider'),
            'card_lastfour' => $this->translator->trans('Last four numbers:', [], 'Modules.Stancer.Translationdataprovider'),
            'payment_id' => $this->translator->trans('Payment ID:', [], 'Modules.Stancer.Translationdataprovider'),
            'refund_title' => $this->translator->trans('Payment refund', [], 'Modules.Stancer.Translationdataprovider'),
            'stancer_payment' => $this->translator->trans('Stancer payment', [], 'Modules.stancer.Translationdataprovider'),
            'status' => $this->translator->trans('Status', [], 'Modules.Stancer.Translationdataprovider'),
            'transaction_details' => $this->translator->trans('Transaction details:', [], 'Modules.Stancer.Translationdataprovider'),
            'payment_canceled' => $this->translator->trans('Payment has been canceled', [], 'Modules.Stancer.Translationdataprovider'),
        ];
    }
}
