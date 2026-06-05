<?php

namespace Sovic\Cms\Form\Admin;

use Sovic\Cms\Entity\MenuItem;
use Sovic\Cms\Form\FormTheme;
use Sovic\Cms\Repository\MenuItemRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PageGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod('POST');

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
            'menuItem',
            EntityType::class,
            [
                'label' => 'Menu pro tuto skupinu stránek',
                'class' => MenuItem::class,
                'choice_label' => fn(MenuItem $item) => $item->getName() ?? $item->getPosition(),
                'required' => false,
                'placeholder' => '— bez menu —',
                'query_builder' => fn(MenuItemRepository $repo) => $repo->createRootQueryBuilder(),
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
