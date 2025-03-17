<?php

namespace App\Controller;

use App\Entity\Dofus;
use App\Entity\Quest;
use App\Entity\QuestStep;
use App\Entity\UserQuest;
use App\Entity\UserQuestStep;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
            'pseudo' => $user->getPseudo(),
            'avatar' => $user->getAvatar() ? "http://127.0.0.1:8000/".$user->getAvatar() : 'https://media.sketchfab.com/models/d52a4a18836041ad9de2c521bb7b59fd/thumbnails/ad60671f4f2b43739b7e1b90afb8b4f7/88b98a6714c844698fe609bab4b29d5f.jpeg',
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

    #[Route('/api/user/quest', name: 'get_validated_quests', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getValidatedQuests(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur est bien récupéré
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        // Récupération des quêtes validées
        $validatedQuests = $em->getRepository(UserQuest::class)->findBy(['user' => $user]);

        // Vérifier si la requête retourne bien un résultat
        if (!$validatedQuests) {
            return $this->json([]);
        }

        $data = array_map(fn($userQuest) => [
            'id' => $userQuest->getQuest()->getId(),
            'title' => $userQuest->getQuest()->getTitle(),
        ], $validatedQuests);

        return $this->json($data);
    }

    #[Route('/api/user/quest/validate', name: 'validate_quest', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function validateQuest(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        if (!isset($data['quest_id'])) {
            return $this->json(['error' => 'quest_id requis'], 400);
        }

        $quest = $em->getRepository(Quest::class)->find($data['quest_id']);

        if (!$quest) {
            return $this->json(['error' => 'Quête non trouvée'], 404);
        }

        // Vérifier si la quête est déjà validée
        $existingQuest = $em->getRepository(UserQuest::class)->findOneBy(['user' => $user, 'quest' => $quest]);

        if ($existingQuest) {
            // Suppression de la validation (dévalider la quête)
            $steps = $em->getRepository(UserQuestStep::class)->findBy(['user' => $user, 'step' => $quest->getSteps()]);
            foreach ($steps as $step) {
                $em->remove($step);
            }
            $em->remove($existingQuest);
            $em->flush();

            return $this->json(['message' => 'Quête dévalidée'], 200);
        }

        // Validation de la quête et de toutes ses étapes
        $userQuest = new UserQuest();
        $userQuest->setUser($user);
        $userQuest->setQuest($quest);
        $em->persist($userQuest);

        foreach ($quest->getSteps() as $step) {
            $existingStep = $em->getRepository(UserQuestStep::class)->findOneBy(['user' => $user, 'step' => $step]);
            if (!$existingStep) {
                $userQuestStep = new UserQuestStep();
                $userQuestStep->setUser($user);
                $userQuestStep->setStep($step);
                $em->persist($userQuestStep);
            }
        }

        $em->flush();

        return $this->json(['message' => 'Quête validée avec succès'], 201);
    }

    #[Route('/api/user/quest/unvalidate', name: 'unvalidate_quest', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function unvalidateQuest(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        if (!isset($data['quest_id'])) {
            return $this->json(['error' => 'quest_id requis'], 400);
        }

        $quest = $em->getRepository(Quest::class)->find($data['quest_id']);

        if (!$quest) {
            return $this->json(['error' => 'Quête non trouvée'], 404);
        }

        // Vérifier si la quête est déjà validée
        $existingQuest = $em->getRepository(UserQuest::class)->findOneBy(['user' => $user, 'quest' => $quest]);

        if (!$existingQuest) {
            return $this->json(['error' => 'Quête non validée'], 400);
        }

        // Suppression de la validation de la quête
        $em->remove($existingQuest);
        $em->flush();

        return $this->json(['message' => 'Quête dévalidée'], 200);
    }

    /* GESTION UTILISATEUR */
    #[Route('/api/user', name: 'get_user', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getUserProfile(Security $security, SerializerInterface $serializer): JsonResponse {
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(["message" => "Utilisateur non connecté"], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Sérialiser avec gestion des références circulaires
        $userData = $serializer->serialize($user, 'json', ['groups' => 'user:read']);

        return new JsonResponse($userData, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/user/update', name: 'update_user', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function updateUser(Request $request, EntityManagerInterface $em, Security $security, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(["message" => "Utilisateur non connecté"], 401);
        }

        $data = json_decode($request->getContent(), true);

        $user->setPseudo($data['pseudo']);
        $password = $data['newPassword'];

        if($data['newPassword'] && $data['newPassword'] == $data['confirmPassword']) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['newPassword']));
        }

        $em->persist($user);
        $em->flush();

        return $this->json(["message" => $password]);
    }

    #[Route('/api/user/avatar', name: 'upload_avatar', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function uploadAvatar(Request $request, EntityManagerInterface $em, Security $security): JsonResponse
    {
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(["message" => "Utilisateur non connecté"], 401);
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('avatar');

        if (!$file) {
            return new JsonResponse(["message" => "Aucun fichier reçu."], 400);
        }

        // Vérifier si le fichier est lisible
        if (!$file->isValid()) {
            return new JsonResponse(["message" => "Fichier invalide ou corrompu."], 400);
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '.' . $file->guessExtension();
        try {
            $file->move($uploadDir, $fileName);
            $user->setAvatar('/uploads/avatars/' . $fileName);
            $em->persist($user);
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(["message" => "Erreur lors de l'upload : " . $e->getMessage()], 500);
        }

        return $this->json(["message" => "Avatar mis à jour", "avatar" => "http://localhost:8000".$user->getAvatar()]);
    }

    #[Route('/api/user/delete', name: 'delete_account', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteAccount(EntityManagerInterface $em, Security $security): JsonResponse
    {
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(["message" => "Utilisateur non connecté"], 401);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(["message" => "Compte supprimé avec succès."]);
    }

    #[Route('/api/register', name: 'register_user', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Vérification des données envoyées
        if (empty($data['username']) || empty($data['password']) || empty($data['pseudo'])) {
            return new JsonResponse(["error" => "Tous les champs sont requis"], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $em->getRepository(User::class)->findOneBy(['username' => $data['username']]);
        if ($existingUser) {
            return new JsonResponse(["error" => "Ce nom d'utilisateur est déjà pris"], Response::HTTP_CONFLICT);
        }

        // Création d'un nouvel utilisateur
        $user = new User();
        $user->setUsername($data['username']);
        $user->setPseudo($data['pseudo']);
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);
        $user->setAvatar("https://dofusplanet.fr/img/illustrations/gods/ecaflip.png");

        // Validation des données avec Symfony Validator
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse(["error" => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        // Sauvegarde en BDD
        $em->persist($user);
        $em->flush();

        return new JsonResponse(["message" => "Utilisateur créé avec succès"], Response::HTTP_CREATED);
    }

    #[Route('/api/logout', name: 'logout_user', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return new JsonResponse(["message" => "Déconnexion réussie"], 200);
    }
}
