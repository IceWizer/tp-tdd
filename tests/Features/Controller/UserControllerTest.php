<?php

namespace App\Tests\Features\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/user');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $content = $client->getResponse()->getContent();
        if ($content === false) {
            $this->fail('The response content is empty');
        }
        $this->assertJson($content);

        $responseData = json_decode($content, true);

        if (is_array($responseData) === false) {
            $this->fail('The response content is not a valid JSON');
        }
        $this->assertArrayHasKey('email', $responseData);

        if (array_key_exists('email', $responseData) === false) {
            $this->fail('The response content does not contain the email key');
        }
        $this->assertEquals('@', $responseData['email']);
    }
}
