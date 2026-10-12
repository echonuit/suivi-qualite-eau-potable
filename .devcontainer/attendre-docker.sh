#!/usr/bin/env bash
# Attend que le Docker embarqué dans le devcontainer réponde.
# Appelé à la création (initialiser.sh) et à chaque démarrage (postStartCommand).

set -euo pipefail

# Attend Docker pendant $1 essais espacés de 2 secondes.
attendre() {
    for _ in $(seq 1 "$1"); do
        docker info > /dev/null 2>&1 && return 0
        sleep 2
    done
    return 1
}

attendre 15 && exit 0

# Docker ne répond pas. Cause connue : il est réglé par défaut sur l'ancien
# iptables (« legacy »), que les noyaux Linux récents ne fournissent plus.
# On le bascule sur iptables-nft, puis on le relance.
echo "Docker ne démarre pas : nouvel essai avec iptables-nft."
sudo update-alternatives --set iptables /usr/sbin/iptables-nft > /dev/null
sudo update-alternatives --set ip6tables /usr/sbin/ip6tables-nft > /dev/null
if ! pgrep -x dockerd > /dev/null; then
    sudo sh -c '/usr/local/share/docker-init.sh > /tmp/docker-init.log 2>&1 &'
fi

if ! attendre 30; then
    echo "Docker ne répond toujours pas. Son journal est dans /tmp/dockerd.log." >&2
    exit 1
fi
