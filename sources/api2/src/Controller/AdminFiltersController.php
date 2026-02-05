<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\DBAL\Connection;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Admin Filters Controller
 *
 * Provides filter data (seasons, competitions, events) for admin pages.
 * All results are filtered according to the authenticated user's restrictions.
 */
#[Route('/admin/filters')]
#[OA\Tag(name: '21. App4 - Filters')]
class AdminFiltersController extends AbstractController
{
    private const SECTIONS = [
        1 => 'Competitions_Internationales',
        2 => 'Competitions_Nationales',
        3 => 'Competitions_Regionales',
        4 => 'Tournois_Internationaux',
        5 => 'Continents',
        100 => 'Divers',
    ];

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * Get available seasons (filtered by user restrictions)
     */
    #[Route('/seasons', name: 'admin_filters_seasons', methods: ['GET'])]
    public function getSeasons(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $sql = "SELECT Code, Etat FROM kp_saison WHERE Code > '1900' ORDER BY Code DESC";
        $result = $this->connection->executeQuery($sql);
        $rows = $result->fetchAllAssociative();

        $allowedSeasons = $user->getAllowedSeasons();
        $activeSeason = null;

        $seasons = [];
        foreach ($rows as $row) {
            // Filter by user restrictions
            if ($allowedSeasons !== null && !in_array($row['Code'], $allowedSeasons)) {
                continue;
            }
            $isActive = $row['Etat'] === 'A';
            if ($isActive) {
                $activeSeason = $row['Code'];
            }
            $seasons[] = [
                'code' => $row['Code'],
                'active' => $isActive,
            ];
        }

        return $this->json([
            'seasons' => $seasons,
            'activeSeason' => $activeSeason,
        ]);
    }

    /**
     * Get competitions for a season (filtered by user restrictions)
     * Returns competitions grouped by section, with enActif and codeTypeclt
     */
    #[Route('/competitions', name: 'admin_filters_competitions', methods: ['GET'])]
    public function getCompetitions(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $season = $request->query->get('season');
        if (!$season) {
            $season = $this->getActiveSeason();
        }

        // Check season access
        $allowedSeasons = $user->getAllowedSeasons();
        if ($allowedSeasons !== null && !in_array($season, $allowedSeasons)) {
            return $this->json(['message' => 'Access denied for this season'], 403);
        }

        $sql = "SELECT c.Code, c.Libelle, c.Soustitre, c.Soustitre2,
                       c.Titre_actif, c.Code_ref, c.Code_tour, c.Code_niveau,
                       c.En_actif, c.Code_typeclt,
                       g.section, g.ordre
                FROM kp_competition c
                LEFT JOIN kp_groupe g ON c.Code_ref = g.Groupe
                WHERE c.Code_saison = ?
                ORDER BY g.section, g.ordre,
                    COALESCE(c.Code_ref, 'z'), c.Code_tour, c.GroupOrder, c.Code";

        $result = $this->connection->executeQuery($sql, [$season]);
        $rows = $result->fetchAllAssociative();

        $allowedCompetitions = $user->getAllowedCompetitions();

        $groups = [];
        foreach ($rows as $row) {
            // Filter by user restrictions
            if ($allowedCompetitions !== null && !in_array($row['Code'], $allowedCompetitions)) {
                continue;
            }

            $section = (int) ($row['section'] ?? 100);
            $sectionLabel = self::SECTIONS[$section] ?? 'Divers';

            if (!isset($groups[$section])) {
                $groups[$section] = [
                    'section' => $section,
                    'sectionLabel' => $sectionLabel,
                    'competitions' => [],
                ];
            }

            $groups[$section]['competitions'][] = [
                'code' => $row['Code'],
                'libelle' => $row['Libelle'],
                'soustitre' => $row['Soustitre'] ?: null,
                'soustitre2' => $row['Soustitre2'] ?: null,
                'titreActif' => $row['Titre_actif'] === 'O',
                'enActif' => $row['En_actif'] === 'O',
                'codeTypeclt' => $row['Code_typeclt'] ?: null,
                'codeRef' => $row['Code_ref'] ?: null,
            ];
        }

        return $this->json([
            'season' => $season,
            'groups' => array_values($groups),
        ]);
    }

    /**
     * Get events for a season (filtered by user restrictions)
     * Only returns events that have competitions the user has access to in the given season
     */
    #[Route('/events', name: 'admin_filters_events', methods: ['GET'])]
    public function getEvents(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $season = $request->query->get('season');

        $allowedEvents = $user->getAllowedEvents();
        $allowedCompetitions = $user->getAllowedCompetitions();

        // Build query: only events that have competitions in the given season
        // linked through journée-événement relationships
        $params = [];
        $sql = "SELECT DISTINCT e.Id, e.Libelle, e.Date_debut, e.Date_fin, e.Publication
                FROM kp_evenement e
                INNER JOIN kp_evenement_journee ej ON ej.Id_evenement = e.Id
                INNER JOIN kp_journee j ON j.Id = ej.Id_journee";

        $where = [];
        if ($season) {
            $where[] = 'j.Code_saison = ?';
            $params[] = $season;
        }

        // Filter by user competition restrictions
        if ($allowedCompetitions !== null) {
            $placeholders = implode(',', array_fill(0, count($allowedCompetitions), '?'));
            $where[] = "j.Code_competition IN ($placeholders)";
            $params = array_merge($params, $allowedCompetitions);
        }

        // Filter by user event restrictions
        if ($allowedEvents !== null) {
            $placeholders = implode(',', array_fill(0, count($allowedEvents), '?'));
            $where[] = "e.Id IN ($placeholders)";
            $params = array_merge($params, $allowedEvents);
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY e.Date_debut DESC, e.Libelle';

        $result = $this->connection->executeQuery($sql, $params);
        $rows = $result->fetchAllAssociative();

        $events = [];
        foreach ($rows as $row) {
            $events[] = [
                'id' => (int) $row['Id'],
                'libelle' => $row['Libelle'],
                'dateDebut' => $row['Date_debut'],
                'dateFin' => $row['Date_fin'],
                'publication' => $row['Publication'] === 'O',
            ];
        }

        return $this->json([
            'events' => $events,
        ]);
    }

    /**
     * Get match IDs for a competition (used for match sheet PDF links)
     */
    #[Route('/match-ids', name: 'admin_filters_match_ids', methods: ['GET'])]
    public function getMatchIds(Request $request): JsonResponse
    {
        $season = $request->query->get('season');
        $competition = $request->query->get('competition');

        if (!$season || !$competition) {
            return $this->json(['message' => 'season and competition are required'], 400);
        }

        /** @var User $user */
        $user = $this->getUser();

        // Check competition access
        $allowedCompetitions = $user->getAllowedCompetitions();
        if ($allowedCompetitions !== null && !in_array($competition, $allowedCompetitions)) {
            return $this->json(['message' => 'Access denied for this competition'], 403);
        }

        $sql = "SELECT m.Id
                FROM kp_journee j
                INNER JOIN kp_match m ON j.Id = m.Id_journee
                WHERE j.Code_saison = ?
                AND j.Code_competition = ?
                ORDER BY m.Numero_ordre";

        $result = $this->connection->executeQuery($sql, [$season, $competition]);
        $rows = $result->fetchAllAssociative();

        $matchIds = array_map(fn($row) => (int) $row['Id'], $rows);

        return $this->json([
            'matchIds' => $matchIds,
        ]);
    }

    /**
     * Get competitions linked to an event (via journée-événement relationships)
     */
    #[Route('/event-competitions', name: 'admin_filters_event_competitions', methods: ['GET'])]
    public function getEventCompetitions(Request $request): JsonResponse
    {
        $eventId = $request->query->get('eventId');

        if (!$eventId) {
            return $this->json(['message' => 'eventId is required'], 400);
        }

        /** @var User $user */
        $user = $this->getUser();

        // Check event access
        $allowedEvents = $user->getAllowedEvents();
        if ($allowedEvents !== null && !in_array((int) $eventId, $allowedEvents)) {
            return $this->json(['message' => 'Access denied for this event'], 403);
        }

        $sql = "SELECT DISTINCT c.Code, c.Libelle, c.Code_ref
                FROM kp_competition c
                INNER JOIN kp_journee j ON j.Code_competition = c.Code AND j.Code_saison = c.Code_saison
                INNER JOIN kp_evenement_journee ej ON ej.Id_journee = j.Id
                WHERE ej.Id_evenement = ?
                ORDER BY c.Code";

        $result = $this->connection->executeQuery($sql, [(int) $eventId]);
        $rows = $result->fetchAllAssociative();

        $allowedCompetitions = $user->getAllowedCompetitions();

        $competitions = [];
        foreach ($rows as $row) {
            // Filter by user restrictions
            if ($allowedCompetitions !== null && !in_array($row['Code'], $allowedCompetitions)) {
                continue;
            }

            $competitions[] = [
                'code' => $row['Code'],
                'libelle' => $row['Libelle'],
                'codeRef' => $row['Code_ref'] ?: null,
            ];
        }

        return $this->json([
            'eventId' => (int) $eventId,
            'competitions' => $competitions,
        ]);
    }

    private function getActiveSeason(): string
    {
        $sql = "SELECT Code FROM kp_saison WHERE Etat = 'A' LIMIT 1";
        $result = $this->connection->executeQuery($sql);
        return $result->fetchOne() ?: (string) date('Y');
    }
}
