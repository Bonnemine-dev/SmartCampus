<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    // Identifiant unique généré automatiquement pour la salle.
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Assert\Notblank]
    private ?int $id = null;

    // Nom de la salle, limité à 50 caractères.
    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Length(min: 4,max: 4, maxMessage: 'Le nom d\'une salle ne peut pas dépasser les 4 caractères')]
    private ?string $nom = null;

    // Étage où se trouve la salle.
    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Range(min: 0,max: 5, notinRangeMessage: 'Un étage ne peut pas être négatif et il n\'existe pas de bâtiments avec plus de 5 étages')]
    private ?int $etage = null;

    // Numéro de la salle.
    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Range(min: 1,max: 9, notInRangeMessage: 'Les numéros de salle sont uniquement compris entre 1 et 9')]
    private ?int $numero = null;


    // Tableau réunissant l'ensemble des orientations.
    public const Orientation = ['NORD','SUD','EST','OUEST'];
    // Orientation de la salle (nord ou sud).
    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: Salle::Orientation, message: 'Choisie une orientation valide')]
    private ?string $orientation = null;

    // Nombre de fenêtres dans la salle (nullable).
    #[ORM\Column(nullable: true)]
    #[Assert\Positive]
    #[Assert\NotBlank]
    private ?int $nb_fenetres = null;

    // Nombre d'ordinateurs dans la salle (nullable).
    #[ORM\Column(nullable: true)]
    #[Assert\Positive]
    #[Assert\NotBlank]
    private ?int $nb_ordis = null;

    // Relation ManyToOne avec le bâtiment, avec jointure non nullable.
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
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
