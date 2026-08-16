<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public const CLIENT_ROLE_REFERENCE = 'role_client';
    public const ADMIN_ROLE_REFERENCE = 'role_admin';

    public function load(ObjectManager $manager): void
    {
        $clientRole = new Role();
        $clientRole->setName('ROLE_CLIENT');

        $manager->persist($clientRole);

        $adminRole = new Role();
        $adminRole->setName('ROLE_ADMIN');

        $manager->persist($adminRole);

        $manager->flush();

        $this->addReference(
            self::CLIENT_ROLE_REFERENCE,
            $clientRole
        );

        $this->addReference(
            self::ADMIN_ROLE_REFERENCE,
            $adminRole
        );
    }
}