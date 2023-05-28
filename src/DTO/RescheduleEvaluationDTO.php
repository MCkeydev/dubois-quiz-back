<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * DTO (Data Transfer Object) pour le report d'une évaluation.
 */
class RescheduleEvaluationDTO
{
    public \DateTimeImmutable $startsAt;

    public \DateTimeImmutable $endsAt;

    #[Callback(callback: "validate")]
    public function validate(ExecutionContextInterface $context)
    {
        // Obtention de la date actuelle
        $now = new \DateTime();

        // Vérifie si la date de fin est antérieure à la date de début
        if ($this->endsAt <= $this->startsAt) {
            // Construit une violation si la date de fin est antérieure à la date de début
            $context->buildViolation('La date de fin ne doit pas être antérieure à la date de début.')
                ->atPath('endsAt')
                ->addViolation();
        }

        // Vérifie si la date de début est antérieure à la date actuelle
        if ($this->startsAt < $now->format('Y-m-d\TH:i')) {
            // Construit une violation si la date de début est antérieure à la date actuelle
            $context->buildViolation('La date de début ne doit pas être antérieure à la date actuelle.')
                ->atPath('startsAt')
                ->addViolation();
        }

        // Vérifie si la date de fin est antérieure à la date actuelle
        if ($this->endsAt < $now->format('Y-m-d\TH:i')) {
            // Construit une violation si la date de fin est antérieure à la date actuelle
            $context->buildViolation('La date de fin ne doit pas être antérieure à la date actuelle.')
                ->atPath('endsAt')
                ->addViolation();
        }
    }
}
