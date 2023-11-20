<?php

namespace App\Entity;

use App\Repository\BatimentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Twig\Node\Expression\Binary\LessBinary;


#[ORM\Entity(repositoryClass: BatimentRepository::class)]
class Batiment
{
    // Identifiant unique généré automatiquement pour le bâtiment.
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?int $id = null;

    // Nom du bâtiment, limité à 50 caractères.
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1,max: 1, maxMessage: 'Le nom d\'un bâtiment n\'est q\'une lettre comme par exemple \'D\'')]
    private ?string $nom = null;

    // Description optionnelle du bâtiment, limitée à 300 caractères.
    #[ORM\Column(length: 300, nullable: true)]
    #[Assert\Length(min:0, max:300, maxMessage: 'La description ne doit pas dépasser 300 caractères')]
    private ?string $description = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
