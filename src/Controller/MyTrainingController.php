<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CustomerOrderRepository;
use App\Repository\LessonProgressRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MyTrainingController extends AbstractController
{
    #[Route(
        '/mes-formations',
        name: 'app_my_training',
        methods: ['GET']
    )]
    public function index(
        CustomerOrderRepository $customerOrderRepository,
        LessonProgressRepository $lessonProgressRepository
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $paidOrders = $customerOrderRepository
            ->findPaidByUser($user);

        $pendingOrders = $customerOrderRepository
            ->findPendingByUser($user);

        $courseProgress = [];
        $lessonProgress = [];

        foreach ($paidOrders as $order) {
            foreach ($order->getItems() as $item) {
                $course = $item->getCourse();
                $lesson = $item->getLesson();

                if ($course !== null) {
                    $totalLessons = $course
                        ->getLessons()
                        ->count();

                    $completedLessons = $lessonProgressRepository
                        ->countCompletedLessonsForCourse(
                            $user,
                            $course
                        );

                    $courseProgress[$course->getId()] = [
                        'completed' => $completedLessons,
                        'total' => $totalLessons,
                        'isCompleted' => $lessonProgressRepository
                            ->isCourseCompleted(
                                $user,
                                $course
                            ),
                    ];

                    foreach ($course->getLessons() as $courseLesson) {
                        $lessonProgress[$courseLesson->getId()] =
                            $lessonProgressRepository
                                ->isLessonCompleted(
                                    $user,
                                    $courseLesson
                                );
                    }
                }

                if ($lesson !== null) {
                    $lessonProgress[$lesson->getId()] =
                        $lessonProgressRepository
                            ->isLessonCompleted(
                                $user,
                                $lesson
                            );
                }
            }
        }

        return $this->render(
            'account/my_training.html.twig',
            [
                'paidOrders' => $paidOrders,
                'pendingOrders' => $pendingOrders,
                'courseProgress' => $courseProgress,
                'lessonProgress' => $lessonProgress,
            ]
        );
    }
}