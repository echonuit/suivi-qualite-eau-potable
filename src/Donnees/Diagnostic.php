<?php

declare(strict_types=1);

namespace EauPotable\Donnees;

use PDO;

/**
 * Exemple fourni avec le squelette : interroge la base pour vérifier qu'elle répond.
 *
 * Il montre la forme attendue de tout accès à la base : une classe qui reçoit
 * la connexion, et une requête préparée.
 */
final class Diagnostic
{
    public function __construct(private readonly PDO $pdo) {}

    public function versionPostgresql(): string
    {
        $requete = $this->pdo->prepare("SELECT current_setting('server_version')");
        $requete->execute();

        return (string) $requete->fetchColumn();
    }
}
