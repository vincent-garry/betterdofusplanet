<?php

namespace App\Controller;

use App\Entity\Dofus;
use App\Entity\DofusQuest;
use App\Entity\Quest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dofus-quests')]
class DofusQuestController extends AbstractController
{
    #[Route('/{dofusId}', name: 'list_quests_by_dofus', methods: ['GET'])]
    public function listByDofus(int $dofusId, EntityManagerInterface $em): JsonResponse
    {
        $dofus = $em->getRepository(Dofus::class)->find($dofusId);
        if (!$dofus) {
            return $this->json(['error' => 'Dofus introuvable'], 404);
        }

        $quests = $em->getRepository(DofusQuest::class)->findBy(['dofus' => $dofus], ['questOrder' => 'ASC']);
        $data = [];

        foreach ($quests as $dq) {
            $quest = $dq->getQuest();
            $data[] = [
                'id' => $quest->getId(),
                'title' => $quest->getTitle(),
                'description' => $quest->getDescription(),
                'order' => $dq->getQuestOrder()
            ];
        }

        return $this->json($data);
    }

    #[Route('', name: 'create_dofus_quest', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['dofus_id'], $data['quest_id'], $data['quest_order'])) {
            return $this->json(['error' => 'Données incomplètes'], 400);
        }

        $dofus = $em->getRepository(Dofus::class)->find($data['dofus_id']);
        $quest = $em->getRepository(Quest::class)->find($data['quest_id']);

        if (!$dofus || !$quest) {
            return $this->json(['error' => 'Dofus ou Quête introuvable'], 404);
        }

        $dofusQuest = new DofusQuest();
        $dofusQuest->setDofus($dofus);
        $dofusQuest->setQuest($quest);
        $dofusQuest->setQuestOrder($data['quest_order']);

        $em->persist($dofusQuest);
        $em->flush();

        return $this->json(['message' => 'Liaison ajoutée avec succès'], 201);
    }
}
