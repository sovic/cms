<?php

namespace Sovic\Cms\Form\Admin;

use Sovic\Cms\Ai\Enum\AiModel;
use Sovic\Cms\Form\FormTheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class AiSettingsForm extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'default_model' => '',
            'current_model' => null,
        ]);
        $resolver->setAllowedTypes('default_model', 'string');
        $resolver->setAllowedTypes('current_model', ['null', 'string']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod('POST');

        $builder->add(
            'api_key',
            PasswordType::class,
            [
                'label' => 'Anthropic API klíč',
                'required' => false,
                'always_empty' => true,
                'constraints' => [
                    new Length(max: 255),
                ],
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'sk-ant-...',
                ],
                'help' => 'Klíč se ukládá šifrovaně. Ponechte prázdné, pokud jej nechcete měnit.',
            ]
        );

        $choices = AiModel::getChoices();
        // keep previously saved model selectable, even if not offered anymore
        $currentModel = $options['current_model'];
        if (!empty($currentModel) && !in_array($currentModel, $choices, true)) {
            $choices[$currentModel] = $currentModel;
        }

        $builder->add(
            'model',
            ChoiceType::class,
            [
                'label' => 'Model',
                'required' => false,
                'choices' => $choices,
                'placeholder' => 'Výchozí (' . $options['default_model'] . ')',
                'help' => 'Výchozí model je nastaven v konfiguraci aplikace.',
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
