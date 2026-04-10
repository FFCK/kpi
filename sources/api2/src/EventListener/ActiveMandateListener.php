<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Reads the X-Active-Mandate header from the request and applies
 * the corresponding mandate filters to the authenticated User entity.
 *
 * This ensures that all controllers using $user->getAllowedSeasons() etc.
 * automatically get the mandate's restrictions instead of the user's base filters.
 */
#[AsEventListener(event: KernelEvents::CONTROLLER, priority: 0)]
class ActiveMandateListener
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly Connection $connection
    ) {
    }

    public function __invoke(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $mandateId = $request->headers->get('X-Active-Mandate');

        if ($mandateId === null || $mandateId === '') {
            return;
        }

        $mandateId = (int) $mandateId;
        if ($mandateId <= 0) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return;
        }

        // Load the mandate and verify it belongs to this user
        $row = $this->connection->fetchAssociative(
            'SELECT id, niveau, filtre_saison, filtre_competition, limitation_equipe_club, filtre_journee, id_evenement
             FROM kp_user_mandat WHERE id = ? AND user_code = ?',
            [$mandateId, $user->getCode()]
        );

        if (!$row) {
            return;
        }

        $user->applyMandate(
            (int) $row['id'],
            (int) $row['niveau'],
            $row['filtre_saison'],
            $row['filtre_competition'],
            $row['limitation_equipe_club'],
            $row['filtre_journee'],
            $row['id_evenement']
        );
    }
}
