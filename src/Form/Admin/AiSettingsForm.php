<?php

namespace Sovic\Cms\Form\Admin;

use Sovic\Cms\Form\FormTheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class AiSettingsForm extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'default_model' => '',
        ]);
        $resolver->setAllowedTypes('default_model', 'string');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod('POST');

        $builder->add(
            'api_key',
            PasswordType::class,
            [
                'label' => 'Anthropic API klíč',
                'required' => false,
                'always_empty' => true,
                'constraints' => [
                    new Length(max: 255),
                ],
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'sk-ant-...',
                ],
                'help' => 'Klíč se ukládá šifrovaně. Ponechte prázdné, pokud jej nechcete měnit.',
            ]
        );

        $builder->add(
            'model',
            TextType::class,
            [
                'label' => 'Model',
                'required' => false,
                'constraints' => [
                    new Length(max: 64),
                ],
                'attr' => [
                    'placeholder' => $options['default_model'],
                ],
                'help' => 'Ponechte prázdné pro výchozí model (' . $options['default_model'] . ').',
            ]
        );

        $builder->add(
            'remove_key',
            CheckboxType::class,
            [
                'label' => 'Odstranit uložený klíč',
                'required' => false,
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
