#!/bin/bash
set -e

echo "-------------------------------------------"
echo "   🧪 Test d'activation ACF Pro"
echo "-------------------------------------------"

# Vérifie que le .env existe
if [ ! -f .env ]; then
    echo "❌ Fichier .env introuvable. Lance d'abord ./scripts/01_setup-project.sh"
    exit 1
fi

# Export des variables du .env
set -a
source .env
set +a

# Détermination du nom du conteneur WordPress
WORDPRESS_CONTAINER="${PROJECT_NAME}_wordpress"

echo ""
echo "🔍 Vérification de l'état du conteneur WordPress..."

# Vérifier si le conteneur WordPress est démarré
if docker ps --format '{{.Names}}' | grep -q "^${WORDPRESS_CONTAINER}$"; then
    echo "✅ Conteneur WordPress est démarré"
    
    echo ""
    echo "🔍 Vérification du plugin ACF Pro..."
    
    # Vérifier si le plugin ACF Pro est installé
    if docker exec -i "$WORDPRESS_CONTAINER" wp plugin is-installed advanced-custom-fields-pro --allow-root 2>/dev/null; then
        echo "✅ Plugin ACF Pro est installé"
        
        # Vérifier si le plugin est activé
        if docker exec -i "$WORDPRESS_CONTAINER" wp plugin is-active advanced-custom-fields-pro --allow-root 2>/dev/null; then
            echo "✅ Plugin ACF Pro est activé"
        else
            echo "⚠️  Plugin ACF Pro n'est pas activé"
            echo "   Vous pouvez l'activer avec :"
            echo "   docker exec -i ${WORDPRESS_CONTAINER} wp plugin activate advanced-custom-fields-pro --allow-root"
        fi
    else
        echo "❌ Plugin ACF Pro n'est pas installé"
        echo "   Vérifiez que le sous-module a été initialisé :"
        echo "   ./scripts/02_setup-acf-pro.sh"
    fi
else
    echo "⚠️  Conteneur WordPress n'est pas démarré"
    echo "   Vous devez d'abord démarrer le conteneur :"
    echo "   docker-compose up -d wordpress"
fi

echo ""
echo "-------------------------------------------"
echo "🎉 Test terminé"
echo "-------------------------------------------"