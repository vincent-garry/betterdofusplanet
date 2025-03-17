<?php

namespace App\Entity;

use App\Repository\UserQuestStepRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserQuestStepRepository::class)]
#[ORM\UniqueConstraint(columns: ["user_id", "step_id"])]
class UserQuestStep
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userQuestSteps')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userQuestSteps')]
    #[ORM\JoinColumn(nullable: false)]
    private ?QuestStep $step = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStep(): ?QuestStep
    {
        return $this->step;
    }

    public function setStep(?QuestStep $step): static
    {
        $this->step = $step;

        return $this;
    }
}
