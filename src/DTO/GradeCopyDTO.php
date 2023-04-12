<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

class GradeCopyDTO
{
    #[NotBlank]
    #[NotNull]
    public string $commentary;

    #[NotBlank]
    #[NotNull]
    public float $score;
}