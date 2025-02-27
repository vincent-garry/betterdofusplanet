<?php

namespace App\Controller;

use App\Entity\Dofus;
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

        $data = array_map(function ($dofus) {
            return [
                'id' => $dofus->getId(),
                'name' => $dofus->getName(),
                'description' => $dofus->getDescription(),
                'image' => $dofus->getImage(),
                'level' => $dofus->getLevel(),
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

        $em->flush();

        return $this->json(['message' => 'Dofus mis à jour']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Dofus $dofus, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($dofus);
        $em->flush();

        return $this->json(['message' => 'Dofus supprimé']);
    }
}
