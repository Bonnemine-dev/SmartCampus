<?php

namespace App\Entity;

use App\Repository\DonneesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DonneesRepository::class)]
class Donnees
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?float $temperature = null;

    #[ORM\Column]
    private ?int $humidite = null;

    #[ORM\Column]
    private ?int $tauxcarbone = null;

    #[ORM\ManyToOne(inversedBy: 'donnees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?experimentation $experimentation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function setTemperature(float $temperature): static
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getHumidite(): ?int
    {
        return $this->humidite;
    }

    public function setHumidite(int $humidite): static
    {
        $this->humidite = $humidite;

        return $this;
    }

    public function getTauxcarbone(): ?int
    {
        return $this->tauxcarbone;
    }

    public function setTauxcarbone(int $tauxcarbone): static
    {
        $this->tauxcarbone = $tauxcarbone;

        return $this;
    }

    public function getExperimentation(): ?experimentation
    {
        return $this->experimentation;
    }

    public function setExperimentation(?experimentation $experimentation): static
    {
        $this->experimentation = $experimentation;

        return $this;
    }
}
