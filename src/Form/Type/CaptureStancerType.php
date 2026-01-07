<?php

namespace Stancer\Form\Type;

use PrestaShopBundle\Translation\TranslatorInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;

class CaptureStancerType extends AbstractType
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
            ->add('capture', ButtonType::class,
                [
                    'label' => $this->translator->trans('Capture the authorized payment'),
                    'attr' => ['class' => 'btn btn-primary'],
                ]
            );
    }
}
