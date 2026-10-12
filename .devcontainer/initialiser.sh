#!/usr/bin/env bash
# Prépare l'environnement à la création du Codespace ou du devcontainer.
# Lancé une seule fois, par « postCreateCommand » dans devcontainer.json.
# Il fait à votre place les étapes de la section « Installer l'environnement »
# de CONTRIBUTING.md.

set -euo pipefail
cd "$(dirname "$0")/.."

# 1. Réglages locaux. UID_GID fait tourner le conteneur « app » sous votre
#    identité : les fichiers qu'il crée (vendor/, caches) vous appartiennent.
if [ ! -f .env ]; then
    cp .env.example .env
    echo "UID_GID=$(id -u):$(id -g)" >> .env
    echo "Fichier .env créé."
fi

# 2. Hook qui vérifie le format des messages de commit.
git config core.hooksPath .githooks

# 3. Docker démarre en même temps que le conteneur : on attend qu'il réponde.
.devcontainer/attendre-docker.sh

# 4. Application et base, puis outils de développement.
docker compose up -d --build
docker compose exec -T app composer install --no-interaction --no-progress

echo
echo "Environnement prêt. L'application répond sur le port 8080 (onglet « Ports »)."
echo "Vérifiez votre travail avec : docker compose exec app composer check"
