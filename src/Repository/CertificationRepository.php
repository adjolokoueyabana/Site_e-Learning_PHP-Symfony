<?php

namespace App\Repository;

use App\Entity\Certification;
use App\Entity\Theme;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CertificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct(
            $registry,
            Certification::class
        );
    }

    public function findForUserAndTheme(
        User $user,
        Theme $theme
    ): ?Certification {
        return $this->findOneBy([
            'user' => $user,
            'theme' => $theme,
        ]);
    }

    /**
     * Returns all certifications earned by a user.
     *
     * @return Certification[]
     */
    public function findByUserWithTheme(User $user): array
    {
        return $this->createQueryBuilder('certification')
            ->innerJoin('certification.theme', 'theme')
            ->addSelect('theme')
            ->andWhere('certification.user = :user')
            ->setParameter('user', $user)
            ->orderBy('certification.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}