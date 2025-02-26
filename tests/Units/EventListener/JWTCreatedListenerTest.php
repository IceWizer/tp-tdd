<?php

namespace App\Tests\EventListener;

use App\Entity\User;
use App\EventListener\JWTCreatedListener;
use App\Tests\Common\BaseTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @coversDefaultClass \App\EventListener\JWTCreatedListener
 */
class JWTCreatedListenerTest extends BaseTestCase
{
    /**
     * @covers ::onJWTCreated
     */
    public function testOnJWTCreated(): void
    {
        $listener = new JWTCreatedListener();
        $user = new User();
        $user->setEmail('admin@icewize.fr');
        $user->setEmailVerifiedAt(new \DateTimeImmutable());
        $event = new JWTCreatedEvent([], $user);

        $listener->onJWTCreated($event);

        $this->assertArrayHasKey('email', $event->getData());
        $this->assertEquals('admin@icewize.fr', $event->getData()['email']);
    }

    /**
     * @covers ::onJWTCreated
     */
    public function testOnJWTCreatedWithInvalidUser(): void
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);
        $this->expectExceptionMessage('You need to be logged in');

        $listener = new JWTCreatedListener();
        $user = new class() implements UserInterface {
            public function getRoles(): array
            {
                return ["ROLE_USER"];
            }

            public function getPassword(): string
            {
                return '';
            }

            public function getSalt(): string
            {
                return '';
            }

            public function getUsername(): string
            {
                return '';
            }

            public function getUserIdentifier(): string
            {
                return '';
            }

            public function eraseCredentials(): void {}
        };
        $event = new JWTCreatedEvent([], $user);

        $listener->onJWTCreated($event);
    }

    /**
     * @covers ::onJWTCreated
     */
    public function testOnJWTCreatedWithUnverifiedUser(): void
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);
        $this->expectExceptionMessage('You need to activate your account');

        $listener = new JWTCreatedListener();
        $user = new User();
        $user->setEmail('admin@icewize.fr');

        $event = new JWTCreatedEvent([], $user);

        $listener->onJWTCreated($event);
    }
}
