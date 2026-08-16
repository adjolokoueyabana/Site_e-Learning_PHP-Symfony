<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\CustomerOrder;
use App\Entity\Lesson;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CustomerOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerOrder::class);
    }

    /**
     * Returns the paid orders of a user with their purchased content.
     *
     * Complete courses are loaded with their lessons so the user's
     * training dashboard can provide direct lesson access.
     *
     * @return CustomerOrder[]
     */
    public function findPaidByUser(User $user): array
    {
        return $this->createQueryBuilder('customerOrder')
            ->leftJoin('customerOrder.items', 'item')
            ->addSelect('item')
            ->leftJoin('item.course', 'course')
            ->addSelect('course')
            ->leftJoin('course.lessons', 'courseLesson')
            ->addSelect('courseLesson')
            ->leftJoin('item.lesson', 'lesson')
            ->addSelect('lesson')
            ->andWhere('customerOrder.user = :user')
            ->andWhere('customerOrder.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', CustomerOrder::STATUS_PAID)
            ->orderBy('customerOrder.createdAt', 'DESC')
            ->addOrderBy('courseLesson.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the pending orders of a user with their selected content.
     *
     * @return CustomerOrder[]
     */
    public function findPendingByUser(User $user): array
    {
        return $this->createQueryBuilder('customerOrder')
            ->leftJoin('customerOrder.items', 'item')
            ->addSelect('item')
            ->leftJoin('item.course', 'course')
            ->addSelect('course')
            ->leftJoin('item.lesson', 'lesson')
            ->addSelect('lesson')
            ->andWhere('customerOrder.user = :user')
            ->andWhere('customerOrder.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', CustomerOrder::STATUS_PENDING)
            ->orderBy('customerOrder.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds the most recent pending order for a specific course.
     */
    public function findPendingCourseOrder(
        User $user,
        Course $course
    ): ?CustomerOrder {
        return $this->createQueryBuilder('customerOrder')
            ->innerJoin('customerOrder.items', 'item')
            ->addSelect('item')
            ->andWhere('customerOrder.user = :user')
            ->andWhere('customerOrder.status = :status')
            ->andWhere('item.course = :course')
            ->setParameter('user', $user)
            ->setParameter('status', CustomerOrder::STATUS_PENDING)
            ->setParameter('course', $course)
            ->orderBy('customerOrder.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Finds the most recent pending order for a specific lesson.
     */
    public function findPendingLessonOrder(
        User $user,
        Lesson $lesson
    ): ?CustomerOrder {
        return $this->createQueryBuilder('customerOrder')
            ->innerJoin('customerOrder.items', 'item')
            ->addSelect('item')
            ->andWhere('customerOrder.user = :user')
            ->andWhere('customerOrder.status = :status')
            ->andWhere('item.lesson = :lesson')
            ->setParameter('user', $user)
            ->setParameter('status', CustomerOrder::STATUS_PENDING)
            ->setParameter('lesson', $lesson)
            ->orderBy('customerOrder.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}