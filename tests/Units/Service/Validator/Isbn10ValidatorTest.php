<?php

namespace App\Tests\Units\Service\Validator;

use App\Tests\Common\BaseTestCase;

/**
 * @coversDefaultClass Isbn10Validator
 */
class Isbn10ValidatorTest extends BaseTestCase
{

    public function testValidIsbn10()
    {
        $isbn10 = '6138535081';
        $validator = new \App\Service\Validator\Isbn10Validator();
        $this->assertTrue($validator->validate($isbn10));
    }

    public function testInvalidIsbn10()
    {
        $isbn10 = '6138535082';
        $validator = new \App\Service\Validator\Isbn10Validator();
        $this->assertFalse($validator->validate($isbn10));
    }

    public function testValidIsbn10WithX()
    {
        $isbn10 = '013162959X';
        $validator = new \App\Service\Validator\Isbn10Validator();
        $this->assertTrue($validator->validate($isbn10));
    }

    public function testInvalidIsbn10WithX()
    {
        $isbn10 = '013162958X';
        $validator = new \App\Service\Validator\Isbn10Validator();
        $this->assertFalse($validator->validate($isbn10));
    }

    public function testInvalidLength()
    {

        $isbn9 = '100020000'; // Sum = 22, 22%11 = 0
        $validator = new \App\Service\Validator\Isbn10Validator();
        $this->assertFalse($validator->validate($isbn9));
    }

    public function testInvalidLength2()
    {
        $isbn11 = '10020000002'; // Sum = 22, 22%11 = 0
        $validator = new \App\Service\Validator\Isbn10Validator();
        $this->assertFalse($validator->validate($isbn11));
    }

    public function testOnlyAlphanumericIsbn()
    {

        $isbnInvalid = 'AZERTYAZEX';
        $validator = new \App\Service\Validator\Isbn10Validator();
        $this->assertFalse($validator->validate($isbnInvalid));
    }

    public function testOnlyXIsbn()
    {
        $isbnInvalid = 'XXXXXXXXXX';
        $validator = new \App\Service\Validator\Isbn10Validator();
        $this->assertFalse($validator->validate($isbnInvalid));
    }

    public function testOnlyKeyAlphaNumeric()
    {
        $isbnInvalid = '1234567890A';
        $validator = new \App\Service\Validator\Isbn10Validator();
        $this->assertFalse($validator->validate($isbnInvalid));
    }
}
