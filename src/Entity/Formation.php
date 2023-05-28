<?php

namespace App\Entity;

use App\Interfaces\EntityInterface;
use App\Repository\FormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @Entity Entité "Formation" - Représente une formation.
 */
#[ORM\Entity(repositoryClass: FormationRepository::class)]
class Formation implements EntityInterface
{
    /**
     * @var int|null Identifiant unique de la formation.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api', 'studentCopy'])]
    private ?int $id = null;

    /**
     * @var string|null Nom de la formation.
     */
    #[ORM\Column(length: 255)]
    #[NotNull]
    #[NotBlank]
    #[Groups(['api', 'studentCopy'])]
    private ?string $name = null;

    /**
     * @var \DateTimeImmutable|null Date et heure de la création de la formation.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection Liste des utilisateurs associés à la formation.
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'formations')]
    private Collection $users;

    /**
     * @var Collection Liste des évaluations associées à la formation.
     */
    #[ORM\OneToMany(mappedBy: 'formation', targetEntity: Evaluation::class)]
    private Collection $evaluations;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->users = new ArrayCollection();
        $this->evaluations = new ArrayCollection();
    }

    /**
     * Récupère l'ID de la formation.
     *
     * @return int|null L'ID de la formation.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère le nom de la formation.
     *
     * @return string|null Le nom de la formation.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Définit le nom de la formation.
     *
     * @param string $name Le nouveau nom de la formation.
     *
     * @return self L'instance de la formation.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Récupère la date de création de la formation.
     *
     * @return \DateTimeImmutable|null La date de création de la formation.
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Récupère la liste des utilisateurs associés à la formation.
     *
     * @return Collection<int, User> La liste des utilisateurs associés à la formation.
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * Ajoute un utilisateur à la formation.
     *
     * @param User $user L'utilisateur à ajouter.
     *
     * @return self L'instance de la formation.
     */
    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addFormation($this);
        }

        return $this;
    }

    /**
     * Supprime un utilisateur de la formation.
     *
     * @param User $user L'utilisateur à supprimer.
     *
     * @return self L'instance de la formation.
     */
    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeFormation($this);
        }

        return $this;
    }

    /**
     * Récupère la liste des évaluations associées à la formation.
     *
     * @return Collection<int, Evaluation> La liste des évaluations associées à la formation.
     */
    public function getEvaluations(): Collection
    {
        return $this->evaluations;
    }

    /**
     * Ajoute une évaluation à la formation.
     *
     * @param Evaluation $evaluation L'évaluation à ajouter.
     *
     * @return self L'instance de la formation.
     */
    public function addEvaluation(Evaluation $evaluation): self
    {
        if (!$this->evaluations->contains($evaluation)) {
            $this->evaluations->add($evaluation);
            $evaluation->setFormation($this);
        }

        return $this;
    }

    /**
     * Supprime une évaluation de la formation.
     *
     * @param Evaluation $evaluation L'évaluation à supprimer.
     *
     * @return self L'instance de la formation.
     */
    public function removeEvaluation(Evaluation $evaluation): self
    {
        if ($this->evaluations->removeElement($evaluation)) {
            if ($evaluation->getFormation() === $this) {
                $evaluation->setFormation(null);
            }
        }

        return $this;
    }
}
