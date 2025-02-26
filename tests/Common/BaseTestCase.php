<?php

namespace App\Tests\Common;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

abstract class BaseTestCase extends TestCase
{

    /**
     * getPrivateProperty
     *
     * @param  class-string|object $className
     * @param  string $propertyName
     * @return ReflectionProperty
     */
    protected function getPrivateProperty($className, $propertyName)
    {
        $reflector = new ReflectionClass($className);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }
}
