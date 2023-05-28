<?php

namespace App\Entity;

use App\Interfaces\EntityInterface;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *  Entité "User" - Représente les utilisateurs de l'application.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EntityInterface
{
    /**
     * @var int|null Identifiant unique de l'utilisateur.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getEvaluation', 'fetchStudentCopy', 'getUser'])]
    private ?int $id = null;

    /**
     * @var string|null Adresse email de l'utilisateur.
     */
    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['getEvaluation', 'fetchStudentCopy', 'getUser', 'api'])]
    private ?string $email = null;

    /**
     * @var array Rôles de l'utilisateur.
     */
    #[ORM\Column]
    #[Groups(['getUser'])]
    private array $roles = [];

    /**
     * @var string|null Mot de passe hashé de l'utilisateur.
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, StudentCopy> Les copies d'étudiant associées à l'utilisateur.
     */
    #[ORM\OneToMany(mappedBy: 'student', targetEntity: StudentCopy::class)]
    private Collection $studentCopies;

    /**
     * @var string|null Nom de l'utilisateur.
     */
    #[ORM\Column(length: 255)]
    #[Groups(['getUser', 'api', 'studentCopy'])]
    private ?string $name = null;

    /**
     * @var string|null Prénom de l'utilisateur.
     */
    #[ORM\Column(length: 255)]
    #[Groups(['getUser', 'api', 'studentCopy'])]
    private ?string $surname = null;

    /**
     * @var Collection<int, Quiz> Les quiz créés par l'utilisateur.
     */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Quiz::class)]
    private Collection $quizzes;

    /**
     * @var Collection<int, Evaluation> Les évaluations créées par l'utilisateur.
     */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Evaluation::class)]
    private Collection $evaluations;

    /**
     * @var Collection<int, Formation> Les formations auxquelles l'utilisateur est associé.
     */
    #[ORM\ManyToMany(targetEntity: Formation::class, inversedBy: 'users')]
    private Collection $formations;

    public function __construct()
    {
        $this->studentCopies = new ArrayCollection();
        $this->quizzes = new ArrayCollection();
        $this->evaluations = new ArrayCollection();
        $this->formations = new ArrayCollection();
    }

    /**
     * Récupère l'ID de l'utilisateur.
     *
     * @return int|null L'ID de l'utilisateur.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère l'adresse email de l'utilisateur.
     *
     * @return string|null L'adresse email de l'utilisateur.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Définit l'adresse email de l'utilisateur.
     *
     * @param string $email La nouvelle adresse email de l'utilisateur.
     *
     * @return self L'instance de l'utilisateur.
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Récupère les rôles de l'utilisateur.
     *
     * @return array Les rôles de l'utilisateur.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // garantit que chaque utilisateur a au moins le rôle ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Définit les rôles de l'utilisateur.
     *
     * @param array $roles Les nouveaux rôles de l'utilisateur.
     *
     * @return self L'instance de l'utilisateur.
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Récupère le mot de passe hashé de l'utilisateur.
     *
     * @return string Le mot de passe hashé de l'utilisateur.
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Définit le mot de passe hashé de l'utilisateur.
     *
     * @param string $password Le nouveau mot de passe hashé de l'utilisateur.
     *
     * @return self L'instance de l'utilisateur.
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Supprime les informations sensibles de l'utilisateur.
     *
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // Si vous stockez des données temporaires sensibles sur l'utilisateur, effacez-les ici
        // $this->plainPassword = null;
    }

    /**
     * Récupère les copies d'étudiant associées à l'utilisateur.
     *
     * @return Collection<int, StudentCopy> Les copies d'étudiant associées à l'utilisateur.
     */
    public function getStudentCopies(): Collection
    {
        return $this->studentCopies;
    }

    /**
     * Ajoute une copie d'étudiant à l'utilisateur.
     *
     * @param StudentCopy $studentCopy La copie d'étudiant à ajouter.
     *
     * @return self L'instance de l'utilisateur.
     */
    public function addStudentCopy(StudentCopy $studentCopy): self
    {
        if (!$this->studentCopies->contains($studentCopy)) {
            $this->studentCopies->add($studentCopy);
            $studentCopy->setStudent($this);
        }

        return $this;
    }

    /**
     * Supprime une copie d'étudiant de l'utilisateur.
     *
     * @param StudentCopy $studentCopy La copie d'étudiant à supprimer.
     *
     * @return self L'instance de l'utilisateur.
     */
    public function removeStudentCopy(StudentCopy $studentCopy): self
    {
        if ($this->studentCopies->removeElement($studentCopy)) {
            // Définit le côté propriétaire à null (sauf si déjà modifié)
            if ($studentCopy->getStudent() === $this) {
                $studentCopy->setStudent(null);
            }
        }

        return $this;
    }

    /**
     * Récupère le nom de l'utilisateur.
     *
     * @return string|null Le nom de l'utilisateur.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Définit le nom de l'utilisateur.
     *
     * @param string $name Le nouveau nom de l'utilisateur.
     *
     * @return self L'instance de l'utilisateur.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Récupère le prénom de l'utilisateur.
     *
     * @return string|null Le prénom de l'utilisateur.
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    /**
     * Définit le prénom de l'utilisateur.
     *
     * @param string $surname Le nouveau prénom de l'utilisateur.
     *
     * @return self L'instance de l'utilisateur.
     */
    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Récupère les quiz créés par l'utilisateur.
     *
     * @return Collection<int, Quiz> Les quiz créés par l'utilisateur.
     */
    public function getQuizzes(): Collection
    {
        return $this->quizzes;
    }

    /**
     * Ajoute un quiz créé par l'utilisateur.
     *
     * @param Quiz $quiz Le quiz à ajouter.
     *
     * @return self L'instance de l'utilisateur.
     */
    public function addQuiz(Quiz $quiz): self
    {
        if (!$this->quizzes->contains($quiz)) {
            $this->quizzes->add($quiz);
            $quiz->setAuthor($this);
        }

        return $this;
    }

    /**
     * Supprime un quiz créé par l'utilisateur.
     *
     * @param Quiz $quiz Le quiz à supprimer.
     *
     * @return self L'instance de l'utilisateur.
     */
    public function removeQuiz(Quiz $quiz): self
    {
        if ($this->quizzes->removeElement($quiz)) {
            // Définit le côté propriétaire à null (sauf si déjà modifié)
            if ($quiz->getAuthor() === $this) {
                $quiz->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * Récupère les évaluations créées par l'utilisateur.
     *
     * @return Collection<int, Evaluation> Les évaluations créées par l'utilisateur.
     */
    public function getEvaluations(): Collection
    {
        return $this->evaluations;
    }

    /**
     * Ajoute une évaluation créée par l'utilisateur.
     *
     * @param Evaluation $evaluation L'évaluation à ajouter.
     *
     * @return self L'instance de l'utilisateur.
     */
    public function addEvaluation(Evaluation $evaluation): self
    {
        if (!$this->evaluations->contains($evaluation)) {
            $this->evaluations->add($evaluation);
            $evaluation->setAuthor($this);
        }

        return $this;
    }

    /**
     * Supprime une évaluation créée par l'utilisateur.
     *
     * @param Evaluation $evaluation L'évaluation à supprimer.
     *
     * @return self L'instance de l'utilisateur.
     */
    public function removeEvaluation(Evaluation $evaluation): self
    {
        if ($this->evaluations->removeElement($evaluation)) {
            // Définit le côté propriétaire à null (sauf si déjà modifié)
            if ($evaluation->getAuthor() === $this) {
                $evaluation->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * Récupère les formations auxquelles l'utilisateur est associé.
     *
     * @return Collection<int, Formation> Les formations auxquelles l'utilisateur est associé.
     */
    public function getFormations(): Collection
    {
        return $this->formations;
    }

    /**
     * Ajoute une formation à l'utilisateur.
     *
     * @param Formation $formation La formation à ajouter.
     *
     * @return self L'instance de l'utilisateur.
     */
    public function addFormation(Formation $formation): self
    {
        if (!$this->formations->contains($formation)) {
            $this->formations->add($formation);
        }

        return $this;
    }

    /**
     * Supprime une formation de l'utilisateur.
     *
     * @param Formation $formation La formation à supprimer.
     *
     * @return self L'instance de l'utilisateur.
     */
    public function removeFormation(Formation $formation): self
    {
        $this->formations->removeElement($formation);

        return $this;
    }
}
