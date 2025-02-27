<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\DofusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DofusRepository::class)]
class Dofus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $questOrder = 1;

    /**
     * @var Collection<int, DofusQuest>
     */
    #[ORM\OneToMany(targetEntity: DofusQuest::class, mappedBy: 'dofus')]
    private Collection $dofusQuests;

    #[ORM\Column(nullable: true)]
    private ?int $level = null;

    #[ORM\Column]
    private ?bool $principal = true;

    public function __construct()
    {
        $this->dofusQuests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getQuestOrder(): ?int
    {
        return $this->questOrder;
    }

    public function setQuestOrder(int $questOrder): static
    {
        $this->questOrder = $questOrder;

        return $this;
    }

    /**
     * @return Collection<int, DofusQuest>
     */
    public function getDofusQuests(): Collection
    {
        return $this->dofusQuests;
    }

    public function addDofusQuest(DofusQuest $dofusQuest): static
    {
        if (!$this->dofusQuests->contains($dofusQuest)) {
            $this->dofusQuests->add($dofusQuest);
            $dofusQuest->setDofus($this);
        }

        return $this;
    }

    public function removeDofusQuest(DofusQuest $dofusQuest): static
    {
        if ($this->dofusQuests->removeElement($dofusQuest)) {
            // set the owning side to null (unless already changed)
            if ($dofusQuest->getDofus() === $this) {
                $dofusQuest->setDofus(null);
            }
        }

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function isPrincipal(): ?bool
    {
        return $this->principal;
    }

    public function setPrincipal(bool $principal): static
    {
        $this->principal = $principal;

        return $this;
    }
}
