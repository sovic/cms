<?php

namespace SovicCms\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignIn extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id' => 'sign_in',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod('POST');
        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'form.sign_in.email',
                    'attr' => [
                        'placeholder' => 'form.sign_in.email',
                    ],
                    'row_attr' => [
                        'class' => 'mb-3',
                    ],
                ]
            )
            ->add(
                'password',
                PasswordType::class,
                [
                    'label' => 'form.sign_in.password',
                    'attr' => [
                        'placeholder' => 'form.sign_in.password',
                        'autocomplete' => 'current-password',
                    ],
                    'row_attr' => [
                        'class' => 'mb-3',
                    ],
                ]
            )
            ->add(
                'remember',
                CheckboxType::class,
                [
                    'label' => 'form.sign_in.remember',
                    'required' => false,
                    'row_attr' => [
                        'class' => 'mb-3',
                    ],
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'form.sign_in.submit',
                    'attr' => [
                        'class' => 'w-100 btn btn-primary',
                    ],
                ]
            );
        $builder->add('_csrf_token', HiddenType::class);
    }
}
