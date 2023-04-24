<?php

namespace App\Entity;

use App\Interfaces\OwnedEntityInterface;
use App\Repository\EvaluationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: EvaluationRepository::class)]
class Evaluation implements OwnedEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getEvaluation'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['getEvaluation'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Type('DateTimeImmutable')]
    #[NotBlank]
    #[NotNull]
    #[Groups(['getEvaluation'])]
    private ?\DateTimeImmutable $startsAt = null;

    #[ORM\Column]
    #[Type('DateTimeImmutable')]
    #[NotBlank]
    #[NotNull]
    #[Groups(['getEvaluation'])]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\Column]
    #[Groups(['getEvaluation'])]
    private ?bool $isLocked = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'evaluations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getEvaluation'])]
    private ?Quiz $quiz = null;

    #[ORM\OneToMany(mappedBy: 'evaluation', targetEntity: StudentCopy::class)]
    #[Groups(['getEvaluation'])]
    private Collection $studentCopies;

    #[ORM\ManyToOne(inversedBy: 'evaluations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'evaluations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formation $formation = null;

    #[ORM\Column(nullable: true)]
    #[Groups('api')]
    private ?int $averageScore = null;

    #[Callback]
    public function validateDateTime(ExecutionContextInterface $context) {
        if ($this->getStartsAt() > $this->getEndsAt()) {
            $context->buildViolation('The start date must be anterior to the ends date.')
                ->atPath('startsAt')
                ->addViolation();
        }
    }

    public function __construct()
    {
        $this->isLocked = false;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->formations = new ArrayCollection();
        $this->studentCopies = new ArrayCollection();
    }

    public function isOwner(User $user): bool
    {
        return $this->getAuthor() === $user;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt($startsAt): self
    {
        if ($startsAt instanceof \DateTimeImmutable) {
            $this->startsAt = $startsAt;
        } else if (is_string($startsAt)) {
            $this->startsAt = new \DateTimeImmutable($startsAt);
        }

        return $this;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt($endsAt): self
    {
        if ($endsAt instanceof \DateTimeImmutable) {
            $this->endsAt = $endsAt;
        } else if (is_string($endsAt)) {
            $this->endsAt = new \DateTimeImmutable($endsAt);
        }

        return $this;
    }

    public function isIsLocked(): ?bool
    {
        return $this->isLocked;
    }

    public function setIsLocked(bool $isLocked): self
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): self
    {
        $this->quiz = $quiz;

        return $this;
    }

    /**
     * @return Collection<int, StudentCopy>
     */
    public function getStudentCopies(): Collection
    {
        return $this->studentCopies;
    }

    public function addStudentCopy(StudentCopy $studentCopy): self
    {
        if (!$this->studentCopies->contains($studentCopy)) {
            $this->studentCopies->add($studentCopy);
            $studentCopy->setEvaluation($this);
        }

        return $this;
    }

    public function removeStudentCopy(StudentCopy $studentCopy): self
    {
        if ($this->studentCopies->removeElement($studentCopy)) {
            // set the owning side to null (unless already changed)
            if ($studentCopy->getEvaluation() === $this) {
                $studentCopy->setEvaluation(null);
            }
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): self
    {
        $this->formation = $formation;

        return $this;
    }

    public function getAverageScore(): ?int
    {
        return $this->averageScore;
    }

    public function setAverageScore(?int $averageScore): self
    {
        $this->averageScore = $averageScore;

        return $this;
    }

}
