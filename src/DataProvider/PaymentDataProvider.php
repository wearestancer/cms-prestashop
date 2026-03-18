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

use Stancer;
use Stancer\Enum\ApiVersion;

class PaymentDataProvider
{
    /**
     * Format the payment Object for display and form
     *
     * @param Stancer\Payment $payment the Stancer payment linked to the order
     *
     * @return array
     */
    public function getPaymentData(Stancer\Payment $payment): array
    {
        // We Cannot fetch refunds properly with V2 so we use V1 temporarly
        Stancer\Config::getGlobal()->setVersion(ApiVersion::VERSION_1);

        return [
            'id' => $payment->id,
            'raw_amount' => $payment->amount,
            'total_amount' => $this->formatPrice($payment->amount / 100),
            'refunded_amount' => $payment->getRefundedAmount(),
            'refunded_amount_formated' => $this->formatPrice($payment->getRefundedAmount() / 100),
            'status' => $payment->status->value,
            'refundable_amount' => $payment->getRefundableAmount(),
            'refundable_amount_formated' => $this->formatPrice($payment->getRefundableAmount() / 100),
            'card_id' => $payment->card->id,
            'method' => $payment->method,
        ];
        Stancer\Config::getGlobal()->setVersion(ApiVersion::VERSION_2);
    }

    private function formatPrice(int|float $amount): string
    {
        $context = \Context::getContext();
        if ($context === null || $context->getCurrentLocale() === null) {
            return (string) $amount;
        }

        return $context->getCurrentLocale()->formatPrice($amount, $context->currency->iso_code);
    }
}
