<?php

namespace App\Tests\Units\Service\Validator;

use App\Tests\Common\BaseTestCase;

/**
 * @coversDefaultClass Isbn10Validator
 */
class Isbn13ValidatorTest extends BaseTestCase
{

    public function testValidIsbn13()
    {
        $isbn10 = '9780131629592';
        $validator = new \App\Service\Validator\Isbn13Validator();
        $this->assertTrue($validator->validate($isbn10));
    }

    public function testInvalidIsbn13()
    {
        $isbn10 = '9780131629591';
        $validator = new \App\Service\Validator\Isbn13Validator();
        $this->assertFalse($validator->validate($isbn10));
    }
    public function testInvalidLength()
    {

        $isbn9 = '130022200'; // Sum = 20, 20%10 = 0
        $validator = new \App\Service\Validator\Isbn13Validator();
        $this->assertFalse($validator->validate($isbn9));
    }

    public function testInvalidLength2()
    {
        $isbn11 = '130022000000002'; // Sum = 20, 20%10 = 0
        $validator = new \App\Service\Validator\Isbn13Validator();
        $this->assertFalse($validator->validate($isbn11));
    }

    public function testOnlyAlphanumericIsbn()
    {

        $isbnInvalid = 'AZERTYAZERTYX';
        $validator = new \App\Service\Validator\Isbn13Validator();
        $this->assertFalse($validator->validate($isbnInvalid));
    }
}
