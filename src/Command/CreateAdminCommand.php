<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creates a verified administrator account.'
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Administrator email address'
            )
            ->addArgument(
                'firstName',
                InputArgument::REQUIRED,
                'Administrator first name'
            )
            ->addArgument(
                'lastName',
                InputArgument::REQUIRED,
                'Administrator last name'
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $email = strtolower(
            trim((string) $input->getArgument('email'))
        );

        $firstName = trim(
            (string) $input->getArgument('firstName')
        );

        $lastName = trim(
            (string) $input->getArgument('lastName')
        );

        if (
            $this->userRepository->findOneBy([
                'email' => $email,
            ]) !== null
        ) {
            $output->writeln(
                '<error>Un utilisateur existe déjà avec cette adresse e-mail.</error>'
            );

            return Command::FAILURE;
        }

        $adminRole = $this->roleRepository->findOneBy([
            'name' => 'ROLE_ADMIN',
        ]);

        if ($adminRole === null) {
            $output->writeln(
                '<error>Le rôle ROLE_ADMIN est introuvable.</error>'
            );

            return Command::FAILURE;
        }

        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $passwordQuestion = new Question(
            'Mot de passe administrateur : '
        );

        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);

        $passwordQuestion->setValidator(
            function (?string $password): string {
                $password = (string) $password;

                if (trim($password) === '') {
                    throw new \RuntimeException(
                        'Le mot de passe ne peut pas être vide.'
                    );
                }

                if (mb_strlen($password) < 8) {
                    throw new \RuntimeException(
                        'Le mot de passe doit contenir au moins 8 caractères.'
                    );
                }

                if (!preg_match('/[A-Z]/', $password)) {
                    throw new \RuntimeException(
                        'Le mot de passe doit contenir au moins une lettre majuscule.'
                    );
                }

                if (!preg_match('/[a-z]/', $password)) {
                    throw new \RuntimeException(
                        'Le mot de passe doit contenir au moins une lettre minuscule.'
                    );
                }

                if (!preg_match('/[0-9]/', $password)) {
                    throw new \RuntimeException(
                        'Le mot de passe doit contenir au moins un chiffre.'
                    );
                }

                if (!preg_match('/[^A-Za-z0-9]/', $password)) {
                    throw new \RuntimeException(
                        'Le mot de passe doit contenir au moins un caractère spécial.'
                    );
                }

                return $password;
            }
        );

        $passwordQuestion->setMaxAttempts(3);

        $plainPassword = $helper->ask(
            $input,
            $output,
            $passwordQuestion
        );

        if (!is_string($plainPassword)) {
            $output->writeln(
                '<error>Impossible de récupérer le mot de passe.</error>'
            );

            return Command::FAILURE;
        }

        $user = new User();

        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRole($adminRole);
        $user->setVerified(true);
        $user->setActivationToken(null);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plainPassword
        );

        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(
            '<info>Le compte administrateur a été créé avec succès.</info>'
        );

        $output->writeln(
            sprintf(
                '<info>Administrateur : %s</info>',
                $email
            )
        );

        return Command::SUCCESS;
    }
}