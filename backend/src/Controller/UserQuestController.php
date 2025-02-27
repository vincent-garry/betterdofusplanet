<?php

namespace App\Controller;

use App\Entity\UserQuest;
use App\Form\UserQuestType;
use App\Repository\UserQuestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user/quest')]
final class UserQuestController extends AbstractController
{
    #[Route(name: 'app_user_quest_index', methods: ['GET'])]
    public function index(UserQuestRepository $userQuestRepository): Response
    {
        return $this->render('user_quest/index.html.twig', [
            'user_quests' => $userQuestRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_quest_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userQuest = new UserQuest();
        $form = $this->createForm(UserQuestType::class, $userQuest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($userQuest);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_quest_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user_quest/new.html.twig', [
            'user_quest' => $userQuest,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_quest_show', methods: ['GET'])]
    public function show(UserQuest $userQuest): Response
    {
        return $this->render('user_quest/show.html.twig', [
            'user_quest' => $userQuest,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_quest_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserQuest $userQuest, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserQuestType::class, $userQuest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_quest_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user_quest/edit.html.twig', [
            'user_quest' => $userQuest,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_quest_delete', methods: ['POST'])]
    public function delete(Request $request, UserQuest $userQuest, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$userQuest->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($userQuest);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_quest_index', [], Response::HTTP_SEE_OTHER);
    }
}
