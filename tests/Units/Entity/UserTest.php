<?php

namespace App\Tests\Units\Entity;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Common\BaseTestCase;
use Symfony\Component\Uid\Uuid;

class UserTest extends BaseTestCase
{
    public function testId(): void
    {
        $user = new User();
        $this->assertNull($user->getId());
    }

    public function testEmail(): void
    {
        $user = new User();
        $user->setEmail('admin@icewize.fr');
        $this->assertEquals('admin@icewize.fr', $user->getEmail());
    }

    public function testEmailVerifiedAt(): void
    {
        $user = new User();
        $user->setEmailVerifiedAt(new \DateTimeImmutable());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getEmailVerifiedAt());
    }

    public function testPassword(): void
    {
        $user = new User();
        $user->setPassword('password');
        $this->assertEquals('password', $user->getPassword());
    }

    public function testRoles(): void
    {
        $user = new User();
        // Test default role
        $this->assertEquals(['ROLE_USER'], $user->getRoles());

        $user->setRoles(['ROLE_ADMIN']);
        $this->assertEquals(['ROLE_ADMIN'], $user->getRoles());
    }

    public function testToken(): void
    {
        $user = new User();
        $user->setToken('token');
        $this->assertEquals('token', $user->getToken());
    }

    public function testGetUserIdentifier(): void
    {
        $user = new User();

        $refletedUser = new \ReflectionClass($user);
        $property = $refletedUser->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, Uuid::fromString('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa'));

        $this->assertEquals('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', $user->getUserIdentifier());
    }

    public function testGetUserIdentifierWithNullId(): void
    {
        $user = new User();
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The user identifier is not set.');
        $user->getUserIdentifier();
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        $user->setPassword('password');
        $user->setToken('token');
        $user->setEmail('u@icewize.fr');

        $u1 = clone $user;

        $user->eraseCredentials();

        $this->assertEquals($u1, $user);
    }

    public function testIsVerified(): void
    {
        $user = new User();
        $this->assertFalse($user->isVerified());

        $user->setEmailVerifiedAt(new \DateTimeImmutable());
        $this->assertTrue($user->isVerified());
    }
}
