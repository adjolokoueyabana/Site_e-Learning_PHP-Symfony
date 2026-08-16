<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\LessonProgress;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LessonProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct(
            $registry,
            LessonProgress::class
        );
    }

    public function findForUserAndLesson(
        User $user,
        Lesson $lesson
    ): ?LessonProgress {
        return $this->findOneBy([
            'user' => $user,
            'lesson' => $lesson,
        ]);
    }

    public function isLessonCompleted(
        User $user,
        Lesson $lesson
    ): bool {
        $progress = $this->findForUserAndLesson(
            $user,
            $lesson
        );

        return $progress !== null
            && $progress->isCompleted();
    }

    public function countCompletedLessonsForCourse(
        User $user,
        Course $course
    ): int {
        return (int) $this->createQueryBuilder('progress')
            ->select('COUNT(progress.id)')
            ->innerJoin('progress.lesson', 'lesson')
            ->andWhere('progress.user = :user')
            ->andWhere('lesson.course = :course')
            ->andWhere('progress.completed = :completed')
            ->setParameter('user', $user)
            ->setParameter('course', $course)
            ->setParameter('completed', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Determines whether every lesson in a course has been completed.
     */
    public function isCourseCompleted(
        User $user,
        Course $course
    ): bool {
        $lessonCount = $course->getLessons()->count();

        if ($lessonCount === 0) {
            return false;
        }

        $completedCount = $this->countCompletedLessonsForCourse(
            $user,
            $course
        );

        return $completedCount === $lessonCount;
    }
}