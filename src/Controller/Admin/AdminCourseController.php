<?php

namespace App\Controller\Admin;

use App\Entity\Course;
use App\Entity\User;
use App\Form\Admin\CourseType;
use App\Repository\CourseRepository;
use App\Repository\OrderItemRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/cursus')]
final class AdminCourseController extends AbstractController
{
    #[Route(
        '',
        name: 'app_admin_course_index',
        methods: ['GET']
    )]
    public function index(
        CourseRepository $courseRepository
    ): Response {
        return $this->render(
            'admin/course/index.html.twig',
            [
                'courses' => $courseRepository->findBy(
                    [],
                    ['title' => 'ASC']
                ),
            ]
        );
    }

    #[Route(
        '/nouveau',
        name: 'app_admin_course_new',
        methods: ['GET', 'POST']
    )]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $course = new Course();

        $form = $this->createForm(
            CourseType::class,
            $course
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            $user = $this->getUser();

            $course->setSlug(
                $this->generateUniqueSlug(
                    $course->getTitle(),
                    $slugger,
                    $entityManager
                )
            );

            if ($user instanceof User) {
                $course->setCreatedBy(
                    $user->getId()
                );

                $course->setUpdatedBy(
                    $user->getId()
                );
            }

            $entityManager->persist($course);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Le cursus a été créé avec succès.'
            );

            return $this->redirectToRoute(
                'app_admin_course_index'
            );
        }

        return $this->render(
            'admin/course/new.html.twig',
            [
                'course' => $course,
                'form' => $form,
            ]
        );
    }

    #[Route(
        '/{id}/modifier',
        name: 'app_admin_course_edit',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST']
    )]
    public function edit(
        Course $course,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $originalTitle = $course->getTitle();

        $form = $this->createForm(
            CourseType::class,
            $course
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            $user = $this->getUser();

            if (
                $originalTitle
                !== $course->getTitle()
            ) {
                $course->setSlug(
                    $this->generateUniqueSlug(
                        $course->getTitle(),
                        $slugger,
                        $entityManager,
                        $course
                    )
                );
            }

            $course->setUpdatedAt(
                new \DateTimeImmutable()
            );

            if ($user instanceof User) {
                $course->setUpdatedBy(
                    $user->getId()
                );
            }

            $entityManager->flush();

            $this->addFlash(
                'success',
                'Le cursus a été modifié avec succès.'
            );

            return $this->redirectToRoute(
                'app_admin_course_index'
            );
        }

        return $this->render(
            'admin/course/edit.html.twig',
            [
                'course' => $course,
                'form' => $form,
            ]
        );
    }

    #[Route(
        '/{id}/supprimer',
        name: 'app_admin_course_delete',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    public function delete(
        Course $course,
        Request $request,
        EntityManagerInterface $entityManager,
        OrderItemRepository $orderItemRepository
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete_course_' . $course->getId(),
            (string) $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        if (!$course->getLessons()->isEmpty()) {
            $this->addFlash(
                'danger',
                'Ce cursus ne peut pas être supprimé car il contient encore des leçons.'
            );

            return $this->redirectToRoute(
                'app_admin_course_index'
            );
        }

        $orderItemCount = $orderItemRepository->count([
            'course' => $course,
        ]);

        if ($orderItemCount > 0) {
            $this->addFlash(
                'danger',
                'Ce cursus ne peut pas être supprimé car il est lié à une commande utilisateur.'
            );

            return $this->redirectToRoute(
                'app_admin_course_index'
            );
        }

        try {
            $entityManager->remove($course);
            $entityManager->flush();
        } catch (ForeignKeyConstraintViolationException) {
            $this->addFlash(
                'danger',
                'Ce cursus ne peut pas être supprimé car il est encore utilisé par des données du site.'
            );

            return $this->redirectToRoute(
                'app_admin_course_index'
            );
        }

        $this->addFlash(
            'success',
            'Le cursus a été supprimé avec succès.'
        );

        return $this->redirectToRoute(
            'app_admin_course_index'
        );
    }

    private function generateUniqueSlug(
        string $title,
        SluggerInterface $slugger,
        EntityManagerInterface $entityManager,
        ?Course $currentCourse = null
    ): string {
        $baseSlug = strtolower(
            $slugger->slug($title)->toString()
        );

        $slug = $baseSlug;
        $counter = 2;

        while (true) {
            $existingCourse = $entityManager
                ->getRepository(Course::class)
                ->findOneBy([
                    'slug' => $slug,
                ]);

            if (
                $existingCourse === null
                || (
                    $currentCourse !== null
                    && $existingCourse->getId()
                        === $currentCourse->getId()
                )
            ) {
                return $slug;
            }

            $slug = $baseSlug . '-' . $counter;

            ++$counter;
        }
    }
}