<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

/**
 * Season Operations Service
 *
 * Handles season management operations including add, activate, copy RC and copy competitions.
 */
class SeasonOperationsService
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * List all seasons
     */
    public function listSeasons(): array
    {
        $sql = "SELECT Code, Etat, Nat_debut, Nat_fin, Inter_debut, Inter_fin
                FROM kp_saison
                WHERE Code > '1900'
                ORDER BY Code DESC";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();

        return array_map(function ($row) {
            return [
                'code' => $row['Code'],
                'active' => $row['Etat'] === 'A',
                'natDebut' => $row['Nat_debut'],
                'natFin' => $row['Nat_fin'],
                'interDebut' => $row['Inter_debut'],
                'interFin' => $row['Inter_fin'],
            ];
        }, $result->fetchAllAssociative());
    }

    /**
     * Get active season code
     */
    public function getActiveSeason(): ?string
    {
        $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();
        return $result->fetchOne() ?: null;
    }

    /**
     * Add a new season
     */
    public function addSeason(string $code, ?string $natDebut, ?string $natFin, ?string $interDebut, ?string $interFin): void
    {
        // Check if season already exists
        $sql = "SELECT COUNT(*) FROM kp_saison WHERE Code = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$code]);

        if ((int) $result->fetchOne() > 0) {
            throw new \Exception("Season $code already exists");
        }

        $sql = "INSERT INTO kp_saison (Code, Etat, Nat_debut, Nat_fin, Inter_debut, Inter_fin)
                VALUES (?, 'I', ?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->executeStatement([$code, $natDebut, $natFin, $interDebut, $interFin]);
    }

    /**
     * Activate a season (deactivates all others)
     */
    public function activateSeason(string $code): void
    {
        // Check if season exists
        $sql = "SELECT COUNT(*) FROM kp_saison WHERE Code = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$code]);

        if ((int) $result->fetchOne() === 0) {
            throw new \Exception("Season $code not found");
        }

        $this->connection->beginTransaction();

        try {
            // Deactivate all seasons
            $sql = "UPDATE kp_saison SET Etat = 'I' WHERE Etat = 'A'";
            $this->connection->executeStatement($sql);

            // Activate the selected season
            $sql = "UPDATE kp_saison SET Etat = 'A' WHERE Code = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->executeStatement([$code]);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Get competitions for a season
     */
    public function getCompetitions(string $seasonCode): array
    {
        $sql = "SELECT Code, Libelle, Code_typeclt, Statut
                FROM kp_competition
                WHERE Code_saison = ?
                ORDER BY Libelle";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$seasonCode]);

        return array_map(function ($row) {
            return [
                'code' => $row['Code'],
                'libelle' => $row['Libelle'],
                'typeClassement' => $row['Code_typeclt'],
                'statut' => $row['Statut'],
            ];
        }, $result->fetchAllAssociative());
    }

    /**
     * Copy RC from one season to another
     */
    public function copyRc(string $sourceCode, string $targetCode): array
    {
        if ($sourceCode === $targetCode) {
            throw new \Exception('Source and target seasons must be different');
        }

        // Check source has RC
        $sql = "SELECT COUNT(*) FROM kp_rc WHERE Code_saison = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$sourceCode]);

        if ((int) $result->fetchOne() === 0) {
            throw new \Exception("No RC found for season $sourceCode");
        }

        // Get all RC from source
        $sql = "SELECT Code_competition, Matric, Ordre
                FROM kp_rc
                WHERE Code_saison = ?
                ORDER BY Code_competition, Ordre";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$sourceCode]);
        $rcList = $result->fetchAllAssociative();

        $copied = 0;
        $skipped = 0;

        $this->connection->beginTransaction();

        try {
            foreach ($rcList as $rc) {
                // Check if RC already exists
                $sql = "SELECT COUNT(*) FROM kp_rc WHERE Code_saison = ? AND Code_competition = ? AND Matric = ?";
                $stmt = $this->connection->prepare($sql);
                $result = $stmt->executeQuery([$targetCode, $rc['Code_competition'], $rc['Matric']]);

                if ((int) $result->fetchOne() === 0) {
                    $sql = "INSERT INTO kp_rc (Code_saison, Code_competition, Matric, Ordre) VALUES (?, ?, ?, ?)";
                    $stmt = $this->connection->prepare($sql);
                    $stmt->executeStatement([$targetCode, $rc['Code_competition'], $rc['Matric'], $rc['Ordre']]);
                    $copied++;
                } else {
                    $skipped++;
                }
            }

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        return [
            'message' => 'RC copied successfully',
            'copied' => $copied,
            'skipped' => $skipped,
        ];
    }

    /**
     * Copy competitions from one season to another
     */
    public function copyCompetitions(string $sourceCode, string $targetCode, array $competitionCodes, bool $copyMatches = false): array
    {
        if ($sourceCode === $targetCode) {
            throw new \Exception('Source and target seasons must be different');
        }

        if (empty($competitionCodes)) {
            throw new \Exception('At least one competition code is required');
        }

        $yearOffset = (int)$targetCode - (int)$sourceCode;

        $this->connection->beginTransaction();

        try {
            $copied = 0;
            $skipped = 0;
            $journeesCopied = 0;
            $matchesCopied = 0;
            $details = [];

            foreach ($competitionCodes as $code) {
                // Check if competition already exists in target
                $sql = "SELECT COUNT(*) FROM kp_competition WHERE Code = ? AND Code_saison = ?";
                $stmt = $this->connection->prepare($sql);
                $result = $stmt->executeQuery([$code, $targetCode]);

                if ((int) $result->fetchOne() > 0) {
                    $skipped++;
                    $details[] = ['code' => $code, 'status' => 'skipped', 'reason' => 'already exists'];
                    continue;
                }

                // Get source competition
                $sql = "SELECT * FROM kp_competition WHERE Code = ? AND Code_saison = ?";
                $stmt = $this->connection->prepare($sql);
                $result = $stmt->executeQuery([$code, $sourceCode]);
                $compet = $result->fetchAssociative();

                if (!$compet) {
                    $details[] = ['code' => $code, 'status' => 'skipped', 'reason' => 'not found in source'];
                    continue;
                }

                $isTypeCP = $compet['Code_typeclt'] === 'CP';

                // Insert new competition
                $sql = "INSERT INTO kp_competition (
                    Code, Code_saison, Code_niveau, Libelle, Soustitre, Soustitre2,
                    Web, BandeauLink, LogoLink, SponsorLink, En_actif, Titre_actif,
                    Bandeau_actif, Logo_actif, Sponsor_actif, Kpi_ffck_actif,
                    ToutGroup, TouteSaisons, Code_ref, GroupOrder, Code_typeclt,
                    points_grid, multi_competitions, ranking_structure_type,
                    Age_min, Age_max, Sexe, Code_tour, Nb_equipes, Verrou, Statut,
                    Qualifies, Elimines, Points, Date_calcul, Mode_calcul,
                    Date_publication, Date_publication_calcul, Mode_publication_calcul,
                    Code_uti_calcul, Code_uti_publication, Publication, Date_publi,
                    Code_uti_publi, commentairesCompet
                ) VALUES (
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?
                )";

                $stmt = $this->connection->prepare($sql);
                $stmt->executeStatement([
                    $compet['Code'],
                    $targetCode,
                    $compet['Code_niveau'],
                    $compet['Libelle'],
                    $compet['Soustitre'],
                    $compet['Soustitre2'],
                    $compet['Web'],
                    $compet['BandeauLink'],
                    $compet['LogoLink'],
                    $compet['SponsorLink'],
                    $compet['En_actif'],
                    $compet['Titre_actif'],
                    $compet['Bandeau_actif'],
                    $compet['Logo_actif'],
                    $compet['Sponsor_actif'],
                    $compet['Kpi_ffck_actif'],
                    $compet['ToutGroup'],
                    $compet['TouteSaisons'],
                    $compet['Code_ref'],
                    $compet['GroupOrder'],
                    $compet['Code_typeclt'],
                    $compet['points_grid'] ?? null,
                    $compet['multi_competitions'] ?? null,
                    $compet['ranking_structure_type'] ?? 'team',
                    $compet['Age_min'],
                    $compet['Age_max'],
                    $compet['Sexe'],
                    $compet['Code_tour'],
                    0, // Nb_equipes = 0
                    'N', // Verrou = 'N'
                    'ATT', // Statut = 'ATT'
                    $compet['Qualifies'],
                    $compet['Elimines'],
                    $compet['Points'],
                    '0000-00-00 00:00:00',
                    $compet['Mode_calcul'],
                    '0000-00-00 00:00:00',
                    '0000-00-00 00:00:00',
                    $compet['Mode_publication_calcul'],
                    '',
                    '',
                    '', // Publication = ''
                    '0000-00-00 00:00:00',
                    '',
                    '' // commentairesCompet
                ]);

                $copied++;

                // Copy journees
                $sql = "SELECT * FROM kp_journee WHERE Code_competition = ? AND Code_saison = ?";
                $stmt = $this->connection->prepare($sql);
                $result = $stmt->executeQuery([$code, $sourceCode]);
                $journees = $result->fetchAllAssociative();

                // For CP type without copyMatches, only copy first journee
                if ($isTypeCP && !$copyMatches && count($journees) > 0) {
                    $journees = [reset($journees)];
                }

                foreach ($journees as $journee) {
                    $oldIdJournee = $journee['Id'];
                    $newIdJournee = $this->getNextJourneeId();

                    $newDateDebut = $this->adjustDateSameWeekday($journee['Date_debut'], $yearOffset);
                    $newDateFin = $this->adjustDateSameWeekday($journee['Date_fin'], $yearOffset);

                    $sql = "INSERT INTO kp_journee (
                        Id, Code_competition, Code_saison, Date_debut, Date_fin,
                        Nom, Libelle, Lieu, Departement, Plan_eau,
                        Responsable_insc, Responsable_insc_adr, Responsable_insc_cp, Responsable_insc_ville,
                        Responsable_R1, Etat, Type, Code_organisateur, Organisateur,
                        Organisateur_adr, Organisateur_cp, Organisateur_ville,
                        Delegue, ChefArbitre, Validation, Code_uti, Phase, Niveau, Etape,
                        Nbequipes, Publication, Id_dupli, Public_prin, Public_sec
                    ) VALUES (
                        ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?,
                        ?, ?, ?, ?,
                        ?, ?, ?, ?, ?,
                        ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?
                    )";

                    $stmt = $this->connection->prepare($sql);
                    $stmt->executeStatement([
                        $newIdJournee,
                        $code,
                        $targetCode,
                        $newDateDebut,
                        $newDateFin,
                        $journee['Nom'],
                        $journee['Libelle'],
                        '', // Lieu
                        '', // Departement
                        '', // Plan_eau
                        '', // Responsable_insc
                        '', // Responsable_insc_adr
                        '', // Responsable_insc_cp
                        '', // Responsable_insc_ville
                        '', // Responsable_R1
                        $journee['Etat'],
                        $journee['Type'],
                        '', // Code_organisateur
                        '', // Organisateur
                        '', // Organisateur_adr
                        '', // Organisateur_cp
                        '', // Organisateur_ville
                        '', // Delegue
                        '', // ChefArbitre
                        'N', // Validation
                        '',
                        $journee['Phase'],
                        $journee['Niveau'],
                        $journee['Etape'],
                        $journee['Nbequipes'],
                        '', // Publication
                        null, // Id_dupli
                        'O',
                        'O'
                    ]);

                    $journeesCopied++;

                    // Copy matches for CP type if copyMatches is true
                    if ($isTypeCP && $copyMatches) {
                        $sql = "SELECT * FROM kp_match WHERE Id_journee = ?";
                        $stmt = $this->connection->prepare($sql);
                        $result = $stmt->executeQuery([$oldIdJournee]);
                        $matches = $result->fetchAllAssociative();

                        foreach ($matches as $match) {
                            $newDateMatch = $this->adjustDateSameWeekday($match['Date_match'], $yearOffset);

                            $sql = "INSERT INTO kp_match (
                                Id_journee, Libelle, Type, Statut, Date_match, Heure_match, Heure_fin,
                                Terrain, Numero_ordre, Periode,
                                Id_equipeA, Id_equipeB, ColorA, ColorB,
                                ScoreA, ScoreB, ScoreDetailA, ScoreDetailB, CoeffA, CoeffB,
                                Commentaires_officiels, Commentaires,
                                Arbitre_principal, Arbitre_secondaire,
                                Matric_arbitre_principal, Matric_arbitre_secondaire,
                                Secretaire, Chronometre, Timeshoot, Ligne1, Ligne2,
                                Publication, Code_uti, Validation
                            ) VALUES (
                                ?, ?, ?, ?, ?, ?, ?,
                                ?, ?, ?,
                                ?, ?, ?, ?,
                                ?, ?, ?, ?, ?, ?,
                                ?, ?,
                                ?, ?,
                                ?, ?,
                                ?, ?, ?, ?, ?,
                                ?, ?, ?
                            )";

                            $stmt = $this->connection->prepare($sql);
                            $stmt->executeStatement([
                                $newIdJournee,
                                $match['Libelle'],
                                $match['Type'],
                                'ATT',
                                $newDateMatch,
                                $match['Heure_match'],
                                '00:00:00',
                                $match['Terrain'],
                                $match['Numero_ordre'],
                                $match['Periode'],
                                null, null, null, null,
                                null, null, null, null, 1, 1,
                                null, null,
                                null, null,
                                null, null,
                                null, null, null, null, null,
                                '', '', ''
                            ]);

                            $matchesCopied++;
                        }
                    }
                }

                $details[] = [
                    'code' => $code,
                    'status' => 'copied',
                    'journees' => count($journees),
                    'matches' => $isTypeCP && $copyMatches ? $matchesCopied : 0
                ];
            }

            $this->connection->commit();

            return [
                'message' => 'Competitions copied successfully',
                'copied' => $copied,
                'skipped' => $skipped,
                'journeesCopied' => $journeesCopied,
                'matchesCopied' => $matchesCopied,
                'details' => $details,
            ];
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Get next journee ID
     */
    private function getNextJourneeId(): int
    {
        $sql = "SELECT MAX(Id) + 1 FROM kp_journee";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();
        return (int) $result->fetchOne() ?: 1;
    }

    /**
     * Adjust date to keep same weekday after year offset
     */
    private function adjustDateSameWeekday(?string $dateStr, int $yearOffset): ?string
    {
        if (empty($dateStr) || $dateStr === '0000-00-00') {
            return $dateStr;
        }

        $date = new \DateTime($dateStr);
        $originalDayOfWeek = (int) $date->format('N');

        $date->modify("+$yearOffset years");

        $newDayOfWeek = (int) $date->format('N');
        $dayDiff = $originalDayOfWeek - $newDayOfWeek;

        if ($dayDiff > 3) {
            $dayDiff -= 7;
        } elseif ($dayDiff < -3) {
            $dayDiff += 7;
        }

        $date->modify("$dayDiff days");

        return $date->format('Y-m-d');
    }
}
