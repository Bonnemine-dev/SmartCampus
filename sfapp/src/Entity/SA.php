<?php

namespace App\Entity;

use App\Repository\SARepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\OneToMany(mappedBy: 'SA', targetEntity: Experimentation::class)]
    private Collection $experimentations;

    public function __construct()
    {
        $this->experimentations = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Experimentation>
     */
    public function getExperimentations(): Collection
    {
        return $this->experimentations;
    }

    public function addExperimentation(Experimentation $experimentation): static
    {
        if (!$this->experimentations->contains($experimentation)) {
            $this->experimentations->add($experimentation);
            $experimentation->setSA($this);
        }

        return $this;
    }

    public function removeExperimentation(Experimentation $experimentation): static
    {
        if ($this->experimentations->removeElement($experimentation)) {
            // set the owning side to null (unless already changed)
            if ($experimentation->getSA() === $this) {
                $experimentation->setSA(null);
            }
        }

        return $this;
    }
}
