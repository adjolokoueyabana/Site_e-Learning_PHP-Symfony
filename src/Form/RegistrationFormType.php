<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'autocomplete' => 'given-name',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir votre prénom.'
                    ),
                    new Length(
                        max: 100
                    ),
                ],
            ])

            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'autocomplete' => 'family-name',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir votre nom.'
                    ),
                    new Length(
                        max: 100
                    ),
                ],
            ])

            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'attr' => [
                    'autocomplete' => 'email',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir votre adresse e-mail.'
                    ),
                    new Email(
                        message: 'Veuillez saisir une adresse e-mail valide.'
                    ),
                ],
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'form-control',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'form-control',
                    ],
                ],
                'invalid_message' => 'Les deux mots de passe doivent être identiques.',
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir un mot de passe.'
                    ),
                    new Length(
                        min: 8,
                        max: 4096,
                        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'
                    ),
                    new Regex(
                        pattern: '/[A-Z]/',
                        message: 'Le mot de passe doit contenir au moins une lettre majuscule.'
                    ),
                    new Regex(
                        pattern: '/[a-z]/',
                        message: 'Le mot de passe doit contenir au moins une lettre minuscule.'
                    ),
                    new Regex(
                        pattern: '/[0-9]/',
                        message: 'Le mot de passe doit contenir au moins un chiffre.'
                    ),
                    new Regex(
                        pattern: '/[^A-Za-z0-9]/',
                        message: 'Le mot de passe doit contenir au moins un caractère spécial.'
                    ),
                ],
            ])

            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J’accepte les conditions d’utilisation.',
                'mapped' => false,
                'constraints' => [
                    new IsTrue(
                        message: 'Vous devez accepter les conditions d’utilisation.'
                    ),
                ],
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}