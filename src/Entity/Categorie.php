<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $label = null;

    #[ORM\Column(length: 20)]
    private ?string $groupe = null; // revenus | essentiel | loisir | epargne

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $montantPrevu = null;

    #[ORM\Column]
    private int $ordre = 0;

    public function getId(): ?int { return $this->id; }

    public function getLabel(): ?string { return $this->label; }
    public function setLabel(string $label): static { $this->label = $label; return $this; }

    public function getGroupe(): ?string { return $this->groupe; }
    public function setGroupe(string $groupe): static { $this->groupe = $groupe; return $this; }

    public function getMontantPrevu(): ?string { return $this->montantPrevu; }
    public function setMontantPrevu(?string $montantPrevu): static { $this->montantPrevu = $montantPrevu; return $this; }

    public function getOrdre(): int { return $this->ordre; }
    public function setOrdre(int $ordre): static { $this->ordre = $ordre; return $this; }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'label'        => $this->label,
            'groupe'       => $this->groupe,
            'montantPrevu' => $this->montantPrevu ? (float) $this->montantPrevu : 0,
            'ordre'        => $this->ordre,
        ];
    }
}
