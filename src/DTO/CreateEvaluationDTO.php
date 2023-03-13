<?php

namespace App\DTO;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CreateEvaluationDTO
{
    #[Groups(['getEvaluation'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[Type('DateTimeImmutable')]
    #[Groups(['getEvaluation'])]
    public ?\DateTimeImmutable $startsAt = null;

    #[Type('DateTimeImmutable')]
    #[Groups(['getEvaluation'])]
    public ?\DateTimeImmutable $endsAt = null;

    #[Groups(['getEvaluation'])]
    private ?bool $isLocked = null;

    private ?\DateTimeImmutable $updatedAt = null;

    #[Callback]
    public function validateDateTime(ExecutionContextInterface $context) {
        if ($this->startsAt < new \DateTimeImmutable()) {
            $context->buildViolation("The start date must be anterior to today's date.")
                ->atPath('startsAt')
                ->addViolation();
        }

        if ($this->startsAt > $this->endsAt) {
            $context->buildViolation('The start date must be anterior to the ends date.')
                ->atPath('startsAt')
                ->addViolation();
        }
    }

}
