<?php

namespace App\Controller;

use App\Entity\Dofus;
use App\Entity\Quest;
use App\Entity\QuestStep;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

final class UserController extends AbstractController
{
    #[Route('/api/me', name: 'app_me', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if(!$user){
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        return $this->json([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/api/user', name: 'app_user', methods: ['GET'])]
    public function users(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        $data[] = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
            ];
        }
        return $this->json($users);
    }

    #[Route('/api/dashboard/stats', name: 'dashboard_stats', methods: ['GET'])]
    public function getStats(EntityManagerInterface $em): JsonResponse {
        return $this->json([
            'totalDofus' => $em->getRepository(Dofus::class)->count([]),
            'totalQuests' => $em->getRepository(Quest::class)->count([]),
            'totalSteps' => $em->getRepository(QuestStep::class)->count([]),
            'totalUsers' => $em->getRepository(User::class)->count([]),
        ]);
    }

    #[Route('/api/dashboard/quests-by-dofus', name: 'dashboard_quests_by_dofus', methods: ['GET'])]
    public function getQuestsByDofus(EntityManagerInterface $em): JsonResponse {
        $dofusRepo = $em->getRepository(Dofus::class);
        $questsRepo = $em->getRepository(Quest::class);

        $dofus = $dofusRepo->findAll();
        $data = [];

        foreach ($dofus as $d) {
            $count = $questsRepo->createQueryBuilder('q')
                ->select('COUNT(q.id)')
                ->join('q.dofusQuests', 'dq')
                ->where('dq.dofus = :dofus')
                ->setParameter('dofus', $d)
                ->getQuery()->getSingleScalarResult();

            $data[] = ['name' => $d->getName(), 'count' => $count];
        }

        return $this->json($data);
    }

    #[Route('/api/dashboard/quests-over-time', name: 'dashboard_quests_over_time', methods: ['GET'])]
    public function getQuestsOverTime(EntityManagerInterface $em): JsonResponse {
        $result = $em->createQuery("
        SELECT DATE(q.createdAt) as date, COUNT(q.id) as count 
        FROM App\Entity\Quest q 
        GROUP BY date 
        ORDER BY date ASC
    ")->getResult();

        return $this->json($result);
    }

    #[Route('/api/dashboard/recent-data', name: 'dashboard_recent_data', methods: ['GET'])]
    public function getRecentData(EntityManagerInterface $em): JsonResponse {
        $recentQuests = $em->getRepository(Quest::class)->findBy([], ['createdAt' => 'DESC'], 5);
        $recentSteps = $em->getRepository(QuestStep::class)->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->json([
            'quests' => array_map(fn($q) => ['id' => $q->getId(), 'title' => $q->getTitle()], $recentQuests),
            'steps' => array_map(fn($s) => ['id' => $s->getId(), 'title' => $s->getTitle()], $recentSteps),
        ]);
    }
}
