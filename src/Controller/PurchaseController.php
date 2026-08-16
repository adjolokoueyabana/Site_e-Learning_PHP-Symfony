<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\CustomerOrder;
use App\Entity\Lesson;
use App\Entity\User;
use App\Service\PurchaseService;
use App\Service\StripePaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PurchaseController extends AbstractController
{
    #[Route(
        '/achat/cursus/{slug}',
        name: 'app_purchase_course',
        methods: ['POST']
    )]
    public function purchaseCourse(
        Request $request,
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Course $course,
        PurchaseService $purchaseService,
        StripePaymentService $stripePaymentService,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute(
                'app_login'
            );
        }

        $csrfToken = (string) $request
            ->request
            ->get('_token');

        if (!$this->isCsrfTokenValid(
            'purchase_course_' . $course->getId(),
            $csrfToken
        )) {
            $this->addFlash(
                'danger',
                'Le formulaire d’achat est invalide. Veuillez réessayer.'
            );

            return $this->redirectToRoute(
                'app_course_show',
                [
                    'slug' => $course->getSlug(),
                ]
            );
        }

        try {
            $order = $purchaseService
                ->createCourseOrder(
                    $user,
                    $course
                );
        } catch (\DomainException $exception) {
            $this->addFlash(
                'danger',
                $exception->getMessage()
            );

            return $this->redirectToRoute(
                'app_course_show',
                [
                    'slug' => $course->getSlug(),
                ]
            );
        }

        return $this->startStripeCheckout(
            $order,
            $user,
            $stripePaymentService,
            $entityManager
        );
    }

    #[Route(
        '/achat/lecon/{slug}',
        name: 'app_purchase_lesson',
        methods: ['POST']
    )]
    public function purchaseLesson(
        Request $request,
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Lesson $lesson,
        PurchaseService $purchaseService,
        StripePaymentService $stripePaymentService,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute(
                'app_login'
            );
        }

        $csrfToken = (string) $request
            ->request
            ->get('_token');

        if (!$this->isCsrfTokenValid(
            'purchase_lesson_' . $lesson->getId(),
            $csrfToken
        )) {
            $this->addFlash(
                'danger',
                'Le formulaire d’achat est invalide. Veuillez réessayer.'
            );

            return $this->redirectToRoute(
                'app_lesson_show',
                [
                    'slug' => $lesson->getSlug(),
                ]
            );
        }

        try {
            $order = $purchaseService
                ->createLessonOrder(
                    $user,
                    $lesson
                );
        } catch (\DomainException $exception) {
            $this->addFlash(
                'danger',
                $exception->getMessage()
            );

            return $this->redirectToRoute(
                'app_lesson_show',
                [
                    'slug' => $lesson->getSlug(),
                ]
            );
        }

        return $this->startStripeCheckout(
            $order,
            $user,
            $stripePaymentService,
            $entityManager
        );
    }

    private function startStripeCheckout(
        CustomerOrder $order,
        User $user,
        StripePaymentService $stripePaymentService,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
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
}