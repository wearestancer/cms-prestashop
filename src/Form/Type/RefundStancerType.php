<?php

namespace Stancer\Form\Type;

use PrestaShopBundle\Form\Admin\Type\AmountCurrencyType;
use PrestaShopBundle\Translation\TranslatorInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class RefundStancerType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator,
    ) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('refund_amount', AmountCurrencyType::class,
                [
                    'amount_constraints' => [
                        new GreaterThanOrEqual([
                            'value' => '0',
                        ]),
                        new LessThanOrEqual([
                            'value' => $options['data']['raw_amount'] / 100,
                            'message' => $this->translator->trans('Invalid value: you cannot refund more than the remaining amount'),
                        ]),
                    ],
                    'currencies' => [
                        'EUR' => '€',
                    ],
                    'data' => (string) $options['data']['raw_amount'] / 100,
                    'attr' => ['type' => 'coucou', 'placeholder' => $options['data']['raw_amount'] / 100, 'value' => $options['data']['raw_amount'] / 100],
                ])
            ->add('change_invoice_status', CheckboxType::class,
                [
                    'label' => $this->translator->trans('change the invoice status to refunded'),
                    'required' => false,
                ])
            ->add('refund', SubmitType::class,
                [
                    'label' => $this->translator->trans('refund the payment'),
                    'attr' => [
                        'class' => 'btn btn-primary',
                        'data_payment_id' => $options['data']['id'],
                        'data_amount' => $options['data']['raw_amount'] / 100,
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
