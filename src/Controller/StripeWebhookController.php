<?php

namespace App\Controller;

use App\Entity\CustomerOrder;
use App\Repository\CustomerOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class StripeWebhookController extends AbstractController
{
    private const EXPECTED_CURRENCY = 'eur';

    public function __construct(
        private readonly string $stripeWebhookSecret
    ) {
    }

    #[Route(
        '/stripe/webhook',
        name: 'app_stripe_webhook',
        methods: ['POST']
    )]
    public function webhook(
        Request $request,
        CustomerOrderRepository $customerOrderRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $payload = $request->getContent();

        $signature = $request->headers->get(
            'Stripe-Signature'
        );

        if ($signature === null) {
            return new JsonResponse(
                [
                    'error' => 'Missing Stripe signature.',
                ],
                400
            );
        }

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $this->stripeWebhookSecret
            );
        } catch (\UnexpectedValueException) {
            return new JsonResponse(
                [
                    'error' => 'Invalid webhook payload.',
                ],
                400
            );
        } catch (SignatureVerificationException) {
            return new JsonResponse(
                [
                    'error' => 'Invalid webhook signature.',
                ],
                400
            );
        }

        if (
            $event->type
            !== 'checkout.session.completed'
        ) {
            return new JsonResponse([
                'received' => true,
            ]);
        }

        $session = $event->data->object;

        $orderId = $session->metadata->order_id ?? null;

        if ($orderId === null) {
            return new JsonResponse(
                [
                    'error' => 'Order ID is missing.',
                ],
                400
            );
        }

        $order = $customerOrderRepository->find(
            (int) $orderId
        );

        if (!$order instanceof CustomerOrder) {
            return new JsonResponse(
                [
                    'error' => 'Order not found.',
                ],
                404
            );
        }

        if (
            $order->getStatus()
            === CustomerOrder::STATUS_PAID
        ) {
            return new JsonResponse([
                'received' => true,
            ]);
        }

        if (
            $order->getStripeCheckoutSessionId() !== null
            && $order->getStripeCheckoutSessionId()
                !== $session->id
        ) {
            return new JsonResponse(
                [
                    'error' => 'Checkout Session mismatch.',
                ],
                400
            );
        }

        if ($session->payment_status !== 'paid') {
            return new JsonResponse([
                'received' => true,
            ]);
        }

        if (
            !$this->isExpectedCurrency(
                $session->currency ?? null
            )
        ) {
            return new JsonResponse(
                [
                    'error' => 'Unexpected payment currency.',
                ],
                400
            );
        }

        $expectedAmount = $this->convertAmountToCents(
            $order->getTotalAmount()
        );

        $stripeAmount = $session->amount_total ?? null;

        if (
            $stripeAmount === null
            || (int) $stripeAmount !== $expectedAmount
        ) {
            return new JsonResponse(
                [
                    'error' => 'Payment amount mismatch.',
                ],
                400
            );
        }

        $order->setStatus(
            CustomerOrder::STATUS_PAID
        );

        $order->setStripeCheckoutSessionId(
            $session->id
        );

        $order->setUpdatedAt(
            new \DateTimeImmutable()
        );

        $order->setUpdatedBy(
            $order->getUser()?->getId()
        );

        $entityManager->flush();

        return new JsonResponse([
            'received' => true,
        ]);
    }

    private function isExpectedCurrency(
        ?string $currency
    ): bool {
        if ($currency === null) {
            return false;
        }

        return strtolower($currency)
            === self::EXPECTED_CURRENCY;
    }

    /**
     * Converts an amount in euros to integer cents.
     */
    private function convertAmountToCents(
        string $amount
    ): int {
        return (int) round(
            ((float) $amount) * 100
        );
    }
}