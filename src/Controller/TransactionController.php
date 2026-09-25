<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/transactions')]
#[IsGranted('ROLE_USER')]
class TransactionController extends AbstractController
{
    #[Route('', name: 'api_transactions_list', methods: ['GET'])]
    public function list(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $mois = $request->query->get('mois');
        $criteria = $mois ? ['mois' => $mois] : [];
        $transactions = $em->getRepository(Transaction::class)->findBy($criteria, ['date' => 'DESC']);
        return $this->json(array_map(fn($t) => $t->toArray(), $transactions));
    }

    #[Route('', name: 'api_transactions_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['categorieId']) || !isset($data['montant']) || empty($data['date'])) {
            return $this->json(['error' => 'categorieId, montant et date sont requis.'], Response::HTTP_BAD_REQUEST);
        }

        $cat = $em->getRepository(Categorie::class)->find($data['categorieId']);
        if (!$cat) {
            return $this->json(['error' => 'Catégorie introuvable.'], Response::HTTP_NOT_FOUND);
        }

        try {
            $date = new \DateTime($data['date']);
        } catch (\Exception) {
            return $this->json(['error' => 'Format de date invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $t = new Transaction();
        $t->setCategorie($cat);
        $t->setMontant((string)$data['montant']);
        $t->setDescription($data['description'] ?? null);
        $t->setDate($date);
        $t->setMois($data['mois'] ?? $date->format('Y-m'));

        $em->persist($t);
        $em->flush();

        return $this->json($t->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_transactions_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $t = $em->getRepository(Transaction::class)->find($id);
        if (!$t) {
            return $this->json(['error' => 'Transaction introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!empty($data['categorieId'])) {
            $cat = $em->getRepository(Categorie::class)->find($data['categorieId']);
            if (!$cat) return $this->json(['error' => 'Catégorie introuvable.'], Response::HTTP_NOT_FOUND);
            $t->setCategorie($cat);
        }
        if (isset($data['montant'])) $t->setMontant((string)$data['montant']);
        if (array_key_exists('description', $data)) $t->setDescription($data['description']);
        if (!empty($data['date'])) {
            try { $t->setDate(new \DateTime($data['date'])); }
            catch (\Exception) { return $this->json(['error' => 'Format de date invalide.'], Response::HTTP_BAD_REQUEST); }
        }
        if (!empty($data['mois'])) $t->setMois($data['mois']);

        $em->flush();
        return $this->json($t->toArray());
    }

    #[Route('/{id}', name: 'api_transactions_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $t = $em->getRepository(Transaction::class)->find($id);
        if (!$t) {
            return $this->json(['error' => 'Transaction introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($t);
        $em->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
