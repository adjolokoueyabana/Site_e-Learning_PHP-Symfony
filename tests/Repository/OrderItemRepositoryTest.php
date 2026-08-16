<?php

namespace App\Tests\Repository;

use App\Entity\Course;
use App\Entity\CustomerOrder;
use App\Entity\Lesson;
use App\Entity\OrderItem;
use App\Entity\Role;
use App\Entity\Theme;
use App\Entity\User;
use App\Repository\OrderItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class OrderItemRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    private OrderItemRepository $orderItemRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(
            EntityManagerInterface::class
        );

        /** @var OrderItemRepository $orderItemRepository */
        $orderItemRepository = static::getContainer()->get(
            OrderItemRepository::class
        );

        $this->entityManager = $entityManager;
        $this->orderItemRepository = $orderItemRepository;

        $this->entityManager
            ->getConnection()
            ->beginTransaction();
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();

        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }

        $this->entityManager->clear();

        parent::tearDown();
    }

    public function testPendingCourseDoesNotGrantOwnership(): void
    {
        [
            'user' => $user,
            'course' => $course,
        ] = $this->createLearningData();

        $this->createCourseOrder(
            $user,
            $course,
            CustomerOrder::STATUS_PENDING
        );

        $this->entityManager->flush();

        self::assertFalse(
            $this->orderItemRepository->userOwnsCourse(
                $user,
                $course
            )
        );
    }

    public function testPaidCourseGrantsCourseOwnership(): void
    {
        [
            'user' => $user,
            'course' => $course,
        ] = $this->createLearningData();

        $this->createCourseOrder(
            $user,
            $course,
            CustomerOrder::STATUS_PAID
        );

        $this->entityManager->flush();

        self::assertTrue(
            $this->orderItemRepository->userOwnsCourse(
                $user,
                $course
            )
        );
    }

    public function testPaidLessonGrantsLessonOwnership(): void
    {
        [
            'user' => $user,
            'lesson' => $lesson,
        ] = $this->createLearningData();

        $this->createLessonOrder(
            $user,
            $lesson,
            CustomerOrder::STATUS_PAID
        );

        $this->entityManager->flush();

        self::assertTrue(
            $this->orderItemRepository->userOwnsLesson(
                $user,
                $lesson
            )
        );
    }

    public function testPaidCourseGrantsAccessToItsLesson(): void
    {
        [
            'user' => $user,
            'course' => $course,
            'lesson' => $lesson,
        ] = $this->createLearningData();

        $this->createCourseOrder(
            $user,
            $course,
            CustomerOrder::STATUS_PAID
        );

        $this->entityManager->flush();

        self::assertTrue(
            $this->orderItemRepository->userOwnsLesson(
                $user,
                $lesson
            )
        );
    }

    public function testAnotherUserDoesNotOwnPurchasedContent(): void
    {
        [
            'user' => $buyer,
            'course' => $course,
            'lesson' => $lesson,
            'role' => $role,
        ] = $this->createLearningData();

        $otherUser = $this->createUser(
            'other-user@example.test',
            $role
        );

        $this->createCourseOrder(
            $buyer,
            $course,
            CustomerOrder::STATUS_PAID
        );

        $this->entityManager->flush();

        self::assertFalse(
            $this->orderItemRepository->userOwnsCourse(
                $otherUser,
                $course
            )
        );

        self::assertFalse(
            $this->orderItemRepository->userOwnsLesson(
                $otherUser,
                $lesson
            )
        );
    }

    /**
     * @return array{
     *     role: Role,
     *     user: User,
     *     theme: Theme,
     *     course: Course,
     *     lesson: Lesson
     * }
     */
    private function createLearningData(): array
    {
        $suffix = bin2hex(
            random_bytes(6)
        );

        $role = new Role();

        $role->setName(
            'ROLE_REPOSITORY_TEST_' . $suffix
        );

        $this->entityManager->persist(
            $role
        );

        $user = $this->createUser(
            'buyer-' . $suffix . '@example.test',
            $role
        );

        $theme = new Theme();

        $theme
            ->setName(
                'Theme Repository Test ' . $suffix
            )
            ->setSlug(
                'theme-repository-test-' . $suffix
            )
            ->setDescription(
                'Theme created for repository tests.'
            );

        $this->entityManager->persist(
            $theme
        );

        $course = new Course();

        $course
            ->setTitle(
                'Course Repository Test ' . $suffix
            )
            ->setSlug(
                'course-repository-test-' . $suffix
            )
            ->setDescription(
                'Course created for repository tests.'
            )
            ->setPrice('60.00')
            ->setTheme($theme);

        $this->entityManager->persist(
            $course
        );

        $lesson = new Lesson();

        $lesson
            ->setTitle(
                'Lesson Repository Test ' . $suffix
            )
            ->setSlug(
                'lesson-repository-test-' . $suffix
            )
            ->setContent(
                'Lesson content used for repository tests.'
            )
            ->setVideoUrl(null)
            ->setPrice('20.00')
            ->setPosition(1)
            ->setCourse($course);

        $this->entityManager->persist(
            $lesson
        );

        return [
            'role' => $role,
            'user' => $user,
            'theme' => $theme,
            'course' => $course,
            'lesson' => $lesson,
        ];
    }

    private function createUser(
        string $email,
        Role $role
    ): User {
        $user = new User();

        $user
            ->setEmail($email)
            ->setPassword(
                'repository-test-password'
            )
            ->setFirstName('Repository')
            ->setLastName('Test')
            ->setVerified(true)
            ->setRole($role);

        $this->entityManager->persist(
            $user
        );

        return $user;
    }

    private function createCourseOrder(
        User $user,
        Course $course,
        string $status
    ): CustomerOrder {
        $order = new CustomerOrder();

        $order
            ->setUser($user)
            ->setStatus($status)
            ->setTotalAmount(
                (string) $course->getPrice()
            );

        $item = new OrderItem();

        $item
            ->setCourse($course)
            ->setTitleSnapshot(
                (string) $course->getTitle()
            )
            ->setUnitPrice(
                (string) $course->getPrice()
            );

        $order->addItem(
            $item
        );

        $this->entityManager->persist(
            $order
        );

        return $order;
    }

    private function createLessonOrder(
        User $user,
        Lesson $lesson,
        string $status
    ): CustomerOrder {
        $order = new CustomerOrder();

        $order
            ->setUser($user)
            ->setStatus($status)
            ->setTotalAmount(
                (string) $lesson->getPrice()
            );

        $item = new OrderItem();

        $item
            ->setLesson($lesson)
            ->setTitleSnapshot(
                (string) $lesson->getTitle()
            )
            ->setUnitPrice(
                (string) $lesson->getPrice()
            );

        $order->addItem(
            $item
        );

        $this->entityManager->persist(
            $order
        );

        return $order;
    }
}