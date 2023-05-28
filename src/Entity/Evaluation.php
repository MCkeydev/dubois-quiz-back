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

/**
 * @Entity Entité "Evaluation" - Représente une évaluation.
 */
#[ORM\Entity(repositoryClass: EvaluationRepository::class)]
class Evaluation implements OwnedEntityInterface
{
    /**
     * @var int|null Identifiant unique de l'évaluation.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getEvaluation', 'api'])]
    private ?int $id = null;

    /**
     * @var \DateTimeImmutable|null Date et heure de la création de l'évaluation.
     */
    #[ORM\Column]
    #[Groups(['getEvaluation', 'api'])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var \DateTimeImmutable|null Date et heure de début de l'évaluation.
     */
    #[ORM\Column]
    #[Type('DateTimeImmutable')]
    #[NotBlank]
    #[NotNull]
    #[Groups(['getEvaluation', 'api'])]
    private ?\DateTimeImmutable $startsAt = null;

    /**
     * @var \DateTimeImmutable|null Date et heure de fin de l'évaluation.
     */
    #[ORM\Column]
    #[Type('DateTimeImmutable')]
    #[NotBlank]
    #[NotNull]
    #[Groups(['getEvaluation', 'api'])]
    private ?\DateTimeImmutable $endsAt = null;

    /**
     * @var bool|null Indicateur si l'évaluation est verrouillée ou non.
     */
    #[ORM\Column]
    #[Groups(['getEvaluation'])]
    private ?bool $isLocked = null;

    /**
     * @var \DateTimeImmutable|null Date et heure de la dernière mise à jour de l'évaluation.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Quiz|null Le quiz associé à l'évaluation.
     */
    #[ORM\ManyToOne(inversedBy: 'evaluations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getEvaluation', 'api', 'studentCopy'])]
    private ?Quiz $quiz = null;

    /**
     * @var Collection Liste des copies d'étudiant associées à l'évaluation.
     */
    #[ORM\OneToMany(mappedBy: 'evaluation', targetEntity: StudentCopy::class)]
    #[Groups(['getEvaluation'])]
    private Collection $studentCopies;

    /**
     * @var User|null L'auteur de l'évaluation.
     */
    #[ORM\ManyToOne(inversedBy: 'evaluations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    /**
     * @var Formation|null La formation associée à l'évaluation.
     */
    #[ORM\ManyToOne(inversedBy: 'evaluations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['api', 'studentCopy'])]
    private ?Formation $formation = null;

    /**
     * @var int|null Le score moyen de l'évaluation.
     */
    #[ORM\Column(nullable: true)]
    #[Groups(['api', 'studentCopy'])]
    private ?int $averageScore = null;

    /**
     * @var int|null Le nombre de copies pour l'évaluation.
     */
    #[ORM\Column]
    #[Groups(['api', 'studentCopy'])]
    private ?int $copyCount = null;

    #[Callback]
    public function validateDateTime(ExecutionContextInterface $context)
    {
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
        $this->studentCopies = new ArrayCollection();
        $this->copyCount = 0;
    }

    public function isOwner(User $user): bool
    {
        return $this->getAuthor() === $user;
    }
    // ...

    /**
     * Récupère l'ID de l'évaluation.
     *
     * @return int|null L'ID de l'évaluation.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère la date de création de l'évaluation.
     *
     * @return \DateTimeImmutable|null La date de création de l'évaluation.
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Définit la date de création de l'évaluation.
     *
     * @param \DateTimeImmutable $createdAt La nouvelle date de création de l'évaluation.
     *
     * @return self L'instance de l'évaluation.
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Récupère la date de début de l'évaluation.
     *
     * @return \DateTimeImmutable|null La date de début de l'évaluation.
     */
    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    /**
     * Définit la date de début de l'évaluation.
     *
     * @param \DateTimeImmutable|string $startsAt La nouvelle date de début de l'évaluation.
     *
     * @return self L'instance de l'évaluation.
     */
    public function setStartsAt($startsAt): self
    {
        if ($startsAt instanceof \DateTimeImmutable) {
            $this->startsAt = $startsAt;
        } elseif (is_string($startsAt)) {
            $this->startsAt = new \DateTimeImmutable($startsAt);
        }

        return $this;
    }

    /**
     * Récupère la date de fin de l'évaluation.
     *
     * @return \DateTimeImmutable|null La date de fin de l'évaluation.
     */
    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    /**
     * Définit la date de fin de l'évaluation.
     *
     * @param \DateTimeImmutable|string $endsAt La nouvelle date de fin de l'évaluation.
     *
     * @return self L'instance de l'évaluation.
     */
    public function setEndsAt($endsAt): self
    {
        if ($endsAt instanceof \DateTimeImmutable) {
            $this->endsAt = $endsAt;
        } elseif (is_string($endsAt)) {
            $this->endsAt = new \DateTimeImmutable($endsAt);
        }

        return $this;
    }

    /**
     * Récupère l'état de verrouillage de l'évaluation.
     *
     * @return bool|null Vrai si l'évaluation est verrouillée, faux sinon.
     */
    public function isIsLocked(): ?bool
    {
        return $this->isLocked;
    }

    /**
     * Définit l'état de verrouillage de l'évaluation.
     *
     * @param bool $isLocked Le nouvel état de verrouillage de l'évaluation.
     *
     * @return self L'instance de l'évaluation.
     */
    public function setIsLocked(bool $isLocked): self
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    /**
     * Récupère le quiz associé à l'évaluation.
     *
     * @return Quiz|null Le quiz associé à l'évaluation.
     */
    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    /**
     * Définit le quiz associé à l'évaluation.
     *
     * @param Quiz|null $quiz Le nouveau quiz à associer à l'évaluation.
     *
     * @return self L'instance de l'évaluation.
     */
    public function setQuiz(?Quiz $quiz): self
    {
        $this->quiz = $quiz;

        return $this;
    }

    /**
     * Récupère les copies des étudiants pour l'évaluation.
     *
     * @return Collection<int, StudentCopy> Les copies des étudiants pour l'évaluation.
     */
    public function getStudentCopies(): Collection
    {
        return $this->studentCopies;
    }

    /**
     * Ajoute une copie d'étudiant à l'évaluation.
     *
     * @param StudentCopy $studentCopy La copie de l'étudiant à ajouter.
     *
     * @return self L'instance de l'évaluation.
     */
    public function addStudentCopy(StudentCopy $studentCopy): self
    {
        if (!$this->studentCopies->contains($studentCopy)) {
            $this->studentCopies->add($studentCopy);
            $studentCopy->setEvaluation($this);
        }

        return $this;
    }

    /**
     * Supprime une copie d'étudiant de l'évaluation.
     *
     * @param StudentCopy $studentCopy La copie de l'étudiant à supprimer.
     *
     * @return self L'instance de l'évaluation.
     */
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

    /**
     * Récupère la date de mise à jour de l'évaluation.
     *
     * @return \DateTimeImmutable|null La date de mise à jour de l'évaluation.
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Définit la date de mise à jour de l'évaluation.
     *
     * @param \DateTimeImmutable $updatedAt La nouvelle date de mise à jour de l'évaluation.
     *
     * @return self L'instance de l'évaluation.
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Récupère l'auteur de l'évaluation.
     *
     * @return User|null L'auteur de l'évaluation.
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Définit l'auteur de l'évaluation.
     *
     * @param User|null $author Le nouvel auteur de l'évaluation.
     *
     * @return self L'instance de l'évaluation.
     */
    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Récupère la formation associée à l'évaluation.
     *
     * @return Formation|null La formation associée à l'évaluation.
     */
    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    /**
     * Définit la formation associée à l'évaluation.
     *
     * @param Formation|null $formation La nouvelle formation à associer à l'évaluation.
     *
     * @return self L'instance de l'évaluation.
     */
    public function setFormation(?Formation $formation): self
    {
        $this->formation = $formation;

        return $this;
    }

    /**
     * Récupère le score moyen de l'évaluation.
     *
     * @return int|null Le score moyen de l'évaluation.
     */
    public function getAverageScore(): ?int
    {
        return $this->averageScore;
    }

    /**
     * Définit le score moyen de l'évaluation.
     *
     * @param int|null $averageScore Le nouveau score moyen de l'évaluation.
     *
     * @return self L'instance de l'évaluation.
     */
    public function setAverageScore(?int $averageScore): self
    {
        $this->averageScore = $averageScore;

        return $this;
    }

    /**
     * Récupère le nombre de copies pour l'évaluation.
     *
     * @return int|null Le nombre de copies pour l'évaluation.
     */
    public function getCopyCount(): ?int
    {
        return $this->copyCount;
    }

    /**
     * Définit le nombre de copies pour l'évaluation.
     *
     * @param int $copyCount Le nouveau nombre de copies pour l'évaluation.
     *
     * @return self L'instance de l'évaluation.
     */
    public function setCopyCount(int $copyCount): self
    {
        $this->copyCount = $copyCount;

        return $this;
    }

    /**
     * Incrémente le nombre de copies de l'évaluation.
     *
     * @return self L'instance de l'évaluation.
     */
    public function incrementCopyCount(): self
    {
        ++$this->copyCount;

        return $this;
    }
}
