<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function countActiveReservationsByUser(User $user)
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.user = :user')
            ->andWhere('r.active = true')
            ->setParameter('user', $user->getId()->toBinary())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countActiveReservationsByBook(Book $book)
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.book = :book')
            ->andWhere('r.active = true')
            ->setParameter('book', $book->getId()->toBinary())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
