<?php

namespace App\Tests\Units\Service;

use App\Entity\Book;
use App\Entity\User;
use App\Repository\ReservationRepository;
use App\Service\ReservationService;
use App\Tests\Common\BaseTestCase;
use Doctrine\ORM\EntityManagerInterface;

class ReservationServiceTest extends BaseTestCase
{
    private ReservationRepository $reservationRepository;
    private ReservationService $reservationService;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reservationRepository = $this->createMock(ReservationRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->reservationService = new ReservationService($this->reservationRepository, $this->em);
    }

    public function testMakeReservations()
    {
        $user = new User();
        $book = new Book();

        $result = $this->reservationService->makeReservation($user, $book);

        $this->assertTrue($result);
    }

    public function testMax3Reservations()
    {
        $user = new User();
        $book1 = new Book();
        $book2 = new Book();
        $book3 = new Book();
        $book4 = new Book();

        $this->reservationRepository->expects($this->exactly(4))
            ->method('countActiveReservationsByUser')
            ->willReturnCallback(function ($user) {
                static $count = 0;
                return $count++;
            });

        $this->reservationRepository->expects($this->exactly(4))
            ->method('countActiveReservationsByBook')
            ->willReturn(0);

        $result = $this->reservationService->makeReservation($user, $book1);

        $this->assertTrue($result);
        $result = $this->reservationService->makeReservation($user, $book2);

        $this->assertTrue($result);
        $result = $this->reservationService->makeReservation($user, $book3);

        $this->assertTrue($result);
        $result = $this->reservationService->makeReservation($user, $book4);

        $this->assertFalse($result);
    }

    public function testMax1BookReservedAtATime(): void
    {
        $user1 = new User();
        $user2 = new User();
        $book = new Book();

        $this->reservationRepository->expects($this->exactly(2))
            ->method('countActiveReservationsByUser')
            ->willReturn(0);

        $this->reservationRepository->expects($this->exactly(2))
            ->method('countActiveReservationsByBook')
            ->willReturnCallback(function ($book) {
                static $count = 0;
                return $count++;
            });

        $result = $this->reservationService->makeReservation($user1, $book);

        $this->assertTrue($result);
        $result = $this->reservationService->makeReservation($user2, $book);

        $this->assertFalse($result);
    }
}
