<?php

namespace App\Tests\Units\Entity;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('testId@icewize.fr');
        $user->setPassword($this->hasher->hashPassword($user, 'Not24get'));
        $user->setEmailVerifiedAt(new \DateTimeImmutable());

        // Use reflection to set id
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, "aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa");

        $manager->persist($user);
    }
}
