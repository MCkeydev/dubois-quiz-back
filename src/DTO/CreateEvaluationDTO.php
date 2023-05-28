<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * DTO (Data Transfer Object) pour la création d'une évaluation.
 */
class CreateEvaluationDTO
{
    public \DateTimeImmutable $startsAt;

    public \DateTimeImmutable $endsAt;

    #[NotNull(message: "L'ID du quiz ne peut pas être nul.")]
    #[Positive(message: "L'ID du quiz doit être un entier positif.")]
    public int $quiz;

    #[NotNull(message: "L'ID de la formation ne peut pas être nul.")]
    #[Positive(message: "L'ID de la formation doit être un entier positif.")]
    public int $formation;

    /**
     * Valide l'objet CreateEvaluationDTO.
     *
     * @param ExecutionContextInterface $context Le contexte d'exécution pour la validation
     */
    #[Callback(callback: "validate")]
    public function validate(ExecutionContextInterface $context)
    {
        // Obtention de la date actuelle
        $now = new \DateTime();

        // Vérifie si la date de fin est antérieure à la date de début
        if ($this->endsAt <= $this->startsAt) {
            // Construit une violation si la date de fin est antérieure à la date de début
            $context->buildViolation("La date de fin ne doit pas être antérieure à la date de début.")
                ->atPath('endsAt')
                ->addViolation();
        }

        // Vérifie si la date de début est antérieure à la date actuelle
        if ($this->startsAt < $now->format('Y-m-d\TH:i')) {
            // Construit une violation si la date de début est antérieure à la date actuelle
            $context->buildViolation("La date de début ne doit pas être antérieure à la date actuelle.")
                ->atPath('startsAt')
                ->addViolation();
        }

        // Vérifie si la date de fin est antérieure à la date actuelle
        if ($this->endsAt < $now->format('Y-m-d\TH:i')) {
            // Construit une violation si la date de fin est antérieure à la date actuelle
            $context->buildViolation("La date de fin ne doit pas être antérieure à la date actuelle.")
                ->atPath('endsAt')
                ->addViolation();
        }
    }
}
