<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\User;
use App\Repository\LessonProgressRepository;
use App\Repository\OrderItemRepository;
use App\Repository\ThemeRepository;
use App\Service\ContentAccessService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TrainingController extends AbstractController
{
    #[Route(
        '/formations',
        name: 'app_training_index',
        methods: ['GET']
    )]
    public function index(
        ThemeRepository $themeRepository
    ): Response {
        return $this->render(
            'training/index.html.twig',
            [
                'themes' => $themeRepository->findAllWithCourses(),
            ]
        );
    }

    #[Route(
        '/cursus/{slug}',
        name: 'app_course_show',
        methods: ['GET']
    )]
    public function show(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Course $course,
        OrderItemRepository $orderItemRepository,
        ContentAccessService $contentAccessService,
        LessonProgressRepository $lessonProgressRepository
    ): Response {
        $user = $this->getUser();

        $authenticatedUser = $user instanceof User
            ? $user
            : null;

        $ownsCourse = false;
        $isCourseCompleted = false;
        $completedLessonCount = 0;

        if ($authenticatedUser !== null) {
            $ownsCourse = $orderItemRepository->userOwnsCourse(
                $authenticatedUser,
                $course
            );

            $completedLessonCount = $lessonProgressRepository
                ->countCompletedLessonsForCourse(
                    $authenticatedUser,
                    $course
                );

            $isCourseCompleted = $lessonProgressRepository
                ->isCourseCompleted(
                    $authenticatedUser,
                    $course
                );
        }

        $lessons = $course->getLessons()->toArray();

        usort(
            $lessons,
            static fn (
                Lesson $firstLesson,
                Lesson $secondLesson
            ): int => $firstLesson->getPosition()
                <=> $secondLesson->getPosition()
        );

        $lessonStates = [];

        foreach ($lessons as $lesson) {
            $canAccess = false;
            $isCompleted = false;

            if ($authenticatedUser !== null) {
                $canAccess = $contentAccessService
                    ->canAccessLesson(
                        $authenticatedUser,
                        $lesson
                    );

                if ($canAccess) {
                    $isCompleted = $lessonProgressRepository
                        ->isLessonCompleted(
                            $authenticatedUser,
                            $lesson
                        );
                }
            }

            $lessonStates[$lesson->getId()] = [
                'canAccess' => $canAccess,
                'isCompleted' => $isCompleted,
            ];
        }

        return $this->render(
            'training/show.html.twig',
            [
                'course' => $course,
                'lessons' => $lessons,
                'ownsCourse' => $ownsCourse,
                'completedLessonCount' => $completedLessonCount,
                'isCourseCompleted' => $isCourseCompleted,
                'lessonStates' => $lessonStates,
            ]
        );
    }

    #[Route(
        '/lecon/{slug}',
        name: 'app_lesson_show',
        methods: ['GET']
    )]
    public function lesson(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Lesson $lesson,
        ContentAccessService $contentAccessService,
        LessonProgressRepository $lessonProgressRepository
    ): Response {
        $user = $this->getUser();

        $authenticatedUser = $user instanceof User
            ? $user
            : null;

        $canAccessLesson = $contentAccessService->canAccessLesson(
            $authenticatedUser,
            $lesson
        );

        $isLessonCompleted = false;
        $isCourseCompleted = false;

        if (
            $authenticatedUser !== null
            && $canAccessLesson
        ) {
            $isLessonCompleted = $lessonProgressRepository
                ->isLessonCompleted(
                    $authenticatedUser,
                    $lesson
                );

            $isCourseCompleted = $lessonProgressRepository
                ->isCourseCompleted(
                    $authenticatedUser,
                    $lesson->getCourse()
                );
        }

        $courseLessons = $lesson
            ->getCourse()
            ->getLessons()
            ->toArray();

        usort(
            $courseLessons,
            static fn (
                Lesson $firstLesson,
                Lesson $secondLesson
            ): int => $firstLesson->getPosition()
                <=> $secondLesson->getPosition()
        );

        $previousLesson = null;
        $nextLesson = null;

        foreach ($courseLessons as $index => $courseLesson) {
            if ($courseLesson->getId() !== $lesson->getId()) {
                continue;
            }

            if ($index > 0) {
                $previousLesson = $courseLessons[$index - 1];
            }

            if ($index < count($courseLessons) - 1) {
                $nextLesson = $courseLessons[$index + 1];
            }

            break;
        }

        $canAccessPreviousLesson = false;
        $canAccessNextLesson = false;

        if ($authenticatedUser !== null) {
            if ($previousLesson instanceof Lesson) {
                $canAccessPreviousLesson = $contentAccessService
                    ->canAccessLesson(
                        $authenticatedUser,
                        $previousLesson
                    );
            }

            if ($nextLesson instanceof Lesson) {
                $canAccessNextLesson = $contentAccessService
                    ->canAccessLesson(
                        $authenticatedUser,
                        $nextLesson
                    );
            }
        }

        return $this->render(
            'training/lesson.html.twig',
            [
                'lesson' => $lesson,
                'canAccessLesson' => $canAccessLesson,
                'isLessonCompleted' => $isLessonCompleted,
                'isCourseCompleted' => $isCourseCompleted,
                'previousLesson' => $previousLesson,
                'nextLesson' => $nextLesson,
                'canAccessPreviousLesson' => $canAccessPreviousLesson,
                'canAccessNextLesson' => $canAccessNextLesson,
            ]
        );
    }
}