<?php
declare(strict_types=1);

namespace Stancer\DataProvider;

class CardDataProvider
{
    /**
     * Format the card Object for display and form
     *
     * @param Stancer\Card $stancerCard the Stancer card linked to the order
     *
     * @return array
     */
    public function getCardData(\Stancer\Card $stancerCard): array
    {
        if (!$stancerCard) {
            return [];
        }

        return [
            'brand' => $stancerCard->brand,
            'last_four' => $stancerCard->last4,
            'expiration_date' => $stancerCard->exp_month . '/' . $stancerCard->exp_year,
        ];
    }
}
