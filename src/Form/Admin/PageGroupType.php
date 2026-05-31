<?php

namespace Sovic\Cms\Form\Admin;

use Sovic\Cms\Form\FormTheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PageGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod('POST');

        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'Název',
                'required' => true,
                'attr' => [
                    'length' => 255,
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
