<?php
declare(strict_types=1);

namespace Stancer\DataProvider;

class PaymentDataProvider
{
    /**
     * Format the payment Object for display and form
     *
     * @param Stancer\Payment $payment the Stancer payment linked to the order
     *
     * @return array
     */
    public function getPaymentData(\Stancer\Payment $payment): array
    {
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
