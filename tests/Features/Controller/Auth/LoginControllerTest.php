<?php

namespace App\Tests\Features\Controller\Auth;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class LoginControllerTest extends WebTestCase
{
    public \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    public EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->enableProfiler();
        /** @var EntityManagerInterface */
        $em = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager');
        $this->em = $em;

        //Annule le commit
        /** @var \Doctrine\DBAL\Connection @connection */
        $connection = $em->getConnection();
        $connection->beginTransaction();
        $connection->setAutoCommit(false);
    }

    /**
     * @covers \App\Controller\Auth\LoginController::login
     */
    public function testLogin(): string
    {
        $this->client->jsonRequest(
            'POST',
            '/api/auth/login',
            [
                'email' => 'admin@icewize.fr',
                'password' => 'Not24get',
            ]
        );

        $this->assertResponseIsSuccessful();

        $content = $this->client->getResponse()->getContent();
        if ($content === false) {
            $this->fail('The response content is empty');
        }
        $this->assertJson($content);
        $responseData = json_decode($content, true);

        if (is_array($responseData) === false) {
            $this->fail('The response content is not a valid JSON');
        }
        $this->assertArrayHasKey('token', $responseData);

        if (array_key_exists('token', $responseData) === false) {
            $this->fail('The response content does not contain the token key');
        }
        $this->assertIsString($responseData['token']);

        return $responseData['token'];
    }

    /**
     * @depends testLogin
     * @covers \App\Controller\Auth\LoginController::logout
     */
    public function testLogout(string $token): void
    {
        $this->client->setServerParameter('HTTP_Authorization', 'Bearer ' . $token);
        $this->client->jsonRequest(
            'POST',
            '/api/auth/logout'
        );

        $this->assertResponseIsSuccessful();
    }

    /**
     * @covers \App\Controller\Auth\LoginController::register
     */
    public function testRegister(): string
    {
        $this->client->jsonRequest(
            'POST',
            '/api/auth/register',
            [
                'email' => 'admin@icewize.fr',
                'password' => 'Not24get',
            ]
        );

        $content = $this->client->getResponse()->getContent();
        $this->assertResponseStatusCodeSame(400);

        if ($content === false) {
            $this->fail('The response content is empty');
        }
        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString(
            '{"message":"Email already exists"}',
            $content
        );

        $this->client->jsonRequest(
            'POST',
            '/api/auth/register',
            [
                'email' => 'new-user@icewize.fr',
                'password' => 'Not24get',
            ]
        );

        $content = $this->client->getResponse()->getContent();
        $this->assertResponseIsSuccessful();

        if ($content === false) {
            $this->fail('The response content is empty');
        }
        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString(
            '{"message":"User registered successfully"}',
            $content
        );

        $user = $this->em->getRepository(\App\Entity\User::class)
            ->findOneBy(['email' => 'new-user@icewize.fr']);

        $this->assertNotNull($user);
        $this->assertNotEmpty($user->getToken());
        // Check if the token is unique
        $this->assertSame(
            count(
                $this->em->getRepository(\App\Entity\User::class)
                    ->findBy(['token' => $user->getToken()])
            ),
            1
        );
        // Check if the token is between 250 and 350 characters
        $this->assertGreaterThanOrEqual(250, strlen($user->getToken()));
        $this->assertLessThanOrEqual(350, strlen($user->getToken()));

        return $user->getToken();
    }

    /**
     * @depends testRegister
     * @covers \App\Controller\Auth\LoginController::verifyEmail
     */
    public function testVerifyEmail(string $token): void
    {
        $this->client->jsonRequest(
            'POST',
            '/api/auth/verify-email/blablabla' // Invalid token
        );

        $content = $this->client->getResponse()->getContent();
        $this->assertResponseStatusCodeSame(400);
        if ($content === false) {
            $this->fail('The response content is empty');
        }
        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString(
            '{"message":"Invalid token"}',
            $content
        );

        $this->client->jsonRequest(
            'POST',
            '/api/auth/verify-email/' . $token
        );

        $content = $this->client->getResponse()->getContent();
        $this->assertResponseIsSuccessful();
        if ($content === false) {
            $this->fail('The response content is empty');
        }
        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString(
            '{"message":"Email verified successfully"}',
            $content
        );

        $user = $this->em->getRepository(\App\Entity\User::class)
            ->findOneBy(['token' => $token]);

        $this->assertNull($user);

        $user = $this->em->getRepository(\App\Entity\User::class)
            ->findOneBy(['email' => 'new-user@icewize.fr']);

        $this->assertNotNull($user);
        $this->assertNull($user->getToken());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getEmailVerifiedAt());
    }

    /**
     * @covers \App\Controller\Auth\LoginController::forgotPassword
     */
    public function testForgotPassword(): string
    {
        $this->client->jsonRequest(
            'POST',
            '/api/auth/forgot-password',
            [
                'email' => 'no-user@icewize.fr',
            ]
        );

        // Email not found
        $content = $this->client->getResponse()->getContent();
        $this->assertResponseIsSuccessful();
        if ($content === false) {
            $this->fail('The response content is empty');
        }
        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString(
            '{"message":"OK"}',
            $content
        );

        $this->client->jsonRequest(
            'POST',
            '/api/auth/forgot-password',
            [
                'email' => 'user@icewize.fr',
            ]
        );

        $content = $this->client->getResponse()->getContent();
        $this->assertResponseIsSuccessful();
        if ($content === false) {
            $this->fail('The response content is empty');
        }
        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString(
            '{"message":"OK"}',
            $content
        );

        $user = $this->em->getRepository(\App\Entity\User::class)
            ->findOneBy(['email' => 'user@icewize.fr']);

        $this->assertNotNull($user);
        $this->assertNotNull($user->getToken());
        $this->assertNull($user->getEmailVerifiedAt());

        // Check if the token is unique
        $this->assertSame(
            count(
                $this->em->getRepository(\App\Entity\User::class)
                    ->findBy(['token' => $user->getToken()])
            ),
            1
        );

        // Check if the token is between 250 and 350 characters
        $this->assertThat(
            strlen($user->getToken()),
            $this->logicalAnd(
                $this->greaterThanOrEqual(250),
                $this->lessThanOrEqual(350)
            ),
            'Token length is not between 250 and 350 characters'
        );

        return $user->getToken();
    }


    /**
     * @depends testForgotPassword
     * @covers \App\Controller\Auth\LoginController::resetPassword
     */
    public function testResetPassword(string $token): void
    {
        $this->client->jsonRequest(
            'POST',
            '/api/auth/reset-password/blablabla', // Invalid token
            [
                'password' => 'Not24get',
            ]
        );

        $content = $this->client->getResponse()->getContent();
        $this->assertResponseStatusCodeSame(400);
        if ($content === false) {
            $this->fail('The response content is empty');
        }
        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString(
            '{"message":"Invalid token"}',
            $content
        );

        $this->client->jsonRequest(
            'POST',
            '/api/auth/reset-password/' . $token,
            [
                'password' => 'Not24get',
            ]
        );

        $content = $this->client->getResponse()->getContent();
        $this->assertResponseIsSuccessful();
        if ($content === false) {
            $this->fail('The response content is empty');
        }
        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString(
            '{"message":"Password reset successfully"}',
            $content
        );

        $user = $this->em->getRepository(\App\Entity\User::class)
            ->findOneBy(['token' => $token]);

        $this->assertNull($user);

        $user = $this->em->getRepository(\App\Entity\User::class)
            ->findOneBy(['email' => 'user@icewize.fr']);

        $this->assertNotNull($user);
        $this->assertNull($user->getToken());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getEmailVerifiedAt());

        /** @var UserPasswordHasherInterface */
        $passwordHasher = $this->client->getContainer()
            ->get('security.password_hasher');
        $this->assertTrue(
            $passwordHasher->isPasswordValid($user, 'Not24get')
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testForgotPasswordMailSenderException(): void
    {
        // Use a different mocking library or strategy if Mockery causes issues
        $mockMailer = $this->createMock(MailerInterface::class);
        $mockMailer->expects($this->once())
            ->method('send')
            ->will($this->throwException(new \Exception('An error occurred while sending the email')));

        // Replacing the service in the container with the mock
        $this->client->getContainer()->set(MailerInterface::class, $mockMailer);

        // Make the request and assert the behavior
        $this->client->jsonRequest(
            'POST',
            '/api/auth/forgot-password',
            [
                'email' => 'user@icewize.fr',
            ]
        );

        $content = $this->client->getResponse()->getContent();
        $this->assertResponseStatusCodeSame(500);
        if ($content === false) {
            $this->fail('The response content is empty');
        }
        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString(
            '{"message":"An error occurred while sending the email"}',
            $content
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRegisterMailSenderException(): void
    {
        // Use a different mocking library or strategy if Mockery causes issues
        $mockMailer = $this->createMock(MailerInterface::class);
        $mockMailer->expects($this->once())
            ->method('send')
            ->will($this->throwException(new \Exception('An error occurred while sending the email')));

        // Replacing the service in the container with the mock
        $this->client->getContainer()->set(MailerInterface::class, $mockMailer);

        // Make the request and assert the behavior
        $this->client->jsonRequest(
            'POST',
            '/api/auth/register',
            [
                'email' => 'non-existing-email@icewize.fr',
                'password' => 'Not24get',
            ]
        );

        $content = $this->client->getResponse()->getContent();
        $this->assertResponseStatusCodeSame(500);
        if ($content === false) {
            $this->fail('The response content is empty');
        }
        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString(
            '{"message":"An error occurred while sending the email"}',
            $content
        );
    }

    public function testValidationForRegister(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/api/auth/register',
            [
                'email' => 'invalid-email',
                'password' => 'Not24get',
            ]
        );

        $content = $this->client->getResponse()->getContent();
        $this->assertResponseStatusCodeSame(400);
        if ($content === false) {
            $this->fail('The response content is empty');
        }
        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString(
            '{"message":"Validation error","errors":{"email":"email.format"}}',
            $content
        );

        $this->client->jsonRequest(
            'POST',
            '/api/auth/register',
            [
                'email' => 'new-user-for-validation@icewize.fr',
                'password' => 23,
            ]
        );

        $content = $this->client->getResponse()->getContent();
        $this->assertResponseStatusCodeSame(400);
        if ($content === false) {
            $this->fail('The response content is empty');
        }
        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString(
            '{"message":"Validation error","errors":{"password":"password.string"}}',
            $content
        );

        $this->client->jsonRequest(
            'POST',
            '/api/auth/register',
            [
                'email' => 'user@icewize.fr',
                'password' => 'short',
            ]
        );
    }

    /**
     * @return void
     */
    public function testValidationForResetPassword(): void
    {
        $user = new \App\Entity\User();
        $user->setEmail('test-validation-for-reset-password@icewize.fr');
        $user->setPassword('Not24get');
        $user->setToken('test-validation-for-reset-password-token');

        $this->em->persist($user);
        $this->em->flush();

        $this->client->jsonRequest(
            'POST',
            '/api/auth/reset-password/test-validation-for-reset-password-token',
            [
                'password' => 12,
            ]
        );

        $content = $this->client->getResponse()->getContent();
        $this->assertResponseStatusCodeSame(400);
        if ($content === false) {
            $this->fail('The response content is empty');
        }
        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString(
            '{"message":"Validation error","errors":{"password":"This value should be of type string."}}',
            $content
        );
    }
}
