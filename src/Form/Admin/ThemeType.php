<?php

namespace App\Form\Admin;

use App\Entity\Theme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ThemeType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'Nom du thème',
                    'attr' => [
                        'placeholder' => 'Exemple : Informatique',
                    ],
                    'constraints' => [
                        new NotBlank(
                            message: 'Veuillez saisir un nom de thème.'
                        ),
                        new Length(
                            max: 150,
                            maxMessage: 'Le nom du thème ne peut pas dépasser {{ limit }} caractères.'
                        ),
                    ],
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'label' => 'Description',
                    'required' => false,
                    'empty_data' => '',
                    'attr' => [
                        'rows' => 6,
                        'placeholder' => 'Description du thème',
                    ],
                    'constraints' => [
                        new Length(
                            max: 5000,
                            maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.'
                        ),
                    ],
                ]
            );
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => Theme::class,
        ]);
    }
}