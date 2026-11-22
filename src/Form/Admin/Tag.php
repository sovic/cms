<?php

namespace Sovic\Cms\Form\Admin;

use Sovic\Cms\Entity\TagGroup;
use Sovic\Cms\Form\FormTheme;
use Sovic\Cms\Repository\TagGroupRepository;
use Sovic\Cms\Tag\TagColor;
use Sovic\Common\Entity\Project;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Tag extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'project' => null,
        ]);
        $resolver->setAllowedTypes('project', ['null', Project::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Project|null $project */
        $project = $options['project'];

        $builder->setMethod('POST');

        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'Název',
                'required' => true,
                'attr' => [
                    'maxlength' => 100,
                ],
            ]
        );

        $builder->add(
            'tagGroup',
            EntityType::class,
            [
                'label' => 'Skupina',
                'class' => TagGroup::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => '— bez skupiny —',
                'query_builder' => fn(TagGroupRepository $repo) => $repo->createOrderedQueryBuilder($project),
            ]
        );

        $builder->add(
            'color',
            EnumType::class,
            [
                'label' => 'Barva',
                'class' => TagColor::class,
                'choice_label' => static fn(TagColor $color) => $color->trans(),
                'expanded' => true,
                'required' => false,
                'placeholder' => 'Bez barvy',
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
