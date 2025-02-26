<?php

namespace App\Utils;

class StrRandom
{
    /**
     * The maximum length of the random string
     * Used to limit time to generate random string
     * 
     * @var int
     */
    public static int $maxRandomLength = 32768;

    /**
     * Generates a random string of a given length (between $minLength and $maxLength)
     *
     * @param  int $minLength
     * @param  int $maxLength Optional. If not provided, the string length will be equal to $minLength
     * @return string
     */
    public function generateRandomString(int $minLength, int $maxLength = null): string
    {
        if ($maxLength === null) {
            $maxLength = $minLength;
        }

        if ($minLength > $maxLength) {
            throw new \InvalidArgumentException('Min length cannot be greater than max length');
        }

        if ($minLength < 1) {
            throw new \InvalidArgumentException('Min length must be at least 1');
        }

        if ($maxLength > self::$maxRandomLength) {
            throw new \InvalidArgumentException('Max length is too large (max: ' . self::$maxRandomLength . ')');
        }

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        $length = rand($minLength, $maxLength);

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }
}
