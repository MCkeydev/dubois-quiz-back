<?php

namespace App\Entity;

use App\Interfaces\OwnedEntityInterface;
use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @Entity Entité "Quiz" - Représente un quiz.
 */
#[ORM\Entity(repositoryClass: QuizRepository::class)]
class Quiz implements OwnedEntityInterface
{
    /**
     * @var int|null Identifiant unique du quiz.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('api')]
    private ?int $id = null;

    /**
     * @var string|null Titre du quiz.
     */
    #[ORM\Column(length: 255)]
    #[NotBlank]
    #[NotNull]
    #[Groups(['api', 'getEvaluation', 'studentCopy'])]
    private ?string $title = null;

    /**
     * @var Collection Liste des évaluations associées au quiz.
     */
    #[ORM\OneToMany(mappedBy: 'quiz', targetEntity: Evaluation::class)]
    private Collection $evaluations;

    /**
     * @var Collection Liste des questions du quiz.
     */
    #[ORM\OneToMany(mappedBy: 'Quiz', targetEntity: Question::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['api', 'getEvaluation'])]
    #[NotBlank]
    #[Count(min: 1, minMessage: 'Au moins une question doit être fournie.')]
    private Collection $questions;

    /**
     * @var User|null L'auteur du quiz.
     */
    #[ORM\ManyToOne(inversedBy: 'quizzes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getEvaluation'])]
    private ?User $author = null;

    /**
     * @var int|null Score maximum du quiz.
     */
    #[ORM\Column]
    #[Groups(['api', 'getEvaluation', 'studentCopy'])]
    private ?int $maxScore = null;

    public function __construct()
    {
        $this->evaluations = new ArrayCollection();
        $this->questions = new ArrayCollection();
    }

    /**
     * Vérifie si l'utilisateur est propriétaire du quiz.
     *
     * @param User $user L'utilisateur à vérifier.
     *
     * @return bool Vrai si l'utilisateur est propriétaire, faux sinon.
     */
    public function isOwner(User $user): bool
    {
        return $this->getAuthor() === $user;
    }

    /**
     * Récupère l'ID du quiz.
     *
     * @return int|null L'ID du quiz.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère le titre du quiz.
     *
     * @return string|null Le titre du quiz.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Définit le titre du quiz.
     *
     * @param string $title Le nouveau titre du quiz.
     *
     * @return self L'instance du quiz.
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Récupère la liste des évaluations associées au quiz.
     *
     * @return Collection<int, Evaluation> La liste des évaluations associées au quiz.
     */
    public function getEvaluations(): Collection
    {
        return $this->evaluations;
    }

    /**
     * Ajoute une évaluation au quiz.
     *
     * @param Evaluation $evaluation L'évaluation à ajouter.
     *
     * @return self L'instance du quiz.
     */
    public function addEvaluation(Evaluation $evaluation): self
    {
        if (!$this->evaluations->contains($evaluation)) {
            $this->evaluations->add($evaluation);
            $evaluation->setQuiz($this);
        }

        return $this;
    }

    /**
     * Supprime une évaluation du quiz.
     *
     * @param Evaluation $evaluation L'évaluation à supprimer.
     *
     * @return self L'instance du quiz.
     */
    public function removeEvaluation(Evaluation $evaluation): self
    {
        if ($this->evaluations->removeElement($evaluation)) {
            if ($evaluation->getQuiz() === $this) {
                $evaluation->setQuiz(null);
            }
        }

        return $this;
    }

    /**
     * Récupère la liste des questions du quiz.
     *
     * @return Collection<int, Question> La liste des questions du quiz.
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    /**
     * Ajoute une question au quiz.
     *
     * @param Question $question La question à ajouter.
     *
     * @return self L'instance du quiz.
     */
    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setQuiz($this);
        }

        return $this;
    }

    /**
     * Supprime une question du quiz.
     *
     * @param Question $question La question à supprimer.
     *
     * @return self L'instance du quiz.
     */
    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            if ($question->getQuiz() === $this) {
                $question->setQuiz(null);
            }
        }

        return $this;
    }

    /**
     * Récupère l'auteur du quiz.
     *
     * @return User|null L'auteur du quiz.
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Définit l'auteur du quiz.
     *
     * @param User|null $author Le nouvel auteur du quiz.
     *
     * @return self L'instance du quiz.
     */
    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Récupère le score maximum du quiz.
     *
     * @return int|null Le score maximum du quiz.
     */
    public function getMaxScore(): ?int
    {
        return $this->maxScore;
    }

    /**
     * Définit le score maximum du quiz.
     *
     * @param int $maxScore Le nouveau score maximum du quiz.
     *
     * @return self L'instance du quiz.
     */
    public function setMaxScore(int $maxScore): self
    {
        $this->maxScore = $maxScore;

        return $this;
    }
}
