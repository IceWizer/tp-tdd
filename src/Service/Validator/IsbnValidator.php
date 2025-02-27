<?php

namespace App\Service\Validator;

abstract class IsbnValidator
{
    protected string $isbnRegex;

    protected function validateFormat(string $isbn): bool
    {
        return preg_match($this->isbnRegex, $isbn) === 1;
    }

    abstract public function validate(string $isbn): bool;
}
