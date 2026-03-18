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

namespace Stancer\Form\Type;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Form\Admin\Type\AmountCurrencyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Contracts\Translation\TranslatorInterface;

class RefundStancerType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator,
    ) {
        $this->translator = $translator;
    }

    /**
     * Build the Stancer refund form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('refund_amount', AmountCurrencyType::class,
                [
                    'label' => $this->translator->trans('Amount', [], 'Modules.Stancer.Refundstancer'),
                    'amount_constraints' => [
                        new GreaterThan([
                            'value' => '0',
                            'message' => $this->translator->trans('Invalid amount: the value must be positive', [], 'Modules.Stancer.Refundstancertype'),
                        ]),
                        new LessThanOrEqual([
                            'value' => $options['data']['refundable_amount'] / 100,
                            'message' => $this->translator->trans(
                                'Invalid amount: you cannot refund more than the remaining amount (%refundable_amount%)',
                                ['%refundable_amount%' => $options['data']['refundable_amount_formated']],
                                'Modules.Stancer.Refundstancertype'),
                        ]),
                    ],
                    'currencies' => [
                        'EUR' => '€',
                    ],
                ])
            ->add('change_invoice_status', CheckboxType::class,
                [
                    'label' => $this->translator->trans('Set the invoice status to "Refunded"', [], 'Modules.Stancer.Refundstancertype'),
                    'required' => false,
                    'data' => true,
                ])
            ->add('refund', SubmitType::class,
                [
                    'label' => $this->translator->trans('Refund the payment', [], 'Modules.Stancer.Refundstancertype'),
                    'attr' => [
                        'class' => 'btn btn-primary',
                        'data-payment_id' => $options['data']['id'],
                        'data-amount' => $options['data']['raw_amount'] / 100,
                        'data-refundable' => $options['data']['refundable_amount'] / 100,
                    ],
                ]
            )
            ->add('payment_id', HiddenType::class,
                [
                    'data' => $options['data']['id'],
                ])
            ->setAction($options['data']['action']);
    }
}
