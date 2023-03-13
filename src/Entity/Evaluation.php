<?php

namespace App\Entity;

use App\Interfaces\OwnedEntityInterface;
use App\Repository\EvaluationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\DateTime;
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
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Type('DateTimeImmutable')]
    #[NotBlank]
    #[NotNull]
    private ?\DateTimeImmutable $startsAt = null;

    #[ORM\Column]
    #[Type('DateTimeImmutable')]
    #[NotBlank]
    #[NotNull]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\Column]
    private ?bool $isLocked = null;

    #[ORM\ManyToOne(inversedBy: 'evaluations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'evaluations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    #[ORM\ManyToMany(targetEntity: Formation::class, inversedBy: 'evaluations')]
    private Collection $Formations;

    #[ORM\OneToMany(mappedBy: 'evaluation', targetEntity: StudentCopy::class)]
    private Collection $studentCopies;

    #[Callback]
    public function validateDateTime(ExecutionContextInterface $context, $payload) {
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
        $this->Formations = new ArrayCollection();
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

    public function setStartsAt(\DateTimeImmutable $startsAt): self
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(\DateTimeImmutable $endsAt): self
    {
        $this->endsAt = $endsAt;

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

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
     * @return Collection<int, Formation>
     */
    public function getFormations(): Collection
    {
        return $this->Formations;
    }

    public function addFormation(Formation $formation): self
    {
        if (!$this->Formations->contains($formation)) {
            $this->Formations->add($formation);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): self
    {
        $this->Formations->removeElement($formation);

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

}
