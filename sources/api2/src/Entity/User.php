<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User entity mapped to legacy kp_user table
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private string $code;
    private string $password;
    private int $niveau;
    private ?string $nom = null;
    private ?string $prenom = null;
    private ?string $filtreCompetition = null;
    private ?string $limitClubs = null;
    private ?string $club = null;

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getNiveau(): int
    {
        return $this->niveau;
    }

    public function setNiveau(int $niveau): self
    {
        $this->niveau = $niveau;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getFiltreCompetition(): ?string
    {
        return $this->filtreCompetition;
    }

    public function setFiltreCompetition(?string $filtreCompetition): self
    {
        $this->filtreCompetition = $filtreCompetition;
        return $this;
    }

    public function getLimitClubs(): ?string
    {
        return $this->limitClubs;
    }

    public function setLimitClubs(?string $limitClubs): self
    {
        $this->limitClubs = $limitClubs;
        return $this;
    }

    public function getClub(): ?string
    {
        return $this->club;
    }

    public function setClub(?string $club): self
    {
        $this->club = $club;
        return $this;
    }

    // UserInterface methods

    public function getUserIdentifier(): string
    {
        return $this->code;
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];

        // Map profile levels to roles
        if ($this->niveau <= 1) {
            $roles[] = 'ROLE_SUPER_ADMIN';
            $roles[] = 'ROLE_ADMIN';
        } elseif ($this->niveau <= 2) {
            $roles[] = 'ROLE_ADMIN';
        } elseif ($this->niveau <= 3) {
            $roles[] = 'ROLE_MANAGER';
        } elseif ($this->niveau <= 9) {
            $roles[] = 'ROLE_STAFF';
        }

        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {
        // Nothing to erase
    }

    /**
     * Get user data for JWT payload and API responses
     */
    public function toArray(): array
    {
        return [
            'id' => $this->code,
            'name' => $this->nom,
            'firstname' => $this->prenom,
            'profile' => $this->niveau,
        ];
    }
}
