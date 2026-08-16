<?php

namespace App\Form\Admin;

use App\Entity\Course;
use App\Entity\Lesson;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Url;

final class LessonType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add(
                'course',
                EntityType::class,
                [
                    'class' => Course::class,
                    'choice_label' => 'title',
                    'label' => 'Cursus',
                    'placeholder' => 'Choisir un cursus',
                    'placeholder_attr' => [
                        'disabled' => 'disabled',
                    ],
                    'constraints' => [
                        new NotBlank(
                            message: 'Veuillez choisir un cursus.'
                        ),
                    ],
                ]
            )
            ->add(
                'title',
                TextType::class,
                [
                    'label' => 'Titre de la leçon',
                    'attr' => [
                        'placeholder' => 'Exemple : Les bases du HTML',
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
                'content',
                TextareaType::class,
                [
                    'label' => 'Fiche de cours',
                    'attr' => [
                        'rows' => 12,
                        'placeholder' => 'Contenu pédagogique de la leçon',
                    ],
                    'constraints' => [
                        new NotBlank(
                            message: 'Veuillez saisir le contenu de la leçon.'
                        ),
                    ],
                ]
            )
            ->add(
                'videoUrl',
                TextType::class,
                [
                    'label' => 'URL de la vidéo',
                    'required' => false,
                    'empty_data' => null,
                    'attr' => [
                        'placeholder' => 'https://...',
                    ],
                    'constraints' => [
                        new Url(
                            message: 'Veuillez saisir une URL valide.'
                        ),
                        new Length(
                            max: 500,
                            maxMessage: 'L’URL ne peut pas dépasser {{ limit }} caractères.'
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
            )
            ->add(
                'position',
                IntegerType::class,
                [
                    'label' => 'Position dans le cursus',
                    'attr' => [
                        'min' => 1,
                        'step' => 1,
                    ],
                    'constraints' => [
                        new NotBlank(
                            message: 'Veuillez saisir une position.'
                        ),
                        new Positive(
                            message: 'La position doit être supérieure ou égale à 1.'
                        ),
                    ],
                ]
            );
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
        ]);
    }
}