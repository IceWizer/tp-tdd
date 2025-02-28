<?php

namespace App\Controller;

use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/member')]
final class MemberController extends AbstractController
{
    private MemberRepository $memberRepository;

    public function __construct(MemberRepository $memberRepository)
    {
        $this->memberRepository = $memberRepository;
    }

    #[Route('/notify-unreturned-books', methods: ['POST'])]
    public function sendUnreturnedMail(): Response
    {
        $members = $this->memberRepository->findAllMembersWithCountOfUnreturnedBooks();

        foreach ($members as $member) {
            if ($member['unreturned_books_count'] > 0) {
                $this->notifyMember($member);
            }
        }

        return $this->json(['message' => 'Notification emails sent successfully.']);
    }

    private function notifyMember($member): void
    {
        $email = $member['email'];
        $subject = "Unreturned Books Notification";
        $message = "Dear " . $member['name'] . ",\n\nYou have these unreturned books. Please return them at your earliest convenience.\n\nThank you.";

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            mail($email, $subject, $message);
        } else {
            error_log("Invalid email address: $email");
        }
    }
}
