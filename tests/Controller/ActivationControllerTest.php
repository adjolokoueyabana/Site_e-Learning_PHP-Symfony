<?php

namespace App\Tests\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ActivationControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $entityManager;

    private UrlGeneratorInterface $urlGenerator;

    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        /*
         * Keep the same kernel and Doctrine services when a test
         * performs several HTTP requests.
         */
        $this->client->disableReboot();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(
            EntityManagerInterface::class
        );

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = static::getContainer()->get(
            UrlGeneratorInterface::class
        );

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(
            UserRepository::class
        );

        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;

        $this->entityManager
            ->getConnection()
            ->beginTransaction();
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();

        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }

        $this->entityManager->clear();

        parent::tearDown();
    }

    public function testValidActivationTokenActivatesUser(): void
    {
        $token = bin2hex(
            random_bytes(32)
        );

        $user = $this->createUser(
            false,
            $token
        );

        $userId = $user->getId();

        self::assertNotNull(
            $userId
        );

        self::assertFalse(
            $user->isVerified()
        );

        self::assertSame(
            $token,
            $user->getActivationToken()
        );

        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'app_account_activate',
                [
                    'token' => $token,
                ]
            )
        );

        self::assertResponseRedirects(
            $this->urlGenerator->generate(
                'app_home'
            )
        );

        $activatedUser = $this->findUser(
            $userId
        );

        self::assertTrue(
            $activatedUser->isVerified()
        );

        self::assertNull(
            $activatedUser->getActivationToken()
        );
    }

    public function testInvalidActivationTokenDoesNotActivateUser(): void
    {
        $validToken = bin2hex(
            random_bytes(32)
        );

        $user = $this->createUser(
            false,
            $validToken
        );

        $userId = $user->getId();

        self::assertNotNull(
            $userId
        );

        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'app_account_activate',
                [
                    'token' => 'invalid-token',
                ]
            )
        );

        self::assertResponseRedirects(
            $this->urlGenerator->generate(
                'app_home'
            )
        );

        $unchangedUser = $this->findUser(
            $userId
        );

        self::assertFalse(
            $unchangedUser->isVerified()
        );

        self::assertSame(
            $validToken,
            $unchangedUser->getActivationToken()
        );
    }

    public function testAlreadyVerifiedUserRemainsVerified(): void
    {
        $token = bin2hex(
            random_bytes(32)
        );

        $user = $this->createUser(
            true,
            $token
        );

        $userId = $user->getId();

        self::assertNotNull(
            $userId
        );

        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'app_account_activate',
                [
                    'token' => $token,
                ]
            )
        );

        self::assertResponseRedirects(
            $this->urlGenerator->generate(
                'app_home'
            )
        );

        $verifiedUser = $this->findUser(
            $userId
        );

        self::assertTrue(
            $verifiedUser->isVerified()
        );

        self::assertSame(
            $token,
            $verifiedUser->getActivationToken()
        );
    }

    public function testActivationLinkCannotBeUsedTwice(): void
    {
        $token = bin2hex(
            random_bytes(32)
        );

        $user = $this->createUser(
            false,
            $token
        );

        $userId = $user->getId();

        self::assertNotNull(
            $userId
        );

        $activationUrl = $this->urlGenerator->generate(
            'app_account_activate',
            [
                'token' => $token,
            ]
        );

        $this->client->request(
            'GET',
            $activationUrl
        );

        self::assertResponseRedirects(
            $this->urlGenerator->generate(
                'app_home'
            )
        );

        $activatedUser = $this->findUser(
            $userId
        );

        self::assertTrue(
            $activatedUser->isVerified()
        );

        self::assertNull(
            $activatedUser->getActivationToken()
        );

        $this->client->request(
            'GET',
            $activationUrl
        );

        self::assertResponseRedirects(
            $this->urlGenerator->generate(
                'app_home'
            )
        );

        $userAfterSecondRequest = $this->findUser(
            $userId
        );

        self::assertTrue(
            $userAfterSecondRequest->isVerified()
        );

        self::assertNull(
            $userAfterSecondRequest->getActivationToken()
        );
    }

    private function createUser(
        bool $verified,
        ?string $activationToken
    ): User {
        $suffix = bin2hex(
            random_bytes(6)
        );

        $role = new Role();

        $role->setName(
            'ROLE_ACTIVATION_TEST_' . $suffix
        );

        $this->entityManager->persist(
            $role
        );

        $user = new User();

        $user
            ->setEmail(
                'activation-' . $suffix . '@example.test'
            )
            ->setPassword(
                'activation-test-password'
            )
            ->setFirstName('Activation')
            ->setLastName('Test')
            ->setRole($role)
            ->setVerified($verified)
            ->setActivationToken($activationToken);

        $this->entityManager->persist(
            $user
        );

        $this->entityManager->flush();

        return $user;
    }

    private function findUser(
        int $userId
    ): User {
        $this->entityManager->clear();

        $user = $this->userRepository->find(
            $userId
        );

        self::assertInstanceOf(
            User::class,
            $user
        );

        return $user;
    }
}