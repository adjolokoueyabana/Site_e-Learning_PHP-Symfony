<?php

namespace App\Controller\Admin;

use App\Entity\CustomerOrder;
use App\Entity\User;
use App\Repository\CustomerOrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/utilisateurs')]
final class AdminUserController extends AbstractController
{
    #[Route(
        '',
        name: 'app_admin_user_index',
        methods: ['GET']
    )]
    public function index(
        UserRepository $userRepository
    ): Response {
        return $this->render(
            'admin/user/index.html.twig',
            [
                'users' => $userRepository->findAll(),
            ]
        );
    }

    #[Route(
        '/{id}/commandes',
        name: 'app_admin_user_orders',
        requirements: ['id' => '\d+'],
        methods: ['GET']
    )]
    public function orders(
        User $user,
        CustomerOrderRepository $customerOrderRepository
    ): Response {
        $orders = $customerOrderRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render(
            'admin/user/orders.html.twig',
            [
                'user' => $user,
                'orders' => $orders,
            ]
        );
    }

    #[Route(
        '/{id}/activer',
        name: 'app_admin_user_activate',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    public function activate(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid(
            'activate_user_' . $user->getId(),
            (string) $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        if ($user->isVerified()) {
            $this->addFlash(
                'danger',
                'Ce compte est déjà activé.'
            );

            return $this->redirectToRoute(
                'app_admin_user_index'
            );
        }

        $admin = $this->getUser();
        $now = new \DateTimeImmutable();

        $user->setVerified(true);
        $user->setActivationToken(null);
        $user->setUpdatedAt($now);

        if ($admin instanceof User) {
            $user->setUpdatedBy(
                $admin->getId()
            );
        }

        $entityManager->flush();

        $this->addFlash(
            'success',
            sprintf(
                'Le compte de %s %s a été activé.',
                $user->getFirstName(),
                $user->getLastName()
            )
        );

        return $this->redirectToRoute(
            'app_admin_user_index'
        );
    }

    #[Route(
        '/{userId}/commandes/{orderId}/annuler',
        name: 'app_admin_order_cancel',
        requirements: [
            'userId' => '\d+',
            'orderId' => '\d+',
        ],
        methods: ['POST']
    )]
    public function cancelOrder(
        int $userId,
        int $orderId,
        Request $request,
        UserRepository $userRepository,
        CustomerOrderRepository $customerOrderRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $userRepository->find(
            $userId
        );

        if (!$user instanceof User) {
            throw $this->createNotFoundException(
                'Utilisateur introuvable.'
            );
        }

        $order = $customerOrderRepository->find(
            $orderId
        );

        if (
            !$order instanceof CustomerOrder
            || $order->getUser()?->getId() !== $user->getId()
        ) {
            throw $this->createNotFoundException(
                'Commande introuvable.'
            );
        }

        if (!$this->isCsrfTokenValid(
            'cancel_order_' . $order->getId(),
            (string) $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        if (
            $order->getStatus()
            !== CustomerOrder::STATUS_PENDING
        ) {
            $this->addFlash(
                'danger',
                'Seule une commande en attente peut être annulée.'
            );

            return $this->redirectToRoute(
                'app_admin_user_orders',
                [
                    'id' => $user->getId(),
                ]
            );
        }

        $admin = $this->getUser();
        $now = new \DateTimeImmutable();

        $order->setStatus(
            CustomerOrder::STATUS_CANCELLED
        );

        $order->setUpdatedAt($now);

        if ($admin instanceof User) {
            $order->setUpdatedBy(
                $admin->getId()
            );
        }

        $entityManager->flush();

        $this->addFlash(
            'success',
            sprintf(
                'La commande n°%d a été annulée.',
                $order->getId()
            )
        );

        return $this->redirectToRoute(
            'app_admin_user_orders',
            [
                'id' => $user->getId(),
            ]
        );
    }
}