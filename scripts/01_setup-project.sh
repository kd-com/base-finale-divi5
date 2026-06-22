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
# Volontairement restreint à [A-Za-z0-9] : pas de $, ", \, `, ', espace.
# Évite deux bugs vécus en prod (projet equitasun) :
#  - un $ non échappé dans un .env est interprété par Docker Compose comme
#    le début d'une interpolation de variable (WARN: variable not set)
#  - un " ou ' littéral casse les valeurs lues par certains scripts/outils
#    qui ne nettoient pas le contenu du .env avant utilisation
generate_password() {
    local length="${1:-16}"
    local pass=""
    while [ "${#pass}" -lt "$length" ]; do
        pass="${pass}$(openssl rand -base64 32 | tr -dc 'A-Za-z0-9')"
    done
    echo "${pass:0:$length}"
}

# Échappement défensif d'une valeur avant écriture dans le .env.
# Ceinture et bretelles : même si generate_password ne produit plus de
# caractères spéciaux, cette fonction neutralise un $ resté dans une valeur
# saisie manuellement par l'utilisateur (ex: mot de passe personnalisé).
escape_env_value() {
    local val="$1"
    # Échappe les $ pour Docker Compose ($ -> $$)
    val="${val//\$/\$\$}"
    # Échappe les guillemets doubles et backslashes au cas où la valeur
    # serait quand même entourée de guillemets ailleurs dans la chaîne.
    val="${val//\\/\\\\}"
    val="${val//\"/\\\"}"
    echo "$val"
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
        # Création explicite pour les deux hosts les plus utilisés (% pour
        # les connexions inter-containers, localhost pour les connexions
        # directes/scripts) afin d'éviter le mismatch d'host rencontré sur
        # le projet equitasun (Access denied alors que le compte existe).
        docker exec shared-db mysql -uroot -prootpassword -e "CREATE USER IF NOT EXISTS '$DB_USER'@'%' IDENTIFIED BY '$DB_PASSWORD';"
        docker exec shared-db mysql -uroot -prootpassword -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
        docker exec shared-db mysql -uroot -prootpassword -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'%';"
        docker exec shared-db mysql -uroot -prootpassword -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';"
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
# IMPORTANT : aucune valeur n'est entourée de guillemets. Docker Compose ne
# retire pas les guillemets d'un .env (contrairement à un shell) : une valeur
# écrite comme DB_USER="x" est lue littéralement comme la chaîne "x" avec
# les guillemets inclus, ce qui casse les connexions MySQL (vécu sur le
# projet equitasun : Access denied for user '"equitasun_user"'@'localhost').
# Chaque valeur passe en plus par escape_env_value() pour neutraliser un $
# qui aurait pu être saisi manuellement (ask), même si generate_password()
# n'en produit plus.
cat <<EOF > .env
###############################################
# 🌐 Configuration Projet WordPress
###############################################

PROJECT_NAME=$(escape_env_value "$PROJECT_NAME")
WP_DOMAIN=$(escape_env_value "$WP_DOMAIN")
WP_TITLE=$(escape_env_value "$WP_TITLE")
WP_ADMIN_USER=$(escape_env_value "$WP_ADMIN_USER")
WP_ADMIN_PASSWORD=$(escape_env_value "$WP_ADMIN_PASSWORD")
WP_ADMIN_EMAIL=$(escape_env_value "$WP_ADMIN_EMAIL")

WP_CHILD_THEME_NAME=$(escape_env_value "$WP_CHILD_THEME_NAME")

###############################################
# 🗄️ Base de données
###############################################

DB_MODE=$(escape_env_value "$DB_MODE")
DB_HOST=$(escape_env_value "$DB_HOST")
DB_NAME=$(escape_env_value "$DB_NAME")
DB_USER=$(escape_env_value "$DB_USER")
DB_PASSWORD=$(escape_env_value "$DB_PASSWORD")
DB_PREFIX=$(escape_env_value "$DB_PREFIX")
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