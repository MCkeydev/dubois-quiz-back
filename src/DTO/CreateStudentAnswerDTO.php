<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * DTO (Data Transfer Object) pour la création d'une réponse dans la copie d'un élève.
 */
class CreateStudentAnswerDTO
{
    #[NotNull(message: "L'ID de la question ne peut pas être nul.")]
    public ?int $question = null;  // ID de l'entité de la question à laquelle on répond

    #[NotBlank(allowNull: true, message: "La réponse ne peut pas être vide.")]
    public ?string $answer = null; // Réponse sous forme de texte

    public ?int $choice = null;    // ID du choix QCM (entité Answer)

    /**
     * Valide l'objet CreateStudentAnswerDTO.
     *
     * @param ExecutionContextInterface $context Le contexte d'exécution pour la validation
     */
    #[Callback(callback: "validate")]
    public function validate(ExecutionContextInterface $context)
    {
        if (null !== $this->answer && null !== $this->choice) {
            $context->buildViolation("Vous ne pouvez pas spécifier à la fois une réponse et un choix.")
                ->atPath('answer')
                ->atPath('choice')
                ->addViolation();
        }

        if (null === $this->answer && null === $this->choice) {
            $context->buildViolation("Vous devez spécifier un choix ou une réponse.")
                ->atPath('answer')
                ->atPath('choice')
                ->addViolation();
        }
    }
}