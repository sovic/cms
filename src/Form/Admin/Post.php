<?php

namespace Sovic\Cms\Form\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\CommonUi\Form\FormTheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class Post extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Sovic\Cms\Entity\Post $post */
        $post = $builder->getData();

        /** @noinspection PhpUnusedLocalVariableInspection */
        $editing = $post && $this->entityManager->contains($post);

        $builder->setMethod('POST');

        // basic

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

        $builder->add(
            'public',
            CheckboxType::class,
            [
                'label' => 'Veřejný',
                'required' => false,
            ]
        );
        $builder->add(
            'publishDate',
            DateType::class,
            [
                'label' => 'Datum publikování',
                'required' => false,
            ]
        );


        // content

        $builder->add(
            'content',
            TextareaType::class,
            [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'rich-text-editor',
                    'rows' => 20,
                ],
            ]
        );

        // meta data

        $builder->add(
            'urlId',
            TextType::class,
            [
                'label' => 'URL identifikátor (nepovinné, vytvoří se automaticky z názvu pokud není vyplněno)',
                'required' => false,
                'attr' => [
                    'length' => 255,
                ],
            ]
        );

        $builder->add(
            'metaTitle',
            TextType::class,
            [
                'label' => 'Titulek (nepovinné, použije se název pokud není vyplněno)',
                'required' => false,
                'attr' => [
                    'length' => 255,
                ],
            ]
        );

        $builder->add(
            'metaDescription',
            TextType::class,
            [
                'label' => 'Popis',
                'required' => false,
                'attr' => [
                    'length' => 255,
                ],
            ]
        );

        $builder->add(
            'metaKeywords',
            TextType::class,
            [
                'label' => 'Klíčová slova (oddělená čárkou)',
                'required' => false,
                'attr' => [
                    'length' => 255,
                ],
            ]
        );

        // submit

        $builder->add('save', SubmitType::class, [
            'label' => 'Uložit změny',
            'attr' => [
                'class' => FormTheme::BtnSubmitClass,
            ],
        ]);
    }
}
