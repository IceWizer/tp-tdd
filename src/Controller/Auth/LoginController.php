<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Email as ConstraintsEmail;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;

class LoginController extends AbstractController
{
    /**
     * Unable to test this method because it's handled by LexikJWTAuthenticationBundle
     * @codeCoverageIgnore
     */
    #[Route('/api/auth/login', name: 'api_login')]
    public function login(#[CurrentUser] ?User $user): Response
    {
        return $this->json([
            'user' => $user,
        ]);
    }

    #[Route('/api/auth/logout', name: 'api_logout')]
    public function logout(): Response
    {
        return $this->json([
            'message' => 'logout successful',
        ]);
    }

    #[Route("/api/auth/register", name: "api_register", methods: ["POST"])]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository, MailerInterface $mailer): Response
    {
        $payload = $request->getPayload();

        // Check the unicity of the email
        if ($userRepository->findOneBy(['email' => $payload->get('email')]) !== null) {
            return $this->json([
                'message' => 'Email already exists',
            ], 400);
        }

        // Validate the payload
        $validator = Validation::createValidator();

        $rules = [
            'email' => [new Required(), new NotBlank(message: "email.not_blank"), new ConstraintsEmail(message: "email.format")],
            'password' => [new Required(), new NotBlank(message: "password.not_blank"), new Type('string', "password.string")],
        ];

        $violations = [];
        foreach ($rules as $key => $value) {
            $violation = $validator->validate($payload->get($key), $value);
            if ($violation->has(0)) {
                $violations[$key] = $violation->get(0)->getMessage();
            }
        }

        if (empty($violations) === false) {
            return $this->json([
                'message' => 'Validation error',
                'errors' => $violations,
            ], 400);
        }

        /** @var string */
        $emailField = $payload->get('email');
        /** @var string */
        $passwordField = $payload->get('password');

        $em->beginTransaction();

        $user = new User();

        $user->setEmail($emailField);
        // Hash the password
        $user->setPassword($passwordHasher->hashPassword($user, $passwordField));

        $user->setToken($userRepository->generateToken(250, 350));

        $em->persist($user);
        $em->flush();

        $email = (new Email())
            ->to($emailField)
            ->subject('Vérification de votre adresse email')
            ->text('Veuillez cliquer sur ce lien pour valider votre adresse email')
            ->html('<a href="' . $this->getParameter("app.app_url") . 'verify-email/' . $user->getToken() . '">Cliquez ici pour valider votre adresse email</a>');

        try {
            $mailer->send($email);
        } catch (\Exception $e) {
            $em->rollback();
            return $this->json([
                'message' => 'An error occurred while sending the email',
            ], 500);
        }

        $em->commit();

        return $this->json([
            'message' => 'User registered successfully',
        ]);
    }

    #[Route("/api/auth/verify-email/{token}", name: "api_verify_email", methods: ["POST"])]
    public function verifyEmail(string $token, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['token' => $token]);

        if (!$user) {
            return $this->json([
                'message' => 'Invalid token',
            ], 400);
        }

        $user->setEmailVerifiedAt(new \DateTimeImmutable());
        $user->setToken(null);

        $em->flush();

        return $this->json([
            'message' => 'Email verified successfully',
        ]);
    }

    #[Route("/api/auth/forgot-password", name: "api_forgot_password", methods: ["POST"], options: ["no_auth" => true])]
    public function forgotPassword(Request $request, EntityManagerInterface $em, UserRepository $userRepository, MailerInterface $mailer): Response
    {
        $payload = $request->getPayload();

        $em->beginTransaction();

        /** @var string */
        $emailField = $payload->get('email');

        $user = $userRepository->findOneBy(['email' => $emailField]);

        if (!$user) {
            return $this->json([
                'message' => 'OK',
            ]);
        }

        $user->setToken($userRepository->generateToken(250, 350));
        $user->setEmailVerifiedAt(null);

        $email = (new Email())
            ->to($emailField)
            ->subject('Récupération de mot de passe')
            ->text('Veuillez cliquer sur ce lien pour réinitialiser votre mot de passe')
            ->html('<a href="' . $this->getParameter("app.app_url") . 'reset-password/' . $user->getToken() . '">Cliquez ici pour réinitialiser votre mot de passe</a>');

        try {
            $mailer->send($email);
        } catch (\Exception $e) {
            $em->rollback();
            return $this->json([
                'message' => 'An error occurred while sending the email',
            ], 500);
        }

        $em->flush();
        $em->commit();

        return $this->json([
            'message' => 'OK',
        ]);
    }

    #[Route("/api/auth/reset-password/{token}", name: "api_reset_password", methods: ["POST"])]
    public function resetPassword(Request $request, string $token, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['token' => $token]);

        if ($user === null) {
            return $this->json([
                'message' => 'Invalid token',
            ], 400);
        }

        $payload = $request->getPayload();

        // Validate the payload
        $validator = Validation::createValidator();

        $rules = [
            'password' => [new Required(), new Type('string')],
        ];

        $violations = [];
        foreach ($rules as $key => $value) {
            $violation = $validator->validate($payload->get($key), $value);
            if (count($violation) > 0) {
                $violations[$key] = $violation->get(0)->getMessage();
            }
        }

        if (count($violations) > 0) {
            return $this->json([
                'message' => 'Validation error',
                'errors' => $violations,
            ], 400);
        }

        /** @var string */
        $passwordField = $payload->get('password');

        $payload = $request->getPayload();

        $user->setPassword($passwordHasher->hashPassword($user, $passwordField));
        $user->setToken(null);
        $user->setEmailVerifiedAt(new \DateTimeImmutable());

        $em->flush();

        return $this->json([
            'message' => 'Password reset successfully',
        ]);
    }
}
