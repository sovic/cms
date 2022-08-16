<?php

namespace SovicCms\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ForgotPassword extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod('POST');
        $builder
            ->add(
                'email',
                TextType::class,
                [
                    'label' => 'form.forgot_password.email',
                    'attr' => [
                        'placeholder' => 'form.forgot_password.email',
                    ],
                    'row_attr' => [
                        'class' => 'mb-3',
                    ],
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'form.forgot_password.submit',
                    'attr' => [
                        'class' => 'w-100 btn btn-primary',
                    ],
                ]
            );
        $builder->add('_csrf_token', HiddenType::class);
    }
}
