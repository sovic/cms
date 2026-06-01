<?php

namespace Sovic\Cms\Form\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\MenuItem;
use Sovic\Cms\Entity\PageGroup;
use Sovic\Cms\Form\Admin\Trait\MetaFormTrait;
use Sovic\Cms\Form\FormTheme;
use Sovic\Cms\Repository\MenuItemRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class Page extends AbstractType
{
    use MetaFormTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Sovic\Cms\Entity\Page $page */
        $page = $builder->getData();
        $editing = $page && $this->entityManager->contains($page);

        $builder->setMethod('POST');

        // basic

        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'Název',
                'required' => true,
                'attr' => [
                    'length' => 200,
                ],
            ]
        );

        $title = '
            URL identifikátor se používá pro tvorbu URL adresy stránky. 
            Pokud není vyplněn, bude automaticky vygenerován z názvu stránky.
            Měl by být unikátní a může obsahovat pouze písmena, čísla a pomlčky.
        ';
        $builder->add(
            'urlId',
            TextType::class,
            [
                'label' => 'URL identifikátor (nepovinné)',
                'required' => false,
                'attr' => [
                    'length' => 200,
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'bottom',
                    'title' => $title,
                ],
            ]
        );

        $builder->add(
            'heading',
            TextType::class,
            [
                'label' => 'Nadpis',
                'required' => false,
                'attr' => [
                    'length' => 150,
                ],
            ]
        );

        if (!$editing) {
            $builder->add(
                'isPublic',
                CheckboxType::class,
                [
                    'label' => 'Publikováno',
                    'required' => false,
                ]
            );
        }

        $builder->add(
            'lang',
            TextType::class,
            [
                'label' => 'Jazyk',
                'required' => false,
                'attr' => [
                    'length' => 5,
                ],
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

        // settings

        if (!$editing) {
            $builder->add(
                'hasToc',
                CheckboxType::class,
                [
                    'label' => 'Obsah (TOC)',
                    'required' => false,
                    'getter' => fn(\Sovic\Cms\Entity\Page $page) => $page->hasToc(),
                    'setter' => fn(\Sovic\Cms\Entity\Page $page, bool $value) => $page->setHasToc($value),
                ]
            );

            $builder->add(
                'isInSitemap',
                CheckboxType::class,
                [
                    'label' => 'Zobrazit v sitemap.xml',
                    'required' => false,
                ]
            );
        }

        $builder->add(
            'contentType',
            TextType::class,
            [
                'label' => 'Typ obsahu',
                'required' => false,
                'attr' => [
                    'length' => 255,
                ],
            ]
        );

        $builder->add(
            'theme',
            TextType::class,
            [
                'label' => 'Téma',
                'required' => false,
                'attr' => [
                    'length' => 255,
                ],
            ]
        );

        $builder->add(
            'pageGroup',
            EntityType::class,
            [
                'label' => 'Skupina stránek',
                'class' => PageGroup::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => '— bez skupiny —',
            ]
        );

        $builder->add(
            'menuItem',
            EntityType::class,
            [
                'label' => 'Menu stránky',
                'class' => MenuItem::class,
                'choice_label' => fn(MenuItem $item) => $item->getName() ?? $item->getPosition(),
                'required' => false,
                'placeholder' => '— bez položky menu —',
                'query_builder' => fn(MenuItemRepository $repo) => $repo->createRootQueryBuilder(),
            ]
        );

        // meta data

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
