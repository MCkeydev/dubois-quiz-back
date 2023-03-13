<?php

namespace App\DTO;
use Symfony\Component\Validator\Constraints\DateTime;
class CreateEvaluationDTO
{
    private int $quizId;
    #[DateTime]
    private $createdAt;
}
