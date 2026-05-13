<?php

namespace Sovic\Cms\Form\Admin;

use Sovic\Cms\Form\FormTheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailSettingsBrandingForm extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod('POST');

        $builder->add(
            'primary_color',
            ColorType::class,
            [
                'label' => 'Primární barva',
                'required' => false,
                'attr' => [
                    'length' => 7,
                ],
            ]
        );

        $builder->add(
            'secondary_color',
            ColorType::class,
            [
                'label' => 'Sekundární barva',
                'required' => false,
                'attr' => [
                    'length' => 7,
                ],
            ]
        );

        $builder->add('save', SubmitType::class, [
            'label' => 'Uložit změny',
            'attr' => [
                'class' => FormTheme::BtnSubmitClass,
            ],
        ]);
    }
}
