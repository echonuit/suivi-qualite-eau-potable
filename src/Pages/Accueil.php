<?php

declare(strict_types=1);

namespace EauPotable\Pages;


/**
 * Exemple fourni avec le squelette : la page d'accueil dit si l'environnement est prêt.
 *
 * À remplacer par votre première vraie page. Elle montre la forme d'une page :
 * elle appelle les autres couches, puis rend du HTML dont chaque valeur est échappée.
 */
final class Accueil
{
    public function afficher(): string
    {
        ob_start(); ?>
    
<div >
    <section >
        <div >
            <span >QUALITÉ DE L'EAU EN FRANCE</span>

            <h1>
                Comprendre et explorer
                <span>la qualité de notre eau.</span>
            </h1>

            <p>
                Découvrez les données disponibles sur la qualité de l'eau
                en France et explorez les différents indicateurs de qualité
                de manière simple et accessible.
            </p>

            <div >
                <a href="/index.php?page=donnees" class="btn btn-primary">
                    Explorer les données
                </a>

                <a href="/index.php?page=inscription" class="btn btn-secondary">
                    Créer un compte
                </a>
            </div>
        </div>

        <div >
            <div >
                <span>💧</span>
            </div>
        </div>
    </section>


    <!-- Introduction text / explications-->
    <section>
        <span>NOTRE OBJECTIF</span>

        <h2>
            Des données pour mieux comprendre notre eau
        </h2>

        <p>
            La qualité de l'eau est un enjeu essentiel pour la santé,
            l'environnement et les territoires. Notre objectif est de
            rendre les données disponibles plus accessibles et plus
            faciles à comprendre.
        </p>
    </section>
</div>

        <?php
        return (new Layout("Accueil","Page d'accueil",ob_get_clean()))->afficher();
    }

    private static function echapper(string $texte): string
    {
        return htmlspecialchars($texte, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
