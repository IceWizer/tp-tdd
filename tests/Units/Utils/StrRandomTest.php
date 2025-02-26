<?php

namespace App\Tests\Units\Utils;

use App\Utils\StrRandom;
use PHPUnit\Framework\TestCase;

class StrRandomTest extends TestCase
{
    private StrRandom $strRandom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strRandom = new StrRandom();
    }

    /**
     * @covers \App\Utils\StrRandom::generateRandomString()
     */
    public function testStrRandom(): void
    {
        // General case
        $str = $this->strRandom->generateRandomString(32, 64);
        $this->assertIsString($str);
        $this->assertGreaterThanOrEqual(32, strlen($str)); // Is the string length greater than or equal to 32?
        $this->assertLessThanOrEqual(64, strlen($str)); // Is the string length less than or equal to 64?

        $str2 = $this->strRandom->generateRandomString(32, 64);
        $this->assertNotEquals($str, $str2); // Are the two strings different?
    }

    /**
     * @covers \App\Utils\StrRandom::generateRandomString()
     */
    public function testStrRandomLimitCases(): void
    {
        $str = $this->strRandom->generateRandomString(1);
        $this->assertIsString($str);
        $this->assertEquals(1, strlen($str)); // Is the string length equal to 1?

        $str = $this->strRandom->generateRandomString(StrRandom::$maxRandomLength);
        $this->assertIsString($str);
        $this->assertEquals(StrRandom::$maxRandomLength, strlen($str)); // Is the string length equal to StrRandom::$maxRandomLength);?
    }

    /**
     * @covers \App\Utils\StrRandom::generateRandomString()
     */
    public function testStrRandomEdgeCases(): void
    {
        $str = $this->strRandom->generateRandomString(32);
        $this->assertIsString($str);
        $this->assertEquals(32, strlen($str)); // Is the string length equal to 32?
    }

    /**
     * @covers \App\Utils\StrRandom::generateRandomString()
     */
    public function testGenerateRandomStringThrowsExceptionForInvalidMaxLength(): void
    {
        // Expect an exception when minLength is greater than maxLength
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Min length cannot be greater than max length');

        // This should trigger the exception
        $this->strRandom->generateRandomString(64, 32);
    }

    /**
     * @covers \App\Utils\StrRandom::generateRandomString()
     */
    public function testGenerateRandomStringThrowsExceptionForInvalidMinLength(): void
    {
        // Expect an exception when minLength is less than 1
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Min length must be at least 1');

        // This should trigger the exception
        $this->strRandom->generateRandomString(0);
    }

    /**
     * @covers \App\Utils\StrRandom::generateRandomString()
     */
    public function testGenerateRandomStringThrowsExceptionForInvalidMaxLength2(): void
    {
        // Expect an exception when maxLength is greater than StrRandom::$maxRandomLength
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Max length is too large (max: ' . StrRandom::$maxRandomLength . ')');

        // This should trigger the exception
        $this->strRandom->generateRandomString(1, StrRandom::$maxRandomLength + 1);
    }
}
