#!/bin/bash
set -e

echo "-------------------------------------------"
echo "   📦 Chargement des variables..."
echo "-------------------------------------------"

# Vérifie que le .env existe
if [ ! -f .env ]; then
    echo "❌ Fichier .env introuvable. Lance d'abord ./scripts/01_setup-project.sh"
    exit 1
fi

# Export des variables du .env (compatible Mac/PC)
# Note : compatible avec un .env sans guillemets autour des valeurs (format
# généré par la version corrigée de 01_setup-project.sh) comme avec l'ancien
# format entre guillemets — `source` gère les deux nativement en bash.
set -a
source .env
set +a

# Détermination du nom du conteneur WordPress
WORDPRESS_CONTAINER="${PROJECT_NAME}_wordpress"

###############################################
# 📝 Vérification du fichier wp-config.local.php
###############################################

echo ""
echo "📝 Vérification du fichier wp-config.local.php..."

# Si wp-config.local.php existe et est un répertoire, le supprimer
if [ -d ./wp-config.local.php ]; then
    echo "⚠️  wp-config.local.php est un répertoire, suppression..."
    rm -rf ./wp-config.local.php
fi

# Vérifier si le fichier existe
if [ ! -f ./wp-config.local.php ]; then
    echo "❌ Le fichier wp-config.local.php n'existe pas !"
    echo "   Veuillez créer ce fichier avant de lancer le script."
    exit 1
fi

echo "✅ Fichier wp-config.local.php trouvé"

###############################################
# 📦 Démarrage du conteneur WordPress
###############################################

echo ""
echo "📦 Démarrage du conteneur WordPress..."

# Arrêter le conteneur s'il tourne
docker-compose down 2>/dev/null || true

# Démarrer avec le wp-config.local.php déjà en place
docker-compose up -d wordpress

echo "⏳ Attente du démarrage complet..."
sleep 8

###############################################
# ⏳ Fonction d'attente MySQL/MariaDB
###############################################

wait_for_mysql() {
    local host="$1"
    local user="$2"
    local password="$3"
    local db="$4"
    local max_retries=20
    local count=0
    local sleep_time=3

    echo "⏳ Vérification MySQL/MariaDB ($host)..."

    while ! docker exec shared-db mysql \
        -h"$host" \
        -u"$user" \
        -p"$password" \
        --ssl-mode=DISABLED \
        -e "USE \`$db\`;" >/dev/null 2>&1; do
        
        count=$((count + 1))
        if [ $count -ge $max_retries ]; then
            echo "❌ MySQL/MariaDB n'est pas prêt après $((max_retries * sleep_time)) secondes."
            echo "   Vérifie le host, l'utilisateur et le mot de passe dans .env"
            exit 1
        fi
        echo "   ➜ En attente de MySQL/MariaDB... ($count/$max_retries)"
        sleep $sleep_time
    done

    echo "✅ MySQL/MariaDB est prêt !"
}

wait_for_mysql "$DB_HOST" "$DB_USER" "$DB_PASSWORD" "$DB_NAME"

###############################################
# 🔐 Génération des clés de sécurité
###############################################

echo ""
echo "🔐 Génération des clés de sécurité WordPress..."

# Récupération des clés depuis l'API WordPress
SECURITY_KEYS=$(curl -s https://api.wordpress.org/secret-key/1.1/salt/)

if [ -z "$SECURITY_KEYS" ]; then
    echo "⚠️  Impossible de récupérer les clés depuis l'API WordPress"
    echo "   Génération de clés aléatoires localement..."
    
    # Génération locale si l'API ne répond pas (compatible Mac/PC)
    AUTH_KEY=$(openssl rand -base64 32)
    SECURE_AUTH_KEY=$(openssl rand -base64 32)
    LOGGED_IN_KEY=$(openssl rand -base64 32)
    NONCE_KEY=$(openssl rand -base64 32)
    AUTH_SALT=$(openssl rand -base64 32)
    SECURE_AUTH_SALT=$(openssl rand -base64 32)
    LOGGED_IN_SALT=$(openssl rand -base64 32)
    NONCE_SALT=$(openssl rand -base64 32)
    
    SECURITY_KEYS="define('AUTH_KEY', '$AUTH_KEY');
define('SECURE_AUTH_KEY', '$SECURE_AUTH_KEY');
define('LOGGED_IN_KEY', '$LOGGED_IN_KEY');
define('NONCE_KEY', '$NONCE_KEY');
define('AUTH_SALT', '$AUTH_SALT');
define('SECURE_AUTH_SALT', '$SECURE_AUTH_SALT');
define('LOGGED_IN_SALT', '$LOGGED_IN_SALT');
define('NONCE_SALT', '$NONCE_SALT');"
else
    echo "✅ Clés récupérées de l'API WordPress"
fi

###############################################
# 🔐 Mise à jour des clés dans wp-config.local.php
###############################################

echo ""
echo "🔐 Mise à jour des clés de sécurité dans wp-config.local.php..."

# Créer une sauvegarde
cp ./wp-config.local.php ./wp-config.local.php.backup

# Supprimer les anciennes clés (lignes entre AUTH_KEY et NONCE_SALT)
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    sed -i '' "/define('AUTH_KEY'/,/define('NONCE_SALT'/d" ./wp-config.local.php
else
    # Linux et Windows (Git Bash)
    sed -i "/define('AUTH_KEY'/,/define('NONCE_SALT'/d" ./wp-config.local.php
fi

# Insérer les nouvelles clés avant $table_prefix
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    sed -i '' "/\$table_prefix/i\\
$SECURITY_KEYS
" ./wp-config.local.php
else
    # Linux et Windows (Git Bash)
    sed -i "/\$table_prefix/i\\$SECURITY_KEYS" ./wp-config.local.php
fi

echo "✅ Clés de sécurité mises à jour dans wp-config.local.php"

###############################################
# 🔄 Redémarrage pour prendre en compte les clés
###############################################

echo ""
echo "🔄 Redémarrage du conteneur pour appliquer les nouvelles clés..."

docker-compose restart wordpress

echo "⏳ Attente du redémarrage..."
sleep 5

###############################################
# ⚡ Installation WordPress via WP-CLI
###############################################

echo ""
echo "⚡ Installation WordPress..."

# Vérifier si WordPress est déjà installé
if docker exec -i "$WORDPRESS_CONTAINER" wp core is-installed --allow-root 2>/dev/null; then
    echo "⚠️  WordPress est déjà installé, mise à jour des URLs..."
    docker exec -i "$WORDPRESS_CONTAINER" wp option update siteurl "https://$WP_DOMAIN" --allow-root
    docker exec -i "$WORDPRESS_CONTAINER" wp option update home "https://$WP_DOMAIN" --allow-root
else
    echo "🆕 Nouvelle installation WordPress..."
    docker exec -i "$WORDPRESS_CONTAINER" wp core install \
        --url="https://$WP_DOMAIN" \
        --title="$WP_TITLE" \
        --admin_user="$WP_ADMIN_USER" \
        --admin_password="$WP_ADMIN_PASSWORD" \
        --admin_email="$WP_ADMIN_EMAIL" \
        --skip-email \
        --allow-root
fi

###############################################
# 🔒 Vérification HTTPS dans la base
###############################################

echo ""
echo "🔒 Vérification de la configuration HTTPS..."
docker exec -i "$WORDPRESS_CONTAINER" wp option get siteurl --allow-root
docker exec -i "$WORDPRESS_CONTAINER" wp option get home --allow-root

###############################################
# 🧹 Vidage du cache
###############################################

echo ""
echo "🧹 Vidage du cache WordPress..."
docker exec -i "$WORDPRESS_CONTAINER" wp cache flush --allow-root 2>/dev/null || echo "⚠️  Pas de cache à vider"

###############################################
# ✅ Résumé final
###############################################

echo ""
echo "-------------------------------------------"
echo "🎉 WordPress configuré avec succès !"
echo "-------------------------------------------"
echo "URL : https://$WP_DOMAIN"
echo "Admin : $WP_ADMIN_USER"
echo "Mot de passe : $WP_ADMIN_PASSWORD"
echo "Email : $WP_ADMIN_EMAIL"
echo "Préfixe de table : $DB_PREFIX"
echo ""
echo "📁 Fichiers utilisés :"
echo "   ✅ wp-config.local.php (avec nouvelles clés de sécurité)"
echo "   ✅ wp-config.local.php.backup (sauvegarde)"
echo "   ✅ wp-content/ (persistent)"
echo ""
echo "🔐 Configuration sécurisée :"
echo "   ✅ Clés de sécurité générées et insérées"
echo "   ✅ HTTPS activé via Traefik"
echo "   ✅ Configuration X-Forwarded-Proto"
echo "   ✅ WP_HOME et WP_SITEURL en HTTPS"
echo "   ✅ Base de données connectée"
echo "   ✅ Mode debug configuré"
echo ""
echo "⚠️  Prochaines étapes :"
echo "   1. Videz le cache du navigateur (Ctrl+Shift+R ou Cmd+Shift+R)"
echo "   2. Accédez à : https://$WP_DOMAIN"
echo "   3. Connectez-vous avec les identifiants ci-dessus"
echo "   4. Installez et activez le thème DIVI"
echo "   5. Testez le Visual Builder (il devrait fonctionner !)"
echo ""
echo "🔍 En cas de problème :"
echo "   - Vérifiez les logs : docker logs $WORDPRESS_CONTAINER"
echo "   - Vérifiez la console navigateur (F12)"
echo "   - Restaurez la sauvegarde si besoin : cp wp-config.local.php.backup wp-config.local.php"
echo ""