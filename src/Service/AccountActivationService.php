<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccountActivationService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $mailFromAddress
    ) {
    }

    public function sendActivationEmail(User $user): void
    {
        if ($user->getActivationToken() === null) {
            throw new \LogicException(
                'The user does not have an activation token.'
            );
        }

        $activationUrl = $this->urlGenerator->generate(
            'app_account_activate',
            [
                'token' => $user->getActivationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new TemplatedEmail())
            ->from($this->mailFromAddress)
            ->to((string) $user->getEmail())
            ->subject('Activez votre compte Knowledge Learning')
            ->htmlTemplate('emails/account_activation.html.twig')
            ->context([
                'user' => $user,
                'activationUrl' => $activationUrl,
            ]);

        $this->mailer->send($email);
    }
}