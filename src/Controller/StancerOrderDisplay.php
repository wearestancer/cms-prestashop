<?php
declare(strict_types=1);

namespace Stancer\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Stancer\DataProvider\CardDataProvider;
use Stancer\DataProvider\PaymentDataProvider;
use Stancer\DataProvider\TranslationDataProvider;
use Stancer\Form\Type\CaptureStancerType;
use Stancer\Form\Type\RefundStancerType;
use Stancer\Service\PaymentState;
use Symfony\Component\Form\FormFactoryInterface;

class StancerOrderDisplay extends FrameworkBundleAdminController
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private \Twig\Environment $twig,
        private PaymentState $paymentState,
        private PaymentDataProvider $paymentDataProvider,
        private CardDataProvider $cardDataProvider,
        private TranslationDataProvider $translationDataProvider)
    {
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
        $stancerPayment = $this->paymentState->getStancerPayment($orderId);
        if (!$stancerPayment) {
            return null;
        }

        $paymentData = $this->paymentDataProvider->getPaymentData($stancerPayment);
        $cardData = $this->cardDataProvider->getCardData($stancerPayment->card);
        $stancerForDisplay = [...$paymentData, ...$cardData];

        $refundStancerForm = $this->formFactory->createNamed(
            'refund_stancer_payment',
            RefundStancerType::class,
            [
                ...$paymentData,
                'action' => $this->generateUrl('stancer_order_refund', ['orderId' => $orderId]),
            ],
            [],
        );
        $captureStancerForm = $this->formFactory->createNamed(
            'capture_stancer_payment',
            CaptureStancerType::class,
            [
                ...$paymentData,
                'action' => $this->generateUrl('stancer_order_capture', ['orderId' => $orderId]),
            ],
            [],
        );

        return $this->twig->render(
            '@Modules/stancer/views/templates/admin/order/stancer_order.html.twig',
            [
                'stancerForDisplay' => $stancerForDisplay,
                'translatedLabel' => $this->translationDataProvider->getOrderLabelTranslation(),
                'refundForm' => $refundStancerForm->createView(),
                'captureForm' => $captureStancerForm->createView(),
            ]
        );
    }
}
