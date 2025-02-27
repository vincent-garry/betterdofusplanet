<?php

namespace App\Controller;

use App\Entity\Dofus;
use App\Entity\DofusQuest;
use App\Entity\Quest;
use App\Entity\QuestReward;
use App\Repository\DofusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/quests', name: 'api_quests_')]
class QuestController extends AbstractController
{
    #[Route('', name: 'list_quests', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $quests = $em->getRepository(Quest::class)->findAll();
        $data = [];

        foreach ($quests as $quest) {
            $dofusQuest = $em->getRepository(DofusQuest::class)->findOneBy(['quest' => $quest]);
            $questRewards = $em->getRepository(QuestReward::class)->findBy(['quest' => $quest]);

            $rewardsData = [];
            foreach ($questRewards as $reward) {
                $rewardsData[] = [
                    'name' => $reward->getRewardName(),
                    'quantity' => $reward->getQuantity()
                ];
            }

            $data[] = [
                'id' => $quest->getId(),
                'title' => $quest->getTitle(),
                'description' => $quest->getDescription(),
                'level' => $quest->getLevel(),
                'order' => $dofusQuest ? $dofusQuest->getQuestOrder() : null,
                'dofus' => $dofusQuest ? [
                    'id' => $dofusQuest->getDofus()->getId(),
                    'name' => $dofusQuest->getDofus()->getName()
                ] : null,
                'rewards' => $rewardsData // Correctif ici pour inclure toutes les récompenses
            ];
        }

        return $this->json($data);
    }


    #[Route('', name: 'create_quest', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title'], $data['description'], $data['level'], $data['dofus_id'], $data['quest_order'], $data['rewards'])) {
            return $this->json(['error' => 'Données manquantes'], 400);
        }

        $dofus = $em->getRepository(Dofus::class)->find($data['dofus_id']);
        if (!$dofus) {
            return $this->json(['error' => 'Dofus non trouvé'], 404);
        }

        $quest = new Quest();
        $quest->setTitle($data['title']);
        $quest->setDescription($data['description']);
        $quest->setLevel((int) $data['level']);

        // Associer la quête au dofus avec un ordre
        $dofusQuest = new DofusQuest();
        $dofusQuest->setQuest($quest);
        $dofusQuest->setDofus($dofus);
        $dofusQuest->setQuestOrder((int) $data['quest_order']);
        $em->persist($dofusQuest);

        // Ajouter les récompenses
        foreach ($data['rewards'] as $rewardData) {
            $reward = new QuestReward();
            $reward->setRewardName($rewardData['name']);
            $reward->setQuantity((int) $rewardData['quantity']);
            $reward->setQuest($quest);
            $em->persist($reward);
        }

        $em->persist($quest);
        $em->flush();

        return $this->json(['message' => 'Quête créée avec succès'], 201);
    }


    #[Route('/{id}', name: 'update_quest', methods: ['PUT'])]
    public function update(Request $request, EntityManagerInterface $em, DofusRepository $dofusRepo, Quest $quest): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérification des données
        if (!isset($data['title'], $data['description'], $data['level'], $data['dofus_id'], $data['quest_order'], $data['rewards'])) {
            return $this->json(['error' => 'Données manquantes'], 400);
        }

        // Vérification du Dofus
        $dofus = $dofusRepo->find($data['dofus_id']);
        if (!$dofus) {
            return $this->json(['error' => 'Dofus non trouvé'], 404);
        }

        // Mise à jour de la quête
        $quest->setTitle($data['title']);
        $quest->setDescription($data['description']);
        $quest->setLevel((int) $data['level']);

        // Mise à jour de la relation avec le Dofus
        $dofusQuest = $em->getRepository(DofusQuest::class)->findOneBy(['quest' => $quest]);
        if (!$dofusQuest) {
            $dofusQuest = new DofusQuest();
            $dofusQuest->setQuest($quest);
            $em->persist($dofusQuest);
        }
        $dofusQuest->setDofus($dofus);
        $dofusQuest->setQuestOrder((int) $data['quest_order']);

        // Suppression des anciennes récompenses
        $existingRewards = $em->getRepository(QuestReward::class)->findBy(['quest' => $quest]);
        foreach ($existingRewards as $reward) {
            $em->remove($reward);
        }

        // Ajout des nouvelles récompenses
        foreach ($data['rewards'] as $rewardData) {
            $reward = new QuestReward();
            $reward->setQuest($quest);
            $reward->setRewardName($rewardData['name']);
            $reward->setQuantity((int) $rewardData['quantity']);
            $em->persist($reward);
        }

        $em->flush();

        return $this->json(['message' => 'Quête mise à jour avec succès'], 200);
    }

    #[Route('/{id}', name: 'delete_quest', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $em, Quest $quest): JsonResponse
    {
        // Supprimer les relations dans dofus_quest
        foreach ($quest->getDofusQuests() as $dofusQuest) {
            $em->remove($dofusQuest);
        }

        // Supprimer les récompenses liées
        $questRewards = $em->getRepository(QuestReward::class)->findBy(['quest' => $quest]);
        foreach ($questRewards as $reward) {
            $em->remove($reward);
        }

        // Supprimer la quête elle-même
        $em->remove($quest);
        $em->flush();

        return $this->json(['message' => 'Quête supprimée avec succès'], 200);
    }

}
