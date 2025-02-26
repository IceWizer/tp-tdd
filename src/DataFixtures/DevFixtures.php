<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

abstract class DevFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ["dev"];
    }
}
