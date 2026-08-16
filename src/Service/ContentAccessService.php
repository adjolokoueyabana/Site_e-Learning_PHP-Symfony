<?php

namespace App\Service;

use App\Entity\Lesson;
use App\Entity\User;
use App\Repository\OrderItemRepository;

class ContentAccessService
{
    public function __construct(
        private readonly OrderItemRepository $orderItemRepository
    ) {
    }

    /**
     * Determines whether the user can access a lesson.
     *
     * A lesson is accessible when the user bought either
     * the lesson itself or the complete course containing it.
     */
    public function canAccessLesson(
        ?User $user,
        Lesson $lesson
    ): bool {
        if ($user === null) {
            return false;
        }

        return $this->orderItemRepository->userOwnsLesson(
            $user,
            $lesson
        );
    }
}