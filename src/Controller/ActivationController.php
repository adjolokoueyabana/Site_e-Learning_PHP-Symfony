<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ActivationController extends AbstractController
{
    #[Route(
        '/activation/{token}',
        name: 'app_account_activate',
        methods: ['GET']
    )]
    public function activate(
        string $token,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $userRepository->findOneBy([
            'activationToken' => $token,
        ]);

        if ($user === null) {
            $this->addFlash(
                'danger',
                'Le lien d’activation est invalide ou a déjà été utilisé.'
            );

            return $this->redirectToRoute('app_home');
        }

        if ($user->isVerified()) {
            $this->addFlash(
                'success',
                'Votre compte est déjà activé.'
            );

            return $this->redirectToRoute('app_home');
        }

        $user->setVerified(true);
        $user->setActivationToken(null);
        $user->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        $this->addFlash(
            'success',
            'Votre compte Knowledge Learning est maintenant activé.'
        );

        return $this->redirectToRoute('app_home');
    }
}