<?php

namespace App\Tests\Units\Service\Utils;

use App\Service\Utils\EncryptionService;
use App\Tests\Common\BaseTestCase;

class EncryptionServiceTest extends BaseTestCase
{
    /**
     * @covers \App\Service\Utils\EncryptionService::__construct()
     */
    public function testConstruct(): void
    {
        $encryptionService = new EncryptionService('secret');
        $this->assertInstanceOf(EncryptionService::class, $encryptionService);

        $encryptionKey = $this->getPrivateProperty(EncryptionService::class, 'encryptionKey')->getValue($encryptionService);
        $this->assertIsString($encryptionKey);
        $this->assertEquals('b1e72b7a', $encryptionKey);
    }

    /**
     * @covers \App\Service\Utils\EncryptionService::encrypt()
     */
    public function testEncrypt(): void
    {
        $encryptionService = new EncryptionService('secret');
        $encrypted = $encryptionService->encrypt('Hello, world!');
        $this->assertIsString($encrypted);
        $this->assertNotEquals('Hello, world!', $encrypted);
    }

    /**
     * @covers \App\Service\Utils\EncryptionService::decrypt()
     */
    public function testDecrypt(): void
    {
        $encryptionService = new EncryptionService('secret');
        $encrypted = $encryptionService->encrypt('Hello, world!');
        $decrypted = $encryptionService->decrypt($encrypted);
        $this->assertIsString($decrypted);
        $this->assertEquals('Hello, world!', $decrypted);
    }

    /**
     * @covers \App\Service\Utils\EncryptionService::decrypt()
     */
    public function testDecryptEmptyData(): void
    {
        $encryptionService = new EncryptionService('secret');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Data is empty');
        $encryptionService->decrypt('');
    }

    /**
     * @covers \App\Service\Utils\EncryptionService::decrypt()
     */
    public function testDecryptNotBase64EncodedData(): void
    {
        $encryptionService = new EncryptionService('secret');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Data is not base64 encoded');
        $encryptionService->decrypt('Hello, world!');
    }

    /**
     * @covers \App\Service\Utils\EncryptionService::decrypt()
     */
    public function testDecryptInvalidData(): void
    {
        $encryptionService = new EncryptionService('secret');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid data');
        $encryptionService->decrypt('aGVsbG8=');
    }

    public function testCypherExceptionForEncrypt(): void
    {
        EncryptionService::$cypherAlgorithm = 'not-a-valid-cypher';
        $encryptionService = new EncryptionService('secret');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cypher algorithm');
        $encryptionService->encrypt('Hello, world!');
    }

    public function testCypherExceptionForDecrypt(): void
    {
        EncryptionService::$cypherAlgorithm = 'not-a-valid-cypher';
        $encryptionService = new EncryptionService('secret');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cypher algorithm');
        $encryptionService->decrypt('aGVsbG8=');
    }

    public function testGetIvLength(): void
    {
        EncryptionService::$cypherAlgorithm = 'aes-256-cbc';
        $encryptionService = new EncryptionService('secret');
        $ivLength = $encryptionService->getIvLength();
        $this->assertIsInt($ivLength);
        $this->assertEquals(16, $ivLength);

        EncryptionService::$cypherAlgorithm = 'aes-128-cbc';
        $encryptionService = new EncryptionService('secret');
        $ivLength = $encryptionService->getIvLength();
        $this->assertIsInt($ivLength);
        $this->assertEquals(16, $ivLength);

        EncryptionService::$cypherAlgorithm = 'aes-192-cbc';
        $encryptionService = new EncryptionService('secret');
        $ivLength = $encryptionService->getIvLength();
        $this->assertIsInt($ivLength);
        $this->assertEquals(16, $ivLength);

        // Invalid cypher algorithm
        EncryptionService::$cypherAlgorithm = 'not-a-valid-cypher';
        $encryptionService = new EncryptionService('secret');
        $ivLength = $encryptionService->getIvLength();
        $this->assertFalse($ivLength);
    }
}
