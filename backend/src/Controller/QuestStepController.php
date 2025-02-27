<?php

namespace App\Controller;

use App\Entity\Quest;
use App\Entity\QuestStep;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/quest-steps')]
class QuestStepController extends AbstractController
{
    #[Route('', name: 'list_quest_steps', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $steps = $em->getRepository(QuestStep::class)->findBy([], ['stepOrder' => 'ASC']);
        $data = [];

        foreach ($steps as $step) {
            $quest = $step->getQuest(); // Récupération de la quête liée

            $data[] = [
                'id' => $step->getId(),
                'title' => $step->getTitle(),
                'description' => $step->getDescription(),
                'positionX' => $step->getPositionX(),
                'positionY' => $step->getPositionY(),
                'image' => $step->getImage(),
                'step_order' => $step->getStepOrder(),
                'quest' => $quest ? [
                    'id' => $quest->getId(),
                    'title' => $quest->getTitle()
                ] : null
            ];
        }

        return $this->json($data);
    }

    #[Route('', name: 'create_quest_step', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title'], $data['description'], $data['position'], $data['step_order'], $data['quest_id'])) {
            return $this->json(['error' => 'Données manquantes'], 400);
        }

        // Nettoyer la chaîne et extraire les coordonnées
        $position = str_replace(['[', ']'], '', $data['position']); // Supprime les crochets
        $positions = explode(';', $position); // Sépare les valeurs

        // Assigner positionX et positionY
        $positionX = isset($positions[0]) && is_numeric($positions[0]) ? (int)$positions[0] : 0;
        $positionY = isset($positions[1]) && is_numeric($positions[1]) ? (int)$positions[1] : 0;

        $quest = $em->getRepository(Quest::class)->find($data['quest_id']);
        if (!$quest) {
            return $this->json(['error' => 'Quête non trouvée'], 404);
        }

        $step = new QuestStep();
        $step->setTitle($data['title']);
        $step->setDescription($data['description']);
        $step->setPositionX($positionX);
        $step->setPositionY($positionY);
        $step->setImage($data['image'] ?? null);
        $step->setStepOrder($data['step_order']);
        $step->setQuest($quest);

        $em->persist($step);
        $em->flush();

        return $this->json(['message' => 'Étape créée avec succès'], 201);
    }

    #[Route('/{id}', name: 'update_quest_step', methods: ['PUT'])]
    public function update(Request $request, EntityManagerInterface $em, QuestStep $step): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title'], $data['description'], $data['position'], $data['step_order'], $data['quest_id'])) {
            return $this->json(['error' => 'Données manquantes'], 400);
        }

        $quest = $em->getRepository(Quest::class)->find($data['quest_id']);
        if (!$quest) {
            return $this->json(['error' => 'Quête non trouvée'], 404);
        }

        // Nettoyer la chaîne et extraire les coordonnées
        $position = str_replace(['[', ']'], '', $data['position']); // Supprime les crochets
        $positions = explode(';', $position); // Sépare les valeurs

        // Assigner positionX et positionY
        $positionX = isset($positions[0]) && is_numeric($positions[0]) ? (int)$positions[0] : 0;
        $positionY = isset($positions[1]) && is_numeric($positions[1]) ? (int)$positions[1] : 0;

        $step->setTitle($data['title']);
        $step->setDescription($data['description']);
        $step->setPositionX($positionX);
        $step->setPositionY($positionY);
        $step->setImage($data['image'] ?? null);
        $step->setStepOrder($data['step_order']);
        $step->setQuest($quest);

        $em->flush();

        return $this->json(['message' => 'Étape mise à jour avec succès'], 200);
    }


    #[Route('/{id}', name: 'delete_quest_step', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $em, QuestStep $step): JsonResponse
    {
        $em->remove($step);
        $em->flush();

        return $this->json(['message' => 'Étape supprimée avec succès'], 200);
    }
}
