#!/usr/bin/env bash
# =====================================================
# sync-file.sh - Synchronisation fichiers multi-environnements
# Compatible : SSH (rsync) et FTP (lftp) avec auto-détection
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
    echo "  $0 pull preprod   # Télécharger les fichiers de preprod"
    echo "  $0 pull prod      # Télécharger les fichiers de prod"
    echo "  $0 push preprod   # Envoyer les fichiers vers preprod"
    exit 1
fi

if [ "$ACTION" != "pull" ] && [ "$ACTION" != "push" ]; then
    echo "❌ Action invalide : $ACTION"
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
        REMOTE_PASS="${PREPROD_PASS:-}"
        ;;
    prod)
        REMOTE_USER="$PROD_USER"
        REMOTE_HOST="$PROD_HOST"
        REMOTE_PATH="$PROD_PATH"
        REMOTE_PASS="${PROD_PASS:-}"
        ;;
    *)
        echo "❌ Environnement inconnu : $ENV"
        exit 1
        ;;
esac

# =====================================================
# Vérifications
# =====================================================
if [ -z "$REMOTE_HOST" ] || [ -z "$REMOTE_USER" ] || [ -z "$REMOTE_PATH" ]; then
    echo "❌ Configuration incomplète pour $ENV"
    echo "Vérifiez REMOTE_HOST, REMOTE_USER et REMOTE_PATH dans .env"
    exit 1
fi

LOCAL_PATH="$PROJECT_ROOT/wp-content/"
REMOTE_FULL_PATH="$REMOTE_PATH/wp-content/"

echo "🔄 Synchronisation fichiers $ENV"
echo "📍 Serveur : $REMOTE_HOST"
echo "👤 User    : $REMOTE_USER"
echo "📂 Local   : $LOCAL_PATH"
echo "📂 Remote  : $REMOTE_FULL_PATH"
echo ""

# =====================================================
# Test de disponibilité SSH
# =====================================================
echo "➡️  Test de connexion SSH..."
ssh -o BatchMode=yes -o ConnectTimeout=${SSH_CONNECT_TIMEOUT:-5} "$REMOTE_USER@$REMOTE_HOST" "exit" >/dev/null 2>&1
SSH_AVAILABLE=$?

# =====================================================
# MODE SSH : rsync
# =====================================================
if [ $SSH_AVAILABLE -eq 0 ]; then
    echo "✅ SSH disponible → utilisation de rsync"
    echo ""
    
    RSYNC_OPTS=(
        -avz
        --progress
        -e "ssh -o StrictHostKeyChecking=no"
        --exclude=".git"
        --exclude=".DS_Store"
        --exclude="node_modules"
        --exclude=".gitignore"
    )
    
    if [ "$ACTION" = "pull" ]; then
        echo "⬇️  PULL : Téléchargement via rsync..."
        rsync "${RSYNC_OPTS[@]}" \
            "$REMOTE_USER@$REMOTE_HOST:$REMOTE_FULL_PATH" \
            "$LOCAL_PATH"
    
    elif [ "$ACTION" = "push" ]; then
        echo "⬆️  PUSH : Envoi via rsync..."
        rsync "${RSYNC_OPTS[@]}" \
            "$LOCAL_PATH" \
            "$REMOTE_USER@$REMOTE_HOST:$REMOTE_FULL_PATH"
    fi
    
    if [ $? -eq 0 ]; then
        echo ""
        echo "✅ Synchronisation rsync terminée !"
    else
        echo ""
        echo "❌ Erreur lors de la synchronisation rsync"
        exit 1
    fi

# =====================================================
# MODE FTP : lftp (fallback)
# =====================================================
else
    echo "⚠️  SSH non disponible → fallback FTP (lftp)"
    
    # Vérifier que PROD_PASS est défini
    if [ -z "$REMOTE_PASS" ]; then
        echo "❌ PROD_PASS (ou PREPROD_PASS) non défini dans .env"
        echo "Le mode FTP nécessite un mot de passe"
        exit 1
    fi
    
    # Vérifier que lftp est installé
    if ! command -v lftp &> /dev/null; then
        echo "❌ lftp n'est pas installé"
        echo "Installation :"
        echo "  • macOS   : brew install lftp"
        echo "  • Ubuntu  : sudo apt install lftp"
        echo "  • Windows : via WSL ou Git Bash avec lftp"
        exit 1
    fi
    
    echo ""
    
    # Créer un script lftp temporaire
    LFTP_SCRIPT="/tmp/lftp_script_$$.txt"
    
    cat > "$LFTP_SCRIPT" << EOF
set ftp:ssl-allow yes
set ftp:ssl-force no
set ftp:ssl-protect-data yes
set ftp:ssl-protect-list yes
set ssl:verify-certificate no
set ftp:passive-mode yes
set net:timeout 30
set net:max-retries 3
set net:reconnect-interval-base 5

open ftp://$REMOTE_HOST:21
user $REMOTE_USER $REMOTE_PASS
EOF

    if [ "$ACTION" = "pull" ]; then
        echo "⬇️  PULL : Téléchargement via FTP..."
        
        cat >> "$LFTP_SCRIPT" << EOF
mirror --verbose --parallel=3 \\
  --exclude=.git/ \\
  --exclude=.DS_Store \\
  --exclude=node_modules/ \\
  $REMOTE_FULL_PATH $LOCAL_PATH
bye
EOF

    elif [ "$ACTION" = "push" ]; then
        echo "⬆️  PUSH : Envoi via FTP..."
        
        cat >> "$LFTP_SCRIPT" << EOF
mirror --reverse --verbose --parallel=3 \\
  --exclude=.git/ \\
  --exclude=.DS_Store \\
  --exclude=.gitignore \\
  --exclude=node_modules/ \\
  $LOCAL_PATH $REMOTE_FULL_PATH
bye
EOF
    fi
    
    # Exécuter lftp
    lftp -f "$LFTP_SCRIPT" 2>&1 | grep -v "get_pass_fd"
    EXIT_CODE=${PIPESTATUS[0]}
    
    # Nettoyer
    rm -f "$LFTP_SCRIPT"
    
    if [ $EXIT_CODE -eq 0 ]; then
        echo ""
        echo "✅ Synchronisation FTP terminée !"
    else
        echo ""
        echo "❌ Erreur lors de la synchronisation FTP (code: $EXIT_CODE)"
        echo ""
        echo "🔍 Vérifications :"
        echo "  1. Vérifiez PROD_PASS dans .env"
        echo "  2. Testez manuellement : lftp -u $REMOTE_USER ftp://$REMOTE_HOST"
        echo "  3. Vérifiez le chemin distant : $REMOTE_PATH"
        exit 1
    fi
fi

echo ""
echo "🎉 Synchronisation terminée !"
 