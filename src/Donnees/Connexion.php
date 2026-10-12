<?php

declare(strict_types=1);

namespace EauPotable\Donnees;

use PDO;

/**
 * Ouvre la connexion à la base PostgreSQL.
 *
 * Les paramètres viennent des variables d'environnement définies dans compose.yaml,
 * pour que le mot de passe ne soit jamais écrit dans le code.
 */
final class Connexion
{
    public static function ouvrir(): PDO
    {
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            self::variable('DB_HOST', 'localhost'),
            self::variable('DB_PORT', '5432'),
            self::variable('DB_NAME', 'eau'),
        );

        return new PDO(
            $dsn,
            self::variable('DB_USER', 'eau'),
            self::variable('DB_PASSWORD', 'eau'),
            [
                // Une erreur SQL lève une exception au lieu de passer inaperçue.
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Les requêtes préparées le sont par PostgreSQL, pas simulées par PDO.
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );
    }

    private static function variable(string $nom, string $defaut): string
    {
        $valeur = getenv($nom);

        return $valeur === false || $valeur === '' ? $defaut : $valeur;
    }
}
