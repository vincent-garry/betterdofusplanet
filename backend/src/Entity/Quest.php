<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\QuestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: QuestRepository::class)]
class Quest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['quest:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['quest:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['quest:read'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['quest:read'])]
    private ?int $level = null;

    /**
     * @var Collection<int, DofusQuest>
     */
    #[ORM\OneToMany(targetEntity: DofusQuest::class, mappedBy: 'quest')]
    #[Groups(['quest:read'])]
    private Collection $dofusQuests;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'quests')]
    #[Groups(['quest:read'])]
    private Collection $prerequisite;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'prerequisite')]
    #[Groups(['quest:read'])]
    private Collection $quests;

    /**
     * @var Collection<int, UserQuest>
     */
    #[ORM\OneToMany(targetEntity: UserQuest::class, mappedBy: 'quest')]
    #[Groups(['quest:read'])]
    private Collection $userQuests;

    /**
     * @var Collection<int, QuestReward>
     */
    #[ORM\OneToMany(targetEntity: QuestReward::class, mappedBy: 'quest')]
    private Collection $questRewards;

    /**
     * @var Collection<int, QuestStep>
     */
    #[ORM\OneToMany(targetEntity: QuestStep::class, mappedBy: 'quest')]
    private Collection $steps;



    public function __construct()
    {
        $this->dofusQuests = new ArrayCollection();
        $this->prerequisite = new ArrayCollection();
        $this->quests = new ArrayCollection();
        $this->userQuests = new ArrayCollection();
        $this->questRewards = new ArrayCollection();
        $this->steps = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): static
    {
        $this->level = $level;

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
            $dofusQuest->setQuest($this);
        }

        return $this;
    }

    public function removeDofusQuest(DofusQuest $dofusQuest): static
    {
        if ($this->dofusQuests->removeElement($dofusQuest)) {
            // set the owning side to null (unless already changed)
            if ($dofusQuest->getQuest() === $this) {
                $dofusQuest->setQuest(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getPrerequisite(): Collection
    {
        return $this->prerequisite;
    }

    public function addPrerequisite(self $prerequisite): static
    {
        if (!$this->prerequisite->contains($prerequisite)) {
            $this->prerequisite->add($prerequisite);
        }

        return $this;
    }

    public function removePrerequisite(self $prerequisite): static
    {
        $this->prerequisite->removeElement($prerequisite);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getQuests(): Collection
    {
        return $this->quests;
    }

    public function addQuest(self $quest): static
    {
        if (!$this->quests->contains($quest)) {
            $this->quests->add($quest);
            $quest->addPrerequisite($this);
        }

        return $this;
    }

    public function removeQuest(self $quest): static
    {
        if ($this->quests->removeElement($quest)) {
            $quest->removePrerequisite($this);
        }

        return $this;
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
            $userQuest->setQuest($this);
        }

        return $this;
    }

    public function removeUserQuest(UserQuest $userQuest): static
    {
        if ($this->userQuests->removeElement($userQuest)) {
            // set the owning side to null (unless already changed)
            if ($userQuest->getQuest() === $this) {
                $userQuest->setQuest(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, QuestReward>
     */
    public function getQuestRewards(): Collection
    {
        return $this->questRewards;
    }

    public function addQuestReward(QuestReward $questReward): static
    {
        if (!$this->questRewards->contains($questReward)) {
            $this->questRewards->add($questReward);
            $questReward->setQuest($this);
        }

        return $this;
    }

    public function removeQuestReward(QuestReward $questReward): static
    {
        if ($this->questRewards->removeElement($questReward)) {
            // set the owning side to null (unless already changed)
            if ($questReward->getQuest() === $this) {
                $questReward->setQuest(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, QuestStep>
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function addStep(QuestStep $step): static
    {
        if (!$this->steps->contains($step)) {
            $this->steps->add($step);
            $step->setQuest($this);
        }

        return $this;
    }

    public function removeStep(QuestStep $step): static
    {
        if ($this->steps->removeElement($step)) {
            // set the owning side to null (unless already changed)
            if ($step->getQuest() === $this) {
                $step->setQuest(null);
            }
        }

        return $this;
    }
}
