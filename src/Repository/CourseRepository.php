<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    /**
     * @return Course[]
     */
    public function findByThemeOrderedByTitle(Theme $theme): array
    {
        return $this->createQueryBuilder('course')
            ->andWhere('course.theme = :theme')
            ->setParameter('theme', $theme)
            ->orderBy('course.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlug(string $slug): ?Course
    {
        return $this->findOneBy([
            'slug' => $slug,
        ]);
    }
}