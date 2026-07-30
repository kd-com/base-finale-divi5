#!/bin/bash
set -e

echo "-------------------------------------------"
echo "   📦 Configuration ACF Pro"
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

###############################################
# 📦 Initialisation du sous-module ACF Pro
###############################################

echo ""
echo "📦 Initialisation du sous-module ACF Pro..."

# Vérifier si le sous-module est déjà initialisé
if [ -d "wp-content/plugins/advanced-custom-fields-pro/.git" ]; then
    echo "ℹ️  Sous-module déjà initialisé, mise à jour..."
    git submodule update --remote wp-content/plugins/advanced-custom-fields-pro
else
    echo "📡 Initialisation du sous-module..."
    git submodule init wp-content/plugins/advanced-custom-fields-pro
    git submodule update wp-content/plugins/advanced-custom-fields-pro
fi

# Vérifier que le sous-module contient des fichiers
if [ -d "wp-content/plugins/advanced-custom-fields-pro" ] && [ "$(ls -A wp-content/plugins/advanced-custom-fields-pro 2>/dev/null)" ]; then
    echo "✅ Sous-module ACF Pro initialisé avec succès"
    echo "   Contenu :"
    ls -la wp-content/plugins/advanced-custom-fields-pro/ | head -n 10
else
    echo "❌ Le sous-module ACF Pro est vide"
    exit 1
fi

echo ""
echo "-------------------------------------------"
echo "🎉 Sous-module ACF Pro prêt !"
echo "-------------------------------------------"
echo "Le plugin sera activé automatiquement lors"
echo "de l'installation WordPress (script 03_init-wp.sh)"