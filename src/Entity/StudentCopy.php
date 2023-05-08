<?php

namespace App\Entity;

use App\Interfaces\OwnedEntityInterface;
use App\Repository\StudentCopyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StudentCopyRepository::class)]
#[UniqueEntity(
    fields: ['student', 'evaluation'],
    message: 'Student can only participate to an evaluation once.',
    errorPath: 'student',
)]
class StudentCopy implements OwnedEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('api')]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['fetchStudentCopy', 'fetchAnswer'])]
    private ?bool $canShare = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['fetchStudentCopy', 'fetchAnswer', 'fetchStudentCopyPreview', 'api'])]
    private ?string $commentary = null;

    #[ORM\OneToMany(mappedBy: 'studentCopy', targetEntity: StudentAnswer::class, orphanRemoval: true)]
    #[Groups(['fetchStudentCopy', 'api'])]
    private Collection $studentAnswers;

    #[ORM\ManyToOne(inversedBy: 'studentCopies')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['fetchStudentCopy', 'api'])]
    private ?User $student = null;

    #[ORM\ManyToOne(inversedBy: 'studentCopies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Evaluation $evaluation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['fetchStudentCopyPreview'])]
    private ?float $score = null;

    #[ORM\Column]
    private ?bool $isLocked = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['fetchStudentCopyPreview'])]
    private ?int $position = null;

    #[ORM\Column]
    #[Groups(['fetchStudentCopy', 'api'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->canShare = false;
        $this->studentAnswers = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function isOwner(User $user): bool
    {
        return $this->getStudent() === $user || $this->getEvaluation()->getAuthor() === $user;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isCanShare(): ?bool
    {
        return $this->canShare;
    }

    public function setCanShare(bool $canShare): self
    {
        $this->canShare = $canShare;

        return $this;
    }

    public function getCommentary(): ?string
    {
        return $this->commentary;
    }

    public function setCommentary(?string $commentary): self
    {
        $this->commentary = $commentary;

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
            $studentAnswer->setStudentCopy($this);
        }

        return $this;
    }

    public function removeStudentAnswer(StudentAnswer $studentAnswer): self
    {
        if ($this->studentAnswers->removeElement($studentAnswer)) {
            // set the owning side to null (unless already changed)
            if ($studentAnswer->getStudentCopy() === $this) {
                $studentAnswer->setStudentCopy(null);
            }
        }

        return $this;
    }

    public function getStudent(): ?User
    {
        return $this->student;
    }

    public function setStudent(?User $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function getEvaluation(): ?Evaluation
    {
        return $this->evaluation;
    }

    public function setEvaluation(?Evaluation $evaluation): self
    {
        $this->evaluation = $evaluation;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): self
    {
        $this->score = $score;

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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
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
}
