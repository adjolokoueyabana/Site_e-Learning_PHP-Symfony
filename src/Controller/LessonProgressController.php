<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Entity\User;
use App\Service\CertificationService;
use App\Service\LessonProgressService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LessonProgressController extends AbstractController
{
    #[Route(
        '/lecon/{slug}/valider',
        name: 'app_lesson_complete',
        methods: ['POST']
    )]
    public function complete(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Lesson $lesson,
        Request $request,
        LessonProgressService $lessonProgressService,
        CertificationService $certificationService
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'complete_lesson_' . $lesson->getId(),
            (string) $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        try {
            $lessonProgressService->completeLesson(
                $user,
                $lesson
            );

            $course = $lesson->getCourse();
            $theme = $course?->getTheme();

            $isCourseCompleted = false;
            $certification = null;

            if ($course !== null) {
                $isCourseCompleted = $lessonProgressService
                    ->isCourseCompleted(
                        $user,
                        $course
                    );
            }

            if ($theme !== null) {
                $certification = $certificationService
                    ->issueIfEligible(
                        $user,
                        $theme
                    );
            }

            if ($certification !== null) {
                $this->addFlash(
                    'success',
                    sprintf(
                        'Félicitations ! Vous avez terminé le thème « %s ». Votre certification %s a été attribuée.',
                        $theme->getName(),
                        $certification->getCertificateNumber()
                    )
                );
            } elseif ($isCourseCompleted && $course !== null) {
                $this->addFlash(
                    'success',
                    sprintf(
                        'La leçon a été validée. Le cursus « %s » est maintenant terminé.',
                        $course->getTitle()
                    )
                );
            } else {
                $this->addFlash(
                    'success',
                    'La leçon a été validée avec succès.'
                );
            }
        } catch (\DomainException $exception) {
            $this->addFlash(
                'danger',
                $exception->getMessage()
            );
        }

        return $this->redirectToRoute(
            'app_lesson_show',
            [
                'slug' => $lesson->getSlug(),
            ]
        );
    }
}