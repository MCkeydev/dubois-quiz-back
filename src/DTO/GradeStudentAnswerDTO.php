<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;

class GradeStudentAnswerDTO
{
    #[NotNull]
    #[Positive]
    public int $answerId;

    #[NotBlank]
    #[NotNull]
    public string $annotation;

    #[NotNull]
    #[PositiveOrZero]
    public int $score;
}
