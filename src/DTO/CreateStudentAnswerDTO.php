<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CreateStudentAnswerDTO
{
    #[NotNull]
    public ?int $question = null;  // ID of the question entity being answered

    #[NotBlank(allowNull: true)]
    public ?string $answer = null; // Answer in text form

    public ?int $choice = null;    // ID of the QCM choice (Answer Entity)

    #[Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if (null !== $this->answer && null !== $this->choice) {
            $context->buildViolation('You can not specify both an answer and a choice')
                ->atPath('answer')
                ->atPath('choice')
                ->addViolation();
        }

        if (null === $this->answer && null === $this->choice) {
            $context->buildViolation('You must specify a choice or an answer')
                ->atPath('answer')
                ->atPath('choice')
                ->addViolation();
        }
    }
}
