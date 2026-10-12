#!/usr/bin/env bash
# Vérifie qu'un titre de pull request ou un sujet de commit suit Conventional Commits,
# tel que le décrit CONTRIBUTING.md.
#
#     .github/scripts/verifie-titre.sh "feat(graphique): trace la limite de qualité"
#
# L'intégration continue lance ce script sur le titre de chaque pull request,
# et le hook .githooks/commit-msg sur chaque message de commit.

set -u

titre="${1:-}"
types='feat|fix|docs|test|refactor|perf|style|build|ci|chore|revert'

refuse() {
    echo "Titre refusé : « $titre »" >&2
    echo "  $1" >&2
    echo "  Forme attendue : <type>(<domaine>): <description>" >&2
    echo "  Exemple        : feat(graphique): trace la limite de qualité" >&2
    echo "  Types permis   : ${types//|/, }" >&2
    exit 1
}

if [[ -z "$titre" ]]; then
    refuse "Le titre est vide."
fi

avec_espace="^($types)(\([^)]*\))?!? +:"
if [[ "$titre" =~ $avec_espace ]]; then
    refuse "Pas d'espace avant les deux-points : ici, ils font partie de la syntaxe."
fi

forme="^($types)(\([a-z0-9-]+\))?!?: [^ ].*$"
if [[ ! "$titre" =~ $forme ]]; then
    refuse "Le titre ne suit pas la forme attendue (type inconnu, domaine mal écrit ou description absente)."
fi

if [[ "$titre" == *. ]]; then
    refuse "Pas de point à la fin de la description."
fi

echo "Titre conforme : $titre"
