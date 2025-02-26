<?php

use App\Entity\User;
use App\Utils\StrRandom;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Log\Logger;

class UserRepositoryTest extends WebTestCase
{
    public \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    /** @var EntityManagerInterface */
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
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testForgotPasswordWithTokenCollision(): void
    {
        $existingToken = 'duplicateToken123';

        // Mock StrRandom::generateRandomString to return a known token
        $mock = $this->createMock(StrRandom::class);
        $mock->method('generateRandomString')
            ->will($this->onConsecutiveCalls($existingToken, 'uniqueToken456'));

        self::getContainer()->set(StrRandom::class, $mock);

        // Step 1: Prepopulate the database with a user and a known token
        $existingUser = new User();
        $existingUser->setEmail('existing-user@icewize.fr');
        $existingUser->setToken($existingToken);
        $existingUser->setPassword('Not24get');
        $this->em->persist($existingUser);
        $this->em->flush();

        // Step 3: Trigger the forgot password process
        $this->client->jsonRequest(
            'POST',
            '/api/auth/forgot-password',
            ['email' => 'user@icewize.fr']
        );

        // Step 4: Assert that the response is successful and the token is now unique
        $this->assertResponseIsSuccessful();
        /** @var User */
        $user = $this->em->getRepository(User::class)
            ->findOneBy(['email' => 'user@icewize.fr']);
        $userToken = $user->getToken();
        $this->assertNotEquals($existingToken, $userToken);
        $this->assertEquals("uniqueToken456", $userToken);

        // Cleanup Mockery
        Mockery::close();
    }
}
