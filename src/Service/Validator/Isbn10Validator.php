<?php

namespace App\Service\Validator;

class Isbn10Validator extends IsbnValidator
{
    protected string $isbnRegex = '/^[0-9]{9}[0-9X]$/';

    public function validate(string $isbn): bool
    {
        if (!$this->validateFormat($isbn)) {
            return false;
        }

        $sum = 0;

        // Equivalent of for ($i = 0; $i < 9; $i++)
        for ($i = -1; ++$i < 9; ) {
            $sum += (int) $isbn[$i] * (10 - $i);
        }

        $sum += $isbn[9] === 'X' ? 10 : (int) $isbn[9];
        return ($sum % 11) === 0;
    }
}
