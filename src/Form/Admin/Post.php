<?php

namespace Sovic\Cms\Form\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Form\Admin\Trait\MetaFormTrait;
use Sovic\Cms\Form\FormTheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class Post extends AbstractType
{
    use MetaFormTrait;

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
                'label' => 'Titulek',
                'required' => true,
                'attr' => [
                    'length' => 255,
                ],
            ]
        );

        $builder->add(
            'subtitle',
            TextType::class,
            [
                'label' => 'Podtitulek',
                'required' => false,
                'attr' => [
                    'length' => 255,
                ],
            ]
        );

        $builder->add(
            'public',
            CheckboxType::class,
            [
                'label' => 'Publikováno',
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

        $builder->add(
            'createDate',
            DateType::class,
            [
                'label' => 'Datum vytvoření',
                'required' => false,
                'disabled' => true,
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

        $builder->add(
            'perex',
            TextareaType::class,
            [
                'required' => false,
                'label' => 'Anotace / perex (nepovinné)',
                'attr' => [
                    'rows' => 5,
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

        $this->addMetaFields($builder);

        // submit

        $builder->add('save', SubmitType::class, [
            'label' => 'Uložit změny',
            'attr' => [
                'class' => FormTheme::BtnSubmitClass,
            ],
        ]);
    }
}
