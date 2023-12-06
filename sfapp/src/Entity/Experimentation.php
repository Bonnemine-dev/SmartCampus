<?php

namespace App\Entity;

use App\Repository\ExperimentationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Config\EtatExperimentation;

#[ORM\Entity(repositoryClass: ExperimentationRepository::class)]
class Experimentation
{
    // Identifiant unique généré automatiquement pour l'expérimentation.
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $id = null;

    // Date de demande de l'expérimentation.
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\DateTime]
    private ?\DateTimeInterface $datedemande = null;

    // Date d'installation de l'expérimentation (nullable).
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\DateTime]
    private ?\DateTimeInterface $dateinstallation = null;

    #[ORM\Column]
    private ?EtatExperimentation $etat = null;

    public function getEtat(): ?EtatExperimentation
    {
        return $this->etat;
    }

    public function setEtat(?EtatExperimentation $etat): void
    {
        $this->etat = $etat;
    }

    #[ORM\OneToMany(mappedBy: 'experimentation', targetEntity: Donnees::class, orphanRemoval: true)]
    private Collection $donnees;

    #[ORM\ManyToOne(inversedBy: 'experimentations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Salle $Salles = null;

    #[ORM\ManyToOne(inversedBy: 'experimentations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SA $SA = null;

    public function __construct()
    {
        $this->donnees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatedemande(): ?\DateTimeInterface
    {
        return $this->datedemande;
    }

    public function setDatedemande(\DateTimeInterface $datedemande): static
    {
        $this->datedemande = $datedemande;

        return $this;
    }

    public function getDateinstallation(): ?\DateTimeInterface
    {
        return $this->dateinstallation;
    }

    public function setDateinstallation(?\DateTimeInterface $dateinstallation): static
    {
        $this->dateinstallation = $dateinstallation;

        return $this;
    }

    /**
     * @return Collection<int, Donnees>
     */
    public function getDonnees(): Collection
    {
        return $this->donnees;
    }

    public function addDonnee(Donnees $donnee): static
    {
        if (!$this->donnees->contains($donnee)) {
            $this->donnees->add($donnee);
            $donnee->setExperimentation($this);
        }

        return $this;
    }

    public function removeDonnee(Donnees $donnee): static
    {
        if ($this->donnees->removeElement($donnee)) {
            // set the owning side to null (unless already changed)
            if ($donnee->getExperimentation() === $this) {
                $donnee->setExperimentation(null);
            }
        }

        return $this;
    }

    public function getSalles(): ?Salle
    {
        return $this->Salles;
    }

    public function setSalles(?Salle $Salles): static
    {
        $this->Salles = $Salles;

        return $this;
    }

    public function getSA(): ?SA
    {
        return $this->SA;
    }

    public function setSA(?SA $SA): static
    {
        $this->SA = $SA;

        return $this;
    }
}
