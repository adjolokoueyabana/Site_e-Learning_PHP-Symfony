<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    #[Route(
        '/connexion',
        name: 'app_login',
        methods: ['GET', 'POST']
    )]
    public function login(
        AuthenticationUtils $authenticationUtils
    ): Response {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(
        '/deconnexion',
        name: 'app_logout',
        methods: ['GET']
    )]
    public function logout(): never
    {
        throw new \LogicException(
            'This method is intercepted by the Symfony logout firewall.'
        );
    }
}