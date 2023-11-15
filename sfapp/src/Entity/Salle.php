<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?int $etage = null;

    #[ORM\Column]
    private ?int $numero = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $orientation = null;

    #[ORM\Column(nullable: true)]
    private ?int $nb_fenetres = null;

    #[ORM\Column(nullable: true)]
    private ?int $nb_ordis = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Batiment $batiment = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEtage(): ?int
    {
        return $this->etage;
    }

    public function setEtage(int $etage): static
    {
        $this->etage = $etage;

        return $this;
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

    public function getOrientation(): ?string
    {
        return $this->orientation;
    }

    public function setOrientation(?string $orientation): static
    {
        $this->orientation = $orientation;

        return $this;
    }

    public function getNbFenetres(): ?int
    {
        return $this->nb_fenetres;
    }

    public function setNbFenetres(?int $nb_fenetres): static
    {
        $this->nb_fenetres = $nb_fenetres;

        return $this;
    }

    public function getNbOrdis(): ?int
    {
        return $this->nb_ordis;
    }

    public function setNbOrdis(?int $nb_ordis): static
    {
        $this->nb_ordis = $nb_ordis;

        return $this;
    }


    public function getBatiment(): ?Batiment
    {
        return $this->batiment;
    }

    public function setBatiment(?Batiment $batiment): static
    {
        $this->batiment = $batiment;

        return $this;
    }
}
