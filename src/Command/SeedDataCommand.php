<?php

namespace App\Command;

use App\Entity\Categorie;
use App\Entity\Objectif;
use App\Entity\PatrimoineValeur;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed-data', description: 'Seed initial data for Cyril')]
class SeedDataCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Check if already seeded
        $existing = $this->em->getRepository(Categorie::class)->findAll();
        if (count($existing) > 0) {
            $output->writeln('<comment>Data already seeded (' . count($existing) . ' categories found). Skipping.</comment>');
            return Command::SUCCESS;
        }

        $output->writeln('<info>Seeding categories...</info>');

        $cats = [];

        // REVENUS
        $cats[] = $this->cat('Salaire', 'revenus', 4000, 0);
        $cats[] = $this->cat('Autres revenus', 'revenus', 0, 1);

        // ESSENTIEL
        $cats[] = $this->cat('Logement', 'essentiel', 800, 0);
        $cats[] = $this->cat('Alimentation', 'essentiel', 300, 1);
        $cats[] = $this->cat('Orange facture', 'essentiel', 40, 2);
        $cats[] = $this->cat('Adobe Photoshop', 'essentiel', 25, 3);
        $cats[] = $this->cat('Association ADV LAOS', 'essentiel', 25, 4);
        $cats[] = $this->cat('Netflix', 'essentiel', 22, 5);
        $cats[] = $this->cat('Deezer', 'essentiel', 20, 6);
        $cats[] = $this->cat('Xbox Live', 'essentiel', 7, 7);
        $cats[] = $this->cat('Autres / imprévus', 'essentiel', 191, 8);

        // LOISIR
        $cats[] = $this->cat('Sorties & loisirs', 'loisir', 350, 0);

        // EPARGNE
        $cats[] = $this->cat('Sécurité bancaire', 'epargne', 1100, 0);
        $cats[] = $this->cat('Bourse', 'epargne', 350, 1);
        $cats[] = $this->cat('Épargne secondaire', 'epargne', 0, 2);

        foreach ($cats as $cat) {
            $this->em->persist($cat);
        }
        $this->em->flush();
        $output->writeln('<info>' . count($cats) . ' categories created.</info>');

        // Map by label for transactions
        $catMap = [];
        foreach ($cats as $cat) {
            $catMap[$cat->getLabel()] = $cat;
        }

        // TRANSACTIONS octobre 2026
        $output->writeln('<info>Seeding transactions for 2026-10...</info>');
        $mois = '2026-10';
        $txs = [
            ['Salaire', 4000, 'Salaire octobre', '2026-10-01'],
            ['Orange facture', 29, 'Orange', '2026-10-05'],
            ['Orange facture', 35, 'Orange (régularisation)', '2026-10-05'],
            ['Sécurité bancaire', 1100, 'Épargne sécurité', '2026-10-01'],
        ];

        foreach ($txs as [$label, $montant, $desc, $date]) {
            if (!isset($catMap[$label])) continue;
            $tx = new Transaction();
            $tx->setCategorie($catMap[$label]);
            $tx->setMontant($montant);
            $tx->setDescription($desc);
            $tx->setMois($mois);
            $tx->setDate(new \DateTime($date));
            $this->em->persist($tx);
        }
        $this->em->flush();
        $output->writeln('<info>Transactions created.</info>');

        // OBJECTIFS
        $output->writeln('<info>Seeding objectifs...</info>');
        $objectifs = [
            ['Sécurité', 2459, 10000, 0],
            ['Bourse (fin d\'année)', 0, 1000, 1],
            ['Voyage', 0, 0, 2],
            ['Skidive (parachute)', 670, 2100, 3],
            ['Apport immobilier / projet', 0, 0, 4],
        ];

        foreach ($objectifs as [$label, $actuel, $cible, $ordre]) {
            $obj = new Objectif();
            $obj->setLabel($label);
            $obj->setMontantActuel($actuel);
            $obj->setMontantCible($cible);
            $obj->setOrdre($ordre);
            $this->em->persist($obj);
        }
        $this->em->flush();
        $output->writeln('<info>Objectifs created.</info>');

        // PATRIMOINE octobre 2026
        $output->writeln('<info>Seeding patrimoine for 2026-10...</info>');
        $patrimoines = [
            ['Liquidités', 1000, 0],
            ['Bourse', 9300, 1],
            ['Crypto', 6000, 2],
            ['Immobilier', 0, 3],
            ['Skidive (parachute)', 670, 4],
        ];

        foreach ($patrimoines as [$label, $valeur, $ordre]) {
            $p = new PatrimoineValeur();
            $p->setLabel($label);
            $p->setValeur($valeur);
            $p->setMois($mois);
            $p->setOrdre($ordre);
            $this->em->persist($p);
        }
        $this->em->flush();
        $output->writeln('<info>Patrimoine created.</info>');

        $output->writeln('<info>✓ Seed complete!</info>');
        return Command::SUCCESS;
    }

    private function cat(string $label, string $groupe, float $prevu, int $ordre): Categorie
    {
        $c = new Categorie();
        $c->setLabel($label);
        $c->setGroupe($groupe);
        $c->setMontantPrevu($prevu);
        $c->setOrdre($ordre);
        return $c;
    }
}
