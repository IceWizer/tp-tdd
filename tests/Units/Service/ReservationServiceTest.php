<?php

namespace App\Tests\Units\Service;

use App\Entity\Book;
use App\Entity\User;
use App\Repository\ReservationRepository;
use App\Service\ReservationService;
use App\Tests\Common\BaseTestCase;

class ReservationServiceTest extends BaseTestCase
{
    private ReservationRepository $reservationRepository;
    private ReservationService $reservationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reservationRepository = $this->createMock(ReservationRepository::class);
        $this->reservationService = new ReservationService($this->reservationRepository);
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

        $result = $this->reservationService->makeReservation($user, $book1);

        $this->assertTrue($result);
        $result = $this->reservationService->makeReservation($user, $book2);

        $this->assertTrue($result);
        $result = $this->reservationService->makeReservation($user, $book3);

        $this->assertTrue($result);
        $result = $this->reservationService->makeReservation($user, $book4);

        $this->assertFalse($result);
    }
}
