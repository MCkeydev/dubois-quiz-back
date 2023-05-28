<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

/**
 * DTO (Data Transfer Object) pour la notation d'une réponse d'étudiant.
 */
class GradeStudentAnswerDTO
{
    #[NotNull(message: "L'ID de la réponse ne peut pas être nul.")]
    #[Positive(message: "L'ID de la réponse doit être un entier positif.")]
    public int $answerId;

    #[NotBlank(message: "L'annotation ne peut pas être vide.")]
    #[NotNull(message: "L'annotation ne peut pas être nulle.")]
    public string $annotation;

    #[NotNull(message: "Le score ne peut pas être nul.")]
    #[PositiveOrZero(message: "Le score doit être un entier positif ou nul.")]
    public int $score;
}
