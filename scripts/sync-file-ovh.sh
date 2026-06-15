#!/usr/bin/env bash

ACTION="$1"
ENV="$2"

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# Charger les variables .env
set -a
source "$PROJECT_ROOT/.env"
set +a

# Sélection des variables selon l'environnement
case "$ENV" in
    preprod)
        REMOTE_USER="$PREPROD_USER"
        REMOTE_HOST="$PREPROD_HOST"
        REMOTE_PATH="$PREPROD_PATH"
        REMOTE_PASSWORD="$PREPROD_PASSWORD"
        ;;
    prod)
        REMOTE_USER="$PROD_USER"
        REMOTE_HOST="$PROD_HOST"
        REMOTE_PATH="$PROD_PATH"
        REMOTE_PASSWORD="$PROD_PASSWORD"
        ;;
    *)
        echo "❌ Environnement inconnu : $ENV"
        exit 1
        ;;
esac
 
# Vérifier si lftp est installé
if ! command -v lftp &> /dev/null; then
    echo "❌ lftp n'est pas installé."
    echo "📦 Installation: brew install lftp"
    exit 1
fi

LOCAL_PATH="$PROJECT_ROOT/wp-content/"
REMOTE_FULL_PATH="$REMOTE_PATH/wp-content/"

echo "🔄 Synchronisation OVH"
echo "   Serveur: $REMOTE_HOST"
echo "   User: $REMOTE_USER"
echo "   Local: $LOCAL_PATH"
echo "   Remote: $REMOTE_FULL_PATH"

# ============================================================
# Créer un fichier de configuration temporaire pour lftp
# (évite les problèmes d'échappement de caractères spéciaux)
# ============================================================
LFTP_SCRIPT="/tmp/lftp_script_$$.txt"

cat > "$LFTP_SCRIPT" << EOF
set ftp:ssl-allow yes
set ftp:ssl-force no
set ftp:ssl-protect-data yes
set ftp:ssl-protect-list yes
set ssl:verify-certificate no
set ftp:passive-mode yes
set net:timeout 10
set net:max-retries 2
set net:reconnect-interval-base 5

open ftp://$REMOTE_HOST:21
user $REMOTE_USER $REMOTE_PASSWORD
EOF

if [ "$ACTION" = "pull" ]; then
    echo "⬇️  PULL : Téléchargement depuis $ENV..."
    
    cat >> "$LFTP_SCRIPT" << EOF
mirror --verbose --parallel=3 \\
  --exclude=.git/ --exclude=.DS_Store --exclude=node_modules/ \\
  $REMOTE_FULL_PATH $LOCAL_PATH
bye
EOF

elif [ "$ACTION" = "push" ]; then
    echo "⬆️  PUSH : Envoi vers $ENV..."
    
    cat >> "$LFTP_SCRIPT" << EOF
mirror --reverse --verbose --parallel=3 \\
  --exclude=.git/ --exclude=.DS_Store --exclude=.gitignore --exclude=node_modules/ \\
  $LOCAL_PATH $REMOTE_FULL_PATH
bye
EOF

else
    echo "❌ Action inconnue : $ACTION"
    echo "Usage: $0 [pull|push] [preprod|prod]"
    rm "$LFTP_SCRIPT"
    exit 1
fi

# Exécuter lftp avec le script
lftp -f "$LFTP_SCRIPT" 2>&1 | grep -v "get_pass_fd"
EXIT_CODE=${PIPESTATUS[0]}

# Nettoyer le fichier temporaire
rm "$LFTP_SCRIPT"

if [ $EXIT_CODE -eq 0 ]; then
    echo "✅ Synchronisation terminée avec succès !"
else
    echo "❌ Erreur lors de la synchronisation (code: $EXIT_CODE)"
    echo ""
    echo "🔍 Vérifications à faire:"
    echo "   1. Vérifiez vos identifiants FTP dans .env"
    echo "   2. Testez la connexion FTP manuellement:"
    echo "      lftp -u $REMOTE_USER ftp://$REMOTE_HOST"
    echo "   3. Vérifiez que le chemin distant existe: $REMOTE_PATH"
    echo "   4. Consultez l'espace client OVH pour vérifier l'état du FTP"
    exit 1
fi