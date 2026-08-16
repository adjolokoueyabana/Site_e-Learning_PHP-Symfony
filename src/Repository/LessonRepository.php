<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    /**
     * Returns all lessons of a course ordered by their position.
     *
     * @return Lesson[]
     */
    public function findByCourseOrderedByPosition(
        Course $course
    ): array {
        return $this->createQueryBuilder('lesson')
            ->andWhere('lesson.course = :course')
            ->setParameter('course', $course)
            ->orderBy('lesson.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlug(
        string $slug
    ): ?Lesson {
        return $this->findOneBy([
            'slug' => $slug,
        ]);
    }

    /**
     * Checks whether a position is already used
     * inside a given course.
     *
     * The optional excluded lesson is useful when editing
     * an existing lesson, so it does not conflict with itself.
     */
    public function positionExistsInCourse(
        Course $course,
        int $position,
        ?Lesson $excludedLesson = null
    ): bool {
        $queryBuilder = $this->createQueryBuilder('lesson')
            ->select('COUNT(lesson.id)')
            ->andWhere('lesson.course = :course')
            ->andWhere('lesson.position = :position')
            ->setParameter('course', $course)
            ->setParameter('position', $position);

        if (
            $excludedLesson !== null
            && $excludedLesson->getId() !== null
        ) {
            $queryBuilder
                ->andWhere('lesson.id != :excludedLessonId')
                ->setParameter(
                    'excludedLessonId',
                    $excludedLesson->getId()
                );
        }

        return (int) $queryBuilder
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
}