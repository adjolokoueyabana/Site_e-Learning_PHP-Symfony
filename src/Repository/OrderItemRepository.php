<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\CustomerOrder;
use App\Entity\Lesson;
use App\Entity\OrderItem;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrderItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItem::class);
    }

    public function userOwnsCourse(
        User $user,
        Course $course
    ): bool {
        $count = $this->createQueryBuilder('item')
            ->select('COUNT(item.id)')
            ->innerJoin('item.customerOrder', 'customerOrder')
            ->andWhere('customerOrder.user = :user')
            ->andWhere('customerOrder.status = :status')
            ->andWhere('item.course = :course')
            ->setParameter('user', $user)
            ->setParameter('status', CustomerOrder::STATUS_PAID)
            ->setParameter('course', $course)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }

    public function userOwnsLesson(
        User $user,
        Lesson $lesson
    ): bool {
        /*
         * A lesson is accessible when the user bought either
         * the lesson itself or its complete course.
         */
        $count = $this->createQueryBuilder('item')
            ->select('COUNT(item.id)')
            ->innerJoin('item.customerOrder', 'customerOrder')
            ->andWhere('customerOrder.user = :user')
            ->andWhere('customerOrder.status = :status')
            ->andWhere(
                'item.lesson = :lesson OR item.course = :course'
            )
            ->setParameter('user', $user)
            ->setParameter('status', CustomerOrder::STATUS_PAID)
            ->setParameter('lesson', $lesson)
            ->setParameter('course', $lesson->getCourse())
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }
}