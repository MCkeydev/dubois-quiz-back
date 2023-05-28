<?php

namespace App\Entity;

use App\Interfaces\OwnedEntityInterface;
use App\Repository\StudentCopyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @Entity Entité "StudentCopy" - Représente une copie de l'étudiant.
 */
#[ORM\Entity(repositoryClass: StudentCopyRepository::class)]
#[UniqueEntity(
    fields: ['student', 'evaluation'],
    message: 'L\'étudiant ne peut participer qu\'à une seule évaluation.',
    errorPath: 'student',
)]
class StudentCopy implements OwnedEntityInterface
{
    /**
     * @var int|null Identifiant unique de la copie de l'étudiant.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api', 'studentCopy'])]
    private ?int $id = null;

    /**
     * @var bool|null Indique si la copie peut être partagée.
     */
    #[ORM\Column]
    #[Groups(['fetchStudentCopy', 'fetchAnswer'])]
    private ?bool $canShare = null;

    /**
     * @var string|null Commentaire associé à la copie de l'étudiant.
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['fetchStudentCopy', 'fetchAnswer', 'fetchStudentCopyPreview', 'api', 'studentCopy'])]
    private ?string $commentary = null;

    /**
     * @var Collection Liste des réponses de l'étudiant associées à la copie.
     */
    #[ORM\OneToMany(mappedBy: 'studentCopy', targetEntity: StudentAnswer::class, orphanRemoval: true)]
    #[Groups(['fetchStudentCopy', 'api', 'studentCopy'])]
    private Collection $studentAnswers;

    /**
     * @var User|null L'étudiant associé à la copie.
     */
    #[ORM\ManyToOne(inversedBy: 'studentCopies')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['fetchStudentCopy', 'api', 'studentCopy'])]
    private ?User $student = null;

    /**
     * @var Evaluation|null L'évaluation associée à la copie.
     */
    #[ORM\ManyToOne(inversedBy: 'studentCopies')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['api', 'studentCopy'])]
    private ?Evaluation $evaluation = null;

    /**
     * @var float|null Score de la copie de l'étudiant.
     */
    #[ORM\Column(nullable: true)]
    #[Groups(['fetchStudentCopyPreview', 'studentCopy'])]
    private ?float $score = null;

    /**
     * @var bool|null Indique si la copie est verrouillée.
     */
    #[ORM\Column]
    private ?bool $isLocked = null;

    /**
     * @var int|null Position de la copie dans l'évaluation.
     */
    #[ORM\Column(nullable: true)]
    #[Groups(['fetchStudentCopyPreview', 'studentCopy'])]
    private ?int $position = null;

    /**
     * @var \DateTimeImmutable|null Date de création de la copie.
     */
    #[ORM\Column]
    #[Groups(['fetchStudentCopy', 'api'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->canShare = false;
        $this->studentAnswers = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * Vérifie si l'utilisateur est propriétaire de la copie.
     *
     * @param User $user L'utilisateur à vérifier.
     *
     * @return bool Vrai si l'utilisateur est propriétaire, faux sinon.
     */
    public function isOwner(User $user): bool
    {
        return $this->getStudent() === $user || $this->getEvaluation()->getAuthor() === $user;
    }

    /**
     * Récupère l'ID de la copie de l'étudiant.
     *
     * @return int|null L'ID de la copie.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Vérifie si la copie peut être partagée.
     *
     * @return bool|null Vrai si la copie peut être partagée, faux sinon.
     */
    public function isCanShare(): ?bool
    {
        return $this->canShare;
    }

    /**
     * Définit si la copie peut être partagée.
     *
     * @param bool $canShare Indique si la copie peut être partagée.
     *
     * @return self L'instance de la copie.
     */
    public function setCanShare(bool $canShare): self
    {
        $this->canShare = $canShare;

        return $this;
    }

    /**
     * Récupère le commentaire associé à la copie de l'étudiant.
     *
     * @return string|null Le commentaire associé à la copie.
     */
    public function getCommentary(): ?string
    {
        return $this->commentary;
    }

    /**
     * Définit le commentaire associé à la copie de l'étudiant.
     *
     * @param string|null $commentary Le commentaire associé à la copie.
     *
     * @return self L'instance de la copie.
     */
    public function setCommentary(?string $commentary): self
    {
        $this->commentary = $commentary;

        return $this;
    }

    /**
     * Récupère la liste des réponses de l'étudiant associées à la copie.
     *
     * @return Collection<int, StudentAnswer> La liste des réponses de l'étudiant.
     */
    public function getStudentAnswers(): Collection
    {
        return $this->studentAnswers;
    }

    /**
     * Ajoute une réponse d'étudiant à la copie.
     *
     * @param StudentAnswer $studentAnswer La réponse d'étudiant à ajouter.
     *
     * @return self L'instance de la copie.
     */
    public function addStudentAnswer(StudentAnswer $studentAnswer): self
    {
        if (!$this->studentAnswers->contains($studentAnswer)) {
            $this->studentAnswers->add($studentAnswer);
            $studentAnswer->setStudentCopy($this);
        }

        return $this;
    }

    /**
     * Supprime une réponse d'étudiant de la copie.
     *
     * @param StudentAnswer $studentAnswer La réponse d'étudiant à supprimer.
     *
     * @return self L'instance de la copie.
     */
    public function removeStudentAnswer(StudentAnswer $studentAnswer): self
    {
        if ($this->studentAnswers->removeElement($studentAnswer)) {
            // Définir le côté propriétaire à null (sauf si déjà modifié)
            if ($studentAnswer->getStudentCopy() === $this) {
                $studentAnswer->setStudentCopy(null);
            }
        }

        return $this;
    }

    /**
     * Récupère l'étudiant associé à la copie.
     *
     * @return User|null L'étudiant associé à la copie.
     */
    public function getStudent(): ?User
    {
        return $this->student;
    }

    /**
     * Définit l'étudiant associé à la copie.
     *
     * @param User|null $student L'étudiant associé à la copie.
     *
     * @return self L'instance de la copie.
     */
    public function setStudent(?User $student): self
    {
        $this->student = $student;

        return $this;
    }

    /**
     * Récupère l'évaluation associée à la copie.
     *
     * @return Evaluation|null L'évaluation associée à la copie.
     */
    public function getEvaluation(): ?Evaluation
    {
        return $this->evaluation;
    }

    /**
     * Définit l'évaluation associée à la copie.
     *
     * @param Evaluation|null $evaluation L'évaluation associée à la copie.
     *
     * @return self L'instance de la copie.
     */
    public function setEvaluation(?Evaluation $evaluation): self
    {
        $this->evaluation = $evaluation;

        return $this;
    }

    /**
     * Récupère le score de la copie de l'étudiant.
     *
     * @return float|null Le score de la copie.
     */
    public function getScore(): ?float
    {
        return $this->score;
    }

    /**
     * Définit le score de la copie de l'étudiant.
     *
     * @param float|null $score Le score de la copie.
     *
     * @return self L'instance de la copie.
     */
    public function setScore(?float $score): self
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Vérifie si la copie est verrouillée.
     *
     * @return bool|null Vrai si la copie est verrouillée, faux sinon.
     */
    public function isIsLocked(): ?bool
    {
        return $this->isLocked;
    }

    /**
     * Définit si la copie est verrouillée.
     *
     * @param bool $isLocked Indique si la copie est verrouillée.
     *
     * @return self L'instance de la copie.
     */
    public function setIsLocked(bool $isLocked): self
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    /**
     * Récupère la position de la copie dans l'évaluation.
     *
     * @return int|null La position de la copie.
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * Définit la position de la copie dans l'évaluation.
     *
     * @param int|null $position La position de la copie.
     *
     * @return self L'instance de la copie.
     */
    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Récupère la date de création de la copie.
     *
     * @return \DateTimeImmutable|null La date de création de la copie.
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Définit la date de création de la copie.
     *
     * @param \DateTimeImmutable $createdAt La date de création de la copie.
     *
     * @return self L'instance de la copie.
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
