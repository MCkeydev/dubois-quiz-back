<?php

namespace App\Entity;

use App\Interfaces\EntityInterface;
use App\Repository\StudentCopyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StudentCopyRepository::class)]
#[UniqueEntity(
    fields: ['student', 'evaluation'],
    errorPath: 'student',
    message: 'Student can only participate to an evaluation once.',
)]
class StudentCopy implements EntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups('fetchStudentCopy')]
    private ?bool $canShare = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('fetchStudentCopy')]
    private ?string $commentary = null;

    #[ORM\Column(nullable: true)]
    #[Groups('fetchStudentCopy')]
    private ?int $averageScore = null;

    #[ORM\OneToMany(mappedBy: 'studentCopy', targetEntity: StudentAnswer::class, orphanRemoval: true)]
    #[Groups('fetchStudentCopy')]
    private Collection $studentAnswers;

    #[ORM\ManyToOne(inversedBy: 'studentCopies')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('fetchStudentCopy')]
    private ?User $student = null;

    #[ORM\ManyToOne(inversedBy: 'professorCopies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $professor = null;

    #[ORM\ManyToOne(inversedBy: 'studentCopies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Evaluation $evaluation = null;

    public function __construct()
    {
        $this->canShare = false;
        $this->studentAnswers = new ArrayCollection();
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

    public function getAverageScore(): ?int
    {
        return $this->averageScore;
    }

    public function setAverageScore(?int $averageScore): self
    {
        $this->averageScore = $averageScore;

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

    public function getProfessor(): ?User
    {
        return $this->professor;
    }

    public function setProfessor(?User $professor): self
    {
        $this->professor = $professor;

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
}
