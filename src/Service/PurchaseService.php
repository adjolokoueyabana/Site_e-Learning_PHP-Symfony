<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\CustomerOrder;
use App\Entity\Lesson;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Repository\CustomerOrderRepository;
use App\Repository\OrderItemRepository;
use Doctrine\ORM\EntityManagerInterface;

class PurchaseService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderItemRepository $orderItemRepository,
        private readonly CustomerOrderRepository $customerOrderRepository
    ) {
    }

    /**
     * Creates or reuses a pending order for a complete course.
     */
    public function createCourseOrder(
        User $user,
        Course $course
    ): CustomerOrder {
        $this->assertUserCanPurchase($user);

        if (
            $this->orderItemRepository->userOwnsCourse(
                $user,
                $course
            )
        ) {
            throw new \DomainException(
                'Vous avez déjà acheté ce cursus.'
            );
        }

        $pendingOrder = $this->customerOrderRepository
            ->findPendingCourseOrder(
                $user,
                $course
            );

        if ($pendingOrder instanceof CustomerOrder) {
            return $pendingOrder;
        }

        $order = new CustomerOrder();

        $order
            ->setUser($user)
            ->setStatus(CustomerOrder::STATUS_PENDING)
            ->setTotalAmount($course->getPrice())
            ->setCreatedBy($user->getId())
            ->setUpdatedBy($user->getId());

        $item = new OrderItem();

        $item
            ->setCourse($course)
            ->setTitleSnapshot($course->getTitle())
            ->setUnitPrice($course->getPrice())
            ->setCreatedBy($user->getId())
            ->setUpdatedBy($user->getId());

        $order->addItem($item);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }

    /**
     * Creates or reuses a pending order for one lesson.
     */
    public function createLessonOrder(
        User $user,
        Lesson $lesson
    ): CustomerOrder {
        $this->assertUserCanPurchase($user);

        if (
            $this->orderItemRepository->userOwnsLesson(
                $user,
                $lesson
            )
        ) {
            throw new \DomainException(
                'Vous avez déjà accès à cette leçon.'
            );
        }

        $pendingOrder = $this->customerOrderRepository
            ->findPendingLessonOrder(
                $user,
                $lesson
            );

        if ($pendingOrder instanceof CustomerOrder) {
            return $pendingOrder;
        }

        $order = new CustomerOrder();

        $order
            ->setUser($user)
            ->setStatus(CustomerOrder::STATUS_PENDING)
            ->setTotalAmount($lesson->getPrice())
            ->setCreatedBy($user->getId())
            ->setUpdatedBy($user->getId());

        $item = new OrderItem();

        $item
            ->setLesson($lesson)
            ->setTitleSnapshot($lesson->getTitle())
            ->setUnitPrice($lesson->getPrice())
            ->setCreatedBy($user->getId())
            ->setUpdatedBy($user->getId());

        $order->addItem($item);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }

    /**
     * Ensures that the user meets the requirements for purchasing content.
     */
    private function assertUserCanPurchase(User $user): void
    {
        if (!$user->isVerified()) {
            throw new \DomainException(
                'Vous devez activer votre compte avant de pouvoir effectuer un achat.'
            );
        }
    }
}