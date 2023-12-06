<?php

namespace App\Entity;

use App\Repository\SARepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Config\EtatSA;

#[ORM\Entity(repositoryClass: SARepository::class)]
class SA
{
    // Identifiant unique généré automatiquement pour le SA.
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $id = null;

    // Numéro du SA.
    #[ORM\Column]
    #[Assert\Range(min: 0,max: 9999, notInRangeMessage: 'Les numéro de SA sont choisie aléatoirement entre 0 et 9999')]
    private ?int $numero = null;

    // Nom du SA, limité à 50 caractères.
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\Length(min: 7, max: 7, maxMessage: 'Le nom d\'un SA est de la forme SA-????, et fais donc 7 caractères')]
    private ?string $nom = null;

    #[ORM\Column]
    private ?EtatSA $etat = null;

    #[ORM\Column]
    private ?bool $disponible = null;

    public function getEtat(): ?EtatSA
    {
        return $this->etat;
    }

    public function setEtat(?EtatSA $etat): void
    {
        $this->etat = $etat;
    }

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

    public function isDisponible(): ?bool
    {
        return $this->disponible;
    }

    public function setDisponible(bool $disponible): static
    {
        $this->disponible = $disponible;

        return $this;
    }
}
