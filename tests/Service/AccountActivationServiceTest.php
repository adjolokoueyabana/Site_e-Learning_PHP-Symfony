<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\AccountActivationService;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class AccountActivationServiceTest extends TestCase
{
    public function testActivationEmailIsSentWithExpectedData(): void
    {
        $mailer = $this->createMock(
            MailerInterface::class
        );

        $urlGenerator = $this->createMock(
            UrlGeneratorInterface::class
        );

        $user = new User();

        $user
            ->setEmail('activation.test@example.com')
            ->setFirstName('Activation')
            ->setLastName('Test')
            ->setPassword('hashed-password')
            ->setActivationToken('test-activation-token');

        $expectedActivationUrl =
            'https://knowledge-learning.test/activation/test-activation-token';

        $urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with(
                'app_account_activate',
                [
                    'token' => 'test-activation-token',
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn(
                $expectedActivationUrl
            );

        $mailer
            ->expects(self::once())
            ->method('send')
            ->with(
                self::callback(
                    function ($email) use (
                        $user,
                        $expectedActivationUrl
                    ): bool {
                        self::assertInstanceOf(
                            TemplatedEmail::class,
                            $email
                        );

                        self::assertSame(
                            'no-reply@knowledge-learning.test',
                            $email->getFrom()[0]->getAddress()
                        );

                        self::assertSame(
                            'activation.test@example.com',
                            $email->getTo()[0]->getAddress()
                        );

                        self::assertSame(
                            'Activez votre compte Knowledge Learning',
                            $email->getSubject()
                        );

                        self::assertSame(
                            'emails/account_activation.html.twig',
                            $email->getHtmlTemplate()
                        );

                        $context = $email->getContext();

                        self::assertSame(
                            $user,
                            $context['user']
                        );

                        self::assertSame(
                            $expectedActivationUrl,
                            $context['activationUrl']
                        );

                        return true;
                    }
                )
            );

        $service = new AccountActivationService(
            $mailer,
            $urlGenerator,
            'no-reply@knowledge-learning.test'
        );

        $service->sendActivationEmail(
            $user
        );
    }

    public function testActivationEmailCannotBeSentWithoutToken(): void
    {
        $mailer = $this->createMock(
            MailerInterface::class
        );

        $urlGenerator = $this->createMock(
            UrlGeneratorInterface::class
        );

        $mailer
            ->expects(self::never())
            ->method('send');

        $urlGenerator
            ->expects(self::never())
            ->method('generate');

        $user = new User();

        $user
            ->setEmail('without-token@example.com')
            ->setFirstName('Without')
            ->setLastName('Token')
            ->setPassword('hashed-password')
            ->setActivationToken(null);

        $service = new AccountActivationService(
            $mailer,
            $urlGenerator,
            'no-reply@knowledge-learning.test'
        );

        $this->expectException(
            \LogicException::class
        );

        $this->expectExceptionMessage(
            'The user does not have an activation token.'
        );

        $service->sendActivationEmail(
            $user
        );
    }
}