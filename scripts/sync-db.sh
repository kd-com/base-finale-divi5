#!/usr/bin/env bash
# =====================================================
# sync-db.sh - Synchronisation BDD multi-environnements
# Compatible : preprod (SSH) et prod (SSH/FTP fallback)
# =====================================================

set -e

ACTION="$1"
ENV="$2"

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# =====================================================
# Chargement des variables .env
# =====================================================
if [ ! -f "$PROJECT_ROOT/.env" ]; then
    echo "❌ Fichier .env introuvable !"
    exit 1
fi

set -a
source "$PROJECT_ROOT/.env"
set +a

# =====================================================
# Validation des arguments
# =====================================================
if [ -z "$ACTION" ] || [ -z "$ENV" ]; then
    echo "Usage: $0 [pull|push] [preprod|prod]"
    echo ""
    echo "Exemples:"
    echo "  $0 pull preprod   # Récupérer la BDD de preprod"
    echo "  $0 pull prod      # Récupérer la BDD de prod"
    echo "  $0 push preprod   # Envoyer la BDD vers preprod"
    exit 1
fi

if [ "$ACTION" != "pull" ] && [ "$ACTION" != "push" ]; then
    echo "❌ Action invalide : $ACTION"
    echo "Actions supportées : pull, push"
    exit 1
fi

# =====================================================
# Sélection de l'environnement
# =====================================================
case "$ENV" in
    preprod)
        REMOTE_USER="$PREPROD_USER"
        REMOTE_HOST="$PREPROD_HOST"
        REMOTE_PATH="$PREPROD_PATH"
        REMOTE_DB_USER="$PREPROD_DB_USER"
        REMOTE_DB_PASS="$PREPROD_DB_PASS"
        REMOTE_DB_NAME="$PREPROD_DB_NAME"
        REMOTE_URL="$REMOTE_PREPROD_URL"
        PHP_DUMP_URL=""
        ;;
    prod)
        REMOTE_USER="$PROD_USER"
        REMOTE_HOST="$PROD_HOST"
        REMOTE_PATH="$PROD_PATH"
        REMOTE_DB_USER="${PROD_DB_USER:-}"
        REMOTE_DB_PASS="${PROD_DB_PASS:-}"
        REMOTE_DB_NAME="${PROD_DB_NAME:-}"
        REMOTE_URL="$REMOTE_PROD_URL"
        PHP_DUMP_URL="${PROD_DB_DUMP_URL}?token=${PROD_DB_DUMP_TOKEN}"
        ;;
    *)
        echo "❌ Environnement inconnu : $ENV"
        exit 1
        ;;
esac

# =====================================================
# Vérifications
# =====================================================
if [ -z "$REMOTE_HOST" ]; then
    echo "❌ REMOTE_HOST non défini pour $ENV"
    exit 1
fi

# =====================================================
# Test de disponibilité SSH
# =====================================================
echo "➡️  Test de connexion SSH vers $REMOTE_HOST..."
ssh -o BatchMode=yes -o ConnectTimeout=${SSH_CONNECT_TIMEOUT:-5} "$REMOTE_USER@$REMOTE_HOST" "exit" >/dev/null 2>&1
SSH_AVAILABLE=$?

if [ $SSH_AVAILABLE -eq 0 ]; then
    echo "✅ SSH disponible"
    SSH_MODE=true
else
    echo "⚠️  SSH non disponible"
    SSH_MODE=false
fi

# =====================================================
# PULL : Récupération de la base de données distante
# =====================================================
if [ "$ACTION" = "pull" ]; then
    echo ""
    echo "📥 PULL : Récupération de la BDD depuis $ENV..."
    
    # -------------------------------------------------
    # Méthode 1 : SSH (preprod ou prod si SSH dispo)
    # -------------------------------------------------
    if [ "$SSH_MODE" = true ]; then
        echo "🔧 Dump via SSH (mysqldump/mariadb-dump)..."
        
        # Tester d'abord mariadb-dump, puis mysqldump
        ssh "$REMOTE_USER@$REMOTE_HOST" "command -v mariadb-dump" >/dev/null 2>&1
        if [ $? -eq 0 ]; then
            DUMP_CMD="mariadb-dump"
        else
            DUMP_CMD="mysqldump"
        fi
        
        ssh "$REMOTE_USER@$REMOTE_HOST" \
            "$DUMP_CMD -u'$REMOTE_DB_USER' -p'$REMOTE_DB_PASS' '$REMOTE_DB_NAME'" \
            > /tmp/db_dump_${ENV}.sql
        
        if [ $? -eq 0 ] && [ -s /tmp/db_dump_${ENV}.sql ]; then
            echo "✅ Dump réussi via SSH"
        else
            echo "❌ Échec du dump via SSH"
            rm -f /tmp/db_dump_${ENV}.sql
            exit 1
        fi
    
    # -------------------------------------------------
    # Méthode 2 : PHP dump sécurisé (prod fallback)
    # -------------------------------------------------
    elif [ "$ENV" = "prod" ] && [ -n "$PHP_DUMP_URL" ] && [ -n "$PROD_DB_DUMP_TOKEN" ]; then
        echo "🔧 Dump via PHP sécurisé ($REMOTE_URL/dump_db.php)..."
        
        curl -L -f -o /tmp/db_dump_${ENV}.sql "$PHP_DUMP_URL" 2>/dev/null
        
        if [ $? -eq 0 ] && [ -s /tmp/db_dump_${ENV}.sql ]; then
            echo "✅ Dump réussi via PHP"
        else
            echo "❌ Échec du dump via PHP"
            echo "Vérifiez que dump_db.php est bien uploadé sur le serveur prod"
            echo "URL testée : $PHP_DUMP_URL"
            rm -f /tmp/db_dump_${ENV}.sql
            exit 1
        fi
    
    else
        echo "❌ Aucune méthode de dump disponible !"
        echo ""
        echo "Pour $ENV, vous devez :"
        if [ "$ENV" = "preprod" ]; then
            echo "  - Configurer l'accès SSH (clé SSH dans secrets GitHub)"
        else
            echo "  - Soit configurer l'accès SSH"
            echo "  - Soit uploader dump_db.php et configurer PROD_DB_DUMP_URL + PROD_DB_DUMP_TOKEN"
        fi
        exit 1
    fi
    
    # -------------------------------------------------
    # Import dans Docker
    # -------------------------------------------------
    echo ""
    echo "📦 Import dans la base de données locale..."
    
    # Détection du conteneur MySQL/MariaDB
    if [ -n "$DB_HOST" ] && docker ps --format '{{.Names}}' | grep -q "^${DB_HOST}$"; then
        CONTAINER="$DB_HOST"
    else
        # Fallback : chercher un conteneur db/mysql
        CONTAINER=$(docker ps --format '{{.Names}}' | grep -E 'db|mysql|mariadb' | head -n 1)
    fi
    
    if [ -z "$CONTAINER" ]; then
        echo "❌ Aucun conteneur MySQL/MariaDB trouvé"
        echo "Démarrez Docker : docker compose up -d"
        exit 1
    fi
    
    echo "📍 Conteneur détecté : $CONTAINER"
    
    # Import
    docker exec -i "$CONTAINER" mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < /tmp/db_dump_${ENV}.sql
    
    if [ $? -eq 0 ]; then
        echo "✅ Import réussi"
    else
        echo "❌ Échec de l'import"
        exit 1
    fi
    
    # -------------------------------------------------
    # Search-Replace des URLs
    # -------------------------------------------------
    echo ""
    echo "🔄 Mise à jour des URLs WordPress..."
    
    WP_CONTAINER="${PROJECT_NAME}_wordpress"
    
    if ! docker ps --format '{{.Names}}' | grep -q "^${WP_CONTAINER}$"; then
        echo "⚠️  Conteneur WordPress non trouvé : $WP_CONTAINER"
        echo "Le search-replace n'a pas été effectué"
        echo "Lancez manuellement :"
        echo "  docker exec -u www-data $WP_CONTAINER wp search-replace '$REMOTE_URL' 'https://$WP_DOMAIN' --all-tables --allow-root"
    else
        docker exec -u www-data "$WP_CONTAINER" \
            wp search-replace "$REMOTE_URL" "https://$WP_DOMAIN" --all-tables --allow-root
        
        if [ $? -eq 0 ]; then
            echo "✅ URLs mises à jour"
        else
            echo "⚠️  Erreur lors du search-replace"
        fi
    fi
    
    # Nettoyage
    rm -f /tmp/db_dump_${ENV}.sql
    
    echo ""
    echo "🎉 Synchronisation BDD terminée !"
    echo "📍 Base locale : $DB_NAME"
    echo "🌐 URL locale : https://$WP_DOMAIN"

# =====================================================
# PUSH : Envoi de la base de données locale vers distant
# =====================================================
elif [ "$ACTION" = "push" ]; then
    echo ""
    echo "📤 PUSH : Envoi de la BDD vers $ENV..."
    
    # Vérification SSH obligatoire pour PUSH
    if [ "$SSH_MODE" != true ]; then
        echo "❌ SSH requis pour envoyer la base de données"
        echo "Le PUSH n'est pas supporté en mode FTP"
        exit 1
    fi
    
    echo "⚠️  ATTENTION : Vous allez ÉCRASER la base de données de $ENV !"
    echo "📍 Base distante : $REMOTE_DB_NAME sur $REMOTE_HOST"
    echo ""
    read -p "Êtes-vous sûr ? (oui/non) : " confirm
    
    if [ "$confirm" != "oui" ]; then
        echo "❌ Opération annulée"
        exit 0
    fi
    
    # -------------------------------------------------
    # Export depuis Docker
    # -------------------------------------------------
    echo ""
    echo "📦 Export de la base locale..."
    
    # Détection du conteneur
    if [ -n "$DB_HOST" ] && docker ps --format '{{.Names}}' | grep -q "^${DB_HOST}$"; then
        CONTAINER="$DB_HOST"
    else
        CONTAINER=$(docker ps --format '{{.Names}}' | grep -E 'db|mysql|mariadb' | head -n 1)
    fi
    
    if [ -z "$CONTAINER" ]; then
        echo "❌ Aucun conteneur MySQL/MariaDB trouvé"
        exit 1
    fi
    
    docker exec -i "$CONTAINER" mysqldump -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" > /tmp/db_local_export.sql
    
    if [ $? -eq 0 ] && [ -s /tmp/db_local_export.sql ]; then
        echo "✅ Export réussi"
    else
        echo "❌ Échec de l'export"
        rm -f /tmp/db_local_export.sql
        exit 1
    fi
    
    # -------------------------------------------------
    # Search-Replace avant envoi
    # -------------------------------------------------
    echo ""
    echo "🔄 Préparation des URLs pour $ENV..."
    
    # Créer une copie pour le search-replace
    cp /tmp/db_local_export.sql /tmp/db_to_push.sql
    
    # Search-replace simple (sed)
    sed -i.bak "s|https://$WP_DOMAIN|$REMOTE_URL|g" /tmp/db_to_push.sql
    sed -i.bak "s|http://$WP_DOMAIN|$REMOTE_URL|g" /tmp/db_to_push.sql
    
    echo "✅ URLs préparées"
    
    # -------------------------------------------------
    # Import sur le serveur distant
    # -------------------------------------------------
    echo ""
    echo "📤 Import sur le serveur distant..."
    
    # Uploader le dump
    scp /tmp/db_to_push.sql "$REMOTE_USER@$REMOTE_HOST:/tmp/db_import.sql"
    
    # Importer
    ssh "$REMOTE_USER@$REMOTE_HOST" \
        "mysql -u'$REMOTE_DB_USER' -p'$REMOTE_DB_PASS' '$REMOTE_DB_NAME' < /tmp/db_import.sql && rm /tmp/db_import.sql"
    
    if [ $? -eq 0 ]; then
        echo "✅ Import distant réussi"
    else
        echo "❌ Échec de l'import distant"
        rm -f /tmp/db_local_export.sql /tmp/db_to_push.sql
        exit 1
    fi
    
    # Nettoyage
    rm -f /tmp/db_local_export.sql /tmp/db_to_push.sql /tmp/db_to_push.sql.bak
    
    echo ""
    echo "🎉 Base de données envoyée avec succès !"
    echo "📍 Base distante : $REMOTE_DB_NAME sur $REMOTE_HOST"
    echo "🌐 URL distante : $REMOTE_URL"
fi
 