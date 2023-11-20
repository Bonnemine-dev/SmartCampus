<?php

namespace App\Entity;

use App\Repository\SARepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SARepository::class)]
class SA
{
    // Identifiant unique généré automatiquement pour le SA.
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Numéro du SA.
    #[ORM\Column]
    private ?int $numero = null;

    // Nom du SA, limité à 50 caractères.
    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    // État du SA, limité à 25 caractères.
    #[ORM\Column(length: 25)]
    private ?string $etat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }
}
