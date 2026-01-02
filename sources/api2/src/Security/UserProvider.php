<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Custom user provider that loads users from legacy kp_user table
 */
class UserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $sql = "SELECT u.Code, u.Pwd, u.Niveau, u.Filtre_competition_sql, u.Limitation_equipe_club,
                       l.Nom, l.Prenom, l.Numero_club
                FROM kp_user u
                LEFT JOIN kp_licence l ON u.Code = l.Matric
                WHERE u.Code = ?";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([$identifier]);
        $row = $result->fetchAssociative();

        if (!$row) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        return $this->createUserFromRow($row);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    private function createUserFromRow(array $row): User
    {
        $user = new User();
        $user->setCode($row['Code']);
        $user->setPassword($row['Pwd'] ?? '');
        $user->setNiveau((int) ($row['Niveau'] ?? 100));
        $user->setNom($row['Nom'] ?? null);
        $user->setPrenom($row['Prenom'] ?? null);
        $user->setFiltreCompetition($row['Filtre_competition_sql'] ?? null);
        $user->setLimitClubs($row['Limitation_equipe_club'] ?? null);
        $user->setClub($row['Numero_club'] ?? null);

        return $user;
    }
}
