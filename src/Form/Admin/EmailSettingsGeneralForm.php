<?php

namespace Sovic\Cms\Form\Admin;

use Sovic\Cms\Form\FormTheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailSettingsGeneralForm extends AbstractType
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
            'default_contact_email',
            EmailType::class,
            [
                'label' => 'Výchozí kontaktní email',
                'required' => false,
                'attr' => [
                    'length' => 200,
                ],
            ]
        );

        $builder->add(
            'email_signature',
            TextareaType::class,
            [
                'label' => 'Podpis e-mailu',
                'required' => false,
                'attr' => [
                    'rows' => 6,
                ],
                'help' => 'Text zobrazený v patičce e-mailu. Povolené HTML tagy: <br>, <strong>, <a>.',
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
