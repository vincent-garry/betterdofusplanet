<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\DofusQuestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DofusQuestRepository::class)]
class DofusQuest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dofusQuests')]
    private ?Dofus $dofus = null;

    #[ORM\ManyToOne(inversedBy: 'dofusQuests')]
    private ?Quest $quest = null;

    #[ORM\Column]
    private ?int $questOrder = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDofus(): ?Dofus
    {
        return $this->dofus;
    }

    public function setDofus(?Dofus $dofus): static
    {
        $this->dofus = $dofus;

        return $this;
    }

    public function getQuest(): ?Quest
    {
        return $this->quest;
    }

    public function setQuest(?Quest $quest): static
    {
        $this->quest = $quest;

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
}
