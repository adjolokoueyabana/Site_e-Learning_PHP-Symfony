<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PasswordResetService
{
    private const TOKEN_LIFETIME = '+1 hour';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $mailFromAddress
    ) {
    }

    /**
     * Creates a temporary reset token and sends the reset e-mail.
     */
    public function sendResetLink(string $email): void
    {
        $user = $this->userRepository->findOneBy([
            'email' => mb_strtolower(trim($email)),
        ]);

        /*
         * Do nothing when the e-mail does not exist.
         * The controller will display the same response in every case
         * to avoid revealing registered e-mail addresses.
         */
        if (!$user instanceof User) {
            return;
        }

        $plainToken = bin2hex(
            random_bytes(32)
        );

        $hashedToken = hash(
            'sha256',
            $plainToken
        );

        $now = new \DateTimeImmutable();

        $user->setResetPasswordToken(
            $hashedToken
        );

        $user->setResetPasswordExpiresAt(
            $now->modify(self::TOKEN_LIFETIME)
        );

        $user->setUpdatedAt($now);
        $user->setUpdatedBy($user->getId());

        $this->entityManager->flush();

        $resetUrl = $this->urlGenerator->generate(
            'app_password_reset',
            [
                'token' => $plainToken,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $emailMessage = (new TemplatedEmail())
            ->from($this->mailFromAddress)
            ->to((string) $user->getEmail())
            ->subject(
                'Réinitialisation de votre mot de passe - Knowledge Learning'
            )
            ->htmlTemplate(
                'emails/password_reset.html.twig'
            )
            ->context([
                'user' => $user,
                'resetUrl' => $resetUrl,
                'expiresAt' => $user->getResetPasswordExpiresAt(),
            ]);

        $this->mailer->send(
            $emailMessage
        );
    }

    /**
     * Finds a user from the raw token contained in the e-mail.
     */
    public function findUserByValidToken(
        string $plainToken
    ): ?User {
        $hashedToken = hash(
            'sha256',
            $plainToken
        );

        $user = $this->userRepository->findOneBy([
            'resetPasswordToken' => $hashedToken,
        ]);

        if (!$user instanceof User) {
            return null;
        }

        if (!$user->isResetPasswordTokenValid()) {
            return null;
        }

        return $user;
    }

    /**
     * Replaces the password and permanently invalidates the reset token.
     */
    public function resetPassword(
        User $user,
        string $plainPassword
    ): void {
        $hashedPassword = $this->passwordHasher
            ->hashPassword(
                $user,
                $plainPassword
            );

        $now = new \DateTimeImmutable();

        $user->setPassword(
            $hashedPassword
        );

        $user->clearResetPasswordToken();
        $user->setUpdatedAt($now);
        $user->setUpdatedBy($user->getId());

        $this->entityManager->flush();
    }
}