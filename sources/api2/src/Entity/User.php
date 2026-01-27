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
    private ?string $filtreSaison = null;
    private ?string $filtreJournee = null;
    private ?string $idEvenement = null;
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

    public function getFiltreSaison(): ?string
    {
        return $this->filtreSaison;
    }

    public function setFiltreSaison(?string $filtreSaison): self
    {
        $this->filtreSaison = $filtreSaison;
        return $this;
    }

    public function getFiltreJournee(): ?string
    {
        return $this->filtreJournee;
    }

    public function setFiltreJournee(?string $filtreJournee): self
    {
        $this->filtreJournee = $filtreJournee;
        return $this;
    }

    public function getIdEvenement(): ?string
    {
        return $this->idEvenement;
    }

    public function setIdEvenement(?string $idEvenement): self
    {
        $this->idEvenement = $idEvenement;
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

    // --- Filter parsing methods ---

    /**
     * Parse pipe-delimited filter: "|val1|val2|" → ["val1", "val2"]
     * Returns null if no restriction (empty string).
     */
    private static function parsePipeFilter(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        return array_values(array_filter(explode('|', trim($value, '|')), fn($v) => $v !== ''));
    }

    /**
     * Parse comma-separated filter: "1,2,3" → ["1", "2", "3"]
     * Returns null if no restriction (empty string).
     */
    private static function parseCommaFilter(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        return array_values(array_filter(explode(',', $value), fn($v) => trim($v) !== ''));
    }

    /** @return string[]|null Allowed seasons, or null if unrestricted */
    public function getAllowedSeasons(): ?array
    {
        return self::parsePipeFilter($this->filtreSaison);
    }

    /** @return string[]|null Allowed competition codes, or null if unrestricted */
    public function getAllowedCompetitions(): ?array
    {
        return self::parsePipeFilter($this->filtreCompetition);
    }

    /** @return int[]|null Allowed event IDs, or null if unrestricted */
    public function getAllowedEvents(): ?array
    {
        $values = self::parsePipeFilter($this->idEvenement);
        return $values !== null ? array_map('intval', $values) : null;
    }

    /** @return int[]|null Allowed journee IDs, or null if unrestricted */
    public function getAllowedJournees(): ?array
    {
        $values = self::parseCommaFilter($this->filtreJournee);
        return $values !== null ? array_map('intval', $values) : null;
    }

    /** @return string[]|null Allowed club codes, or null if unrestricted */
    public function getAllowedClubs(): ?array
    {
        return self::parseCommaFilter($this->limitClubs);
    }

    // --- UserInterface methods ---

    public function getUserIdentifier(): string
    {
        return $this->code;
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];

        // Map profile levels to granular roles
        // Hierarchy is handled by security.yaml role_hierarchy
        $roles[] = match (true) {
            $this->niveau <= 1 => 'ROLE_SUPER_ADMIN',
            $this->niveau <= 2 => 'ROLE_ADMIN',
            $this->niveau <= 3 => 'ROLE_DIVISION',
            $this->niveau <= 4 => 'ROLE_COMPETITION',
            $this->niveau <= 5 => 'ROLE_DELEGATE',
            $this->niveau <= 6 => 'ROLE_ORGANIZER',
            $this->niveau <= 7 => 'ROLE_TEAM',
            $this->niveau <= 8 => 'ROLE_VIEWER',
            $this->niveau <= 9 => 'ROLE_SCORER',
            default => 'ROLE_USER',
        };

        return array_unique($roles);
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // Required by UserInterface but nothing to erase:
        // password is never stored in plain text in this entity
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
            'filters' => [
                'seasons' => $this->getAllowedSeasons(),
                'competitions' => $this->getAllowedCompetitions(),
                'events' => $this->getAllowedEvents(),
                'journees' => $this->getAllowedJournees(),
                'clubs' => $this->getAllowedClubs(),
            ],
        ];
    }
}
