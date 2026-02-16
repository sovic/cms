<?php

namespace Sovic\Cms\Form\Admin;

use Sovic\Common\Project\SettingGroupId;
use Sovic\Cms\Form\FormTheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class Settings extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod('POST');

        $builder->add('groupId', EnumType::class, [
            'label' => 'Skupina',
            'class' => SettingGroupId::class,
            'disabled' => true,
            'choices' => SettingGroupId::cases(),
            'choice_label' => function (SettingGroupId $choice) {
                return $choice->trans();
            },
            'attr' => [
                'readonly' => true,
            ],
        ]);

        $builder->add('description', TextareaType::class, [
            'label' => 'Popis',
            'disabled' => true,
            'attr' => [
                'length' => 255,
                'readonly' => true,
                'rows' => 5,
            ],
        ]);

        $builder->add('key', TextType::class, [
            'label' => 'Klíč',
            'disabled' => true,
            'attr' => [
                'length' => 100,
                'readonly' => true,
            ],
        ]);

        $builder->add('value', TextareaType::class, [
            'label' => 'Hodnota',
            'required' => true,
            'attr' => [
                'length' => 65535,
                'rows' => 10,
            ],
        ]);

        $builder->add('submit', SubmitType::class, [
            'label' => 'Uložit změny',
            'attr' => [
                'class' => FormTheme::BtnSubmitClass,
            ],
        ]);
    }
}
