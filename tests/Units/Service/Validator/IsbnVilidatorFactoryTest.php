<?php

namespace App\Tests\Units\Service\Validator;

use App\Service\Validator\Isbn10Validator;
use App\Service\Validator\Isbn13Validator;
use App\Service\Validator\IsbnValidatorFactory;
use App\Tests\Common\BaseTestCase;
use InvalidArgumentException;

class IsbnVilidatorFactoryTest extends BaseTestCase
{

    public function testCreate10()
    {
        $factory = new IsbnValidatorFactory();
        $validator = $factory->create('1234567890');
        $this->assertInstanceOf(Isbn10Validator::class, $validator);
    }

    public function testCreate13()
    {
        $factory = new IsbnValidatorFactory();
        $validator = $factory->create('9781234567890');
        $this->assertInstanceOf(Isbn13Validator::class, $validator);
    }

    public function testInvalidIsbn()
    {
        $factory = new IsbnValidatorFactory();
        $this->expectException(InvalidArgumentException::class);
        $factory->create('invalid-isbn');
    }
}