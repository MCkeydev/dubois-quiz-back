<?php

namespace App\Entity;

use App\Interfaces\EntityInterface;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formation $Formation = null;

    #[ORM\OneToMany(mappedBy: 'student', targetEntity: StudentCopy::class)]
    private Collection $studentCopies;

    #[ORM\OneToMany(mappedBy: 'professor', targetEntity: StudentCopy::class)]
    private Collection $professorCopies;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $surname = null;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Quiz::class)]
    private Collection $quizzes;

    public function __construct()
    {
        $this->studentCopies = new ArrayCollection();
        $this->quizzes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

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
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFormation(): ?Formation
    {
        return $this->Formation;
    }

    public function setFormation(?Formation $Formation): self
    {
        $this->Formation = $Formation;

        return $this;
    }

    /**
     * @return Collection<int, StudentCopy>
     */
    public function getStudentCopies(): Collection
    {
        return $this->studentCopies;
    }

    public function addStudentCopy(StudentCopy $studentCopy): self
    {
        if (!$this->studentCopies->contains($studentCopy)) {
            $this->studentCopies->add($studentCopy);
            $studentCopy->setStudent($this);
        }

        return $this;
    }

    public function removeStudentCopy(StudentCopy $studentCopy): self
    {
        if ($this->studentCopies->removeElement($studentCopy)) {
            // set the owning side to null (unless already changed)
            if ($studentCopy->getStudent() === $this) {
                $studentCopy->setStudent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StudentCopy>
     */
    public function getProfessorCopies(): Collection
    {
        return $this->studentCopies;
    }

    public function addProfessorCopy(StudentCopy $professorCopy): self
    {
        if (!$this->professorCopies->contains($professorCopy)) {
            $this->professorCopies->add($professorCopy);
            $professorCopy->setStudent($this);
        }

        return $this;
    }

    public function removeProfessorCopy(StudentCopy $professorCopy): self
    {
        if ($this->professorCopies->removeElement($professorCopy)) {
            // set the owning side to null (unless already changed)
            if ($professorCopy->getStudent() === $this) {
                $professorCopy->setStudent(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * @return Collection<int, Quiz>
     */
    public function getQuizzes(): Collection
    {
        return $this->quizzes;
    }

    public function addQuiz(Quiz $quiz): self
    {
        if (!$this->quizzes->contains($quiz)) {
            $this->quizzes->add($quiz);
            $quiz->setAuthor($this);
        }

        return $this;
    }

    public function removeQuiz(Quiz $quiz): self
    {
        if ($this->quizzes->removeElement($quiz)) {
            // set the owning side to null (unless already changed)
            if ($quiz->getAuthor() === $this) {
                $quiz->setAuthor(null);
            }
        }

        return $this;
    }
}
