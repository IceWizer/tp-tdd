<?php

namespace App\Service\Validator;

use InvalidArgumentException;

class IsbnValidatorFactory
{
    /**
     * @var array<class-string>
     */
    private array $validators = [
        Isbn10Validator::class,
        Isbn13Validator::class,
    ];

    public function create(string $isbn): IsbnValidator
    {
        $validator = null;
        foreach ($this->validators as $vali) {
            /** @var IsbnValidator */
            $valiInst = new $vali();
            if ($valiInst->validateFormat($isbn)) {
                $validator = $valiInst;
                break;
            }
        }

        if ($validator === null) {
            throw new InvalidArgumentException('Invalid ISBN');
        }

        return $validator;
    }
}