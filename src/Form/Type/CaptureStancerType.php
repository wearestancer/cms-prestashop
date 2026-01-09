<?php

namespace Stancer\Form\Type;

use PrestaShopBundle\Translation\TranslatorInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class CaptureStancerType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator,
    ) {
        $this->translator = $translator;
    }

    /**
     * Build form for the Stancer capture
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('capture', SubmitType::class,
                [
                    'label' => $this->translator->trans('Capture the payment'),
                    'attr' => ['class' => 'btn btn-primary'],
                ]
            )
            ->setAction($options['data']['action']);
    }
}
