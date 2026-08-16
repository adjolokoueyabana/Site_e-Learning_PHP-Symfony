<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function findClientRole(): ?Role
    {
        return $this->findOneBy([
            'name' => 'ROLE_CLIENT',
        ]);
    }

    public function findAdminRole(): ?Role
    {
        return $this->findOneBy([
            'name' => 'ROLE_ADMIN',
        ]);
    }
}