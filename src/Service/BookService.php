<?php

namespace App\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Format;
use App\Entity\Publisher;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Repository\FormatRepository;
use App\Repository\PublisherRepository;
use Doctrine\ORM\EntityManagerInterface;

class BookService
{
    private AuthorRepository $authorRepository;
    private BookRepository $bookRepository;
    private FormatRepository $formatRepository;
    private PublisherRepository $publisherRepository;

    private EntityManagerInterface $em;
    private WebServiceGetBookDataService $webServiceGetBookDataService;

    public function __construct(
        AuthorRepository $authorRepository,
        BookRepository $bookRepository,
        FormatRepository $formatRepository,
        PublisherRepository $publisherRepository,
        EntityManagerInterface $em,
        WebServiceGetBookDataService $webServiceGetBookDataService
    ) {
        $this->authorRepository = $authorRepository;
        $this->bookRepository = $bookRepository;
        $this->formatRepository = $formatRepository;
        $this->publisherRepository = $publisherRepository;
        $this->em = $em;
        $this->webServiceGetBookDataService = $webServiceGetBookDataService;
    }

    public function getBooks(): array
    {
        return $this->bookRepository->findAll();
    }

    public function addBook(Book $book)
    {
        $this->em->persist($book);
        $this->em->flush();
    }

    public function fillFields(Book $book): Book
    {
        $bookResponse = $this->webServiceGetBookDataService->getBookData($book->getIsbn());

        if ($book->getTitle() === null) {
            $book->setTitle($bookResponse["title"]);
        }
        if ($book->getAuthor() === null) {
            $author = $this->authorRepository->findOneBy(["lastName" => $bookResponse["author"]["lastName"]]);

            if ($author === null) {
                $author = new Author();
                $author->setLastName($bookResponse["author"]["lastName"]);
                $author->setFirstName($bookResponse["author"]["firstName"]);
                $author->setPseudo($bookResponse["author"]["pseudo"]);
                $this->em->persist($author);
            }

            $book->setAuthor($author);
        }
        if ($book->getFormat() === null) {
            $format = $this->formatRepository->findOneBy(["label" => $bookResponse["format"]["label"]]);

            if ($format === null) {
                $format = new Format();
                $format->setLabel($bookResponse["format"]["label"]);
                $this->em->persist($format);
            }

            $book->setFormat($format);
        }

        if ($book->getPublisher() === null) {
            $publisher = $this->publisherRepository->findOneBy(["label" => $bookResponse["publisher"]["label"]]);

            if ($publisher === null) {
                $publisher = new Publisher();
                $publisher->setLabel($bookResponse["publisher"]["label"]);
                $this->em->persist($publisher);
            }

            $book->setPublisher($publisher);
        }
        return $book;
    }

    public function removeBook(Book $book)
    {
        $this->em->remove($book);
        $this->em->flush();
    }
}
