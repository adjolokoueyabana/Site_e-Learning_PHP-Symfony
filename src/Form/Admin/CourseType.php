<?php

namespace App\Form\Admin;

use App\Entity\Course;
use App\Entity\Theme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class CourseType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add(
                'theme',
                EntityType::class,
                [
                    'class' => Theme::class,
                    'choice_label' => 'name',
                    'label' => 'Thème',
                    'placeholder' => 'Choisir un thème',
                    'placeholder_attr' => [
                        'disabled' => 'disabled',
                    ],
                    'constraints' => [
                        new NotBlank(
                            message: 'Veuillez choisir un thème.'
                        ),
                    ],
                ]
            )
            ->add(
                'title',
                TextType::class,
                [
                    'label' => 'Titre du cursus',
                    'attr' => [
                        'placeholder' => 'Exemple : Initiation au développement web',
                    ],
                    'constraints' => [
                        new NotBlank(
                            message: 'Veuillez saisir un titre.'
                        ),
                        new Length(
                            max: 180,
                            maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
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
                        'placeholder' => 'Description du cursus',
                    ],
                    'constraints' => [
                        new Length(
                            max: 5000,
                            maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.'
                        ),
                    ],
                ]
            )
            ->add(
                'price',
                MoneyType::class,
                [
                    'label' => 'Prix',
                    'currency' => 'EUR',
                    'scale' => 2,
                    'attr' => [
                        'min' => '0',
                        'step' => '0.01',
                        'placeholder' => '0,00',
                    ],
                    'constraints' => [
                        new NotBlank(
                            message: 'Veuillez saisir un prix.'
                        ),
                        new GreaterThanOrEqual(
                            value: 0,
                            message: 'Le prix ne peut pas être négatif.'
                        ),
                    ],
                ]
            );
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}