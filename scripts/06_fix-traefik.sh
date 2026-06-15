#!/bin/bash

set -o allexport
source .env
set +o allexport

echo "🔧 === CORRECTION CONFIGURATION TRAEFIK ==="
echo ""

###################################
# 1. Vérifier le réseau Traefik
###################################
echo "1️⃣ Vérification du réseau Traefik..."

if ! docker network ls | grep -q "traefik-network"; then
    echo "❌ Réseau traefik-network introuvable !"
    echo "   Création du réseau..."
    docker network create traefik-network
    echo "✅ Réseau créé"
else
    echo "✅ Réseau traefik-network existe"
fi

###################################
# 2. Connecter WordPress au réseau
###################################
echo ""
echo "2️⃣ Connexion de WordPress au réseau Traefik..."

CONTAINER_NAME="wp-${WP_DOMAIN}"

if docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    # Déconnecter puis reconnecter pour être sûr
    docker network disconnect traefik-network $CONTAINER_NAME 2>/dev/null || true
    docker network connect traefik-network $CONTAINER_NAME
    echo "✅ WordPress connecté au réseau Traefik"
else
    echo "⚠️  Conteneur WordPress non trouvé (${CONTAINER_NAME})"
    echo "   Relancez : docker compose up -d"
fi

###################################
# 3. Vérifier Traefik
###################################
echo ""
echo "3️⃣ Vérification de Traefik..."

if docker ps | grep -q "traefik"; then
    echo "✅ Traefik en cours d'exécution"
    
    echo ""
    echo "📊 Routes Traefik enregistrées :"
    docker compose -f ../docker/docker-compose.yml logs traefik | grep "${WP_DOMAIN}" | tail -5 || echo "⚠️  Aucune route trouvée pour ${WP_DOMAIN}"
else
    echo "❌ Traefik n'est pas en cours d'exécution !"
    echo "   Démarrez-le : docker compose -f ../docker/docker-compose.yml up -d"
fi

###################################
# 4. Redémarrer les conteneurs
###################################
echo ""
echo "4️⃣ Redémarrage des conteneurs..."
docker compose restart wordpress
sleep 3

echo ""
echo "✅ Configuration Traefik corrigée !"
echo ""
echo "🌐 Testez maintenant :"
echo "   Frontend: https://${WP_DOMAIN}.localhost"
echo "   Admin: https://${WP_DOMAIN}.localhost/wp-admin"
echo ""
echo "🔍 Si le problème persiste, vérifiez :"
echo "   1. Logs Traefik : docker compose -f ../docker/docker-compose.yml logs traefik"
echo "   2. Logs WordPress : docker compose logs wordpress"
echo "   3. Accès direct : http://localhost:8080 (ajoutez 'ports: - 8080:80' dans docker-compose.yml)"