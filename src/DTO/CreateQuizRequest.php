<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class CreateQuizRequest
{
    #[NotBlank]
    #[NotNull]
    public string $title;
}