<?php

namespace App\Service;

use App\Entity\CustomerOrder;
use Stripe\Checkout\Session;
use Stripe\StripeClient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripePaymentService
{
    public function __construct(
        private readonly string $stripeSecretKey,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * Creates a Stripe Checkout Session for a pending order.
     */
    public function createCheckoutSession(
        CustomerOrder $order
    ): Session {
        $stripe = new StripeClient(
            $this->stripeSecretKey
        );

        $lineItems = [];

        foreach ($order->getItems() as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item->getTitleSnapshot(),
                    ],
                    'unit_amount' => $this->convertAmountToCents(
                        $item->getUnitPrice()
                    ),
                ],
                'quantity' => 1,
            ];
        }

        $successUrl = $this->urlGenerator->generate(
            'app_payment_success',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $cancelUrl = $this->urlGenerator->generate(
            'app_payment_cancel',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'customer_email' => $order->getUser()?->getEmail(),
            'client_reference_id' => (string) $order->getId(),
            'line_items' => $lineItems,
            'success_url' => $successUrl
                . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'order_id' => (string) $order->getId(),
            ],
        ]);
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