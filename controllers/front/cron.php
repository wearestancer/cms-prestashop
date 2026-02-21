<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2018-2025 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 *
 * @website   https://www.stancer.com
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Front controller for the Stancer payment reconciliation cron job.
 *
 * Stancer does not support webhooks. This controller polls the Stancer API for
 * payments still recorded as "pending" locally and creates the corresponding
 * PrestaShop order when a payment has been captured.
 *
 * Trigger URL (call every 15 minutes from your server cron):
 *   https://example.com/module/stancer/cron?token=YOUR_TOKEN
 *
 * The token is generated automatically on module installation and stored in
 * the PrestaShop configuration under the key STANCER_CRON_TOKEN.
 *
 * Example crontab entry:
 *   *\/15 * * * * curl -s "https://example.com/module/stancer/cron?token=TOKEN" > /dev/null
 */
class StancerCronModuleFrontController extends ModuleFrontController
{
    /**
     * Minimum payment age in minutes before it is eligible for reconciliation.
     *
     * Allows in-progress redirect flows to complete before the cron intervenes.
     */
    const THRESHOLD_MINUTES = 15;

    /** @var Stancer */
    public $module;

    /**
     * Process the reconciliation request.
     *
     * @return void
     */
    public function postProcess(): void
    {
        $this->validateToken();

        $rows = $this->fetchPendingPayments();

        $reconciled = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $row) {
            try {
                $result = $this->processRow($row);

                if (true === $result) {
                    ++$reconciled;
                } else {
                    ++$skipped;
                }
            } catch (Exception $e) {
                $errors[] = sprintf('%s: %s', $row['payment_id'], $e->getMessage());

                PrestaShopLogger::addLog(
                    sprintf(
                        'Stancer cron error for payment %s: %s',
                        $row['payment_id'],
                        $e->getMessage()
                    ),
                    3,
                    null,
                    'StancerCronModuleFrontController',
                    (int) $row['id_stancer_payment']
                );
            }
        }

        $this->sendJson([
            'processed' => count($rows),
            'reconciled' => $reconciled,
            'skipped' => $skipped,
            'errors' => $errors,
        ]);
    }

    /**
     * Validate the security token from the query string.
     *
     * Terminates with HTTP 403 if the token is missing or does not match.
     *
     * @return void
     */
    protected function validateToken(): void
    {
        $token = Tools::getValue('token');
        $expected = Configuration::get('STANCER_CRON_TOKEN');

        if (!$token || !$expected || !hash_equals((string) $expected, (string) $token)) {
            $this->sendJson(['error' => 'Unauthorized'], 403);
        }
    }

    /**
     * Fetch pending payments older than THRESHOLD_MINUTES without an associated order.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function fetchPendingPayments(): array
    {
        $result = Db::getInstance()->executeS(implode(' ', [
            'SELECT *',
            'FROM `' . _DB_PREFIX_ . 'stancer_payment`',
            'WHERE `status` = "pending"',
            'AND (`id_order` IS NULL OR `id_order` = 0)',
            'AND `date_add` <= DATE_SUB(NOW(), INTERVAL ' . (int) static::THRESHOLD_MINUTES . ' MINUTE)',
        ]));

        return is_array($result) ? $result : [];
    }

    /**
     * Process a single pending payment row.
     *
     * @param array<string, mixed> $row Database row from ps_stancer_payment.
     *
     * @return bool|null True if reconciled, null if skipped (no status yet or intermediate).
     */
    protected function processRow(array $row): ?bool
    {
        $payment = new StancerApiPayment((int) $row['id_stancer_payment']);
        $apiPayment = $payment->getApiObject();
        $statusRaw = $apiPayment->getStatus();

        // No status yet — wait for next run.
        if (!$statusRaw) {
            return null;
        }

        // Normalize to string — compatible with both enum (PHP 8.1+) and plain string.
        $status = $statusRaw instanceof Stancer\Payment\Status
            ? $statusRaw->value
            : (string) $statusRaw;

        // Update the local record with the real API status.
        $payment->status = $status;
        $payment->save();

        switch ($status) {
            case Stancer\Payment\Status::TO_CAPTURE->value:
            case Stancer\Payment\Status::CAPTURED->value:
                // These two statuses map to PS_OS_PAYMENT in StancerApiPayment::getOrderState().
                // CAPTURE and CAPTURE_SENT are intermediate states — we wait for the next run
                // when the status will have progressed to to_capture or captured.
                return $this->createOrder($payment, $apiPayment);

            case Stancer\Payment\Status::REFUSED->value:
            case Stancer\Payment\Status::FAILED->value:
            case Stancer\Payment\Status::CANCELED->value:
            case Stancer\Payment\Status::EXPIRED->value:
                PrestaShopLogger::addLog(
                    sprintf(
                        'Stancer cron: payment %s status is "%s" — cart %d will not generate an order.',
                        $row['payment_id'],
                        $status,
                        (int) $row['id_cart']
                    ),
                    2,
                    null,
                    'StancerCronModuleFrontController',
                    (int) $row['id_stancer_payment']
                );

                return true; // Processed — no order will be created for this payment.
        }

        // Intermediate (capture, capture_sent, authorize) or unknown status — wait for next run.
        return null;
    }

    /**
     * Create a PrestaShop order for a captured payment.
     *
     * @param StancerApiPayment $payment    Local payment record.
     * @param Stancer\Payment   $apiPayment Stancer API payment object.
     *
     * @return bool True if the order was created successfully.
     */
    protected function createOrder(StancerApiPayment $payment, Stancer\Payment $apiPayment): bool
    {
        $cart = new Cart((int) $payment->id_cart);

        if (!Validate::isLoadedObject($cart)) {
            PrestaShopLogger::addLog(
                sprintf(
                    'Stancer cron: cart %d not found for payment %s.',
                    (int) $payment->id_cart,
                    $payment->payment_id
                ),
                3,
                null,
                'StancerCronModuleFrontController',
                (int) $payment->id
            );

            return false;
        }

        $orderState = $payment->getOrderState();

        if (!$orderState) {
            return false;
        }

        $this->module->validateOrder(
            $cart->id,
            (int) $orderState,
            $apiPayment->getAmount() / 100,
            $this->module->displayName,
            $this->buildOrderMessage($apiPayment),
            ['transaction_id' => $apiPayment->getId()],
            (int) $cart->id_currency,
            false,
            $cart->secure_key
        );

        if (!$this->module->currentOrder) {
            return false;
        }

        $payment->id_order = (int) $this->module->currentOrder;
        $payment->save();

        PrestaShopLogger::addLog(
            sprintf(
                'Stancer cron: payment %s confirmed, order %d created (cart %d).',
                $payment->payment_id,
                (int) $this->module->currentOrder,
                $cart->id
            ),
            1,
            null,
            'StancerCronModuleFrontController',
            (int) $payment->id
        );

        return true;
    }

    /**
     * Build a short order message for a reconciled payment.
     *
     * @param Stancer\Payment $apiPayment
     *
     * @return string
     */
    protected function buildOrderMessage(Stancer\Payment $apiPayment): string
    {
        return trim(implode("\n", [
            'Transaction',
            $apiPayment->getId(),
            '',
            sprintf('Amount: %.02f %s', $apiPayment->getAmount() / 100, strtoupper((string) $apiPayment->getCurrency())),
            '',
            'Reconciled via Stancer cron job.',
        ]));
    }

    /**
     * Send a JSON response and terminate.
     *
     * @param array<string, mixed> $data
     * @param int                  $statusCode HTTP status code.
     *
     * @return never
     */
    protected function sendJson(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        die(json_encode($data));
    }
}
