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

namespace Stancer\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Order;
use PrestaShop\PrestaShop\Core\Domain\Order\Command\UpdateOrderStatusCommand;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception as OrderException;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentState
{
    protected array $flashMessage;

    public function __construct(private ContainerInterface $container, private TranslatorInterface $translator)
    {
        $this->flashMessage = ['status' => 'error', 'message' => ''];
    }

    /**
     * Adds a flash message to the current session for type.
     *
     * @throws \LogicException
     *
     * @final
     */
    protected function addFlash(string $type, string $message)
    {
        $this->flashMessage = ['status' => $type, 'message' => $message];

        return $this->flashMessage;
    }

    /**
     * Function to capture our payment
     *
     * @param \Stancer\Payment $stancerPayment
     * @param int $orderId
     *
     * @return array an array with information for a flash message
     */
    public function capture(\Stancer\Payment $stancerPayment, int $orderId): array
    {
        $oldStatus = $stancerPayment->status;
        try {
            $stancerPayment->set_status(\Stancer\Payment\Status::CAPTURE);
            $stancerPayment->send();
        } catch (\Exception $e) {
            return $this->addFlash('error', $e->getMessage());
        }
        $newStatus = $stancerPayment->status;
        if ($oldStatus !== $newStatus) {
            if ($stancerPayment->is_success() && $newStatus !== \Stancer\Payment\Status::AUTHORIZED) {
                $this->addFlash('success', $this->translator->trans('The payment has been captured', [], 'Module.Stancer.Stancerorder'));
            }
            $stancerDBPayment = \StancerApiPayment::findByApiPayment($stancerPayment);
            $orderStatusId = (int) $stancerDBPayment->getOrderState();
            $this->updateStatus($orderId, $orderStatusId);
            $stancerDBPayment->save();
        }

        return $this->flashMessage;
    }

    /**
     * function to refund our payment
     *
     * @param string $paymentID
     * @param int $amount
     * @param bool $invoice_status
     * @param int $orderId
     *
     * @return array
     */
    public function refund(
        string $paymentID,
        int $amount,
        bool $invoice_status,
        int $orderId,
    ): array {
        $stancerPayment = new \Stancer\Payment($paymentID);
        try {
            $stancerPayment->refund($amount);
            $this->addFlash(
                'success',
                $this->translator->trans(
                    'Refund processed with success',
                    [],
                    'Modules.Stancer.Paymentstate'
                )
            );
            if ($invoice_status) {
                $stancerApiPayment = \StancerApiPayment::findByApiPayment($stancerPayment);
                $stancerApiPayment->save();
                $refundStatus = (int) $stancerApiPayment->getOrderState();
                if ((int) (new \Order($orderId))->getCurrentState() !== (int) $refundStatus) {
                    $this->updateStatus($orderId, $refundStatus);
                }
            }
        } catch (\Exception $e) {
            return $this->addFlash(
                'error',
                $e->getMessage()
            );
        }

        return $this->flashMessage;
    }

    /**
     * Update the status of an order
     *
     * @param int $orderId
     * @param int $orderStatusId
     *
     * @return array
     */
    private function updateStatus(int $orderId, int $orderStatusId): array
    {
        try {
            $this->container->get('prestashop.core.command_bus')->handle(
                new UpdateOrderStatusCommand(
                    $orderId,
                    $orderStatusId
                )
            );
            $this->addFlash(
                'success',
                $this->translator->trans('Successful update', [], 'Admin.Notifications.Success')
            );
        } catch (OrderException\ChangeOrderStatusException $e) {
            $this->handleChangeOrderStatusException($e);
        } catch (\Exception $e) {
            $this->addFlash(
                'error', $this->translator->trans(
                    'An unexpected error occurred. [%type% code %code%]',
                    [
                        '%type%' => $e::class,
                        '%code%' => $e->getCode(),
                    ],
                    'Admin.Notifications.Error',
                )
            );
        }

        return $this->flashMessage;
    }

    /**
     * Get the stancer Payment from the orderId
     *
     * @param int $orderId the order id
     *
     * @return \Stancer\Payment|null
     */
    public function getStancerPayment(int $orderId): ?\Stancer\Payment
    {
        $stancerDBPayment = \StancerApiPayment::findByOrderId($orderId);
        if (!$stancerDBPayment) {
            return null;
        }

        return $stancerDBPayment->getApiObject();
    }

    /**
     * Handle the Exception from the order status
     *
     * @param OrderException\ChangeOrderStatusException $e
     *
     * @return void
     */
    private function handleChangeOrderStatusException(OrderException\ChangeOrderStatusException $e)
    {
        $orderIds = array_merge(
            $e->getOrdersWithFailedToUpdateStatus(),
            $e->getOrdersWithFailedToSendEmail()
        );

        /** @var \PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId $orderId */
        foreach ($orderIds as $orderId) {
            $this->addFlash(
                'error',
                $this->translator->trans(
                    'An error occurred while changing the status for order #%d, or we were unable to send an email to the customer.',
                    ['#%d' => $orderId->getValue()],
                    'Admin.Orderscustomers.Notification',
                )
            );
        }

        foreach ($e->getOrdersWithAssignedStatus() as $orderId) {
            $this->addFlash(
                'error',
                $this->translator->trans(
                    'Order #%d has already been assigned this status.',
                    ['#%d' => $orderId->getValue()],
                    'Admin.Orderscustomers.Notification',
                )
            );
        }
    }
}
