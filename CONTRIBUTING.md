# Travailler à plusieurs dans le dépôt

Ce document fixe comment une modification arrive sur `main` et comment s'écrivent les messages de commit. Il reprend deux conventions très répandues, [GitHub Flow](https://docs.github.com/fr/get-started/using-github/github-flow) et [Conventional Commits](https://www.conventionalcommits.org/fr/v1.0.0/), que vous retrouverez en stage et en entreprise.

Le sujet vous demande un dépôt à l'historique lisible, qu'une autre équipe pourra reprendre l'an prochain. Ces règles servent à cela, et à pouvoir montrer une version qui tourne au commanditaire à chaque point d'étape.

## Les règles en six lignes

- `main` fonctionne toujours : on peut l'installer et faire la démonstration à tout moment.
- Personne ne pousse directement sur `main`. Toute modification passe par une branche et une pull request.
- Une branche traite un seul sujet et vit quelques jours au plus.
- Une pull request est fusionnée quand l'intégration continue est verte et qu'un autre membre de l'équipe l'a relue.
- La fusion se fait en squash : le titre de la pull request devient le message du commit sur `main`.
- Les messages de commit et les titres de pull request suivent Conventional Commits, en français.

## Installer l'environnement

Il vous faut Git et Docker (Docker Desktop sous Windows et macOS). PHP, Composer et PostgreSQL tournent dans des conteneurs : vous n'avez pas à les installer.

```bash
git clone <adresse de votre dépôt>
cd <dossier du dépôt>
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
git config core.hooksPath .githooks
```

Ouvrez ensuite <http://localhost:8080>. La page d'accueil affiche la version de PHP et celle de PostgreSQL : si les deux apparaissent, tout est en place.

Quelques précisions sur ces commandes :

- Le fichier `.env` contient les réglages propres à votre machine. Changez-y `PORT_WEB` ou `PORT_BASE` si le port 8080 ou 5432 est déjà pris.
- Sous Linux avec Docker classique, renseignez `UID_GID` dans `.env` avant le premier démarrage. Sans cela, le dossier `vendor/` appartient à root et vous ne pourrez plus le supprimer sans `sudo`.
- `composer install` télécharge les outils de développement dans `vendor/`. Relancez-le quand un coéquipier a modifié `composer.json`.
- La dernière ligne active le hook qui vérifie le format de vos messages de commit. Elle se fait une fois par clone.

La base est joignable depuis votre machine, avec `psql` ou un client SQL : hôte `localhost`, port 5432, base, utilisateur et mot de passe `eau`.

`docker compose down` arrête les conteneurs et garde les données. `docker compose down -v` efface aussi la base.

## Où ranger quoi

Le dépôt reprend les trois couches que demande le sujet.

| Emplacement | Ce qu'on y met |
| --- | --- |
| `public/` | Ce qu'Apache sert : `index.php`, point d'entrée unique, et vos fichiers CSS, JavaScript et images |
| `src/Metier/` | La logique métier : analyse des limites, valeurs « inférieures à », métabolites. Elle ne lit ni la requête HTTP ni la base, et n'appelle pas le réseau |
| `src/Donnees/` | L'accès aux données : appels à Hub'Eau, cache, base en requêtes préparées |
| `src/Pages/` | Les pages : elles lisent l'adresse, appellent les deux autres couches et rendent le HTML |
| `tests/` | Les tests, rangés comme `src/`, et les cas réels tirés de l'API dans `tests/fichiers/` |
| `docs/` | L'ébauche de la page d'un réseau, et vos documents : note d'architecture, inventaire des données, documentation technique |
| `docker/`, `compose.yaml` | L'environnement de développement |
| `.github/` | L'intégration continue et le modèle de pull request |

Chaque couche contient une classe d'exemple : `CodeReseau`, `Diagnostic` et `Accueil`. Elles montrent la forme attendue et sont faites pour être remplacées par les vôtres.

Le nom d'une classe donne son fichier : `EauPotable\Metier\CodeReseau` se trouve dans `src/Metier/CodeReseau.php`. Tant que vous respectez cette correspondance, Composer charge vos classes sans `require`.

## Vérifier son travail

Une seule commande enchaîne les trois vérifications que fait aussi l'intégration continue :

```bash
docker compose exec app composer check
```

| Commande | Ce qu'elle fait |
| --- | --- |
| `composer format` | Remet le code en forme (PHP-CS-Fixer). Lancez-la avant de commiter |
| `composer format:check` | Signale ce qui n'est pas en forme, sans rien modifier |
| `composer analyse` | Cherche les erreurs sans exécuter le code (PHPStan) |
| `composer test` | Lance tous les tests (PHPUnit). La base doit être démarrée |
| `composer test:metier` | Lance les seuls tests de la logique métier, sans base ni réseau |
| `composer check` | Enchaîne `format:check`, `analyse` et `test` |

Toutes se lancent avec le préfixe `docker compose exec app`. Si vous avez PHP 8.4 et Composer sur votre machine, elles marchent aussi sans lui.

PHPStan vérifie en plus quatre règles propres au projet, réglées dans `phpstan.neon.dist` :

- `$_GET`, `$_POST`, `$_FILES`, `$_REQUEST`, `$_COOKIE` et `$_SESSION` ne se lisent que dans `public/` et `src/Pages/` ;
- les fonctions `curl_*` ne s'appellent que dans `src/Donnees/`. Avec la règle précédente, c'est ce qui garde la logique métier testable sans réseau ;
- `PDO::query()` et `PDO::exec()` sont interdits, pour que toutes les requêtes soient préparées ;
- `var_dump()`, `print_r()` et `var_export()` ne se commitent pas.

PHPStan est réglé au niveau 8, sur une échelle de 0 à 10. Deux de ses exigences surprennent au début :

- chaque paramètre, retour et propriété a un type, et un tableau dit ce qu'il contient dans un commentaire, par exemple `@return array<string, float>` ;
- une valeur qui peut être `null` ou `false` se vérifie avant d'être utilisée. `fopen()` rend `false` quand le fichier est introuvable, et PHPStan vous oblige à traiter ce cas.

Quand PHPStan signale une erreur, corrigez le code plutôt que de faire taire l'outil. Si une règle vous gêne dans un cas légitime, par exemple un script qui crée les tables, discutez-en en équipe et modifiez la configuration dans une pull request.

Ne faites pas dépendre vos tests de Hub'Eau : l'API peut être lente ou indisponible, et l'intégration continue échouerait sans que votre code y soit pour rien. Enregistrez quelques réponses réelles dans `tests/fichiers/` et testez à partir d'elles.

## GitHub Flow, étape par étape

GitHub Flow n'a qu'une branche durable, `main`. Tout le reste se passe sur des branches courtes, qui partent de `main` et y reviennent par une pull request. Il n'y a ni branche `develop` ni branche de version, ce qui convient à une petite équipe qui livre souvent.

### 1. Partir d'une issue

Chaque travail commence par une issue qui dit ce qu'il y a à faire. Une ligne du tableau « Fonctionnalités attendues » du sujet donne en général plusieurs issues : « Graphique d'évolution » se découpe en tracé des valeurs, ligne de limite, ligne de référence, affichage des valeurs « inférieures à ».

Assignez-vous l'issue avant de commencer. C'est ce qui évite que deux personnes écrivent la même chose chacune de son côté.

### 2. Créer une branche depuis `main`

```bash
git switch main
git pull
git switch -c feat/12-ligne-de-limite
```

Le nom suit le modèle `<type>/<numéro de l'issue>-<sujet-court>`, en minuscules, sans accents, avec des traits d'union. Le type est l'un de ceux du tableau plus bas (`feat`, `fix`, `docs`...).

### 3. Commiter et pousser souvent

```bash
git add src/Metier/Limite.php tests/Metier/LimiteTest.php
git commit -m "feat(limites): extrait le seuil et l'unité d'une limite"
git push -u origin feat/12-ligne-de-limite
```

Si vous avez activé le hook à l'installation, Git refuse un message qui ne suit pas le format et vous dit pourquoi.

Faites de petits commits, un par préoccupation. Si la description a besoin d'un « et », ce sont probablement deux commits.

Poussez au moins à la fin de chaque séance. Un travail qui n'existe que sur votre machine n'est ni sauvegardé ni visible du reste de l'équipe.

### 4. Ouvrir la pull request tôt

Ouvrez la pull request en brouillon (« draft ») dès le premier commit poussé. L'équipe voit ce qui est en cours, et l'intégration continue tourne à chaque nouveau commit. Passez-la en « ready for review » quand elle est prête à être relue.

Le titre suit Conventional Commits, puisque c'est lui qui restera sur `main`. La description dit trois choses : ce qui change, comment le vérifier, et quelle issue elle ferme.

```markdown
Extrait l'opérateur, le nombre et l'unité d'une limite de qualité comme `<=50 mg/L`, et trace la ligne sur le graphique.

Pour vérifier : ouvrir `/reseau/013000618?parametres=1340`, la limite doit apparaître à 50 mg/L.

Closes #12
```

La ligne `Closes #12` ferme l'issue automatiquement au moment de la fusion.

Gardez les pull requests petites. Au-delà de quelques centaines de lignes, la relecture devient superficielle, et les conflits avec le travail des autres se multiplient.

### 5. Faire relire

La relecture est faite par un membre de l'équipe qui n'a pas écrit le code. Le relecteur lit le diff, et quand la modification touche un comportement, il récupère la branche et rejoue le parcours décrit dans la pull request.

L'auteur répond à chaque commentaire, par un commit ou par une réponse. Pendant la relecture, ajoutez de nouveaux commits au lieu de modifier les anciens : le relecteur voit ainsi ce qui a changé depuis son dernier passage.

### 6. Mettre la branche à jour

Si `main` a avancé pendant votre travail, ramenez ses changements dans votre branche avant de fusionner.

```bash
git fetch origin
git merge origin/main
```

Résolvez les conflits s'il y en a, relancez les tests, poussez. Un `merge` suffit ici et vous évite de réécrire l'historique d'une branche déjà poussée. Le commit de fusion disparaîtra de toute façon avec le squash.

### 7. Fusionner et nettoyer

Trois conditions : l'intégration continue est verte, un relecteur a approuvé, toutes les conversations sont résolues. L'auteur clique alors sur « Squash and merge », vérifie que le message proposé reprend bien le titre de la pull request, puis supprime la branche.

```bash
git switch main
git pull
git branch -d feat/12-ligne-de-limite
```

### Si `main` est cassée

Réparer `main` passe avant tout le reste, parce que toutes les branches en partent. Annulez le commit fautif avec `git revert` dans une pull request, fusionnez, puis reprenez la correction au calme sur une nouvelle branche.

## Conventional Commits

Un message de commit a cette forme :

```
<type>(<domaine>): <description>

<corps facultatif : pourquoi ce changement>

<pied facultatif : Closes #12>
```

Avec ce format, `git log --oneline` se lit comme un journal des changements, et l'on retrouve d'un coup d'œil ce qui a touché le graphique ou la recherche de commune.

### Le type

| Type | Quand l'employer |
| --- | --- |
| `feat` | Une nouvelle fonction visible par l'utilisateur |
| `fix` | La correction d'un bogue |
| `docs` | La documentation seule : page d'aide, note d'architecture, documentation technique, README |
| `test` | L'ajout ou la correction de tests, sans toucher au code testé |
| `refactor` | Une réorganisation du code qui ne change pas son comportement |
| `perf` | Une amélioration de performance |
| `style` | La mise en forme du code : indentation, espaces, ordre des imports |
| `build` | Ce qui construit ou installe l'application : Docker Compose, dépendances |
| `ci` | La configuration de l'intégration continue |
| `chore` | L'entretien qui ne rentre dans aucune autre case |
| `revert` | L'annulation d'un commit précédent |

### Le domaine

Le domaine, entre parenthèses, nomme la partie de l'application concernée. Il est facultatif, par exemple pour un commit `ci` ou `build` qui touche tout le dépôt.

Fixez votre liste en équipe dès le début et écrivez-la ici. Les fonctions attendues et les sources de données du sujet donnent un point de départ : `commune`, `reseau`, `parametres`, `graphique`, `limites`, `export`, `url`, `hubeau`, `cache`, `ephy`.

### La description

Elle dit ce que le commit change, en français, en commençant par une minuscule et sans point final. Visez 72 caractères au plus pour la première ligne.

```
feat(commune): propose les communes dès les premières lettres
fix(limites): accepte la virgule décimale dans une limite
test(limites): couvre les limites vides
docs(aide): explique la différence entre limite et référence
refactor(hubeau): isole la pagination des résultats
build: ajoute le service PostgreSQL à Docker Compose
ci: lance les tests sur chaque pull request
```

N'écrivez pas d'espace avant les deux-points : `feat(graphique): ...`, jamais `feat(graphique) : ...`. L'habitude typographique française pousse à en mettre un, mais ce deux-points fait partie de la syntaxe, comme celui d'un `case` en PHP, et les outils qui lisent ces messages ne reconnaissent pas la forme avec espace.

### Le corps

La description dit quoi, le corps dit pourquoi. Il est utile dès que la raison ne se devine pas en lisant le diff.

```
fix(graphique): ne relie plus deux mesures éloignées

Deux points séparés de deux ans étaient reliés par un segment, qui
suggérait une évolution continue entre les deux. La courbe s'interrompt
maintenant quand il n'y a pas de mesure.

Closes #31
```

### Le changement cassant

Un point d'exclamation après le type signale un changement qui casse ce qui existait. Dans ce projet, c'est surtout le cas quand le schéma d'URL change : les adresses déjà partagées ou mises en favori ne redonnent plus la même vue.

```
feat(url)!: place la période dans le chemin de l'adresse

BREAKING CHANGE: les adresses partagées avant ce commit perdent leur période.
```

### Le titre de la pull request

Comme la fusion se fait en squash, les commits de votre branche sont réunis en un seul sur `main`, et son message est le titre de la pull request. C'est donc ce titre qu'il faut soigner en priorité. Écrire aussi vos commits de branche dans ce format reste une bonne habitude : avec un seul commit, GitHub propose son message comme titre.

L'intégration continue vérifie ce titre à chaque modification de la pull request. Vous pouvez le vérifier vous-mêmes avant de l'ouvrir, avec le script qu'elle utilise :

```bash
.github/scripts/verifie-titre.sh "feat(graphique): trace la limite de qualité"
```

## Avant de demander une relecture

Le modèle de pull request, prérempli à l'ouverture, reprend l'essentiel de cette liste.

- [ ] `composer check` passe sur votre machine
- [ ] La logique métier ajoutée ou modifiée a ses tests
- [ ] La branche est à jour avec `main`
- [ ] Le titre de la pull request suit Conventional Commits
- [ ] La description dit comment vérifier le changement
- [ ] Le diff ne contient ni code commenté, ni `var_dump` oublié, ni fichier sans rapport avec le sujet

## Ce qui n'entre pas dans le dépôt

- Les mots de passe, les clés d'API si vous en utilisez un jour, et le fichier `.env`. Si un secret a été poussé, le supprimer dans un commit suivant ne suffit pas, car il reste dans l'historique : changez-le.
- Les données téléchargées : les archives annuelles de data.gouv.fr pèsent 240 à 310 Mo chacune, et le catalogue E-Phy se met à jour chaque semaine. Rangez-les dans `var/`, que Git ignore, et gardez dans le dépôt le script qui les télécharge.
- Ce qui se régénère : les dépendances installées, le cache, les rapports produits par les outils.

Le fichier `.gitignore` fourni écarte déjà `.env`, `vendor/`, les caches des outils et le dossier `var/`. Complétez-le quand un nouvel outil produit ses propres fichiers.

## Régler le dépôt GitHub une fois pour toutes

Ces réglages font respecter les règles ci-dessus par GitHub lui-même. Ils demandent les droits d'administration sur le dépôt.

Dans Settings > Rules > Rulesets, créez une règle sur la branche par défaut :

- exiger une pull request avant toute fusion, avec une approbation ;
- exiger que les deux vérifications de l'intégration continue réussissent : « Mise en forme, analyse et tests » et « Titre au format Conventional Commits ». GitHub ne les propose dans la liste qu'après leur première exécution, donc après une première pull request ;
- bloquer les « force pushes ».

Dans Settings > General > Pull Requests :

- ne garder que « Allow squash merging », avec « Pull request title » comme message par défaut ;
- cocher « Automatically delete head branches ».

## Trois situations courantes

**Vous avez commité sur `main` par erreur, sans avoir poussé.** Commitez ou remisez (`git stash`) d'abord vos modifications en cours. Créez ensuite la branche là où vous êtes, elle emporte vos commits, puis remettez votre `main` local au niveau de celui de GitHub.

```bash
git switch -c feat/12-ligne-de-limite
git switch main
git reset --hard origin/main
git switch feat/12-ligne-de-limite
```

**Le message de votre dernier commit est raté.** S'il n'est pas encore poussé, `git commit --amend` permet de le réécrire. S'il est déjà poussé, laissez-le : c'est le titre de la pull request qui restera sur `main`.

**Git annonce un conflit pendant le `merge`.** Ouvrez les fichiers signalés, choisissez entre les deux versions encadrées par `<<<<<<<` et `>>>>>>>`, relancez les tests, puis `git add` et `git commit`. Si le conflit touche du code que vous ne connaissez pas, résolvez-le avec la personne qui l'a écrit.
