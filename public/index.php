<?php

declare(strict_types=1);

// Point d'entrée unique de l'application : Apache envoie ici toutes les requêtes
// (voir docker/php/apache.conf). Ce fichier choisit la page à afficher, et c'est tout.

use EauPotable\Pages\Accueil;

require dirname(__DIR__) . '/vendor/autoload.php';

$chemin = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Ajoutez vos pages ici, une ligne par adresse. Les adresses de ce projet portent
// toute la vue (/reseau/013000618?parametres=1340) : concevez leur schéma d'abord.
$page = match ($chemin) {
    '/' => (new Accueil())->afficher(),
    default => null,
};

if ($page === null) {
    http_response_code(404);
    $page = 'Page introuvable.';
}

echo $page;
