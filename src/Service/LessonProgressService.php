<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\LessonProgress;
use App\Entity\User;
use App\Repository\LessonProgressRepository;
use Doctrine\ORM\EntityManagerInterface;

class LessonProgressService
{
    public function __construct(
        private readonly LessonProgressRepository $lessonProgressRepository,
        private readonly ContentAccessService $contentAccessService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Marks a lesson as completed for the given user.
     */
    public function completeLesson(
        User $user,
        Lesson $lesson
    ): LessonProgress {
        if (!$this->contentAccessService->canAccessLesson($user, $lesson)) {
            throw new \DomainException(
                'Vous ne pouvez pas valider une leçon à laquelle vous n’avez pas accès.'
            );
        }

        $progress = $this->lessonProgressRepository
            ->findForUserAndLesson($user, $lesson);

        if ($progress === null) {
            $progress = new LessonProgress();
            $progress->setUser($user);
            $progress->setLesson($lesson);
            $progress->setCreatedBy($user->getId());

            $this->entityManager->persist($progress);
        }

        if ($progress->isCompleted()) {
            return $progress;
        }

        $now = new \DateTimeImmutable();

        $progress->setCompleted(true);
        $progress->setCompletedAt($now);
        $progress->setUpdatedAt($now);
        $progress->setUpdatedBy($user->getId());

        $this->entityManager->flush();

        return $progress;
    }

    /**
     * Checks whether all lessons belonging to a course
     * have been completed by the user.
     */
    public function isCourseCompleted(
        User $user,
        Course $course
    ): bool {
        return $this->lessonProgressRepository
            ->isCourseCompleted($user, $course);
    }

    /**
     * Returns the number of completed lessons in a course.
     */
    public function getCompletedLessonCount(
        User $user,
        Course $course
    ): int {
        return $this->lessonProgressRepository
            ->countCompletedLessonsForCourse(
                $user,
                $course
            );
    }
}