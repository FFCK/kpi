<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class EventController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/events/{mode}', name: 'events', methods: ['GET'])]
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
