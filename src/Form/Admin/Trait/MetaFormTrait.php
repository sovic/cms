<?php

namespace Sovic\Cms\Form\Admin\Trait;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

trait MetaFormTrait
{
    public function addMetaFields(FormBuilderInterface $builder): void
    {
        $builder->add(
            'metaTitle',
            TextType::class,
            [
                'label' => 'Titulek (nepovinné)',
                'required' => false,
                'attr' => [
                    'length' => 255,
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'bottom',
                    'title' => 'Titulek se používá pro SEO a zobrazuje se v prohlížeči a ve výsledcích vyhledávání. Pokud není vyplněn, bude automaticky vygenerován z názvu stránky.',
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
    }
}
