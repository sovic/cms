<?php

namespace Sovic\Cms\Form\Admin;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Sovic\Cms\Email\EmailIdInterface;
use Sovic\Cms\Email\EmailSettingsInterface;
use Sovic\CommonUi\Form\FormTheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Email extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'email_list' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $emailList = $options['email_list'] ?? null;
        if (!$emailList instanceof EmailSettingsInterface) {
            throw new InvalidArgumentException('Option "email_list" is required.');
        }

        /** @var \Sovic\Cms\Entity\Email $email */
        $email = $builder->getData();

        /** @noinspection PhpUnusedLocalVariableInspection */
        $editing = $email && $this->entityManager->contains($email);

        $builder->setMethod('POST');

        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'Název šablony',
                'required' => true,
                'attr' => [
                    'length' => 200,
                ],
            ]
        );

        $builder->add(
            'subject',
            TextType::class,
            [
                'label' => 'Předmět emailu',
                'required' => true,
                'attr' => [
                    'length' => 200,
                ],
            ]
        );

        $builder->add(
            'fromName',
            TextType::class,
            [
                'label' => 'Odesílatel - jméno',
                'required' => false,
                'attr' => [
                    'length' => 150,
                ],
            ]
        );

        $builder->add(
            'fromEmail',
            ChoiceType::class,
            [
                'label' => 'Odesílatel - email',
                'required' => false,
                'choices' => $emailList->getDomainEmails(),
                'choice_label' => static function (string $choice): string {
                    return $choice;
                },
                'placeholder' => '-- Vyberte --',
            ]
        );

        $builder->add(
            'body',
            TextareaType::class,
            [
                'label' => 'Tělo emailu',
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'length' => 16777215,
                    'rows' => 15,
                ],
            ]
        );

        $choices = $emailList->getEmailIds();
        // sort by value
        usort($choices, static function (EmailIdInterface $a, EmailIdInterface $b): int {
            return strcmp($a->getLabel(), $b->getLabel());
        });

        $builder->add(
            'emailId',
            ChoiceType::class,
            [
                'label' => 'Systémový email',
                'required' => false,
                'choices' => $choices,
                'choice_value' => static function (null|EmailIdInterface|string $choice): ?string {
                    if (is_string($choice)) {
                        return $choice;
                    }

                    return $choice?->getId();
                },
                'choice_label' => static function (EmailIdInterface|string $choice): string {
                    if (is_string($choice)) {
                        return $choice;
                    }

                    return $choice->getLabel();
                },
                'placeholder' => '-- Vyberte --',
                'choice_attr' => function (EmailIdInterface $choice) {
                    return [
                        'data-variables' => implode(', ', $choice->getVariables()),
                    ];
                },
                'data' => $email?->getEmailId(),
                'mapped' => false,
            ],
        );

        $builder->add('save', SubmitType::class, [
            'label' => 'Uložit změny',
            'attr' => [
                'class' => FormTheme::BtnSubmitClass,
            ],
        ]);
    }
}
