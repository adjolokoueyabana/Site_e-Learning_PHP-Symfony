<?php

namespace App\Tests\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RegistrationControllerTest extends WebTestCase
{
    private const TEST_EMAIL = 'jean.test@example.com';

    public function testUserCanRegister(): void
    {
        $client = static::createClient();

        $container = static::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(
            EntityManagerInterface::class
        );

        /** @var RoleRepository $roleRepository */
        $roleRepository = $container->get(
            RoleRepository::class
        );

        /** @var UserRepository $userRepository */
        $userRepository = $container->get(
            UserRepository::class
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

        $crawler = $client->request(
            'GET',
            '/inscription'
        );

        self::assertResponseIsSuccessful();

        $form = $crawler
            ->filter('form')
            ->form();

        $form['registration_form[firstName]'] = 'Jean';
        $form['registration_form[lastName]'] = 'Test';
        $form['registration_form[email]'] = self::TEST_EMAIL;

        $form[
            'registration_form[plainPassword][first]'
        ] = 'Test123!';

        $form[
            'registration_form[plainPassword][second]'
        ] = 'Test123!';

        $form['registration_form[agreeTerms]'] = '1';

        $client->submit($form);

        self::assertResponseRedirects('/');

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(
            UserRepository::class
        );

        $registeredUser = $userRepository->findOneBy([
            'email' => self::TEST_EMAIL,
        ]);

        self::assertInstanceOf(
            User::class,
            $registeredUser
        );

        self::assertSame(
            'Jean',
            $registeredUser->getFirstName()
        );

        self::assertSame(
            'Test',
            $registeredUser->getLastName()
        );

        self::assertSame(
            self::TEST_EMAIL,
            $registeredUser->getEmail()
        );

        self::assertFalse(
            $registeredUser->isVerified()
        );

        self::assertNotNull(
            $registeredUser->getActivationToken()
        );

        self::assertNotSame(
            '',
            $registeredUser->getActivationToken()
        );

        self::assertNotSame(
            'Test123!',
            $registeredUser->getPassword()
        );

        self::assertSame(
            'ROLE_CLIENT',
            $registeredUser->getRole()?->getName()
        );
    }
}