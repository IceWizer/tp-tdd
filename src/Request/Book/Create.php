<?php

namespace App\Request\Book;

use Symfony\Component\Validator\Constraints as Assert;

class Create
{
    public function __construct(
        #[Assert\NotBlank]
        public string $isbn,
    ) {
    }
}
