<?php

namespace App\Controller;

use App\Repository\BookRepository;
use App\Repository\MemberRepository;
use App\Repository\ReservationRepository;
use App\Service\ReservationService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

final class ReservationController extends AbstractController
{
    private BookRepository $bookRepository;
    private MemberRepository $memberRepository;
    private ReservationRepository $reservationRepository;
    private EntityManagerInterface $em;
    private ReservationService $reservationService;


    public function __construct(
        BookRepository $bookRepository,
        MemberRepository $memberRepository,
        ReservationRepository $reservationRepository,
        EntityManagerInterface $em,
        ReservationService $reservationService,
    ) {
        $this->bookRepository = $bookRepository;
        $this->memberRepository = $memberRepository;
        $this->reservationRepository = $reservationRepository;
        $this->em = $em;
        $this->reservationService = $reservationService;
    }

    #[Route("/reservation/open", methods: ["GET"])]
    public function allOpens(): Response
    {
        return $this->json(["data" => $this->reservationRepository->findBy(["returned_at" => null])]);
    }

    #[Route('/member/{memberId}/reservation/historic', name: 'app_reservation', methods: ["GET"])]
    public function historic(Request $request, string $memberId): Response
    {
        return $this->json(["data" => $this->reservationRepository->findBy(["member_id" => $memberId])]);
    }

    #[Route('/reservation/{reservationId}/return-book', name: 'app_member_reservations_return_book')]
    public function returnBook(string $reservationId): Response
    {
        $reservation = $this->reservationRepository->find(Uuid::fromString($reservationId)->toBinary());

        if ($reservation === null) {
            return $this->json(["message" => "Reservation not found."], Response::HTTP_NOT_FOUND);
        }

        $this->reservationService->endReservation($reservation);

        return $this->json(["message" => "Book returned successfully.", "reservationId" => $reservationId]);
    }

    #[Route('/member/{memberId}/reservation/book/{bookId}', methods: ["POST"])]
    public function reserveBook(string $memberId, string $bookId): Response
    {
        $member = $this->memberRepository->find(Uuid::fromString($memberId)->toBinary());
        $book = $this->bookRepository->find(Uuid::fromString($bookId)->toBinary());

        if ($member === null || $book === null) {
            return $this->json(["message" => "Member or book not found."], Response::HTTP_NOT_FOUND);
        }

        if ($this->reservationService->makeReservation($member, $book)) {
            return $this->json(["message" => "Book reserved successfully."], Response::HTTP_CREATED);
        }

        return $this->json(["message" => "Failed to reserve the book."], Response::HTTP_BAD_REQUEST);
    }

}
