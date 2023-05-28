<?php

namespace App\Entity;

use App\Interfaces\EntityInterface;
use App\Repository\StudentAnswerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 *  Entité "StudentAnswer" - Représente une réponse de l'étudiant.
 */
#[ORM\Entity(repositoryClass: StudentAnswerRepository::class)]
#[UniqueEntity(
    fields: ['studentCopy', 'question'],
    message: 'L\'étudiant ne peut répondre à une question qu\'une seule fois.',
    errorPath: 'question',
)]
class StudentAnswer implements EntityInterface
{
    /**
     * @var int|null Identifiant unique de la réponse de l'étudiant.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['fetchAnswer', 'api', 'studentCopy'])]
    private ?int $id = null;

    /**
     * @var string|null Annotation associée à la réponse de l'étudiant.
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['fetchAnswer', 'api', 'studentCopy'])]
    private ?string $annotation = null;

    /**
     * @var int|null Note attribuée à la réponse de l'étudiant.
     */
    #[ORM\Column(nullable: true)]
    #[Groups(['fetchAnswer', 'api', 'studentCopy'])]
    private ?int $score = null;

    /**
     * @var StudentCopy|null Copie de l'étudiant associée à la réponse.
     */
    #[ORM\ManyToOne(inversedBy: 'studentAnswers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?StudentCopy $studentCopy = null;

    /**
     * @var Question|null Question associée à la réponse de l'étudiant.
     */
    #[ORM\ManyToOne(inversedBy: 'studentAnswers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['fetchAnswer', 'api', 'studentCopy'])]
    private ?Question $question = null;

    /**
     * @var string|null Réponse fournie par l'étudiant.
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[NotBlank(allowNull: true)]
    #[Groups(['fetchAnswer', 'api', 'studentCopy'])]
    private ?string $answer = null;

    /**
     * @var Answer|null Choix de réponse associé à la réponse de l'étudiant.
     */
    #[ORM\ManyToOne]
    #[Groups(['fetchAnswer', 'api', 'studentCopy'])]
    private ?Answer $choice = null;

    /**
     * Valide la réponse de l'étudiant.
     *
     * @param ExecutionContextInterface $context Le contexte de validation.
     *
     * @return void
     */
    #[Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->score > $this->getQuestion()->getMaxScore()) {
            $context->buildViolation('La note attribuée à la réponse ne peut pas être plus élevée que celle indiquée sur le barème.')
                ->atPath('score')
                ->addViolation();
        }
    }

    /**
     * Récupère l'ID de la réponse de l'étudiant.
     *
     * @return int|null L'ID de la réponse de l'étudiant.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère l'annotation associée à la réponse de l'étudiant.
     *
     * @return string|null L'annotation associée à la réponse de l'étudiant.
     */
    public function getAnnotation(): ?string
    {
        return $this->annotation;
    }

    /**
     * Définit l'annotation associée à la réponse de l'étudiant.
     *
     * @param string $annotation La nouvelle annotation.
     *
     * @return self L'instance de la réponse de l'étudiant.
     */
    public function setAnnotation(string $annotation): self
    {
        $this->annotation = $annotation;

        return $this;
    }

    /**
     * Récupère la note attribuée à la réponse de l'étudiant.
     *
     * @return int|null La note attribuée à la réponse de l'étudiant.
     */
    public function getScore(): ?int
    {
        return $this->score;
    }

    /**
     * Définit la note attribuée à la réponse de l'étudiant.
     *
     * @param int $score La nouvelle note.
     *
     * @return self L'instance de la réponse de l'étudiant.
     */
    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Récupère la copie de l'étudiant associée à la réponse.
     *
     * @return StudentCopy|null La copie de l'étudiant associée à la réponse.
     */
    public function getStudentCopy(): ?StudentCopy
    {
        return $this->studentCopy;
    }

    /**
     * Définit la copie de l'étudiant associée à la réponse.
     *
     * @param StudentCopy|null $studentCopy La nouvelle copie de l'étudiant.
     *
     * @return self L'instance de la réponse de l'étudiant.
     */
    public function setStudentCopy(?StudentCopy $studentCopy): self
    {
        $this->studentCopy = $studentCopy;

        return $this;
    }

    /**
     * Récupère la question associée à la réponse de l'étudiant.
     *
     * @return Question|null La question associée à la réponse de l'étudiant.
     */
    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    /**
     * Définit la question associée à la réponse de l'étudiant.
     *
     * @param Question|null $question La nouvelle question.
     *
     * @return self L'instance de la réponse de l'étudiant.
     */
    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Récupère la réponse fournie par l'étudiant.
     *
     * @return string|null La réponse fournie par l'étudiant.
     */
    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    /**
     * Définit la réponse fournie par l'étudiant.
     *
     * @param string $answer La nouvelle réponse.
     *
     * @return self L'instance de la réponse de l'étudiant.
     */
    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Récupère le choix de réponse associé à la réponse de l'étudiant.
     *
     * @return Answer|null Le choix de réponse associé à la réponse de l'étudiant.
     */
    public function getChoice(): ?Answer
    {
        return $this->choice;
    }

    /**
     * Définit le choix de réponse associé à la réponse de l'étudiant.
     *
     * @param Answer|null $choice Le nouveau choix de réponse.
     *
     * @return self L'instance de la réponse de l'étudiant.
     */
    public function setChoice(?Answer $choice): self
    {
        $this->choice = $choice;

        return $this;
    }
}
