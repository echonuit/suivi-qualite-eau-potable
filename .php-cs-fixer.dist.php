<?php

declare(strict_types=1);

// Mise en forme automatique du code : `composer format` corrige, `composer format:check` vérifie.
// Les règles suivent PER Coding Style, le standard commun de la communauté PHP.

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$fichiers = Finder::create()
    ->in([__DIR__ . '/public', __DIR__ . '/src', __DIR__ . '/tests'])
    ->append([__FILE__]);

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS' => true,
        // Chaque fichier commence par declare(strict_types=1) : PHP refuse alors
        // de convertir en silence une chaîne en nombre.
        'declare_strict_types' => true,
        'no_unused_imports' => true,
        'ordered_imports' => true,
    ])
    ->setFinder($fichiers);
