<?php

namespace App\Controller;

use App\Entity\Dofus;
use App\Entity\DofusQuest;
use App\Entity\QuestReward;
use App\Entity\QuestStep;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dofus')]
final class DofusController extends AbstractController
{
    #[Route('', name: 'get_dofuses', methods: ['GET'])]
    public function getDofuses(EntityManagerInterface $em): JsonResponse
    {
        $dofuses = $em->getRepository(Dofus::class)->findAll();

        $data = array_map(function ($dofus) use ($em) {
            $questCount = $em->getRepository(DofusQuest::class)->count(['dofus' => $dofus]);

            return [
                'id' => $dofus->getId(),
                'name' => $dofus->getName(),
                'description' => $dofus->getDescription(),
                'image' => $dofus->getImage(),
                'level' => $dofus->getLevel(),
                'type' => $dofus->getType(),
                'icon' => $dofus->getIcon(),
                'achievementCount' => $dofus->getAchievementCount(),
                'questCount' => $questCount
            ];
        }, $dofuses);

        return $this->json($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['description']) || !isset($data['image']) || !isset($data['level'])) {
            return $this->json(['error' => 'Données invalides'], 400);
        }

        $dofus = new Dofus();
        $dofus->setName($data['name']);
        $dofus->setDescription($data['description']);
        $dofus->setImage($data['image']);
        $dofus->setLevel($data['level']);
        $dofus->setType($data['type']);
        $dofus->setAchievementCount($data['achievementCount']);
        $dofus->setIcon($data['icon']);

        $em->persist($dofus);
        $em->flush();

        return $this->json(['message' => 'Dofus créé avec succès'], 201);
    }


    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Dofus $dofus, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $dofus->setName($data['name']);
        $dofus->setDescription($data['description']);
        $dofus->setImage($data['image']);
        $dofus->setLevel($data['level']);
        $dofus->setType($data['type']);
        $dofus->setAchievementCount($data['achievementCount']);

        $em->flush();

        return $this->json(['message' => 'Dofus mis à jour !']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Dofus $dofus, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($dofus);
        $em->flush();

        return $this->json(['message' => 'Dofus supprimé']);
    }

    #[Route('/{id}', name: 'get_dofus', methods: ['GET'])]
    public function getDofus(int $id, EntityManagerInterface $em): JsonResponse
    {
        $dofus = $em->getRepository(Dofus::class)->find($id);

        if (!$dofus) {
            return $this->json(['error' => 'Dofus non trouvé'], 404);
        }

        // Récupérer le nombre de quêtes liées au Dofus
        $questCount = $em->getRepository(DofusQuest::class)->count(['dofus' => $dofus]);

        return $this->json([
            'id' => $dofus->getId(),
            'name' => $dofus->getName(),
            'image' => $dofus->getImage(),
            'icon' => $dofus->getIcon(),
            'achievementCount' => $dofus->getAchievementCount(),
            'questCount' => $questCount, // Ajout du calcul dynamique
        ]);
    }


    #[Route('/{id}/quests', name: 'dofus_quests', methods: ['GET'])]
    public function getDofusQuests(int $id, EntityManagerInterface $em): JsonResponse
    {
        $dofus = $em->getRepository(Dofus::class)->find($id);

        if (!$dofus) {
            return $this->json(['error' => "Dofus ID $id non trouvé"], 404);
        }// ✅ Ajoute ceci pour voir si l'objet est bien récupéré
        $dofusQuests = $em->getRepository(DofusQuest::class)->findBy(['dofus' => $dofus], ['questOrder' => 'ASC']);

        if (!$dofusQuests) {
            return $this->json(['error' => 'Aucune quête trouvée pour ce Dofus'], 404);
        }

        $questsData = [];

        foreach ($dofusQuests as $dofusQuest) {
            $quest = $dofusQuest->getQuest();
            $steps = $em->getRepository(QuestStep::class)->findBy(['quest' => $quest], ['stepOrder' => 'ASC']);
            $questRewards = $em->getRepository(QuestReward::class)->findBy(['quest' => $quest]);

            $rewardsData = [];
            foreach ($questRewards as $reward) {
                $rewardsData[] = [
                    'name' => $reward->getRewardName(),
                    'quantity' => $reward->getQuantity()
                ];
            }

            $questsData[] = [
                'id' => $quest->getId(),
                'title' => $quest->getTitle(),
                'steps' => array_map(fn($step) => [
                    'id' => $step->getId(),
                    'title' => $step->getTitle(),
                    'description' => $step->getDescription(),
                    'positionX' => $step->getPositionX(),
                    'positionY' => $step->getPositionY(),
                    'image' => $step->getImage(),
                ], $steps,),
                'rewards' => $rewardsData
            ];
        }

        return $this->json($questsData);
    }

}
