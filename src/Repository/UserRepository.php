<?php

namespace App\Repository;

use App\Entity\User;
use App\Utils\StrRandom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private StrRandom $strRandom)
    {
        parent::__construct($registry, User::class);
    }

    public function generateToken(int $min, int $max): string
    {
        $token = $this->strRandom->generateRandomString($min, $max);

        while ($this->findOneBy(['token' => $token])) {
            $token = $this->strRandom->generateRandomString($min, $max);
        }

        return $token;
    }
}
