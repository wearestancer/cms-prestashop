<?php

namespace Stancer\Controller;

require_once _PS_ROOT_DIR_ . '/modules/stancer/stancer.php';

use PrestaShop\PrestaShop\Core\Domain\Order\Command\UpdateOrderStatusCommand;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\ChangeOrderStatusException;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Stancer;
use Stancer\Form\Type\CaptureStancerType;
use Stancer\Form\Type\RefundStancerType;
use Symfony\Component\HttpFoundation\Request;

class StancerOrderController extends FrameworkBundleAdminController
{
    public function __construct()
    {
        return parent::__construct();
    }

    /**
     * Capture an autorized payment
     *
     * @param int $orderId
     * @param Request $request
     *
     * @return void
     */
    public function capturePayment(int $orderId, Request $request)
    {
        new \StancerApi();
        $stancerPayment = $this->getStancerPayment($orderId);
        $oldStatus = $stancerPayment->status;
        $formFactory = $this->get('form.factory');
        $form = $formFactory->createNamed(
            'capture_stancer_payment',
            CaptureStancerType::class,
            [...$this->getPaymentData($stancerPayment), 'action' => ''],
            []
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $stancerPayment->set_status(Stancer\Payment\Status::CAPTURE);
                $stancerPayment->send();
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->redirectToRoute('admin_orders_view', ['orderId' => $orderId]);
            }
            $newStatus = $stancerPayment->status;
            if ($oldStatus !== $newStatus) {
                if ($stancerPayment->is_success() && $newStatus !== Stancer\Payment\Status::AUTHORIZED) {
                    $this->addFlash('success', $this->trans('The payment has been successfully captured', 'Stancer.Invoice'));
                }
                $stancerDBPayment = \StancerApiPayment::findByApiPayment($stancerPayment);
                $orderStatusId = (int) $stancerDBPayment->getOrderState();
                $this->updateStatus($orderId, $orderStatusId);
                $stancerDBPayment->save();
            }
        }
        foreach ($form->getErrors(true, true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }

        return $this->redirectToRoute('admin_orders_view', ['orderId' => $orderId]);
    }

    /**
     * Create a stancer refund
     *
     * @param int $orderId
     * @param Request $request
     *
     * @return void
     */
    public function createRefund(int $orderId, Request $request)
    {
        new \StancerApi();
        $stancerPayment = $this->getStancerPayment($orderId);
        $formFactory = $this->get('form.factory');
        $form = $formFactory->createNamed(
            'refund_stancer_payment',
            RefundStancerType::class,
            [...$this->getPaymentData($stancerPayment, $orderId), 'action' => ''],
            [],
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $payment_id = $data['payment_id'];
            $refundAmount = $data['amount'] * 100;
            $stancerPayment = new Stancer\Payment($payment_id);
            try {
                $stancerPayment->refund($refundAmount);
                $this->addFlash('success', $this->trans('Refund Sucessfull', 'Stancer.Invoice'));
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
            if ($data['change_invoice_status']) {
                $refundStatus = \Configuration::get('PS_OS_REFUND');
                $this->updateStatus($orderId, $refundStatus);
            }
        }
        foreach ($form->getErrors(true, true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }

        return $this->redirectToRoute('admin_orders_view', ['orderId' => $orderId]);
    }

    /**
     * display the stancer payment widget (from stancer Hook)
     *
     * @param array $params
     *
     * @return string|null
     */
    public function displayOrderDashboard(array $params): ?string
    {
        $stancerModule = new \Stancer();

        $orderId = $params['id_order'];
        if (!$stancerModule->isAvailable() || !$orderId || $params['route'] !== 'admin_orders_view') {
            return null;
        }
        $stancerPayment = $this->getStancerPayment($orderId);
        if (!$stancerPayment) {
            return null;
        }
        $paymentData = $this->getPaymentData($stancerPayment, $orderId);
        $cardData = $this->getCardData($stancerPayment->card);
        $stancerForDisplay = [...$paymentData, ...$cardData];
        $formFactory = $this->get('form.factory');

        $refundStancerForm = $formFactory->createNamed(
            'refund_stancer_payment',
            RefundStancerType::class,
            [
                ...$paymentData,
                'action' => $this->generateUrl('stancer_order_refund', ['orderId' => $orderId]),
            ],
            [],
        );
        $captureStancerForm = $formFactory->createNamed(
            'capture_stancer_payment',
            CaptureStancerType::class,
            [
                ...$paymentData,
                'action' => $this->generateUrl('stancer_order_capture', ['orderId' => $orderId]),
            ],
            [],
        );

        return $this->get('twig')->render(
            '@Modules/stancer/views/templates/admin/order/stancer_payment.html.twig',
            [
                'stancerForDisplay' => $stancerForDisplay,
                'refundForm' => $refundStancerForm->createView(),
                'captureForm' => $captureStancerForm->createView(),
            ]
        );
    }

    /**
     * Format the card Object for display and form
     *
     * @param Stancer\Card $stancerCard the Stancer card linked to the order
     *
     * @return array
     */
    private function getCardData(Stancer\Card $stancerCard): array
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

    /**
     * Get the stancer Payment from the orderId
     *
     * @param int $orderId the order id
     *
     * @return Stancer\Payment|null
     */
    private function getStancerPayment(int $orderId): ?Stancer\Payment
    {
        $stancerDBPayment = \StancerApiPayment::findByOrderId($orderId);
        if (!$stancerDBPayment) {
            return null;
        }

        return $stancerDBPayment->getApiObject();
    }

    /**
     * Format the payment Object for display and form
     *
     * @param Stancer\Payment $payment the Stancer payment linked to the order
     *
     * @return array
     */
    private function getPaymentData(Stancer\Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'raw_amount' => $payment->amount,
            'total_amount' => $this->formatPrice($payment->amount / 100),
            'refunded_amount' => $payment->getRefundedAmount(),
            'refunded_amount_formated' => $this->formatPrice($payment->getRefundedAmount() / 100),
            'status' => $payment->status,
            'refundable_amount' => $payment->getRefundableAmount(),
            'refundable_amount_formated' => $this->formatPrice($payment->getRefundableAmount() / 100),
            'card_id' => $payment->card->id,
            'method' => $payment->method,
        ];
    }

    /**
     * Return a formated and readable price
     *
     * @param int|float $amount
     *
     * @return string a formated price
     */
    private function formatPrice(int|float $amount): string
    {
        return $this->getContextLocale()->formatPrice($amount, $this->getContextCurrencyIso());
    }

    /**
     * Handle the Exception from the order status
     *
     * @param ChangeOrderStatusException $e
     *
     * @return void
     */
    private function handleChangeOrderStatusException(ChangeOrderStatusException $e)
    {
        $orderIds = array_merge(
            $e->getOrdersWithFailedToUpdateStatus(),
            $e->getOrdersWithFailedToSendEmail()
        );

        /** @var OrderId $orderId */
        foreach ($orderIds as $orderId) {
            $this->addFlash(
                'error',
                $this->trans(
                    'An error occurred while changing the status for order #%d, or we were unable to send an email to the customer.',
                    'Admin.Orderscustomers.Notification',
                    ['#%d' => $orderId->getValue()]
                )
            );
        }

        foreach ($e->getOrdersWithAssignedStatus() as $orderId) {
            $this->addFlash(
                'error',
                $this->trans(
                    'Order #%d has already been assigned this status.',
                    'Admin.Orderscustomers.Notification',
                    ['#%d' => $orderId->getValue()]
                )
            );
        }
    }

    /**
     * Update the satus of the order
     *
     * @param int $orderId
     * @param int $orderStatusId
     *
     * @return void
     */
    private function updateStatus(int $orderId, int $orderStatusId): void
    {
        try {
            $this->getCommandBus()->handle(
                new UpdateOrderStatusCommand(
                    $orderId,
                    $orderStatusId
                )
            );
            $this->addFlash('success', $this->trans('Successful update', 'Admin.Notifications.Success'));
        } catch (ChangeOrderStatusException $e) {
            $this->handleChangeOrderStatusException($e);
        } catch (\Exception $e) {
            $this->addFlash('error', $this->getFallbackErrorMessage(
                $e::class,
                $e->getMessage(),
                $e->getMessage()
            ));
        }
    }
}
