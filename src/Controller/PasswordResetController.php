<?php

namespace App\Controller;

use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use App\Service\PasswordResetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PasswordResetController extends AbstractController
{
    #[Route(
        '/mot-de-passe-oublie',
        name: 'app_forgot_password',
        methods: ['GET', 'POST']
    )]
    public function request(
        Request $request,
        PasswordResetService $passwordResetService
    ): Response {
        $form = $this->createForm(
            ForgotPasswordType::class
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            $email = (string) $form
                ->get('email')
                ->getData();

            $passwordResetService->sendResetLink(
                $email
            );

            return $this->redirectToRoute(
                'app_forgot_password_sent'
            );
        }

        return $this->render(
            'security/forgot_password.html.twig',
            [
                'form' => $form,
            ]
        );
    }

    #[Route(
        '/mot-de-passe-oublie/e-mail-envoye',
        name: 'app_forgot_password_sent',
        methods: ['GET']
    )]
    public function sent(): Response
    {
        return $this->render(
            'security/forgot_password_sent.html.twig'
        );
    }

    #[Route(
        '/mot-de-passe/reinitialiser/{token}',
        name: 'app_password_reset',
        methods: ['GET', 'POST']
    )]
    public function reset(
        string $token,
        Request $request,
        PasswordResetService $passwordResetService
    ): Response {
        $user = $passwordResetService
            ->findUserByValidToken(
                $token
            );

        if ($user === null) {
            return $this->render(
                'security/reset_password_invalid.html.twig'
            );
        }

        $form = $this->createForm(
            ResetPasswordType::class
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            $plainPassword = (string) $form
                ->get('plainPassword')
                ->getData();

            $passwordResetService->resetPassword(
                $user,
                $plainPassword
            );

            $this->addFlash(
                'success',
                'Votre mot de passe a été modifié. Vous pouvez maintenant vous connecter.'
            );

            return $this->redirectToRoute(
                'app_login'
            );
        }

        return $this->render(
            'security/reset_password.html.twig',
            [
                'form' => $form,
            ]
        );
    }
}