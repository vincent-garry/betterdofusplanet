<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read'])]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, UserQuest>
     */
    #[ORM\OneToMany(targetEntity: UserQuest::class, mappedBy: 'user')]
    private Collection $userQuests;

    /**
     * @var Collection<int, UserQuestStep>
     */
    #[ORM\OneToMany(targetEntity: UserQuestStep::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $userQuestSteps;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $avatar = null;

    public function __construct()
    {
        $this->userQuests = new ArrayCollection();
        $this->userQuestSteps = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, UserQuest>
     */
    public function getUserQuests(): Collection
    {
        return $this->userQuests;
    }

    public function addUserQuest(UserQuest $userQuest): static
    {
        if (!$this->userQuests->contains($userQuest)) {
            $this->userQuests->add($userQuest);
            $userQuest->setUser($this);
        }

        return $this;
    }

    public function removeUserQuest(UserQuest $userQuest): static
    {
        if ($this->userQuests->removeElement($userQuest)) {
            // set the owning side to null (unless already changed)
            if ($userQuest->getUser() === $this) {
                $userQuest->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserQuestStep>
     */
    public function getUserQuestSteps(): Collection
    {
        return $this->userQuestSteps;
    }

    public function addUserQuestStep(UserQuestStep $userQuestStep): static
    {
        if (!$this->userQuestSteps->contains($userQuestStep)) {
            $this->userQuestSteps->add($userQuestStep);
            $userQuestStep->setUser($this);
        }

        return $this;
    }

    public function removeUserQuestStep(UserQuestStep $userQuestStep): static
    {
        if ($this->userQuestSteps->removeElement($userQuestStep)) {
            // set the owning side to null (unless already changed)
            if ($userQuestStep->getUser() === $this) {
                $userQuestStep->setUser(null);
            }
        }

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }
}
