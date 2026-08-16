<?php

namespace App\Controller;

use App\Entity\CustomerOrder;
use App\Entity\User;
use App\Repository\CustomerOrderRepository;
use App\Service\StripePaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PaymentController extends AbstractController
{
    #[Route(
        '/paiement/{id}',
        name: 'app_payment_checkout',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    public function checkout(
        CustomerOrder $order,
        Request $request,
        StripePaymentService $stripePaymentService,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute(
                'app_login'
            );
        }

        if (
            $order->getUser()?->getId()
            !== $user->getId()
        ) {
            throw $this->createAccessDeniedException(
                'You cannot pay this order.'
            );
        }

        if (!$this->isCsrfTokenValid(
            'payment_checkout_' . $order->getId(),
            (string) $request->request->get('_token')
        )) {
            $this->addFlash(
                'danger',
                'Le formulaire de paiement est invalide. Veuillez réessayer.'
            );

            return $this->redirectToRoute(
                'app_my_training'
            );
        }

        if (
            $order->getStatus()
            !== CustomerOrder::STATUS_PENDING
        ) {
            $this->addFlash(
                'danger',
                'Cette commande ne peut plus être payée.'
            );

            return $this->redirectToRoute(
                'app_my_training'
            );
        }

        if (!$user->isVerified()) {
            $this->addFlash(
                'danger',
                'Vous devez activer votre compte avant de pouvoir effectuer un paiement.'
            );

            return $this->redirectToRoute(
                'app_my_training'
            );
        }

        $checkoutSession = $stripePaymentService
            ->createCheckoutSession(
                $order
            );

        $order->setStripeCheckoutSessionId(
            $checkoutSession->id
        );

        $order->setUpdatedAt(
            new \DateTimeImmutable()
        );

        $order->setUpdatedBy(
            $user->getId()
        );

        $entityManager->flush();

        if ($checkoutSession->url === null) {
            throw new \RuntimeException(
                'Stripe Checkout URL was not returned.'
            );
        }

        return new RedirectResponse(
            $checkoutSession->url
        );
    }

    #[Route(
        '/paiement/succes',
        name: 'app_payment_success',
        methods: ['GET']
    )]
    public function success(
        Request $request,
        CustomerOrderRepository $customerOrderRepository
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute(
                'app_login'
            );
        }

        $sessionId = $request->query->get(
            'session_id'
        );

        $order = null;

        if (
            is_string($sessionId)
            && $sessionId !== ''
        ) {
            $order = $customerOrderRepository
                ->findOneBy([
                    'stripeCheckoutSessionId' => $sessionId,
                    'user' => $user,
                ]);
        }

        if (
            $order instanceof CustomerOrder
            && $order->getStatus()
                === CustomerOrder::STATUS_PAID
        ) {
            $this->addFlash(
                'success',
                'Paiement effectué avec succès. Votre contenu est maintenant accessible.'
            );
        } else {
            $this->addFlash(
                'success',
                'Votre paiement a bien été reçu par Stripe. La confirmation est en cours et votre accès sera disponible dès sa validation.'
            );
        }

        return $this->redirectToRoute(
            'app_my_training'
        );
    }

    #[Route(
        '/paiement/annule',
        name: 'app_payment_cancel',
        methods: ['GET']
    )]
    public function cancel(): Response
    {
        $this->addFlash(
            'danger',
            'Le paiement a été annulé. Votre commande reste en attente.'
        );

        return $this->redirectToRoute(
            'app_my_training'
        );
    }
}