<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/budget')]
#[IsGranted('ROLE_USER')]
class BudgetController extends AbstractController
{
    /**
     * Renvoie pour un mois donné : catégories groupées + transactions + totaux.
     * GET /api/budget?mois=2026-10
     */
    #[Route('', name: 'api_budget', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $mois = $request->query->get('mois', (new \DateTime())->format('Y-m'));

        $categories  = $em->getRepository(Categorie::class)->findBy([], ['groupe' => 'ASC', 'ordre' => 'ASC']);
        $transactions = $em->getRepository(Transaction::class)->findBy(['mois' => $mois], ['date' => 'ASC']);

        // Index transactions by categorie id
        $txByCategorie = [];
        foreach ($transactions as $t) {
            $txByCategorie[$t->getCategorie()->getId()][] = $t->toArray();
        }

        $groupes = ['revenus' => [], 'essentiel' => [], 'loisir' => [], 'epargne' => []];
        $totaux  = ['revenus' => 0, 'essentiel' => 0, 'loisir' => 0, 'epargne' => 0];
        $totalPrevu = ['revenus' => 0, 'essentiel' => 0, 'loisir' => 0, 'epargne' => 0];

        foreach ($categories as $cat) {
            $g = $cat->getGroupe();
            $catTx = $txByCategorie[$cat->getId()] ?? [];
            $realise = array_sum(array_column($catTx, 'montant'));
            $prevu = (float)($cat->getMontantPrevu() ?? 0);

            $groupes[$g][] = [
                ...$cat->toArray(),
                'realise'      => $realise,
                'transactions' => $catTx,
            ];

            $totaux[$g]     += $realise;
            $totalPrevu[$g] += $prevu;
        }

        return $this->json([
            'mois'       => $mois,
            'groupes'    => $groupes,
            'totaux'     => $totaux,
            'totalPrevu' => $totalPrevu,
        ]);
    }
}
