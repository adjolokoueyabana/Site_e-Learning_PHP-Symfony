<?php

namespace App\Controller\Admin;

use App\Entity\Theme;
use App\Entity\User;
use App\Form\Admin\ThemeType;
use App\Repository\CertificationRepository;
use App\Repository\ThemeRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/themes')]
final class AdminThemeController extends AbstractController
{
    #[Route(
        '',
        name: 'app_admin_theme_index',
        methods: ['GET']
    )]
    public function index(
        ThemeRepository $themeRepository
    ): Response {
        return $this->render(
            'admin/theme/index.html.twig',
            [
                'themes' => $themeRepository->findBy(
                    [],
                    ['name' => 'ASC']
                ),
            ]
        );
    }

    #[Route(
        '/nouveau',
        name: 'app_admin_theme_new',
        methods: ['GET', 'POST']
    )]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $theme = new Theme();

        $form = $this->createForm(
            ThemeType::class,
            $theme
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            $user = $this->getUser();

            $theme->setSlug(
                $this->generateUniqueSlug(
                    $theme->getName(),
                    $slugger,
                    $entityManager
                )
            );

            if ($user instanceof User) {
                $theme->setCreatedBy(
                    $user->getId()
                );

                $theme->setUpdatedBy(
                    $user->getId()
                );
            }

            $entityManager->persist($theme);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Le thème a été créé avec succès.'
            );

            return $this->redirectToRoute(
                'app_admin_theme_index'
            );
        }

        return $this->render(
            'admin/theme/new.html.twig',
            [
                'theme' => $theme,
                'form' => $form,
            ]
        );
    }

    #[Route(
        '/{id}/modifier',
        name: 'app_admin_theme_edit',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST']
    )]
    public function edit(
        Theme $theme,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $originalName = $theme->getName();

        $form = $this->createForm(
            ThemeType::class,
            $theme
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            $user = $this->getUser();

            if (
                $originalName
                !== $theme->getName()
            ) {
                $theme->setSlug(
                    $this->generateUniqueSlug(
                        $theme->getName(),
                        $slugger,
                        $entityManager,
                        $theme
                    )
                );
            }

            $theme->setUpdatedAt(
                new \DateTimeImmutable()
            );

            if ($user instanceof User) {
                $theme->setUpdatedBy(
                    $user->getId()
                );
            }

            $entityManager->flush();

            $this->addFlash(
                'success',
                'Le thème a été modifié avec succès.'
            );

            return $this->redirectToRoute(
                'app_admin_theme_index'
            );
        }

        return $this->render(
            'admin/theme/edit.html.twig',
            [
                'theme' => $theme,
                'form' => $form,
            ]
        );
    }

    #[Route(
        '/{id}/supprimer',
        name: 'app_admin_theme_delete',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    public function delete(
        Theme $theme,
        Request $request,
        EntityManagerInterface $entityManager,
        CertificationRepository $certificationRepository
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete_theme_' . $theme->getId(),
            (string) $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.'
            );
        }

        if (!$theme->getCourses()->isEmpty()) {
            $this->addFlash(
                'danger',
                'Ce thème ne peut pas être supprimé car il contient encore des cursus.'
            );

            return $this->redirectToRoute(
                'app_admin_theme_index'
            );
        }

        $certificationCount = $certificationRepository->count([
            'theme' => $theme,
        ]);

        if ($certificationCount > 0) {
            $this->addFlash(
                'danger',
                'Ce thème ne peut pas être supprimé car il est lié à une certification utilisateur.'
            );

            return $this->redirectToRoute(
                'app_admin_theme_index'
            );
        }

        try {
            $entityManager->remove($theme);
            $entityManager->flush();
        } catch (ForeignKeyConstraintViolationException) {
            $this->addFlash(
                'danger',
                'Ce thème ne peut pas être supprimé car il est encore utilisé par des données du site.'
            );

            return $this->redirectToRoute(
                'app_admin_theme_index'
            );
        }

        $this->addFlash(
            'success',
            'Le thème a été supprimé avec succès.'
        );

        return $this->redirectToRoute(
            'app_admin_theme_index'
        );
    }

    private function generateUniqueSlug(
        string $name,
        SluggerInterface $slugger,
        EntityManagerInterface $entityManager,
        ?Theme $currentTheme = null
    ): string {
        $baseSlug = strtolower(
            $slugger->slug($name)->toString()
        );

        $slug = $baseSlug;
        $counter = 2;

        while (true) {
            $existingTheme = $entityManager
                ->getRepository(Theme::class)
                ->findOneBy([
                    'slug' => $slug,
                ]);

            if (
                $existingTheme === null
                || (
                    $currentTheme !== null
                    && $existingTheme->getId()
                        === $currentTheme->getId()
                )
            ) {
                return $slug;
            }

            $slug = $baseSlug . '-' . $counter;

            ++$counter;
        }
    }
}