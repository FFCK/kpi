<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'kp_evenement')]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ]
)]
class Event
{
    #[ORM\Id]
    #[ORM\Column(name: 'Id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'Libelle', type: 'string', length: 255, nullable: true)]
    private ?string $libelle = null;

    #[ORM\Column(name: 'Lieu', type: 'string', length: 255, nullable: true)]
    private ?string $place = null;

    #[ORM\Column(name: 'logo', type: 'string', length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(name: 'Publication', type: 'string', length: 1, nullable: true)]
    private ?string $publication = null;

    #[ORM\Column(name: 'app', type: 'string', length: 1, nullable: true)]
    private ?string $app = null;

    #[ORM\Column(name: 'Date_debut', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateDebut = null;

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;
        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(?string $place): self
    {
        $this->place = $place;
        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;
        return $this;
    }

    public function getPublication(): ?string
    {
        return $this->publication;
    }

    public function setPublication(?string $publication): self
    {
        $this->publication = $publication;
        return $this;
    }

    public function getApp(): ?string
    {
        return $this->app;
    }

    public function setApp(?string $app): self
    {
        $this->app = $app;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }
}
