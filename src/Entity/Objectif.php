<?php

namespace App\Entity;

use App\Repository\ObjectifRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ObjectifRepository::class)]
class Objectif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $label = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $montantActuel = '0';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $montantCible = '0';

    #[ORM\Column]
    private int $ordre = 0;

    public function getId(): ?int { return $this->id; }

    public function getLabel(): ?string { return $this->label; }
    public function setLabel(string $label): static { $this->label = $label; return $this; }

    public function getMontantActuel(): string { return $this->montantActuel; }
    public function setMontantActuel(string $montantActuel): static { $this->montantActuel = $montantActuel; return $this; }

    public function getMontantCible(): string { return $this->montantCible; }
    public function setMontantCible(string $montantCible): static { $this->montantCible = $montantCible; return $this; }

    public function getOrdre(): int { return $this->ordre; }
    public function setOrdre(int $ordre): static { $this->ordre = $ordre; return $this; }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'label'         => $this->label,
            'montantActuel' => (float) $this->montantActuel,
            'montantCible'  => (float) $this->montantCible,
            'ordre'         => $this->ordre,
        ];
    }
}
