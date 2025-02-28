<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Request\Book\Create;
use App\Service\BookService;
use App\Service\Validator\IsbnValidator;
use App\Service\Validator\IsbnValidatorFactory;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class BookController extends AbstractController
{
    private BookService $bookService;
    private BookRepository $bookRepository;
    private IsbnValidatorFactory $isbnValidatorFactory;
    private EntityManagerInterface $em;

    public function __construct(
        BookService $bookService,
        BookRepository $bookRepository,
        IsbnValidatorFactory $isbnValidatorFactory,
        EntityManagerInterface $em
    ) {
        $this->bookService = $bookService;
        $this->bookRepository = $bookRepository;
        $this->isbnValidatorFactory = $isbnValidatorFactory;
        $this->em = $em;
    }

    #[Route(path: "/api/book", name: "api_book_index", methods: ["GET"])]
    public function index()
    {
        $books = $this->bookRepository->findAll();
        return $this->json($books);
    }


    #[Route(path: "/api/book/{id}", name: "api_book_show", methods: ["GET"])]
    public function show(int $id)
    {
        $book = $this->bookRepository->find(Uuid::fromString($id)->toBinary());
        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }
        return $this->json($book);
    }


    #[Route(path: "/api/book", name: "api_book_create", methods: ["POST"])]
    public function create(Create $request)
    {
        try {
            /**
             * @var IsbnValidator
             */
            $isbnValidator = $this->isbnValidatorFactory->create();
            if (!$isbnValidator->validate($request->isbn)) {
                throw new InvalidArgumentException('ISBN is invalid');
            }
        } catch (InvalidArgumentException) {
            return $this->json(['error' => 'ISBN is invalid'], 400);
        }

        $book = new Book();
        $book = $this->bookService->fillFields($book);
        $this->bookService->addBook($book);
        return $this->json($book, 201);
    }

    #[Route(path: "/api/book/{id}", name: "api_book_update", methods: ["PUT"])]
    public function update(Create $request, string $id)
    {
        try {
            /**
             * @var IsbnValidator
             */
            $isbnValidator = $this->isbnValidatorFactory->create();
            if (!$isbnValidator->validate($request->isbn)) {
                throw new InvalidArgumentException('ISBN is invalid');
            }
        } catch (InvalidArgumentException) {
            return $this->json(['error' => 'ISBN is invalid'], 400);
        }

        $book = $this->bookRepository->find(Uuid::fromString($id)->toBinary());
        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        $book = $this->bookService->fillFields($book);
        $this->em->flush();

        return $this->json($book, 200);
    }

    #[Route(path: "/api/book/{id}", name: "api_book_delete", methods: ["DELETE"])]
    public function delete(string $id)
    {
        $book = $this->bookRepository->find(Uuid::fromString($id)->toBinary());
        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        $this->em->remove($book);
        $this->em->flush();

        return $this->json(['message' => 'Book deleted successfully'], 204);
    }

    #[Route(path: "/api/book/search", name: "api_book_search", methods: ["GET"])]
    public function search(Request $request)
    {
        $title = $request->query->get('title') ?? "";
        $author = $request->query->get('author') ?? "";
        $isbn = $request->query->get('isbn') ?? "";

        $books = $this->bookRepository->findByTitleAuthorAndIsbn(trim($title), trim($author), trim($isbn));
        return $this->json($books);
    }
}
