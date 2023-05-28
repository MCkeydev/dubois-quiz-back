<?php

namespace App\Entity;

use App\Interfaces\EntityInterface;
use App\Repository\AnswerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @Entity Entité "Answer" - Représente une réponse.
 */
#[ORM\Entity(repositoryClass: AnswerRepository::class)]
class Answer implements EntityInterface
{
    /**
     * @var int|null Identifiant unique de la réponse.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getEvaluation', 'api'])]
    private ?int $id = null;

    /**
     * @var string|null Titre de la réponse.
     */
    #[NotNull]
    #[NotBlank]
    #[ORM\Column(length: 255)]
    #[Groups(['getEvaluation', 'api'])]
    private ?string $title = null;

    /**
     * @var Question|null La question associée à la réponse.
     */
    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    /**
     * Récupère l'ID de la réponse.
     *
     * @return int|null L'ID de la réponse.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère le titre de la réponse.
     *
     * @return string|null Le titre de la réponse.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Définit le titre de la réponse.
     *
     * @param string $title Le nouveau titre de la réponse.
     *
     * @return self L'instance de la réponse.
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Récupère la question associée à la réponse.
     *
     * @return Question|null La question associée à la réponse.
     */
    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    /**
     * Définit la question associée à la réponse.
     *
     * @param Question|null $question La nouvelle question à associer à la réponse.
     *
     * @return self L'instance de la réponse.
     */
    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

        return $this;
    }
}
