<?php

namespace Sovic\Cms\Form\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\MenuItem as MenuItemEntity;
use Sovic\Cms\Entity\Page;
use Sovic\CommonUi\Form\FormTheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuItem extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'pages' => [],
            'parent_choices' => [],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var MenuItemEntity $menuItem */
        $menuItem = $builder->getData();

        /** @noinspection PhpUnusedLocalVariableInspection */
        $editing = $menuItem && $this->entityManager->contains($menuItem);

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

        $builder->add(
            'url',
            TextType::class,
            [
                'label' => 'Odkaz (URL)',
                'required' => false,
                'attr' => [
                    'length' => 200,
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'bottom',
                    'title' => 'Vlastní URL odkaz. Pokud je vybrána stránka, odkaz se generuje automaticky. Externí odkazy musí začínat "https://" a otevírají se v nové záložce.',
                ],
            ]
        );

        // page

        /** @var Page[] $pages */
        $pages = $options['pages'];
        $pageChoices = [];
        foreach ($pages as $page) {
            $pageChoices[$page->getName() . ' (/' . $page->getUrlId() . ')'] = $page->getId();
        }

        $builder->add(
            'pageId',
            ChoiceType::class,
            [
                'label' => 'Stránka',
                'required' => false,
                'choices' => $pageChoices,
                'placeholder' => '-- Žádná --',
            ]
        );

        // parent

        $parentChoices = $options['parent_choices'];

        $builder->add(
            'parentId',
            ChoiceType::class,
            [
                'label' => 'Nadřazená položka',
                'required' => false,
                'choices' => $parentChoices,
                'placeholder' => '-- Žádná (kořenová) --',
            ]
        );

        // settings

        $builder->add(
            'isPublished',
            CheckboxType::class,
            [
                'label' => 'Publikováno',
                'required' => false,
                'getter' => fn(MenuItemEntity $item) => $item->isPublished(),
                'setter' => fn(MenuItemEntity $item, bool $value) => $item->setIsPublished($value),
            ]
        );

        $builder->add(
            'visibility',
            ChoiceType::class,
            [
                'label' => 'Viditelnost',
                'required' => false,
                'choices' => [
                    'Vždy viditelné' => null,
                    'Pouze pro přihlášené' => 'auth',
                    'Pouze pro nepřihlášené' => 'not-auth',
                ],
                'placeholder' => false,
            ]
        );

        $builder->add(
            'sequence',
            IntegerType::class,
            [
                'label' => 'Pořadí',
                'required' => false,
                'attr' => [
                    'min' => 0,
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'bottom',
                    'title' => 'Určuje pořadí položek v rámci stejné úrovně (stejného rodiče). Položky se řadí vzestupně podle této hodnoty, přičemž položky se stejnou hodnotou se řadí podle ID.',
                ],
            ]
        );

        $builder->add(
            'position',
            TextType::class,
            [
                'label' => 'Pozice',
                'required' => false,
                'attr' => [
                    'length' => 255,
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'bottom',
                    'title' => 'Identifikátor pozice menu (např. "header", "footer"). Pouze pro kořenové položky. V případě stejné pozice se bere pouze první kořenová položka a ostatní se ignorují.',
                ],
            ]
        );

        $builder->add(
            'classes',
            TextType::class,
            [
                'label' => 'CSS třídy',
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
