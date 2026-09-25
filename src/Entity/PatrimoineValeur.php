<?php

namespace App\Entity;

use App\Repository\PatrimoineValeurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PatrimoineValeurRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_patrimoine_label_mois', columns: ['label', 'mois'])]
class PatrimoineValeur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $label = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)]
    private string $valeur = '0';

    #[ORM\Column(length: 7)] // YYYY-MM
    private ?string $mois = null;

    #[ORM\Column]
    private int $ordre = 0;

    public function getId(): ?int { return $this->id; }

    public function getLabel(): ?string { return $this->label; }
    public function setLabel(string $label): static { $this->label = $label; return $this; }

    public function getValeur(): string { return $this->valeur; }
    public function setValeur(string $valeur): static { $this->valeur = $valeur; return $this; }

    public function getMois(): ?string { return $this->mois; }
    public function setMois(string $mois): static { $this->mois = $mois; return $this; }

    public function getOrdre(): int { return $this->ordre; }
    public function setOrdre(int $ordre): static { $this->ordre = $ordre; return $this; }

    public function toArray(): array
    {
        return [
            'id'     => $this->id,
            'label'  => $this->label,
            'valeur' => (float) $this->valeur,
            'mois'   => $this->mois,
            'ordre'  => $this->ordre,
        ];
    }
}
