<?php

declare(strict_types=1);

namespace EauPotable\Metier;

use InvalidArgumentException;

/**
 * Exemple fourni avec le squelette : vérifie qu'un texte est un code de réseau.
 *
 * Il montre la forme attendue de la logique métier : une fonction pure, qui ne
 * lit ni la requête HTTP ni la base et n'appelle pas le réseau, et qui se teste
 * donc sans rien démarrer.
 */
final class CodeReseau
{
    /**
     * Un code de réseau a neuf chiffres, par exemple 013000618 pour Aix-Couteron.
     *
     * Il reste une chaîne : converti en entier, il perdrait son zéro initial.
     */
    public static function valider(string $texte): string
    {
        $code = trim($texte);

        if (preg_match('/^\d{9}$/', $code) !== 1) {
            throw new InvalidArgumentException(sprintf('« %s » n\'est pas un code de réseau : 9 chiffres attendus.', $texte));
        }

        return $code;
    }
}
