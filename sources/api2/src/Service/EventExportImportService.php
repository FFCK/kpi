<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

/**
 * Event Export/Import Service
 *
 * Handles event data export to JSON and import from JSON.
 * Exports/imports 13 related tables: kp_evenement, kp_evenement_journee, kp_journee,
 * kp_competition, kp_competition_equipe, kp_competition_equipe_init, kp_competition_equipe_joueur,
 * kp_competition_equipe_journee, kp_competition_equipe_niveau, kp_match, kp_match_detail,
 * kp_match_joueur, kp_chrono.
 *
 * Migrated from GestionOperations.php ExportEvt and ImportEvt.
 */
class EventExportImportService
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * Export all event data as a structured array
     *
     * @param int $eventId Event ID to export
     * @return array Complete event data including all related tables
     * @throws \Exception if event not found
     */
    public function exportEvent(int $eventId): array
    {
        // Get event
        $sql = "SELECT * FROM kp_evenement WHERE Id = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$eventId]);
        $event = $result->fetchAssociative();

        if (!$event) {
            throw new \Exception("Event $eventId not found");
        }

        $export = [
            'kp_evenement' => $event,
            'export_date' => date('Y-m-d H:i:s'),
            'export_version' => '2.0',
        ];

        // Get event journees
        $sql = "SELECT * FROM kp_evenement_journee WHERE Id_evenement = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$eventId]);
        $eventJournees = $result->fetchAllAssociative();
        $export['kp_evenement_journee'] = $eventJournees;

        // Extract journee IDs
        $journeeIds = array_column($eventJournees, 'Id_journee');

        if (empty($journeeIds)) {
            // No journees, return minimal export
            $export['kp_journee'] = [];
            $export['kp_competition'] = [];
            $export['kp_competition_equipe'] = [];
            $export['kp_competition_equipe_init'] = [];
            $export['kp_competition_equipe_joueur'] = [];
            $export['kp_competition_equipe_journee'] = [];
            $export['kp_competition_equipe_niveau'] = [];
            $export['kp_match'] = [];
            $export['kp_match_detail'] = [];
            $export['kp_match_joueur'] = [];
            $export['kp_chrono'] = [];
            return $export;
        }

        // Get journees
        $placeholders = implode(',', array_fill(0, count($journeeIds), '?'));
        $sql = "SELECT * FROM kp_journee WHERE Id IN ($placeholders)";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($journeeIds);
        $journees = $result->fetchAllAssociative();
        $export['kp_journee'] = $journees;

        // Extract competition codes and season
        $competitionCodes = array_unique(array_column($journees, 'Code_competition'));
        $seasonCode = $journees[0]['Code_saison'] ?? null;

        if (!empty($competitionCodes) && $seasonCode) {
            // Get competitions
            $placeholders = implode(',', array_fill(0, count($competitionCodes), '?'));
            $sql = "SELECT * FROM kp_competition WHERE Code_saison = ? AND Code IN ($placeholders)";
            $stmt = $this->connection->prepare($sql);
            $params = array_merge([$seasonCode], $competitionCodes);
            $result = $stmt->executeQuery($params);
            $export['kp_competition'] = $result->fetchAllAssociative();

            // Get competition teams
            $sql = "SELECT * FROM kp_competition_equipe WHERE Code_saison = ? AND Code_compet IN ($placeholders)";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery($params);
            $competitionTeams = $result->fetchAllAssociative();
            $export['kp_competition_equipe'] = $competitionTeams;

            // Extract team IDs
            $teamIds = array_column($competitionTeams, 'Id');

            if (!empty($teamIds)) {
                $teamPlaceholders = implode(',', array_fill(0, count($teamIds), '?'));

                // Get team init
                $sql = "SELECT * FROM kp_competition_equipe_init WHERE Id IN ($teamPlaceholders)";
                $stmt = $this->connection->prepare($sql);
                $result = $stmt->executeQuery($teamIds);
                $export['kp_competition_equipe_init'] = $result->fetchAllAssociative();

                // Get team players
                $sql = "SELECT * FROM kp_competition_equipe_joueur WHERE Id_equipe IN ($teamPlaceholders)";
                $stmt = $this->connection->prepare($sql);
                $result = $stmt->executeQuery($teamIds);
                $export['kp_competition_equipe_joueur'] = $result->fetchAllAssociative();

                // Get team niveau
                $sql = "SELECT * FROM kp_competition_equipe_niveau WHERE Id IN ($teamPlaceholders)";
                $stmt = $this->connection->prepare($sql);
                $result = $stmt->executeQuery($teamIds);
                $export['kp_competition_equipe_niveau'] = $result->fetchAllAssociative();
            } else {
                $export['kp_competition_equipe_init'] = [];
                $export['kp_competition_equipe_joueur'] = [];
                $export['kp_competition_equipe_niveau'] = [];
            }
        } else {
            $export['kp_competition'] = [];
            $export['kp_competition_equipe'] = [];
            $export['kp_competition_equipe_init'] = [];
            $export['kp_competition_equipe_joueur'] = [];
            $export['kp_competition_equipe_niveau'] = [];
        }

        // Get team journee stats
        $journeePlaceholders = implode(',', array_fill(0, count($journeeIds), '?'));
        $sql = "SELECT * FROM kp_competition_equipe_journee WHERE Id_journee IN ($journeePlaceholders)";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($journeeIds);
        $export['kp_competition_equipe_journee'] = $result->fetchAllAssociative();

        // Get matches
        $sql = "SELECT * FROM kp_match WHERE Id_journee IN ($journeePlaceholders)";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery($journeeIds);
        $matches = $result->fetchAllAssociative();
        $export['kp_match'] = $matches;

        // Extract match IDs
        $matchIds = array_column($matches, 'Id');

        if (!empty($matchIds)) {
            $matchPlaceholders = implode(',', array_fill(0, count($matchIds), '?'));

            // Get match details
            $sql = "SELECT * FROM kp_match_detail WHERE Id_match IN ($matchPlaceholders)";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery($matchIds);
            $export['kp_match_detail'] = $result->fetchAllAssociative();

            // Get match players
            $sql = "SELECT * FROM kp_match_joueur WHERE Id_match IN ($matchPlaceholders)";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery($matchIds);
            $export['kp_match_joueur'] = $result->fetchAllAssociative();

            // Get chrono data
            $sql = "SELECT * FROM kp_chrono WHERE IdMatch IN ($matchPlaceholders)";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->executeQuery($matchIds);
            $export['kp_chrono'] = $result->fetchAllAssociative();
        } else {
            $export['kp_match_detail'] = [];
            $export['kp_match_joueur'] = [];
            $export['kp_chrono'] = [];
        }

        return $export;
    }

    /**
     * Import event data from a structured array
     *
     * @param int $eventId Target event ID (must match the ID in the import data)
     * @param array $data Import data from a previous export
     * @throws \Exception if validation fails or import fails
     */
    public function importEvent(int $eventId, array $data): void
    {
        // Validate import data structure
        $this->validateImportData($eventId, $data);

        $this->connection->beginTransaction();

        try {
            $this->connection->executeStatement("SET FOREIGN_KEY_CHECKS = 0");

            // Import event
            $this->importEventRecord($data['kp_evenement']);

            // Import event journees
            $journeeIds = $this->importEventJournees($eventId, $data['kp_evenement_journee']);

            // Import journees
            $this->importJournees($data['kp_journee']);

            // Import competitions
            $competitionCodes = $this->importCompetitions($data['kp_competition']);

            // Import competition teams
            $teamIds = $this->importCompetitionTeams($data['kp_competition_equipe'], $competitionCodes);

            // Import team init
            $this->importCompetitionTeamInit($data['kp_competition_equipe_init'], $teamIds);

            // Import team players
            $this->importCompetitionTeamPlayers($data['kp_competition_equipe_joueur'], $teamIds);

            // Import team journee stats
            $this->importCompetitionTeamJournee($data['kp_competition_equipe_journee'], $teamIds);

            // Import team niveau stats
            $this->importCompetitionTeamNiveau($data['kp_competition_equipe_niveau'], $teamIds);

            // Import matches
            $matchIds = $this->importMatches($data['kp_match'], $journeeIds);

            // Import match details
            $this->importMatchDetails($data['kp_match_detail'], $matchIds);

            // Import match players
            $this->importMatchPlayers($data['kp_match_joueur'], $matchIds);

            // Import chrono
            $this->importChrono($data['kp_chrono'], $matchIds);

            $this->connection->executeStatement("SET FOREIGN_KEY_CHECKS = 1");
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw new \Exception('Event import failed: ' . $e->getMessage());
        }
    }

    /**
     * Validate import data structure
     */
    private function validateImportData(int $eventId, array $data): void
    {
        $requiredKeys = [
            'kp_evenement',
            'kp_evenement_journee',
            'kp_journee',
            'kp_competition',
            'kp_competition_equipe',
            'kp_competition_equipe_init',
            'kp_competition_equipe_joueur',
            'kp_competition_equipe_journee',
            'kp_competition_equipe_niveau',
            'kp_match',
            'kp_match_detail',
            'kp_match_joueur',
            'kp_chrono',
        ];

        foreach ($requiredKeys as $key) {
            if (!isset($data[$key])) {
                throw new \Exception("Missing required key in import data: $key");
            }
        }

        // Validate event ID matches
        if (($data['kp_evenement']['Id'] ?? 0) != $eventId) {
            throw new \Exception("Event ID mismatch: expected $eventId, got " . ($data['kp_evenement']['Id'] ?? 'null'));
        }
    }

    /**
     * Import event record
     */
    private function importEventRecord(array $event): void
    {
        $sql = "INSERT INTO kp_evenement (Id, Libelle, Lieu, Date_debut, Date_fin, Publication, Date_publi, Code_uti_publi, logo, app)
                VALUES (:Id, :Libelle, :Lieu, :Date_debut, :Date_fin, :Publication, :Date_publi, :Code_uti_publi, :logo, :app)
                ON DUPLICATE KEY UPDATE
                    Libelle = VALUES(Libelle),
                    Lieu = VALUES(Lieu),
                    Date_debut = VALUES(Date_debut),
                    Date_fin = VALUES(Date_fin),
                    Publication = VALUES(Publication),
                    Date_publi = VALUES(Date_publi),
                    Code_uti_publi = VALUES(Code_uti_publi),
                    logo = VALUES(logo),
                    app = VALUES(app)";

        $this->connection->executeStatement($sql, [
            'Id' => $event['Id'],
            'Libelle' => $event['Libelle'],
            'Lieu' => $event['Lieu'],
            'Date_debut' => $event['Date_debut'],
            'Date_fin' => $event['Date_fin'],
            'Publication' => $event['Publication'],
            'Date_publi' => $event['Date_publi'],
            'Code_uti_publi' => $event['Code_uti_publi'],
            'logo' => $event['logo'] ?? null,
            'app' => $event['app'] ?? null,
        ]);
    }

    /**
     * Import event journees and return journee IDs
     */
    private function importEventJournees(int $eventId, array $eventJournees): array
    {
        // Delete existing event journees
        $sql = "DELETE FROM kp_evenement_journee WHERE Id_evenement = ?";
        $this->connection->executeStatement($sql, [$eventId]);

        $journeeIds = [];

        $sql = "INSERT INTO kp_evenement_journee (Id_evenement, Id_journee) VALUES (?, ?)";
        $stmt = $this->connection->prepare($sql);

        foreach ($eventJournees as $ej) {
            $stmt->executeStatement([$eventId, $ej['Id_journee']]);
            $journeeIds[] = $ej['Id_journee'];
        }

        return $journeeIds;
    }

    /**
     * Import journees
     */
    private function importJournees(array $journees): void
    {
        foreach ($journees as $j) {
            $sql = "INSERT INTO kp_journee (
                        Id, Code_competition, Code_saison, Date_debut, Date_fin, Nom, Libelle, Lieu,
                        Departement, Plan_eau, Responsable_insc, Responsable_insc_adr, Responsable_insc_cp,
                        Responsable_insc_ville, Responsable_R1, Etat, Type, Code_organisateur, Organisateur,
                        Organisateur_adr, Organisateur_cp, Organisateur_ville, Delegue, ChefArbitre, Rep_athletes,
                        Arb_nj1, Arb_nj2, Arb_nj3, Arb_nj4, Arb_nj5, Validation, Code_uti, Phase, Niveau,
                        Etape, Nbequipes, Publication, Id_dupli, Public_prin, Public_sec
                    ) VALUES (
                        :Id, :Code_competition, :Code_saison, :Date_debut, :Date_fin, :Nom, :Libelle, :Lieu,
                        :Departement, :Plan_eau, :Responsable_insc, :Responsable_insc_adr, :Responsable_insc_cp,
                        :Responsable_insc_ville, :Responsable_R1, :Etat, :Type, :Code_organisateur, :Organisateur,
                        :Organisateur_adr, :Organisateur_cp, :Organisateur_ville, :Delegue, :ChefArbitre, :Rep_athletes,
                        :Arb_nj1, :Arb_nj2, :Arb_nj3, :Arb_nj4, :Arb_nj5, :Validation, :Code_uti, :Phase, :Niveau,
                        :Etape, :Nbequipes, :Publication, :Id_dupli, :Public_prin, :Public_sec
                    )
                    ON DUPLICATE KEY UPDATE
                        Code_competition = VALUES(Code_competition),
                        Code_saison = VALUES(Code_saison),
                        Date_debut = VALUES(Date_debut),
                        Date_fin = VALUES(Date_fin),
                        Nom = VALUES(Nom),
                        Libelle = VALUES(Libelle),
                        Lieu = VALUES(Lieu),
                        Departement = VALUES(Departement),
                        Plan_eau = VALUES(Plan_eau),
                        Responsable_insc = VALUES(Responsable_insc),
                        Responsable_insc_adr = VALUES(Responsable_insc_adr),
                        Responsable_insc_cp = VALUES(Responsable_insc_cp),
                        Responsable_insc_ville = VALUES(Responsable_insc_ville),
                        Responsable_R1 = VALUES(Responsable_R1),
                        Etat = VALUES(Etat),
                        Type = VALUES(Type),
                        Code_organisateur = VALUES(Code_organisateur),
                        Organisateur = VALUES(Organisateur),
                        Organisateur_adr = VALUES(Organisateur_adr),
                        Organisateur_cp = VALUES(Organisateur_cp),
                        Organisateur_ville = VALUES(Organisateur_ville),
                        Delegue = VALUES(Delegue),
                        ChefArbitre = VALUES(ChefArbitre),
                        Rep_athletes = VALUES(Rep_athletes),
                        Arb_nj1 = VALUES(Arb_nj1),
                        Arb_nj2 = VALUES(Arb_nj2),
                        Arb_nj3 = VALUES(Arb_nj3),
                        Arb_nj4 = VALUES(Arb_nj4),
                        Arb_nj5 = VALUES(Arb_nj5),
                        Validation = VALUES(Validation),
                        Code_uti = VALUES(Code_uti),
                        Phase = VALUES(Phase),
                        Niveau = VALUES(Niveau),
                        Etape = VALUES(Etape),
                        Nbequipes = VALUES(Nbequipes),
                        Publication = VALUES(Publication),
                        Id_dupli = VALUES(Id_dupli),
                        Public_prin = VALUES(Public_prin),
                        Public_sec = VALUES(Public_sec)";

            $this->connection->executeStatement($sql, [
                'Id' => $j['Id'],
                'Code_competition' => $j['Code_competition'],
                'Code_saison' => $j['Code_saison'],
                'Date_debut' => $j['Date_debut'],
                'Date_fin' => $j['Date_fin'],
                'Nom' => $j['Nom'],
                'Libelle' => $j['Libelle'],
                'Lieu' => $j['Lieu'],
                'Departement' => $j['Departement'],
                'Plan_eau' => $j['Plan_eau'],
                'Responsable_insc' => $j['Responsable_insc'],
                'Responsable_insc_adr' => $j['Responsable_insc_adr'],
                'Responsable_insc_cp' => $j['Responsable_insc_cp'],
                'Responsable_insc_ville' => $j['Responsable_insc_ville'],
                'Responsable_R1' => $j['Responsable_R1'],
                'Etat' => $j['Etat'],
                'Type' => $j['Type'],
                'Code_organisateur' => $j['Code_organisateur'],
                'Organisateur' => $j['Organisateur'],
                'Organisateur_adr' => $j['Organisateur_adr'],
                'Organisateur_cp' => $j['Organisateur_cp'],
                'Organisateur_ville' => $j['Organisateur_ville'],
                'Delegue' => $j['Delegue'],
                'ChefArbitre' => $j['ChefArbitre'],
                'Rep_athletes' => $j['Rep_athletes'] ?? null,
                'Arb_nj1' => $j['Arb_nj1'] ?? null,
                'Arb_nj2' => $j['Arb_nj2'] ?? null,
                'Arb_nj3' => $j['Arb_nj3'] ?? null,
                'Arb_nj4' => $j['Arb_nj4'] ?? null,
                'Arb_nj5' => $j['Arb_nj5'] ?? null,
                'Validation' => $j['Validation'],
                'Code_uti' => $j['Code_uti'],
                'Phase' => $j['Phase'],
                'Niveau' => $j['Niveau'],
                'Etape' => $j['Etape'],
                'Nbequipes' => $j['Nbequipes'],
                'Publication' => $j['Publication'],
                'Id_dupli' => $j['Id_dupli'],
                'Public_prin' => $j['Public_prin'],
                'Public_sec' => $j['Public_sec'],
            ]);
        }
    }

    /**
     * Import competitions and return competition codes
     */
    private function importCompetitions(array $competitions): array
    {
        $codes = [];

        foreach ($competitions as $c) {
            $sql = "INSERT INTO kp_competition (
                        Code, Code_saison, Code_niveau, Libelle, Soustitre, Soustitre2, Web, BandeauLink, LogoLink,
                        SponsorLink, En_actif, Titre_actif, Bandeau_actif, Logo_actif, Sponsor_actif, Kpi_ffck_actif,
                        ToutGroup, TouteSaisons, Code_ref, GroupOrder, Code_typeclt, Age_min, Age_max, Sexe, Code_tour,
                        Nb_equipes, Verrou, Statut, Qualifies, Elimines, Points, goalaverage, Date_calcul, Mode_calcul,
                        Date_publication, Date_publication_calcul, Mode_publication_calcul, Code_uti_calcul,
                        Code_uti_publication, Publication, Date_publi, Code_uti_publi, commentairesCompet
                    ) VALUES (
                        :Code, :Code_saison, :Code_niveau, :Libelle, :Soustitre, :Soustitre2, :Web, :BandeauLink, :LogoLink,
                        :SponsorLink, :En_actif, :Titre_actif, :Bandeau_actif, :Logo_actif, :Sponsor_actif, :Kpi_ffck_actif,
                        :ToutGroup, :TouteSaisons, :Code_ref, :GroupOrder, :Code_typeclt, :Age_min, :Age_max, :Sexe, :Code_tour,
                        :Nb_equipes, :Verrou, :Statut, :Qualifies, :Elimines, :Points, :goalaverage, :Date_calcul, :Mode_calcul,
                        :Date_publication, :Date_publication_calcul, :Mode_publication_calcul, :Code_uti_calcul,
                        :Code_uti_publication, :Publication, :Date_publi, :Code_uti_publi, :commentairesCompet
                    )
                    ON DUPLICATE KEY UPDATE
                        Code_niveau = VALUES(Code_niveau),
                        Libelle = VALUES(Libelle),
                        Soustitre = VALUES(Soustitre),
                        Soustitre2 = VALUES(Soustitre2),
                        Web = VALUES(Web),
                        BandeauLink = VALUES(BandeauLink),
                        LogoLink = VALUES(LogoLink),
                        SponsorLink = VALUES(SponsorLink),
                        En_actif = VALUES(En_actif),
                        Titre_actif = VALUES(Titre_actif),
                        Bandeau_actif = VALUES(Bandeau_actif),
                        Logo_actif = VALUES(Logo_actif),
                        Sponsor_actif = VALUES(Sponsor_actif),
                        Kpi_ffck_actif = VALUES(Kpi_ffck_actif),
                        ToutGroup = VALUES(ToutGroup),
                        TouteSaisons = VALUES(TouteSaisons),
                        Code_ref = VALUES(Code_ref),
                        GroupOrder = VALUES(GroupOrder),
                        Code_typeclt = VALUES(Code_typeclt),
                        Age_min = VALUES(Age_min),
                        Age_max = VALUES(Age_max),
                        Sexe = VALUES(Sexe),
                        Code_tour = VALUES(Code_tour),
                        Nb_equipes = VALUES(Nb_equipes),
                        Verrou = VALUES(Verrou),
                        Statut = VALUES(Statut),
                        Qualifies = VALUES(Qualifies),
                        Elimines = VALUES(Elimines),
                        Points = VALUES(Points),
                        goalaverage = VALUES(goalaverage),
                        Date_calcul = VALUES(Date_calcul),
                        Mode_calcul = VALUES(Mode_calcul),
                        Date_publication = VALUES(Date_publication),
                        Date_publication_calcul = VALUES(Date_publication_calcul),
                        Mode_publication_calcul = VALUES(Mode_publication_calcul),
                        Code_uti_calcul = VALUES(Code_uti_calcul),
                        Code_uti_publication = VALUES(Code_uti_publication),
                        Publication = VALUES(Publication),
                        Date_publi = VALUES(Date_publi),
                        Code_uti_publi = VALUES(Code_uti_publi),
                        commentairesCompet = VALUES(commentairesCompet)";

            $this->connection->executeStatement($sql, [
                'Code' => $c['Code'],
                'Code_saison' => $c['Code_saison'],
                'Code_niveau' => $c['Code_niveau'],
                'Libelle' => $c['Libelle'],
                'Soustitre' => $c['Soustitre'],
                'Soustitre2' => $c['Soustitre2'],
                'Web' => $c['Web'],
                'BandeauLink' => $c['BandeauLink'],
                'LogoLink' => $c['LogoLink'],
                'SponsorLink' => $c['SponsorLink'],
                'En_actif' => $c['En_actif'],
                'Titre_actif' => $c['Titre_actif'],
                'Bandeau_actif' => $c['Bandeau_actif'],
                'Logo_actif' => $c['Logo_actif'],
                'Sponsor_actif' => $c['Sponsor_actif'],
                'Kpi_ffck_actif' => $c['Kpi_ffck_actif'],
                'ToutGroup' => $c['ToutGroup'],
                'TouteSaisons' => $c['TouteSaisons'],
                'Code_ref' => $c['Code_ref'],
                'GroupOrder' => $c['GroupOrder'],
                'Code_typeclt' => $c['Code_typeclt'],
                'Age_min' => $c['Age_min'],
                'Age_max' => $c['Age_max'],
                'Sexe' => $c['Sexe'],
                'Code_tour' => $c['Code_tour'],
                'Nb_equipes' => $c['Nb_equipes'],
                'Verrou' => $c['Verrou'],
                'Statut' => $c['Statut'],
                'Qualifies' => $c['Qualifies'],
                'Elimines' => $c['Elimines'],
                'Points' => $c['Points'],
                'goalaverage' => $c['goalaverage'],
                'Date_calcul' => $c['Date_calcul'],
                'Mode_calcul' => $c['Mode_calcul'],
                'Date_publication' => $c['Date_publication'],
                'Date_publication_calcul' => $c['Date_publication_calcul'],
                'Mode_publication_calcul' => $c['Mode_publication_calcul'],
                'Code_uti_calcul' => $c['Code_uti_calcul'],
                'Code_uti_publication' => $c['Code_uti_publication'],
                'Publication' => $c['Publication'],
                'Date_publi' => $c['Date_publi'],
                'Code_uti_publi' => $c['Code_uti_publi'],
                'commentairesCompet' => $c['commentairesCompet'],
            ]);

            $codes[] = $c['Code'];
        }

        return array_unique($codes);
    }

    /**
     * Import competition teams and return team IDs
     */
    private function importCompetitionTeams(array $teams, array $competitionCodes): array
    {
        // Delete existing teams for these competitions
        if (!empty($competitionCodes)) {
            $placeholders = implode(',', array_fill(0, count($competitionCodes), '?'));
            $sql = "DELETE FROM kp_competition_equipe WHERE Code_compet IN ($placeholders)";
            $this->connection->executeStatement($sql, $competitionCodes);
        }

        $teamIds = [];

        foreach ($teams as $t) {
            $sql = "INSERT INTO kp_competition_equipe (
                        Id, Code_compet, Code_saison, Libelle, Code_club, logo, color1, color2, colortext,
                        Numero, Poule, Tirage, Pts, Clt, J, G, N, P, F, Plus, Moins, Diff, PtsNiveau, CltNiveau,
                        Id_dupli, Pts_publi, Clt_publi, J_publi, G_publi, N_publi, P_publi, F_publi,
                        Plus_publi, Moins_publi, Diff_publi, PtsNiveau_publi, CltNiveau_publi
                    ) VALUES (
                        :Id, :Code_compet, :Code_saison, :Libelle, :Code_club, :logo, :color1, :color2, :colortext,
                        :Numero, :Poule, :Tirage, :Pts, :Clt, :J, :G, :N, :P, :F, :Plus, :Moins, :Diff, :PtsNiveau, :CltNiveau,
                        :Id_dupli, :Pts_publi, :Clt_publi, :J_publi, :G_publi, :N_publi, :P_publi, :F_publi,
                        :Plus_publi, :Moins_publi, :Diff_publi, :PtsNiveau_publi, :CltNiveau_publi
                    )";

            $this->connection->executeStatement($sql, [
                'Id' => $t['Id'],
                'Code_compet' => $t['Code_compet'],
                'Code_saison' => $t['Code_saison'],
                'Libelle' => $t['Libelle'],
                'Code_club' => $t['Code_club'],
                'logo' => $t['logo'],
                'color1' => $t['color1'],
                'color2' => $t['color2'],
                'colortext' => $t['colortext'],
                'Numero' => $t['Numero'],
                'Poule' => $t['Poule'],
                'Tirage' => $t['Tirage'],
                'Pts' => $t['Pts'],
                'Clt' => $t['Clt'],
                'J' => $t['J'],
                'G' => $t['G'],
                'N' => $t['N'],
                'P' => $t['P'],
                'F' => $t['F'],
                'Plus' => $t['Plus'],
                'Moins' => $t['Moins'],
                'Diff' => $t['Diff'],
                'PtsNiveau' => $t['PtsNiveau'],
                'CltNiveau' => $t['CltNiveau'],
                'Id_dupli' => $t['Id_dupli'],
                'Pts_publi' => $t['Pts_publi'],
                'Clt_publi' => $t['Clt_publi'],
                'J_publi' => $t['J_publi'],
                'G_publi' => $t['G_publi'],
                'N_publi' => $t['N_publi'],
                'P_publi' => $t['P_publi'],
                'F_publi' => $t['F_publi'],
                'Plus_publi' => $t['Plus_publi'],
                'Moins_publi' => $t['Moins_publi'],
                'Diff_publi' => $t['Diff_publi'],
                'PtsNiveau_publi' => $t['PtsNiveau_publi'],
                'CltNiveau_publi' => $t['CltNiveau_publi'],
            ]);

            $teamIds[] = $t['Id'];
        }

        return $teamIds;
    }

    /**
     * Import competition team init
     */
    private function importCompetitionTeamInit(array $inits, array $teamIds): void
    {
        if (!empty($teamIds)) {
            $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
            $sql = "DELETE FROM kp_competition_equipe_init WHERE Id IN ($placeholders)";
            $this->connection->executeStatement($sql, $teamIds);
        }

        foreach ($inits as $i) {
            $sql = "INSERT INTO kp_competition_equipe_init (Id, Pts, Clt, J, G, N, P, F, Plus, Moins, Diff)
                    VALUES (:Id, :Pts, :Clt, :J, :G, :N, :P, :F, :Plus, :Moins, :Diff)";

            $this->connection->executeStatement($sql, [
                'Id' => $i['Id'],
                'Pts' => $i['Pts'],
                'Clt' => $i['Clt'],
                'J' => $i['J'],
                'G' => $i['G'],
                'N' => $i['N'],
                'P' => $i['P'],
                'F' => $i['F'],
                'Plus' => $i['Plus'],
                'Moins' => $i['Moins'],
                'Diff' => $i['Diff'],
            ]);
        }
    }

    /**
     * Import competition team players
     */
    private function importCompetitionTeamPlayers(array $players, array $teamIds): void
    {
        if (!empty($teamIds)) {
            $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
            $sql = "DELETE FROM kp_competition_equipe_joueur WHERE Id_equipe IN ($placeholders)";
            $this->connection->executeStatement($sql, $teamIds);
        }

        foreach ($players as $p) {
            $sql = "INSERT INTO kp_competition_equipe_joueur (Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine)
                    VALUES (:Id_equipe, :Matric, :Nom, :Prenom, :Sexe, :Categ, :Numero, :Capitaine)";

            $this->connection->executeStatement($sql, [
                'Id_equipe' => $p['Id_equipe'],
                'Matric' => $p['Matric'],
                'Nom' => $p['Nom'],
                'Prenom' => $p['Prenom'],
                'Sexe' => $p['Sexe'],
                'Categ' => $p['Categ'],
                'Numero' => $p['Numero'],
                'Capitaine' => $p['Capitaine'],
            ]);
        }
    }

    /**
     * Import competition team journee stats
     */
    private function importCompetitionTeamJournee(array $journees, array $teamIds): void
    {
        if (!empty($teamIds)) {
            $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
            $sql = "DELETE FROM kp_competition_equipe_journee WHERE Id IN ($placeholders)";
            $this->connection->executeStatement($sql, $teamIds);
        }

        foreach ($journees as $j) {
            $sql = "INSERT INTO kp_competition_equipe_journee (
                        Id, Id_journee, Pts, Clt, J, G, N, P, F, Plus, Moins, Diff, PtsNiveau, CltNiveau,
                        Pts_publi, Clt_publi, J_publi, G_publi, N_publi, P_publi, F_publi,
                        Plus_publi, Moins_publi, Diff_publi, PtsNiveau_publi, CltNiveau_publi
                    ) VALUES (
                        :Id, :Id_journee, :Pts, :Clt, :J, :G, :N, :P, :F, :Plus, :Moins, :Diff, :PtsNiveau, :CltNiveau,
                        :Pts_publi, :Clt_publi, :J_publi, :G_publi, :N_publi, :P_publi, :F_publi,
                        :Plus_publi, :Moins_publi, :Diff_publi, :PtsNiveau_publi, :CltNiveau_publi
                    )";

            $this->connection->executeStatement($sql, [
                'Id' => $j['Id'],
                'Id_journee' => $j['Id_journee'],
                'Pts' => $j['Pts'],
                'Clt' => $j['Clt'],
                'J' => $j['J'],
                'G' => $j['G'],
                'N' => $j['N'],
                'P' => $j['P'],
                'F' => $j['F'],
                'Plus' => $j['Plus'],
                'Moins' => $j['Moins'],
                'Diff' => $j['Diff'],
                'PtsNiveau' => $j['PtsNiveau'],
                'CltNiveau' => $j['CltNiveau'],
                'Pts_publi' => $j['Pts_publi'],
                'Clt_publi' => $j['Clt_publi'],
                'J_publi' => $j['J_publi'],
                'G_publi' => $j['G_publi'],
                'N_publi' => $j['N_publi'],
                'P_publi' => $j['P_publi'],
                'F_publi' => $j['F_publi'],
                'Plus_publi' => $j['Plus_publi'],
                'Moins_publi' => $j['Moins_publi'],
                'Diff_publi' => $j['Diff_publi'],
                'PtsNiveau_publi' => $j['PtsNiveau_publi'],
                'CltNiveau_publi' => $j['CltNiveau_publi'],
            ]);
        }
    }

    /**
     * Import competition team niveau stats
     */
    private function importCompetitionTeamNiveau(array $niveaux, array $teamIds): void
    {
        if (!empty($teamIds)) {
            $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
            $sql = "DELETE FROM kp_competition_equipe_niveau WHERE Id IN ($placeholders)";
            $this->connection->executeStatement($sql, $teamIds);
        }

        foreach ($niveaux as $n) {
            $sql = "INSERT INTO kp_competition_equipe_niveau (
                        Id, Niveau, Pts, Clt, J, G, N, P, F, Plus, Moins, Diff, PtsNiveau, CltNiveau,
                        Pts_publi, Clt_publi, J_publi, G_publi, N_publi, P_publi, F_publi,
                        Plus_publi, Moins_publi, Diff_publi, PtsNiveau_publi, CltNiveau_publi
                    ) VALUES (
                        :Id, :Niveau, :Pts, :Clt, :J, :G, :N, :P, :F, :Plus, :Moins, :Diff, :PtsNiveau, :CltNiveau,
                        :Pts_publi, :Clt_publi, :J_publi, :G_publi, :N_publi, :P_publi, :F_publi,
                        :Plus_publi, :Moins_publi, :Diff_publi, :PtsNiveau_publi, :CltNiveau_publi
                    )";

            $this->connection->executeStatement($sql, [
                'Id' => $n['Id'],
                'Niveau' => $n['Niveau'],
                'Pts' => $n['Pts'],
                'Clt' => $n['Clt'],
                'J' => $n['J'],
                'G' => $n['G'],
                'N' => $n['N'],
                'P' => $n['P'],
                'F' => $n['F'],
                'Plus' => $n['Plus'],
                'Moins' => $n['Moins'],
                'Diff' => $n['Diff'],
                'PtsNiveau' => $n['PtsNiveau'],
                'CltNiveau' => $n['CltNiveau'],
                'Pts_publi' => $n['Pts_publi'],
                'Clt_publi' => $n['Clt_publi'],
                'J_publi' => $n['J_publi'],
                'G_publi' => $n['G_publi'],
                'N_publi' => $n['N_publi'],
                'P_publi' => $n['P_publi'],
                'F_publi' => $n['F_publi'],
                'Plus_publi' => $n['Plus_publi'],
                'Moins_publi' => $n['Moins_publi'],
                'Diff_publi' => $n['Diff_publi'],
                'PtsNiveau_publi' => $n['PtsNiveau_publi'],
                'CltNiveau_publi' => $n['CltNiveau_publi'],
            ]);
        }
    }

    /**
     * Import matches and return match IDs
     */
    private function importMatches(array $matches, array $journeeIds): array
    {
        if (!empty($journeeIds)) {
            $placeholders = implode(',', array_fill(0, count($journeeIds), '?'));
            $sql = "DELETE FROM kp_match WHERE Id_journee IN ($placeholders)";
            $this->connection->executeStatement($sql, $journeeIds);
        }

        $matchIds = [];

        foreach ($matches as $m) {
            $sql = "INSERT INTO kp_match (
                        Id, Id_journee, Libelle, Type, Statut, Date_match, Heure_match, Heure_fin, Terrain,
                        Numero_ordre, Periode, Id_equipeA, Id_equipeB, ColorA, ColorB, ScoreA, ScoreB,
                        ScoreDetailA, ScoreDetailB, CoeffA, CoeffB, Commentaires_officiels, Commentaires,
                        Arbitre_principal, Arbitre_secondaire, Matric_arbitre_principal, Matric_arbitre_secondaire,
                        Secretaire, Chronometre, Timeshoot, Ligne1, Ligne2, Publication, Code_uti, Validation
                    ) VALUES (
                        :Id, :Id_journee, :Libelle, :Type, :Statut, :Date_match, :Heure_match, :Heure_fin, :Terrain,
                        :Numero_ordre, :Periode, :Id_equipeA, :Id_equipeB, :ColorA, :ColorB, :ScoreA, :ScoreB,
                        :ScoreDetailA, :ScoreDetailB, :CoeffA, :CoeffB, :Commentaires_officiels, :Commentaires,
                        :Arbitre_principal, :Arbitre_secondaire, :Matric_arbitre_principal, :Matric_arbitre_secondaire,
                        :Secretaire, :Chronometre, :Timeshoot, :Ligne1, :Ligne2, :Publication, :Code_uti, :Validation
                    )";

            $this->connection->executeStatement($sql, [
                'Id' => $m['Id'],
                'Id_journee' => $m['Id_journee'],
                'Libelle' => $m['Libelle'],
                'Type' => $m['Type'],
                'Statut' => $m['Statut'],
                'Date_match' => $m['Date_match'],
                'Heure_match' => $m['Heure_match'],
                'Heure_fin' => $m['Heure_fin'],
                'Terrain' => $m['Terrain'],
                'Numero_ordre' => $m['Numero_ordre'],
                'Periode' => $m['Periode'],
                'Id_equipeA' => $m['Id_equipeA'],
                'Id_equipeB' => $m['Id_equipeB'],
                'ColorA' => $m['ColorA'],
                'ColorB' => $m['ColorB'],
                'ScoreA' => $m['ScoreA'],
                'ScoreB' => $m['ScoreB'],
                'ScoreDetailA' => $m['ScoreDetailA'],
                'ScoreDetailB' => $m['ScoreDetailB'],
                'CoeffA' => $m['CoeffA'],
                'CoeffB' => $m['CoeffB'],
                'Commentaires_officiels' => $m['Commentaires_officiels'],
                'Commentaires' => $m['Commentaires'],
                'Arbitre_principal' => $m['Arbitre_principal'],
                'Arbitre_secondaire' => $m['Arbitre_secondaire'],
                'Matric_arbitre_principal' => $m['Matric_arbitre_principal'],
                'Matric_arbitre_secondaire' => $m['Matric_arbitre_secondaire'],
                'Secretaire' => $m['Secretaire'],
                'Chronometre' => $m['Chronometre'],
                'Timeshoot' => $m['Timeshoot'],
                'Ligne1' => $m['Ligne1'],
                'Ligne2' => $m['Ligne2'],
                'Publication' => $m['Publication'],
                'Code_uti' => $m['Code_uti'],
                'Validation' => $m['Validation'],
            ]);

            $matchIds[] = $m['Id'];
        }

        return $matchIds;
    }

    /**
     * Import match details
     */
    private function importMatchDetails(array $details, array $matchIds): void
    {
        if (!empty($matchIds)) {
            $placeholders = implode(',', array_fill(0, count($matchIds), '?'));
            $sql = "DELETE FROM kp_match_detail WHERE Id_match IN ($placeholders)";
            $this->connection->executeStatement($sql, $matchIds);
        }

        foreach ($details as $d) {
            $sql = "INSERT INTO kp_match_detail (Id, Id_match, Periode, Temps, Id_evt_match, motif, Competiteur, Numero, Equipe_A_B, date_insert)
                    VALUES (:Id, :Id_match, :Periode, :Temps, :Id_evt_match, :motif, :Competiteur, :Numero, :Equipe_A_B, :date_insert)";

            $this->connection->executeStatement($sql, [
                'Id' => $d['Id'],
                'Id_match' => $d['Id_match'],
                'Periode' => $d['Periode'],
                'Temps' => $d['Temps'],
                'Id_evt_match' => $d['Id_evt_match'],
                'motif' => $d['motif'],
                'Competiteur' => $d['Competiteur'],
                'Numero' => $d['Numero'],
                'Equipe_A_B' => $d['Equipe_A_B'],
                'date_insert' => $d['date_insert'],
            ]);
        }
    }

    /**
     * Import match players
     */
    private function importMatchPlayers(array $players, array $matchIds): void
    {
        if (!empty($matchIds)) {
            $placeholders = implode(',', array_fill(0, count($matchIds), '?'));
            $sql = "DELETE FROM kp_match_joueur WHERE Id_match IN ($placeholders)";
            $this->connection->executeStatement($sql, $matchIds);
        }

        foreach ($players as $p) {
            $sql = "INSERT INTO kp_match_joueur (Id_match, Matric, Numero, Equipe, Capitaine)
                    VALUES (:Id_match, :Matric, :Numero, :Equipe, :Capitaine)";

            $this->connection->executeStatement($sql, [
                'Id_match' => $p['Id_match'],
                'Matric' => $p['Matric'],
                'Numero' => $p['Numero'],
                'Equipe' => $p['Equipe'],
                'Capitaine' => $p['Capitaine'],
            ]);
        }
    }

    /**
     * Import chrono data
     */
    private function importChrono(array $chronos, array $matchIds): void
    {
        if (!empty($matchIds)) {
            $placeholders = implode(',', array_fill(0, count($matchIds), '?'));
            $sql = "DELETE FROM kp_chrono WHERE IdMatch IN ($placeholders)";
            $this->connection->executeStatement($sql, $matchIds);
        }

        foreach ($chronos as $c) {
            $sql = "INSERT INTO kp_chrono (IdMatch, action, start_time, start_time_server, run_time, max_time)
                    VALUES (:IdMatch, :action, :start_time, :start_time_server, :run_time, :max_time)";

            $this->connection->executeStatement($sql, [
                'IdMatch' => $c['IdMatch'],
                'action' => $c['action'],
                'start_time' => $c['start_time'],
                'start_time_server' => $c['start_time_server'],
                'run_time' => $c['run_time'],
                'max_time' => $c['max_time'],
            ]);
        }
    }
}
