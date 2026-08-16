<?php

namespace App\Repository;

use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ThemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Theme::class);
    }

    /**
     * Returns all themes ordered alphabetically.
     *
     * @return Theme[]
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('theme')
            ->orderBy('theme.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns all themes with their courses in a single query.
     *
     * @return Theme[]
     */
    public function findAllWithCourses(): array
    {
        return $this->createQueryBuilder('theme')
            ->leftJoin('theme.courses', 'course')
            ->addSelect('course')
            ->orderBy('theme.name', 'ASC')
            ->addOrderBy('course.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlug(string $slug): ?Theme
    {
        return $this->findOneBy([
            'slug' => $slug,
        ]);
    }
}