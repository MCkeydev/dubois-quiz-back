<?php

namespace App\Entity;

use App\Interfaces\OwnedEntityInterface;
use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *  Entité "Question" - Représente une question.
 */
#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question implements OwnedEntityInterface
{
    /**
     * @var int|null Identifiant unique de la question.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api', 'fetchAnswer', 'getEvaluation', 'studentCopy'])]
    private ?int $id = null;

    /**
     * @var string|null Titre de la question.
     */
    #[ORM\Column(length: 255)]
    #[Groups(['api', 'fetchAnswer', 'getEvaluation', 'studentCopy'])]
    private ?string $title = null;

    /**
     * @var int|null Score maximum de la question.
     */
    #[ORM\Column]
    #[Groups(['api', 'fetchAnswer', 'studentCopy'])]
    private ?int $maxScore = null;

    /**
     * @var Quiz|null Le quiz associé à la question.
     */
    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    /**
     * @var Collection Liste des réponses associées à la question.
     */
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Answer::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['api', 'getEvaluation'])]
    private Collection $answers;

    /**
     * @var Collection Liste des réponses des étudiants à la question.
     */
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: StudentAnswer::class, orphanRemoval: true)]
    private Collection $studentAnswers;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->studentAnswers = new ArrayCollection();
    }

    /**
     * Vérifie si l'utilisateur est propriétaire de la question.
     *
     * @param User $user L'utilisateur à vérifier.
     *
     * @return bool Vrai si l'utilisateur est propriétaire, faux sinon.
     */
    public function isOwner(User $user): bool
    {
        return $this->getQuiz()->getAuthor() === $user;
    }

    /**
     * Récupère l'ID de la question.
     *
     * @return int|null L'ID de la question.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère le titre de la question.
     *
     * @return string|null Le titre de la question.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Définit le titre de la question.
     *
     * @param string $title Le nouveau titre de la question.
     *
     * @return self L'instance de la question.
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Récupère le score maximum de la question.
     *
     * @return int|null Le score maximum de la question.
     */
    public function getMaxScore(): ?int
    {
        return $this->maxScore;
    }

    /**
     * Définit le score maximum de la question.
     *
     * @param int $maxScore Le nouveau score maximum de la question.
     *
     * @return self L'instance de la question.
     */
    public function setMaxScore(int $maxScore): self
    {
        $this->maxScore = $maxScore;

        return $this;
    }

    /**
     * Récupère le quiz associé à la question.
     *
     * @return Quiz|null Le quiz associé à la question.
     */
    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    /**
     * Définit le quiz associé à la question.
     *
     * @param Quiz|null $quiz Le nouveau quiz à associer à la question.
     *
     * @return self L'instance de la question.
     */
    public function setQuiz(?Quiz $quiz): self
    {
        $this->quiz = $quiz;

        return $this;
    }

    /**
     * Récupère la liste des réponses associées à la question.
     *
     * @return Collection<int, Answer> La liste des réponses associées à la question.
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    /**
     * Ajoute une réponse à la question.
     *
     * @param Answer $answer La réponse à ajouter.
     *
     * @return self L'instance de la question.
     */
    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setQuestion($this);
        }

        return $this;
    }

    /**
     * Supprime une réponse de la question.
     *
     * @param Answer $answer La réponse à supprimer.
     *
     * @return self L'instance de la question.
     */
    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * Récupère la liste des réponses des étudiants à la question.
     *
     * @return Collection<int, StudentAnswer> La liste des réponses des étudiants à la question.
     */
    public function getStudentAnswers(): Collection
    {
        return $this->studentAnswers;
    }

    /**
     * Ajoute une réponse d'étudiant à la question.
     *
     * @param StudentAnswer $studentAnswer La réponse d'étudiant à ajouter.
     *
     * @return self L'instance de la question.
     */
    public function addStudentAnswer(StudentAnswer $studentAnswer): self
    {
        if (!$this->studentAnswers->contains($studentAnswer)) {
            $this->studentAnswers->add($studentAnswer);
            $studentAnswer->setQuestion($this);
        }

        return $this;
    }

    /**
     * Supprime une réponse d'étudiant de la question.
     *
     * @param StudentAnswer $studentAnswer La réponse d'étudiant à supprimer.
     *
     * @return self L'instance de la question.
     */
    public function removeStudentAnswer(StudentAnswer $studentAnswer): self
    {
        if ($this->studentAnswers->removeElement($studentAnswer)) {
            if ($studentAnswer->getQuestion() === $this) {
                $studentAnswer->setQuestion(null);
            }
        }

        return $this;
    }
}
