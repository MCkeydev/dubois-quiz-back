<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * DTO (Data Transfer Object) pour la notation d'une copie.
 */
class GradeCopyDTO
{
    #[NotNull(message: "Le commentaire ne peut pas être nul.")]
    #[NotBlank(message: "Le commentaire ne peut pas être vide.")]
    public string $commentary;

    #[NotNull(message: "Les réponses ne peuvent pas être nulles.")]
    #[NotBlank(message: "Les réponses ne peuvent pas être vides.")]
    public array $answers;
}