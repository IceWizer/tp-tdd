<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\Reservation;
use App\Entity\User;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReservationService
{
    private ReservationRepository $reservationRepository;
    private EntityManagerInterface $em;


    public function __construct(
        ReservationRepository $reservationRepository,
        EntityManagerInterface $em
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->em = $em;
    }

    public function makeReservation(User $user, Book $book)
    {

        $currentReservationsForMember = $this->reservationRepository->countActiveReservationsByUser($user);
        $isBookReserved = $this->reservationRepository->countActiveReservationsByBook($book) > 0;

        if ($currentReservationsForMember >= 3 || $isBookReserved) {
            return false;
        }

        $this->reservationRepository->saveReservation($user, $book);

        return true;
    }

    public function endReservation(Reservation $reservation)
    {
        $reservation->setReturnedAt(new \DateTimeImmutable());
        $this->em->flush();
    }
}
