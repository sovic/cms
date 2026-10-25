<?php

namespace Sovic\Cms\Form\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Form\Admin\Trait\MetaFormTrait;
use Sovic\Cms\Form\FormTheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class Post extends AbstractType
{
    use MetaFormTrait;

    public function __construct(
        private readonly EntityManagerInterface                 $entityManager,
        #[Autowire('%post_enable_tags%')] private readonly bool $enableTags,
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
            'publishDate',
            DateTimeType::class,
            [
                'label' => 'Datum publikování',
                'required' => false,
                'input' => 'datetime_immutable',
                'attr' => [
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'bottom',
                    'title' => 'Příspěvek se na webu zobrazí, až bude zveřejněn a nastane datum publikování. Lze zadat i datum v budoucnu (naplánované publikování).',
                ],
            ]
        );

        $builder->add(
            'createDate',
            DateTimeType::class,
            [
                'label' => 'Datum vytvoření',
                'required' => false,
                'input' => 'datetime_immutable',
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

        // tags

        if ($this->enableTags) {
            $builder->add(
                'tags',
                TextType::class,
                [
                    'label' => false,
                    'required' => false,
                    'mapped' => false,
                ]
            );
        }

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
