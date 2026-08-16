<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Service\AccountActivationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
    #[Route(
        '/inscription',
        name: 'app_register',
        methods: ['GET', 'POST']
    )]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        RoleRepository $roleRepository,
        UserRepository $userRepository,
        AccountActivationService $accountActivationService
    ): Response {
        $user = new User();

        $form = $this->createForm(
            RegistrationFormType::class,
            $user
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $userRepository->findOneByEmail(
                (string) $user->getEmail()
            );

            if ($existingUser !== null) {
                $this->addFlash(
                    'danger',
                    'Un compte existe déjà avec cette adresse e-mail.'
                );

                return $this->render(
                    'registration/register.html.twig',
                    [
                        'registrationForm' => $form,
                    ]
                );
            }

            $clientRole = $roleRepository->findClientRole();

            if ($clientRole === null) {
                throw $this->createNotFoundException(
                    'The ROLE_CLIENT role could not be found.'
                );
            }

            $plainPassword = (string) $form
                ->get('plainPassword')
                ->getData();

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plainPassword
            );

            $activationToken = bin2hex(
                random_bytes(32)
            );

            $user->setPassword($hashedPassword);
            $user->setRole($clientRole);
            $user->setVerified(false);
            $user->setActivationToken($activationToken);

            $entityManager->persist($user);
            $entityManager->flush();

            $accountActivationService->sendActivationEmail($user);

            $this->addFlash(
                'success',
                'Votre compte a été créé. Un e-mail d’activation vous a été envoyé.'
            );

            return $this->redirectToRoute('app_home');
        }

        return $this->render(
            'registration/register.html.twig',
            [
                'registrationForm' => $form,
            ]
        );
    }
}