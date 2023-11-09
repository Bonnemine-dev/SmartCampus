<?php

namespace App\Entity;

use App\Repository\ExperimentationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExperimentationRepository::class)]
class Experimentation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Salle $Salle = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?SA $SA = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datedemande = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateinstallation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSalle(): ?Salle
    {
        return $this->Salle;
    }

    public function setSalle(Salle $Salle): static
    {
        $this->Salle = $Salle;

        return $this;
    }

    public function getSA(): ?SA
    {
        return $this->SA;
    }

    public function setSA(SA $SA): static
    {
        $this->SA = $SA;

        return $this;
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
}
