<?php

namespace App\Tests\Service;

use App\Entity\Course;
use App\Entity\CustomerOrder;
use App\Entity\Lesson;
use App\Entity\User;
use App\Repository\CustomerOrderRepository;
use App\Repository\OrderItemRepository;
use App\Service\PurchaseService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class PurchaseServiceTest extends TestCase
{
    public function testUnverifiedUserCannotPurchaseCourse(): void
    {
        $entityManager = $this->createMock(
            EntityManagerInterface::class
        );

        $orderItemRepository = $this->createMock(
            OrderItemRepository::class
        );

        $customerOrderRepository = $this->createMock(
            CustomerOrderRepository::class
        );

        $user = new User();
        $user->setVerified(false);

        $course = $this->createMock(
            Course::class
        );

        $entityManager
            ->expects(self::never())
            ->method('persist');

        $entityManager
            ->expects(self::never())
            ->method('flush');

        $service = new PurchaseService(
            $entityManager,
            $orderItemRepository,
            $customerOrderRepository
        );

        $this->expectException(
            \DomainException::class
        );

        $this->expectExceptionMessage(
            'Vous devez activer votre compte avant de pouvoir effectuer un achat.'
        );

        $service->createCourseOrder(
            $user,
            $course
        );
    }

    public function testVerifiedUserCanCreateCourseOrder(): void
    {
        $entityManager = $this->createMock(
            EntityManagerInterface::class
        );

        $orderItemRepository = $this->createMock(
            OrderItemRepository::class
        );

        $customerOrderRepository = $this->createMock(
            CustomerOrderRepository::class
        );

        $user = new User();
        $user->setVerified(true);

        $course = $this->createMock(
            Course::class
        );

        $course
            ->method('getTitle')
            ->willReturn('Test Course');

        $course
            ->method('getPrice')
            ->willReturn('60.00');

        $orderItemRepository
            ->expects(self::once())
            ->method('userOwnsCourse')
            ->with($user, $course)
            ->willReturn(false);

        $customerOrderRepository
            ->expects(self::once())
            ->method('findPendingCourseOrder')
            ->with($user, $course)
            ->willReturn(null);

        $entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(
                self::isInstanceOf(
                    CustomerOrder::class
                )
            );

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $service = new PurchaseService(
            $entityManager,
            $orderItemRepository,
            $customerOrderRepository
        );

        $order = $service->createCourseOrder(
            $user,
            $course
        );

        self::assertSame(
            CustomerOrder::STATUS_PENDING,
            $order->getStatus()
        );

        self::assertSame(
            '60.00',
            $order->getTotalAmount()
        );

        self::assertSame(
            $user,
            $order->getUser()
        );

        self::assertCount(
            1,
            $order->getItems()
        );
    }

    public function testPendingCourseOrderIsReused(): void
    {
        $entityManager = $this->createMock(
            EntityManagerInterface::class
        );

        $orderItemRepository = $this->createMock(
            OrderItemRepository::class
        );

        $customerOrderRepository = $this->createMock(
            CustomerOrderRepository::class
        );

        $user = new User();
        $user->setVerified(true);

        $course = $this->createMock(
            Course::class
        );

        $pendingOrder = new CustomerOrder();

        $orderItemRepository
            ->expects(self::once())
            ->method('userOwnsCourse')
            ->with($user, $course)
            ->willReturn(false);

        $customerOrderRepository
            ->expects(self::once())
            ->method('findPendingCourseOrder')
            ->with($user, $course)
            ->willReturn($pendingOrder);

        $entityManager
            ->expects(self::never())
            ->method('persist');

        $entityManager
            ->expects(self::never())
            ->method('flush');

        $service = new PurchaseService(
            $entityManager,
            $orderItemRepository,
            $customerOrderRepository
        );

        $result = $service->createCourseOrder(
            $user,
            $course
        );

        self::assertSame(
            $pendingOrder,
            $result
        );
    }

    public function testUserCannotBuyOwnedCourseAgain(): void
    {
        $entityManager = $this->createMock(
            EntityManagerInterface::class
        );

        $orderItemRepository = $this->createMock(
            OrderItemRepository::class
        );

        $customerOrderRepository = $this->createMock(
            CustomerOrderRepository::class
        );

        $user = new User();
        $user->setVerified(true);

        $course = $this->createMock(
            Course::class
        );

        $orderItemRepository
            ->expects(self::once())
            ->method('userOwnsCourse')
            ->with($user, $course)
            ->willReturn(true);

        $customerOrderRepository
            ->expects(self::never())
            ->method('findPendingCourseOrder');

        $entityManager
            ->expects(self::never())
            ->method('persist');

        $entityManager
            ->expects(self::never())
            ->method('flush');

        $service = new PurchaseService(
            $entityManager,
            $orderItemRepository,
            $customerOrderRepository
        );

        $this->expectException(
            \DomainException::class
        );

        $this->expectExceptionMessage(
            'Vous avez déjà acheté ce cursus.'
        );

        $service->createCourseOrder(
            $user,
            $course
        );
    }

    public function testVerifiedUserCanCreateLessonOrder(): void
    {
        $entityManager = $this->createMock(
            EntityManagerInterface::class
        );

        $orderItemRepository = $this->createMock(
            OrderItemRepository::class
        );

        $customerOrderRepository = $this->createMock(
            CustomerOrderRepository::class
        );

        $user = new User();
        $user->setVerified(true);

        $lesson = $this->createMock(
            Lesson::class
        );

        $lesson
            ->method('getTitle')
            ->willReturn('Test Lesson');

        $lesson
            ->method('getPrice')
            ->willReturn('10.00');

        $orderItemRepository
            ->expects(self::once())
            ->method('userOwnsLesson')
            ->with($user, $lesson)
            ->willReturn(false);

        $customerOrderRepository
            ->expects(self::once())
            ->method('findPendingLessonOrder')
            ->with($user, $lesson)
            ->willReturn(null);

        $entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(
                self::isInstanceOf(
                    CustomerOrder::class
                )
            );

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $service = new PurchaseService(
            $entityManager,
            $orderItemRepository,
            $customerOrderRepository
        );

        $order = $service->createLessonOrder(
            $user,
            $lesson
        );

        self::assertSame(
            CustomerOrder::STATUS_PENDING,
            $order->getStatus()
        );

        self::assertSame(
            '10.00',
            $order->getTotalAmount()
        );

        self::assertCount(
            1,
            $order->getItems()
        );
    }

    public function testUserCannotBuyOwnedLessonAgain(): void
    {
        $entityManager = $this->createMock(
            EntityManagerInterface::class
        );

        $orderItemRepository = $this->createMock(
            OrderItemRepository::class
        );

        $customerOrderRepository = $this->createMock(
            CustomerOrderRepository::class
        );

        $user = new User();
        $user->setVerified(true);

        $lesson = $this->createMock(
            Lesson::class
        );

        $orderItemRepository
            ->expects(self::once())
            ->method('userOwnsLesson')
            ->with($user, $lesson)
            ->willReturn(true);

        $customerOrderRepository
            ->expects(self::never())
            ->method('findPendingLessonOrder');

        $entityManager
            ->expects(self::never())
            ->method('persist');

        $entityManager
            ->expects(self::never())
            ->method('flush');

        $service = new PurchaseService(
            $entityManager,
            $orderItemRepository,
            $customerOrderRepository
        );

        $this->expectException(
            \DomainException::class
        );

        $this->expectExceptionMessage(
            'Vous avez déjà accès à cette leçon.'
        );

        $service->createLessonOrder(
            $user,
            $lesson
        );
    }
}