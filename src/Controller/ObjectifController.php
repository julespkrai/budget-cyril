<?php

namespace App\Controller;

use App\Entity\Objectif;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/objectifs')]
#[IsGranted('ROLE_USER')]
class ObjectifController extends AbstractController
{
    #[Route('', name: 'api_objectifs_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $objectifs = $em->getRepository(Objectif::class)->findBy([], ['ordre' => 'ASC']);
        return $this->json(array_map(fn($o) => $o->toArray(), $objectifs));
    }

    #[Route('', name: 'api_objectifs_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['label'])) {
            return $this->json(['error' => 'label est requis.'], Response::HTTP_BAD_REQUEST);
        }

        $o = new Objectif();
        $o->setLabel($data['label']);
        $o->setMontantActuel(isset($data['montantActuel']) ? (string)$data['montantActuel'] : '0');
        $o->setMontantCible(isset($data['montantCible']) ? (string)$data['montantCible'] : '0');
        $o->setOrdre($data['ordre'] ?? 0);

        $em->persist($o);
        $em->flush();

        return $this->json($o->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_objectifs_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $o = $em->getRepository(Objectif::class)->find($id);
        if (!$o) {
            return $this->json(['error' => 'Objectif introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['label'])) $o->setLabel($data['label']);
        if (isset($data['montantActuel'])) $o->setMontantActuel((string)$data['montantActuel']);
        if (isset($data['montantCible'])) $o->setMontantCible((string)$data['montantCible']);
        if (isset($data['ordre'])) $o->setOrdre((int)$data['ordre']);

        $em->flush();
        return $this->json($o->toArray());
    }

    #[Route('/{id}', name: 'api_objectifs_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $o = $em->getRepository(Objectif::class)->find($id);
        if (!$o) {
            return $this->json(['error' => 'Objectif introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($o);
        $em->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
