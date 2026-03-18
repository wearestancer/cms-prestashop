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

namespace Stancer\Controller;

require_once _PS_ROOT_DIR_ . '/modules/stancer/stancer.php';

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Stancer\DataProvider\PaymentDataProvider;
use Stancer\Form\Type\CaptureStancerType;
use Stancer\Form\Type\RefundStancerType;
use Stancer\Service\PaymentState;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class StancerOrderController extends FrameworkBundleAdminController
{
    public function __construct(
        private PaymentState $paymentState,
        private PaymentDataProvider $paymentDataProvider,
        private FormFactoryInterface $formFactory,
    ) {
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

        $stancerPayment = $this->paymentState->getStancerPayment($orderId);

        $form = $this->formFactory->createNamed(
            'capture_stancer_payment',
            CaptureStancerType::class,
            [...$this->paymentDataProvider->getPaymentData($stancerPayment), 'action' => ''],
            []
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $flashMessage = $this->paymentState->capture($stancerPayment, $orderId);
            $this->addFlash($flashMessage['status'], $flashMessage['sucess']);
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

        $stancerPayment = $this->paymentState->getStancerPayment($orderId);
        $form = $this->formFactory->createNamed(
            'refund_stancer_payment',
            RefundStancerType::class,
            [...$this->paymentDataProvider->getPaymentData($stancerPayment), 'action' => ''],
            [],
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $flashMessage = $this->paymentState->refund(
                $data['payment_id'],
                (int) (string) ($data['amount'] * 100),
                $data['change_invoice_status'],
                $orderId
            );
            $this->addFlash($flashMessage['status'], $flashMessage['message']);
        }
        foreach ($form->getErrors(true, true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }

        return $this->redirectToRoute('admin_orders_view', ['orderId' => $orderId]);
    }
}
