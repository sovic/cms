<?php

namespace SovicCms\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class UserSettingsNewPassword extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod('POST');
        $builder
            ->add(
                'password',
                PasswordType::class,
                [
                    'required' => false,
                    'label' => 'form.user_settings.old_password',
                    'attr' => [
                        'placeholder' => 'form.user_settings.old_password',
                        'autocomplete' => 'current-password',
                    ],
                    'row_attr' => [
                        'class' => 'mb-3',
                    ],
                ]
            )
            ->add(
                'new_password',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'required' => false,
                    'first_options' => [
                        'label' => 'form.user_settings.new_password',
                        'attr' => [
                            'placeholder' => 'form.user_settings.new_password',
                            'autocomplete' => 'new-password',
                        ],
                        'row_attr' => [
                            'class' => 'mb-3',
                        ],
                    ],
                    'second_options' => [
                        'label' => 'form.user_settings.new_password_check',
                        'attr' => [
                            'placeholder' => 'form.user_settings.new_password_check',
                        ],
                        'row_attr' => [
                            'class' => 'mb-3',
                        ],
                    ],
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'form.user_settings.submit',
                    'attr' => [
                        'class' => 'w-100 btn btn-primary',
                    ],
                ]
            );
    }
}
