<?php

namespace App\Entity;

use App\Repository\QuestRewardRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestRewardRepository::class)]
class QuestReward
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $rewardName = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\ManyToOne(inversedBy: 'questRewards')]
    private ?Quest $quest = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRewardName(): ?string
    {
        return $this->rewardName;
    }

    public function setRewardName(string $rewardName): static
    {
        $this->rewardName = $rewardName;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

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
}
