<?php

namespace App\Service\Validator;

class Isbn13Validator
{
    private function validateFormat(string $isbn): bool
    {
        return preg_match('/^[0-9]{13}$/', $isbn) === 1;
    }

    public function validate(string $isbn): bool
    {
        if (!$this->validateFormat($isbn)) {
            return false;
        }

        $sum = 0;

        // Equivalent of for ($i = 0; $i < 9; $i++)
        for ($i = -1; ++$i < 13; ) {
            $sum += (int) $isbn[$i] * (($i % 2 === 0) * 1 + ($i % 2 === 1) * 3);
        }

        return ($sum % 10) === 0;
    }
}
