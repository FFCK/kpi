<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Responsable de Compétition (RC)
 * Table: kp_rc
 */
#[ORM\Entity]
#[ORM\Table(name: 'kp_rc')]
class Rc
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'Id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'Code_competition', type: 'string', length: 10, nullable: true)]
    private ?string $codeCompetition = null;

    #[ORM\Column(name: 'Code_saison', type: 'string', length: 8, nullable: false)]
    private string $codeSaison;

    #[ORM\Column(name: 'Ordre', type: 'integer', nullable: false)]
    private int $ordre;

    #[ORM\Column(name: 'Matric', type: 'integer', nullable: false)]
    private int $matric;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeCompetition(): ?string
    {
        return $this->codeCompetition;
    }

    public function setCodeCompetition(?string $codeCompetition): self
    {
        $this->codeCompetition = $codeCompetition;
        return $this;
    }

    public function getCodeSaison(): string
    {
        return $this->codeSaison;
    }

    public function setCodeSaison(string $codeSaison): self
    {
        $this->codeSaison = $codeSaison;
        return $this;
    }

    public function getOrdre(): int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;
        return $this;
    }

    public function getMatric(): int
    {
        return $this->matric;
    }

    public function setMatric(int $matric): self
    {
        $this->matric = $matric;
        return $this;
    }
}
