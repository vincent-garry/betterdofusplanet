<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserQuest;
use App\Entity\UserQuestStep;
use App\Entity\Quest;
use App\Entity\QuestStep;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/user/quest-step')]
class UserQuestStepController extends AbstractController
{
    #[Route('/validate', name: 'toggle_quest_step', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggleQuestStep(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        if (!isset($data['step_id'])) {
            return $this->json(['error' => 'step_id requis'], 400);
        }

        $step = $em->getRepository(QuestStep::class)->find($data['step_id']);

        if (!$step) {
            return $this->json(['error' => 'Étape de quête non trouvée'], 404);
        }

        // Vérifier si l'étape est déjà validée
        $existing = $em->getRepository(UserQuestStep::class)->findOneBy(['user' => $user, 'step' => $step]);

        if ($existing) {
            // Suppression de l'étape validée (dévalidation)
            $em->remove($existing);
            $em->flush();

            // Vérifier si la quête associée doit être dévalidée
            $quest = $step->getQuest();
            $totalSteps = count($quest->getSteps());
            $allSteps = $em->getRepository(QuestStep::class)->findBy(['quest' => $quest]);
            $validatedSteps = $em->getRepository(UserQuestStep::class)->findBy([
                'user' => $user,
                'step' => array_map(fn($step) => $step->getId(), $allSteps)
            ]);

            if (count($validatedSteps) === count($allSteps)) {
                $existingQuest = $em->getRepository(UserQuest::class)->findOneBy(['user' => $user, 'quest' => $quest]);

                if (!$existingQuest) {
                    $userQuest = new UserQuest();
                    $userQuest->setUser($user);
                    $userQuest->setQuest($quest);
                    $em->persist($userQuest);
                    $em->flush();
                }
            } else {
                // 🚀 DEBUG: Vérification de la suppression des quêtes
                $existingQuest = $em->getRepository(UserQuest::class)->findOneBy(['user' => $user, 'quest' => $quest]);

                if ($existingQuest) {
                    $em->remove($existingQuest);
                    $em->flush();
                }
            }

            return $this->json(['message' => 'Étape dévalidée'], 200);
        }

        // Validation si elle ne l'est pas encore
        $userQuestStep = new UserQuestStep();
        $userQuestStep->setUser($user);
        $userQuestStep->setStep($step);
        $em->persist($userQuestStep);
        $em->flush();

        // Vérifier si toutes les étapes de la quête sont validées pour marquer la quête comme terminée
        $quest = $step->getQuest();
        $totalSteps = count($quest->getSteps());
        $allSteps = $em->getRepository(QuestStep::class)->findBy(['quest' => $quest]);
        $validatedSteps = $em->getRepository(UserQuestStep::class)->findBy([
            'user' => $user,
            'step' => array_map(fn($step) => $step->getId(), $allSteps)
        ]);

        if (count($validatedSteps) === count($allSteps)) {
            $existingQuest = $em->getRepository(UserQuest::class)->findOneBy(['user' => $user, 'quest' => $quest]);

            if (!$existingQuest) {
                $userQuest = new UserQuest();
                $userQuest->setUser($user);
                $userQuest->setQuest($quest);
                $em->persist($userQuest);
                $em->flush();
            }
        }

        return $this->json(['message' => 'Étape validée'], 201);
    }

    #[Route('', name: 'get_validated_steps', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getValidatedSteps(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $validatedSteps = $em->getRepository(UserQuestStep::class)->findBy(['user' => $user]);

        $data = array_map(fn($step) => [
            'id' => $step->getStep()->getId(),
            'title' => $step->getStep()->getTitle(),
            'quest_id' => $step->getStep()->getQuest()->getId(),
        ], $validatedSteps);

        return $this->json($data);
    }
}
