<?php

namespace App\Service;

use App\Entity\Certification;
use App\Entity\Theme;
use App\Entity\User;
use App\Repository\CertificationRepository;
use App\Repository\LessonProgressRepository;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;

class CertificationService
{
    public function __construct(
        private readonly CertificationRepository $certificationRepository,
        private readonly LessonProgressRepository $lessonProgressRepository,
        private readonly ThemeRepository $themeRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Determines whether every course in a theme
     * has been completed by the user.
     */
    public function isThemeCompleted(
        User $user,
        Theme $theme
    ): bool {
        $courses = $theme->getCourses();

        if ($courses->isEmpty()) {
            return false;
        }

        foreach ($courses as $course) {
            if (
                !$this->lessonProgressRepository->isCourseCompleted(
                    $user,
                    $course
                )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Creates a certification when the whole theme
     * has been completed.
     *
     * Returns the newly created certification or null when
     * the user is not eligible or already owns it.
     */
    public function issueIfEligible(
        User $user,
        Theme $theme
    ): ?Certification {
        if (!$this->isThemeCompleted($user, $theme)) {
            return null;
        }

        $existingCertification = $this->certificationRepository
            ->findForUserAndTheme(
                $user,
                $theme
            );

        if ($existingCertification !== null) {
            return null;
        }

        $certification = new Certification();

        $certification->setUser($user);
        $certification->setTheme($theme);
        $certification->setCertificateNumber(
            $this->generateCertificateNumber()
        );
        $certification->setCreatedBy($user->getId());
        $certification->setUpdatedBy($user->getId());

        $this->entityManager->persist($certification);
        $this->entityManager->flush();

        return $certification;
    }

    /**
     * Synchronizes all certifications already earned by a user.
     *
     * This is useful for courses completed before the automatic
     * certification mechanism was introduced.
     *
     * @return Certification[]
     */
    public function synchronizeUserCertifications(
        User $user
    ): array {
        $createdCertifications = [];

        $themes = $this->themeRepository->findAll();

        foreach ($themes as $theme) {
            $certification = $this->issueIfEligible(
                $user,
                $theme
            );

            if ($certification !== null) {
                $createdCertifications[] = $certification;
            }
        }

        return $createdCertifications;
    }

    /**
     * Generates a unique human-readable certificate number.
     */
    private function generateCertificateNumber(): string
    {
        do {
            $certificateNumber = sprintf(
                'KL-%s-%s',
                (new \DateTimeImmutable())->format('Y'),
                strtoupper(bin2hex(random_bytes(4)))
            );

            $existingCertification = $this->certificationRepository
                ->findOneBy([
                    'certificateNumber' => $certificateNumber,
                ]);
        } while ($existingCertification !== null);

        return $certificateNumber;
    }
}