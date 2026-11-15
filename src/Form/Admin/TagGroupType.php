<?php

namespace Sovic\Cms\Form\Admin;

use Sovic\Cms\Form\FormTheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TagGroupType extends AbstractType
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
                    'maxlength' => 100,
                ],
            ]
        );

        $builder->add(
            'urlId',
            TextType::class,
            [
                'label' => 'URL identifikátor',
                'required' => false,
                'help' => 'Nechte prázdné pro automatické vygenerování z názvu.',
                'attr' => [
                    'maxlength' => 100,
                ],
            ]
        );

        $builder->add(
            'position',
            IntegerType::class,
            [
                'label' => 'Pořadí',
                'required' => false,
                'empty_data' => '0',
                'attr' => [
                    'min' => 0,
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
