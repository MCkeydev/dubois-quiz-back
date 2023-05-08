<?php

namespace App\Entity;

use App\Interfaces\EntityInterface;
use App\Repository\StudentAnswerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: StudentAnswerRepository::class)]
#[UniqueEntity(
    fields: ['studentCopy', 'question'],
    message: 'Student can only answer a question once.',
    errorPath: 'question',
)]
class StudentAnswer implements EntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['fetchAnswer', 'api'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['fetchAnswer', 'api'])]
    private ?string $annotation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['fetchAnswer', 'api'])]
    private ?int $score = null;

    #[ORM\ManyToOne(inversedBy: 'studentAnswers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?StudentCopy $studentCopy = null;

    #[ORM\ManyToOne(inversedBy: 'studentAnswers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['fetchAnswer', 'api'])]
    private ?Question $question = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[NotBlank(allowNull: true)]
    #[Groups(['fetchAnswer', 'api'])]
    private ?string $answer = null;

    #[ORM\ManyToOne]
    #[Groups(['fetchAnswer', 'api'])]
    private ?Answer $choice = null;

    #[Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->score > $this->getQuestion()->getMaxScore()) {
            $context->buildViolation('La note attribuée à la réponse ne peut pas être plus haute que celle indiquée sur le barême.')->atPath('score')
                ->addViolation();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnnotation(): ?string
    {
        return $this->annotation;
    }

    public function setAnnotation(string $annotation): self
    {
        $this->annotation = $annotation;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getStudentCopy(): ?StudentCopy
    {
        return $this->studentCopy;
    }

    public function setStudentCopy(?StudentCopy $studentCopy): self
    {
        $this->studentCopy = $studentCopy;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getChoice(): ?Answer
    {
        return $this->choice;
    }

    public function setChoice(?Answer $choice): self
    {
        $this->choice = $choice;

        return $this;
    }
}
