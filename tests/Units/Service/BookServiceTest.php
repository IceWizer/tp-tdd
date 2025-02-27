<?php

namespace App\Tests\Units\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Format;
use App\Entity\Publisher;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Service\BookService;
use App\Tests\Common\BaseTestCase;
use App\Tests\Utils\Repository\BookRepositoryForTest;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class BookServiceTest extends BaseTestCase
{
    private AuthorRepository $authorRepository;
    private BookRepository $bookRepository;
    private FormatRepository $formatRepository;
    private PublisherRepository $publisherRepository;
    private EntityManagerInterface $em;
    private BookService $bookService;
    private WebServiceGetBookDataService $webServiceGetBookDataService;

    public function setUp(): void
    {
        $this->authorRepository = $this->createMock(AuthorRepository::class);
        $this->bookRepository = $this->createMock(BookRepository::class);
        $this->formatRepository = $this->createMock(FormatRepository::class);
        $this->publisherRepository = $this->createMock(PublisherRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->webServiceGetBookDataService = $this->createMock(WebServiceGetBookDataService::class);
        $this->bookService = new BookService($this->authorRepository, $this->bookRepository, $this->formatRepository, $this->publisherRepository, $this->em, $this->webServiceGetBookDataService);
    }

    public function testGetBooksReturnsEmptyArray()
    {
        $result = $this->bookService->getBooks();

        $this->assertIsArray($result);
        $this->assertEquals([], $result);
    }

    public function testGetBooksRepositoryCalled()
    {
        $this->bookRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $this->bookService->getBooks();
    }

    public function testAddBooks()
    {
        $author = new Author();
        $author->setFirstName('John');
        $author->setLastName('Doe');

        $format = new Format();
        $format->setLabel('Paperback');

        $publisher = new Publisher();
        $publisher->setLabel("Publisher Name");


        $book = new Book();
        $book->setIsbn('978-3-16-148410-0');
        $book->setTitle('Book 1');
        $book->setAuthor($author);
        $book->setFormat($format);
        $book->setPublisher($publisher);

        $this->em->expects($this->once())
            ->method('persist')
            ->with($book);

        $this->em->expects($this->once())
            ->method('flush');

        $this->bookService->addBook($book);
    }
}
