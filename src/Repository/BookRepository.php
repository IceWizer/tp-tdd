<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findByTitleAuthorAndIsbn(string $title, string $author, string $isbn)
    {

        $queryBuilder = $this->createQueryBuilder('b');

        if (!empty($title)) {
            $queryBuilder->andWhere('b.title LIKE :title')
                ->setParameter('title', "%$title%");
        }

        if (!empty($author)) {
            $queryBuilder->andWhere('b.author LIKE :author')
                ->setParameter('author', "%$author%");
        }

        if (!empty($isbn)) {
            $queryBuilder->andWhere('b.isbn = :isbn')
                ->setParameter('isbn', $isbn);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
