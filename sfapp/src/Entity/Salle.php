<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    // Identifiant unique généré automatiquement pour la salle.
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Nom de la salle, limité à 50 caractères.
    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    // Étage où se trouve la salle.
    #[ORM\Column]
    private ?int $etage = null;

    // Numéro de la salle.
    #[ORM\Column]
    private ?int $numero = null;

    // Orientation de la salle (nord ou sud).
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $orientation = null;

    // Nombre de fenêtres dans la salle (nullable).
    #[ORM\Column(nullable: true)]
    private ?int $nb_fenetres = null;

    // Nombre d'ordinateurs dans la salle (nullable).
    #[ORM\Column(nullable: true)]
    private ?int $nb_ordis = null;

    // Relation ManyToOne avec le bâtiment, avec jointure non nullable.
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
