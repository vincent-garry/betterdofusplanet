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
            // Récupération de toutes les relations Quest <-> Dofus (DofusQuest)
            $dofusQuests = $em->getRepository(DofusQuest::class)->findBy(['quest' => $quest], ['questOrder' => 'ASC']); // Tri par ordre croissant

            // Transformation des Dofus en un tableau associatif
            $dofusData = [];
            foreach ($dofusQuests as $dq) {
                if ($dq->getDofus()) {
                    $dofusData[] = [
                        'id' => $dq->getDofus()->getId(),
                        'name' => $dq->getDofus()->getName(),
                        'order' => $dq->getQuestOrder() // ✅ On garde l'ordre ici !
                    ];
                }
            }

            // Récupération des récompenses de la quête
            $questRewards = $em->getRepository(QuestReward::class)->findBy(['quest' => $quest]);
            $rewardsData = array_map(fn($reward) => [
                'name' => $reward->getRewardName(),
                'quantity' => $reward->getQuantity()
            ], $questRewards);

            // Construction de la réponse
            $data[] = [
                'id' => $quest->getId(),
                'title' => $quest->getTitle(),
                'description' => $quest->getDescription(),
                'level' => $quest->getLevel(),
                'dofus' => $dofusData, // ✅ Liste complète des Dofus avec ordre
                'rewards' => $rewardsData,
                'questOrder' => $dofusQuests[0]->getQuestOrder(),
            ];
        }

        return $this->json($data);
    }

    #[Route('', name: 'create_quest', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title'], $data['description'], $data['level'], $data['dofus_ids'], $data['quest_order'], $data['rewards'])) {
            return $this->json(['error' => 'Données manquantes'], 400);
        }

        /*
        $dofus = $em->getRepository(Dofus::class)->find($data['dofus_id']);
        if (!$dofus) {
            return $this->json(['error' => 'Dofus non trouvé'], 404);
        }
        */

        $quest = new Quest();
        $quest->setTitle($data['title']);
        $quest->setDescription($data['description']);
        $quest->setLevel((int) $data['level']);

        foreach($data['dofus_ids'] as $dofusId) {
            $dofus = $em->getRepository(Dofus::class)->find($dofusId);
            if($dofus){
                $dofusQuest = new DofusQuest();
                $dofusQuest->setQuest($quest);
                $dofusQuest->setDofus($dofus);
                $dofusQuest->setQuestOrder($data['quest_order']);
                $em->persist($dofusQuest);
            }
        }

        /* Associer la quête au dofus avec un ordre
        $dofusQuest = new DofusQuest();
        $dofusQuest->setQuest($quest);
        $dofusQuest->setDofus($dofus);
        $dofusQuest->setQuestOrder((int) $data['quest_order']);
        $em->persist($dofusQuest);
        */

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

        if (!isset($data['title'], $data['description'], $data['level'], $data['dofus_ids'], $data['quest_order'], $data['rewards'])) {
            return $this->json(['error' => 'Données manquantes'], 400);
        }

        $quest->setTitle($data['title']);
        $quest->setDescription($data['description']);
        $quest->setLevel((int) $data['level']);

        // Récupération des relations actuelles Dofus <-> Quête
        $existingDofusQuests = $em->getRepository(DofusQuest::class)->findBy(['quest' => $quest]);
        $existingDofusIds = [];
        foreach ($existingDofusQuests as $dq) {
            $existingDofusIds[$dq->getDofus()->getId()] = $dq; // Associe l'ID du Dofus à son objet DofusQuest
        }

        // Liste des nouveaux Dofus à lier
        $newDofusIds = $data['dofus_ids'];

        // Gestion des nouvelles associations et mise à jour des existantes
        foreach ($newDofusIds as $dofusId) {
            if (isset($existingDofusIds[$dofusId])) {
                // Si la relation existe déjà, on met à jour l'ordre de la quête
                $dofusQuest = $existingDofusIds[$dofusId];
                $dofusQuest->setQuestOrder((int) $data['quest_order']);
                unset($existingDofusIds[$dofusId]); // On le retire de la liste des anciennes relations
            } else {
                // Sinon, on ajoute une nouvelle relation
                $dofus = $dofusRepo->find($dofusId);
                if ($dofus) {
                    $dofusQuest = new DofusQuest();
                    $dofusQuest->setQuest($quest);
                    $dofusQuest->setDofus($dofus);
                    $dofusQuest->setQuestOrder((int) $data['quest_order']);
                    $em->persist($dofusQuest);
                }
            }
        }

        // Suppression des relations qui ne sont plus valides
        foreach ($existingDofusIds as $dq) {
            $em->remove($dq);
        }

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

        // Sauvegarde des modifications
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
