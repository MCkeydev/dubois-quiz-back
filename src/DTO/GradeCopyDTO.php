<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class GradeCopyDTO
{
    #[NotNull]
    #[NotBlank]
    public string $commentary;

    #[NotNull]
    #[NotBlank]
    public array $answers;
}