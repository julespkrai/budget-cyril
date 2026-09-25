<?php

namespace App\Controller;

use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/categories')]
#[IsGranted('ROLE_USER')]
class CategorieController extends AbstractController
{
    #[Route('', name: 'api_categories_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $categories = $em->getRepository(Categorie::class)->findBy([], ['groupe' => 'ASC', 'ordre' => 'ASC']);
        return $this->json(array_map(fn($c) => $c->toArray(), $categories));
    }

    #[Route('', name: 'api_categories_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['label']) || empty($data['groupe'])) {
            return $this->json(['error' => 'label et groupe sont requis.'], Response::HTTP_BAD_REQUEST);
        }

        $cat = new Categorie();
        $cat->setLabel($data['label']);
        $cat->setGroupe($data['groupe']);
        $cat->setMontantPrevu(isset($data['montantPrevu']) ? (string)$data['montantPrevu'] : null);
        $cat->setOrdre($data['ordre'] ?? 0);

        $em->persist($cat);
        $em->flush();

        return $this->json($cat->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_categories_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $cat = $em->getRepository(Categorie::class)->find($id);
        if (!$cat) {
            return $this->json(['error' => 'Catégorie introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['label'])) $cat->setLabel($data['label']);
        if (isset($data['groupe'])) $cat->setGroupe($data['groupe']);
        if (array_key_exists('montantPrevu', $data)) $cat->setMontantPrevu($data['montantPrevu'] !== null ? (string)$data['montantPrevu'] : null);
        if (isset($data['ordre'])) $cat->setOrdre((int)$data['ordre']);

        $em->flush();
        return $this->json($cat->toArray());
    }

    #[Route('/{id}', name: 'api_categories_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $cat = $em->getRepository(Categorie::class)->find($id);
        if (!$cat) {
            return $this->json(['error' => 'Catégorie introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($cat);
        $em->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
