#!/bin/bash
set -e

echo "-------------------------------------------"
echo "   🛠  Initialisation du projet WordPress"
echo "-------------------------------------------"

# Fonction de demande
ask() {
    local prompt="$1"
    local default="$2"
    read -p "$prompt [$default] : " reply
    echo "${reply:-$default}"
}

# Génération mot de passe aléatoire
generate_password() {
    openssl rand -base64 12
}

# Capitaliser la première lettre (portable)
capitalize() {
    local str="$1"
    first_char=$(echo "${str:0:1}" | tr '[:lower:]' '[:upper:]')
    rest="${str:1}"
    echo "$first_char$rest"
}

# 🔧 Infos projet
PROJECT_NAME=$(ask "Nom du projet (slug court, ex: test-base)" "my-wp-site")
WP_DOMAIN=$(ask "Nom de domaine local (Traefik)" "$PROJECT_NAME.localhost")
WP_TITLE=$(ask "Titre WordPress" "$(capitalize "$PROJECT_NAME") Website")

# 🔐 Admin WordPress
WP_ADMIN_USER=$(ask "Utilisateur admin WP" "admin")
WP_ADMIN_PASSWORD=$(ask "Mot de passe admin WP" "$(generate_password)")
WP_ADMIN_EMAIL=$(ask "Email admin WP" "admin@example.com")

# 🎨 Thème
WP_CHILD_THEME_NAME=$(ask "Nom du thème enfant" "${PROJECT_NAME}-child")

# 🗄️ Base de données
echo ""
echo "-------------------------------------------"
echo "  🗄️  Configuration MySQL / MariaDB"
echo "-------------------------------------------"
echo "Ton projet WordPress peut utiliser :"
echo " 1) Une base EXTERNE existante (shared-db)"
echo " 2) Une base INTERNE (conteneur Docker MySQL dédié)"
DB_MODE=$(ask "Choisir mode DB : external / internal" "external")

if [ "$DB_MODE" = "external" ]; then
    # Détection du conteneur shared-db
    if docker ps --format '{{.Names}}' | grep -q "^shared-db$"; then
        echo "✅ Conteneur 'shared-db' détecté. Génération automatique des variables DB."
        DB_HOST="shared-db"
        DB_NAME="${PROJECT_NAME}"
        DB_USER="${PROJECT_NAME}_user"
        DB_PASSWORD=$(generate_password)
        DB_PREFIX="kdwp_"

        echo "💡 Vérification / création de la base et de l'utilisateur MySQL..."
        docker exec shared-db mysql -uroot -prootpassword -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`;"
        docker exec shared-db mysql -uroot -prootpassword -e "CREATE USER IF NOT EXISTS '$DB_USER'@'%' IDENTIFIED BY '$DB_PASSWORD';"
        docker exec shared-db mysql -uroot -prootpassword -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'%';"
        docker exec shared-db mysql -uroot -prootpassword -e "FLUSH PRIVILEGES;"
    else
        echo "⚠️  ATTENTION : Aucun conteneur 'shared-db' détecté !"
        echo ""
        echo "Pour utiliser une base externe, vous devez d'abord démarrer le conteneur shared-db :"
        echo "  👉 docker-compose up -d db"
        echo ""
        echo "Les variables de connexion seront générées, mais la base ne sera créée"
        echo "qu'après le démarrage de shared-db."
        echo ""
        
        # Génération des variables même si shared-db n'est pas actif
        DB_HOST="shared-db"
        DB_NAME="${PROJECT_NAME}"
        DB_USER="${PROJECT_NAME}_user"
        DB_PASSWORD=$(generate_password)
        DB_PREFIX="kdwp_"
        
        echo "📝 Variables de connexion générées (à utiliser après démarrage de shared-db)"
    fi
else
    # Mode interne → cohérent pour Docker Compose
    DB_HOST="db"
    DB_NAME="${PROJECT_NAME}"
    DB_USER="${PROJECT_NAME}_user"
    DB_PASSWORD=$(generate_password)
    DB_PREFIX="wp_"
fi

# 📄 Écriture du fichier .env
cat <<EOF > .env
###############################################
# 🌐 Configuration Projet WordPress
###############################################

PROJECT_NAME="$PROJECT_NAME"
WP_DOMAIN="$WP_DOMAIN"
WP_TITLE="$WP_TITLE"
WP_ADMIN_USER="$WP_ADMIN_USER"
WP_ADMIN_PASSWORD="$WP_ADMIN_PASSWORD"
WP_ADMIN_EMAIL="$WP_ADMIN_EMAIL"

WP_CHILD_THEME_NAME="$WP_CHILD_THEME_NAME"

###############################################
# 🗄️ Base de données
###############################################

DB_MODE="$DB_MODE"
DB_HOST="$DB_HOST"
DB_NAME="$DB_NAME"
DB_USER="$DB_USER"
DB_PASSWORD="$DB_PASSWORD"
DB_PREFIX="$DB_PREFIX"
EOF

echo ""
echo "-------------------------------------------"
echo "  🎉 Projet initialisé avec succès !"
echo "-------------------------------------------"
echo "Fichier .env généré :"
cat .env
echo ""

if [ "$DB_MODE" = "external" ] && ! docker ps --format '{{.Names}}' | grep -q "^shared-db$"; then
    echo "⚠️  PROCHAINES ÉTAPES :"
    echo "   1. Démarrer shared-db : docker-compose up -d db"
    echo "   2. Créer la base : ./scripts/02_create-db-user.sh"
    echo "   3. Initialiser WordPress : ./scripts/03_init-wp.sh"
else
    echo "Tu peux maintenant lancer : ./scripts/03_init-wp.sh"
    if [ "$DB_MODE" = "external" ]; then
        echo "(La base de données a déjà été créée automatiquement)"
    fi
fi

