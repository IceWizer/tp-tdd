<?php

namespace App\Repository;

use App\Entity\Member;
use DateInterval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Member>
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function findAllMembersWithCountOfUnreturnedBooks(): array
    {
        return $this->createQueryBuilder('m')
            ->select('m, COUNT(r) AS unreturnedCount')
            ->leftJoin('m.reservations', 'r')
            ->where('r.returnedAt IS NULL')
            ->andWhere('r.getAt < :currentDate')
            // 4 months
            ->setParameter('currentDate', (new \DateTime())->sub(new DateInterval('P4M')))
            ->groupBy('m.id')
            ->getQuery()
            ->getArrayResult();
    }
}
