<?php

namespace App\Controller;

use App\Entity\Certification;
use App\Entity\User;
use App\Repository\CertificationRepository;
use App\Service\CertificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CertificationController extends AbstractController
{
    #[Route(
        '/mes-certifications',
        name: 'app_certification_index',
        methods: ['GET']
    )]
    public function index(
        CertificationService $certificationService,
        CertificationRepository $certificationRepository
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $certificationService->synchronizeUserCertifications(
            $user
        );

        $certifications = $certificationRepository
            ->findByUserWithTheme(
                $user
            );

        return $this->render(
            'account/certifications.html.twig',
            [
                'certifications' => $certifications,
            ]
        );
    }

    #[Route(
        '/mes-certifications/{id}',
        name: 'app_certification_show',
        requirements: ['id' => '\d+'],
        methods: ['GET']
    )]
    public function show(
        int $id,
        CertificationRepository $certificationRepository
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $certification = $certificationRepository->findOneBy([
            'id' => $id,
            'user' => $user,
        ]);

        if (!$certification instanceof Certification) {
            throw $this->createNotFoundException(
                'Certification introuvable.'
            );
        }

        return $this->render(
            'account/certification_show.html.twig',
            [
                'certification' => $certification,
            ]
        );
    }
}