<?php

namespace App\Controller\Admin;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\User;
use App\Form\Admin\LessonType;
use App\Repository\LessonProgressRepository;
use App\Repository\LessonRepository;
use App\Repository\OrderItemRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/lecons')]
final class AdminLessonController extends AbstractController
{
    #[Route(
        '',
        name: 'app_admin_lesson_index',
        methods: ['GET']
    )]
    public function index(
        LessonRepository $lessonRepository
    ): Response {
        return $this->render(
            'admin/lesson/index.html.twig',
            [
                'lessons' => $lessonRepository->findBy(
                    [],
                    [
                        'course' => 'ASC',
                        'position' => 'ASC',
                    ]
                ),
            ]
        );
    }

    #[Route(
        '/nouvelle',
        name: 'app_admin_lesson_new',
        methods: ['GET', 'POST']
    )]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        LessonRepository $lessonRepository
    ): Response {
        $lesson = new Lesson();

        $form = $this->createForm(
            LessonType::class,
            $lesson
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            $course = $lesson->getCourse();
            $position = $lesson->getPosition();

            if (
                $course instanceof Course
                && $position !== null
                && $lessonRepository->positionExistsInCourse(
                    $course,
                    $position
                )
            ) {
                $form->get('position')->addError(
                    new FormError(
                        'Cette position est déjà utilisée dans ce cursus.'
                    )
                );
            }

            if ($form->isValid()) {
                $user = $this->getUser();

                $lesson->setSlug(
                    $this->generateUniqueSlug(
                        $lesson->getTitle(),
                        $slugger,
                        $entityManager
                    )
                );

                if ($user instanceof User) {
                    $lesson->setCreatedBy(
                        $user->getId()
                    );

                    $lesson->setUpdatedBy(
                        $user->getId()
                    );
                }

                $entityManager->persist($lesson);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'La leçon a été créée avec succès.'
                );

                return $this->redirectToRoute(
                    'app_admin_lesson_index'
                );
            }
        }

        return $this->render(
            'admin/lesson/new.html.twig',
            [
                'lesson' => $lesson,
                'form' => $form,
            ]
        );
    }

    #[Route(
        '/{id}/modifier',
        name: 'app_admin_lesson_edit',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST']
    )]
    public function edit(
        Lesson $lesson,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        LessonRepository $lessonRepository
    ): Response {
        $originalTitle = $lesson->getTitle();

        $form = $this->createForm(
            LessonType::class,
            $lesson
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            $course = $lesson->getCourse();
            $position = $lesson->getPosition();

            if (
                $course instanceof Course
                && $position !== null
                && $lessonRepository->positionExistsInCourse(
                    $course,
                    $position,
                    $lesson
                )
            ) {
                $form->get('position')->addError(
                    new FormError(
                        'Cette position est déjà utilisée dans ce cursus.'
                    )
                );
            }

            if ($form->isValid()) {
                $user = $this->getUser();

                if (
                    $originalTitle !== $lesson->getTitle()
                ) {
                    $lesson->setSlug(
                        $this->generateUniqueSlug(
                            $lesson->getTitle(),
                            $slugger,
                            $entityManager,
                            $lesson
                        )
                    );
                }

                $lesson->setUpdatedAt(
                    new \DateTimeImmutable()
                );

                if ($user instanceof User) {
                    $lesson->setUpdatedBy(
                        $user->getId()
                    );
                }

                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'La leçon a été modifiée avec succès.'
                );

                return $this->redirectToRoute(
                    'app_admin_lesson_index'
                );
            }
        }

        return $this->render(
            'admin/lesson/edit.html.twig',
            [
                'lesson' => $lesson,
                'form' => $form,
            ]
        );
    }

    #[Route(
        '/{id}/supprimer',
        name: 'app_admin_lesson_delete',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    public function delete(
        Lesson $lesson,
        Request $request,
        EntityManagerInterface $entityManager,
        OrderItemRepository $orderItemRepository,
        LessonProgressRepository $lessonProgressRepository
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete_lesson_' . $lesson->getId(),
            (string) $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        $orderItemCount = $orderItemRepository->count([
            'lesson' => $lesson,
        ]);

        $progressCount = $lessonProgressRepository->count([
            'lesson' => $lesson,
        ]);

        if (
            $orderItemCount > 0
            || $progressCount > 0
        ) {
            $this->addFlash(
                'danger',
                'Cette leçon ne peut pas être supprimée car elle est liée à un achat ou à la progression d’un utilisateur.'
            );

            return $this->redirectToRoute(
                'app_admin_lesson_index'
            );
        }

        try {
            $entityManager->remove($lesson);
            $entityManager->flush();
        } catch (ForeignKeyConstraintViolationException) {
            $this->addFlash(
                'danger',
                'Cette leçon ne peut pas être supprimée car elle est encore utilisée par des données du site.'
            );

            return $this->redirectToRoute(
                'app_admin_lesson_index'
            );
        }

        $this->addFlash(
            'success',
            'La leçon a été supprimée avec succès.'
        );

        return $this->redirectToRoute(
            'app_admin_lesson_index'
        );
    }

    private function generateUniqueSlug(
        string $title,
        SluggerInterface $slugger,
        EntityManagerInterface $entityManager,
        ?Lesson $currentLesson = null
    ): string {
        $baseSlug = strtolower(
            $slugger->slug($title)->toString()
        );

        $slug = $baseSlug;
        $counter = 2;

        while (true) {
            $existingLesson = $entityManager
                ->getRepository(Lesson::class)
                ->findOneBy([
                    'slug' => $slug,
                ]);

            if (
                $existingLesson === null
                || (
                    $currentLesson !== null
                    && $existingLesson->getId()
                        === $currentLesson->getId()
                )
            ) {
                return $slug;
            }

            $slug = $baseSlug . '-' . $counter;

            ++$counter;
        }
    }
}