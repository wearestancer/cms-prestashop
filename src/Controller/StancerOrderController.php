<?php

namespace Stancer\Controller;

require_once _PS_ROOT_DIR_ . '/modules/stancer/stancer.php';

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Stancer;
use Stancer\Form\Type\CaptureStancerType;
use Stancer\Form\Type\RefundStancerType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StancerOrderController extends FrameworkBundleAdminController
{
    public function displayOrderDashboard(array $params): ?string
    {
        $stancerModule = new \Stancer();

        if (!$stancerModule->isAvailable() || !$params['id_order'] || $params['route'] !== 'admin_orders_view') {
            return null;
        }
        $stancerForDisplay = $this->getPaymentForDisplay($params['id_order']);
        $formFactory = $this->get('form.factory');
        $refundStancerForm = $formFactory->create(RefundStancerType::class, [
            ...$stancerForDisplay,
            'action' => $this->generateUrl('stancer_order_refund')], []
        );
        $captureStancerForm = $formFactory->create(CaptureStancerType::class, $stancerForDisplay, []);

        return $this->get('twig')->render(
            '@Modules/stancer/views/templates/admin/order/stancer_payment.html.twig',
            [
                'stancerForDisplay' => $stancerForDisplay,
                'refundForm' => $refundStancerForm->createView(),
                'captureForm' => $captureStancerForm->createView(),
            ]
        );
    }

    public function getPaymentForDisplay($id)
    {
        $stancerDBPayment = \StancerApiPayment::findByOrderId($id);
        $stancerAPIPayment = $stancerDBPayment->getApiObject();
        $stancerAPIPayment->populate();
        $stancerCard = new Stancer\Card($stancerDBPayment->card_id);
        $stancerCard->populate();

        return [
            'id' => $stancerAPIPayment->id,
            'raw_amount' => $stancerAPIPayment->amount,
            'total_amount' => $stancerAPIPayment->amount,

            'status' => $stancerAPIPayment->status,
            'brand' => $stancerCard->brand,
            'last_four' => $stancerCard->last4,
            'expiration_date' => $stancerCard->exp_month . '/' . $stancerCard->exp_year,
            'method' => $stancerAPIPayment->method,
        ];
    }

    public function createRefund(Request $request)
    {
        $stancerAPi = new \StancerApi();
        $response = new Response(json_encode($request->request->all()));

        return $response;
    }
}
