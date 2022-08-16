<?php

namespace SovicCms\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class NewPassword extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod('POST');
        $builder
            ->add(
                'password',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'required' => true,
                    'first_options' => [
                        'label' => 'form.new_password.password',
                        'attr' => [
                            'placeholder' => 'form.new_password.password',
                        ],
                        'row_attr' => [
                            'class' => 'mb-3',
                        ],
                    ],
                    'second_options' => [
                        'label' => 'form.new_password.password_check',
                        'attr' => [
                            'placeholder' => 'form.new_password.password_check',
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
                    'label' => 'form.new_password.submit',
                    'attr' => [
                        'class' => 'w-100 btn btn-primary',
                    ],
                ]
            );
    }
}
