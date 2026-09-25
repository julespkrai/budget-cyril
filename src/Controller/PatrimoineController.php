<?php

namespace App\Controller;

use App\Entity\PatrimoineValeur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/patrimoine')]
#[IsGranted('ROLE_USER')]
class PatrimoineController extends AbstractController
{
    #[Route('', name: 'api_patrimoine_list', methods: ['GET'])]
    public function list(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $mois = $request->query->get('mois');
        $criteria = $mois ? ['mois' => $mois] : [];
        $valeurs = $em->getRepository(PatrimoineValeur::class)->findBy($criteria, ['ordre' => 'ASC', 'label' => 'ASC']);
        return $this->json(array_map(fn($v) => $v->toArray(), $valeurs));
    }

    #[Route('/upsert', name: 'api_patrimoine_upsert', methods: ['POST'])]
    public function upsert(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['label']) || empty($data['mois'])) {
            return $this->json(['error' => 'label et mois sont requis.'], Response::HTTP_BAD_REQUEST);
        }

        $existing = $em->getRepository(PatrimoineValeur::class)->findOneBy([
            'label' => $data['label'],
            'mois'  => $data['mois'],
        ]);

        if ($existing) {
            $existing->setValeur((string)($data['valeur'] ?? 0));
            $em->flush();
            return $this->json($existing->toArray());
        }

        $v = new PatrimoineValeur();
        $v->setLabel($data['label']);
        $v->setMois($data['mois']);
        $v->setValeur((string)($data['valeur'] ?? 0));
        $v->setOrdre($data['ordre'] ?? 0);

        $em->persist($v);
        $em->flush();

        return $this->json($v->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_patrimoine_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $v = $em->getRepository(PatrimoineValeur::class)->find($id);
        if (!$v) {
            return $this->json(['error' => 'Valeur introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($v);
        $em->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
