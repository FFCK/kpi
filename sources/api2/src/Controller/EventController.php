<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/events/{mode}', name: 'events', methods: ['GET'])]
    #[OA\Get(
        path: '/events/{mode}',
        summary: 'Get events list',
        tags: ['Events'],
        parameters: [
            new OA\Parameter(
                name: 'mode',
                in: 'path',
                required: true,
                description: 'Event mode: std (standard tournaments), champ (championships), or all (all events)',
                schema: new OA\Schema(type: 'string', enum: ['std', 'champ', 'all'])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns list of events',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 123),
                            new OA\Property(property: 'libelle', type: 'string', example: 'Tournoi National'),
                            new OA\Property(property: 'place', type: 'string', example: 'Paris'),
                            new OA\Property(property: 'logo', type: 'string', nullable: true, example: 'logo/event123.png')
                        ]
                    )
                )
            ),
            new OA\Response(response: 403, description: 'Invalid mode')
        ]
    )]
    public function getEvents(string $mode): JsonResponse
    {
        if (!in_array($mode, ['std', 'champ', 'all'])) {
            return new JsonResponse(['error' => 'Invalid mode'], 403);
        }

        $conn = $this->entityManager->getConnection();

        if ($mode === 'all') {
            $sql = "SELECT Id id, Libelle libelle, Lieu place, logo
                FROM kp_evenement
                WHERE Publication = 'O'
                ORDER BY Date_debut DESC, Id DESC";
            $stmt = $conn->prepare($sql);
        } elseif ($mode === 'std') {
            $sql = "SELECT Id id, Libelle libelle, Lieu place, logo
                FROM kp_evenement
                WHERE app = 'O'
                ORDER BY Date_debut DESC, Id DESC";
            $stmt = $conn->prepare($sql);
        } else { // champ
            $sql = "SELECT j.Id id, j.Nom libelle, j.Lieu place,
                CASE
                    WHEN (c.BandeauLink != '' AND c.Bandeau_actif = 'O') THEN CONCAT('logo/', c.BandeauLink)
                    WHEN (c.LogoLink != '' AND c.Logo_actif = 'O') THEN CONCAT('logo/', c.LogoLink)
                    ELSE NULL
                END logo
                FROM kp_journee j
                JOIN kp_competition c ON (j.Code_competition = c.Code AND j.Code_saison = c.Code_saison)
                JOIN kp_groupe g ON (c.Code_ref = g.Groupe)
                JOIN kp_saison s ON (c.Code_saison = s.Code)
                WHERE c.Code_typeclt = 'CHPT'
                AND c.Publication = 'O'
                AND j.Publication = 'O'
                AND s.Etat = 'A'
                ORDER BY j.Date_debut DESC, g.section, g.ordre, c.Code, j.Id";
            $stmt = $conn->prepare($sql);
        }

        $result = $stmt->executeQuery();
        $events = $result->fetchAllAssociative();

        return new JsonResponse($events);
    }

    #[Route('/event/{id}', name: 'event', methods: ['GET'])]
    #[OA\Get(
        path: '/event/{id}',
        summary: 'Get single event',
        tags: ['Events'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Event ID',
                schema: new OA\Schema(type: 'integer', example: 123)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns event details',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 123),
                            new OA\Property(property: 'libelle', type: 'string', example: 'Tournoi National'),
                            new OA\Property(property: 'place', type: 'string', example: 'Paris'),
                            new OA\Property(property: 'logo', type: 'string', nullable: true, example: 'logo/event123.png')
                        ]
                    )
                )
            )
        ]
    )]
    public function getEvent(int $id): JsonResponse
    {
        $conn = $this->entityManager->getConnection();

        if ($id < 3000) {
            $sql = "SELECT Id id, Libelle libelle, Lieu place, logo
                FROM kp_evenement
                WHERE app = 'O'
                AND Id = ?
                ORDER BY Id DESC";
        } else {
            $sql = "SELECT j.Id id, j.Nom libelle, j.Lieu place,
                CASE
                    WHEN (c.BandeauLink != '' AND c.Bandeau_actif = 'O') THEN CONCAT('logo/', c.BandeauLink)
                    WHEN (c.LogoLink != '' AND c.Logo_actif = 'O') THEN CONCAT('logo/', c.LogoLink)
                    ELSE NULL
                END logo
                FROM kp_journee j
                JOIN kp_competition c ON (j.Code_competition = c.Code AND j.Code_saison = c.Code_saison)
                WHERE c.Code_typeclt = 'CHPT'
                AND c.Publication = 'O'
                AND j.Publication = 'O'
                AND j.Id = ?
                ORDER BY j.Id DESC";
        }

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([$id]);
        $event = $result->fetchAllAssociative();

        return new JsonResponse($event);
    }
}
