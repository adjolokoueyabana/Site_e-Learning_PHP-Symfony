<?php

namespace App\Tests\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class SecurityControllerTest extends WebTestCase
{
    private const TEST_EMAIL = 'login.test@example.com';
    private const TEST_PASSWORD = 'Test123!';

    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/connexion'
        );

        self::assertResponseIsSuccessful();
    }

    public function testVerifiedUserCanLogin(): void
    {
        $client = static::createClient();

        $this->createTestUser();

        $crawler = $client->request(
            'GET',
            '/connexion'
        );

        self::assertResponseIsSuccessful();

        $form = $crawler
            ->filter('form')
            ->form();

        $form['_username'] = self::TEST_EMAIL;
        $form['_password'] = self::TEST_PASSWORD;

        $client->submit($form);

        self::assertResponseRedirects('/');
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $client = static::createClient();

        $this->createTestUser();

        $crawler = $client->request(
            'GET',
            '/connexion'
        );

        self::assertResponseIsSuccessful();

        $form = $crawler
            ->filter('form')
            ->form();

        $form['_username'] = self::TEST_EMAIL;
        $form['_password'] = 'WrongPassword123!';

        $client->submit($form);

        self::assertResponseRedirects('/connexion');

        $client->followRedirect();

        self::assertResponseIsSuccessful();
    }

    private function createTestUser(): User
    {
        $container = static::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(
            EntityManagerInterface::class
        );

        /** @var UserRepository $userRepository */
        $userRepository = $container->get(
            UserRepository::class
        );

        /** @var RoleRepository $roleRepository */
        $roleRepository = $container->get(
            RoleRepository::class
        );

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get(
            UserPasswordHasherInterface::class
        );

        $existingUser = $userRepository->findOneBy([
            'email' => self::TEST_EMAIL,
        ]);

        if ($existingUser instanceof User) {
            $entityManager->remove($existingUser);
            $entityManager->flush();
        }

        $clientRole = $roleRepository->findOneBy([
            'name' => 'ROLE_CLIENT',
        ]);

        if (!$clientRole instanceof Role) {
            $clientRole = new Role();
            $clientRole->setName('ROLE_CLIENT');

            $entityManager->persist($clientRole);
            $entityManager->flush();
        }

        $user = new User();

        $user->setEmail(self::TEST_EMAIL);
        $user->setFirstName('Login');
        $user->setLastName('Test');
        $user->setRole($clientRole);
        $user->setVerified(true);
        $user->setActivationToken(null);

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            self::TEST_PASSWORD
        );

        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}