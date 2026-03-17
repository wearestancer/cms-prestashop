<?php
declare(strict_types=1);

namespace Stancer\DataProvider;

use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationDataProvider
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    /**
     * function to ease the use of translation on our DataStringProviders.
     *
     * @param string $message
     * @param array $params
     * @param string $domain
     * @param string|null $locale
     *
     * @return void
     */
    private function trans(string $message, array $params, string $domain, ?string $locale = null)
    {
        return $this->translator->trans($message, $params, $domain, $locale);
    }

    public function getOrderLabelTranslation(): array
    {
        return [
            'amount' => $this->trans('Amount', [], 'Modules.Stancer.Translationdataprovider'),
            'amount_paid' => $this->trans('Amount paid:', [], 'Modules.Stancer.Translationdataprovider'),
            'amount_refunded' => $this->trans('Amount refunded:', [], 'Modules.Stancer.Translationdataprovider'),
            'amount_remaining' => $this->trans('Amount remaining:', [], 'Modules.Stancer.Translationdataprovider'),
            'card_brand' => $this->trans('Card brand:', [], 'Modules.Stancer.Translationdataprovider'),
            'card_details' => $this->trans('Card details:', [], 'Modules.Stancer.Translationdataprovider'),
            'card_expirationdate' => $this->trans('Expiration date:', [], 'Modules.Stancer.Translationdataprovider'),
            'card_lastfour' => $this->trans('Last four numbers:', [], 'Modules.Stancer.Translationdataprovider'),
            'payment_id' => $this->trans('Payment ID:', [], 'Modules.Stancer.Translationdataprovider'),
            'refund_title' => $this->trans('Payment refund', [], 'Modules.Stancer.Translationdataprovider'),
            'stancer_payment' => $this->trans('Stancer payment', [], 'Modules.stancer.Translationdataprovider'),
            'status' => $this->trans('Status', [], 'Modules.Stancer.Translationdataprovider'),
            'transaction_details' => $this->trans('Transaction details:', [], 'Modules.Stancer.Translationdataprovider'),
            'payment_canceled' => $this->trans('Payment has been canceled', [], 'Modules.Stancer.Translationdataprovider'),
        ];
    }
}
