<?php

namespace App\Entity;

use App\Interfaces\OwnedEntityInterface;
use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question implements OwnedEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api', 'fetchAnswer', 'getEvaluation'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['api', 'fetchAnswer'])]
    private ?bool $isQcm = null;

    #[ORM\Column(length: 255)]
    #[Groups(['api', 'fetchAnswer', 'getEvaluation'])]
    private ?string $title = null;

    #[ORM\Column]
    #[Groups(['api', 'fetchAnswer'])]
    private ?int $maxScore = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $Quiz = null;

    #[ORM\OneToMany(mappedBy: 'Question', targetEntity: Answer::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['api', 'getEvaluation'])]
    private Collection $answers;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: StudentAnswer::class, orphanRemoval: true)]
    private Collection $studentAnswers;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->studentAnswers = new ArrayCollection();
    }

    public function isOwner(User $user): bool
    {
        return $this->getQuiz()->getAuthor() === $user;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isIsQcm(): ?bool
    {
        return $this->isQcm;
    }

    public function setIsQcm(bool $isQcm): self
    {
        $this->isQcm = $isQcm;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getMaxScore(): ?int
    {
        return $this->maxScore;
    }

    public function setMaxScore(int $maxScore): self
    {
        $this->maxScore = $maxScore;

        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->Quiz;
    }

    public function setQuiz(?Quiz $Quiz): self
    {
        $this->Quiz = $Quiz;

        return $this;
    }

    /**
     * @return Collection<int, Answer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StudentAnswer>
     */
    public function getStudentAnswers(): Collection
    {
        return $this->studentAnswers;
    }

    public function addStudentAnswer(StudentAnswer $studentAnswer): self
    {
        if (!$this->studentAnswers->contains($studentAnswer)) {
            $this->studentAnswers->add($studentAnswer);
            $studentAnswer->setQuestion($this);
        }

        return $this;
    }

    public function removeStudentAnswer(StudentAnswer $studentAnswer): self
    {
        if ($this->studentAnswers->removeElement($studentAnswer)) {
            // set the owning side to null (unless already changed)
            if ($studentAnswer->getQuestion() === $this) {
                $studentAnswer->setQuestion(null);
            }
        }

        return $this;
    }
}
