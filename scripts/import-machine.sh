#!/bin/bash
# ---------------------------------------------
# Script d'importation WordPress depuis un dépôt Git temporaire
# Version automatique : détecte les conteneurs Docker
# ---------------------------------------------

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

REMOTE_REPO="git@github.com:kd-com/transfert-temporaire.git"
TMP_DIR="$(mktemp -d)"
WP_CONTENT_TARGET="$(pwd)/wp-content"
DUMP_FILE="wp-db-dump.sql"

echo -e "${YELLOW}Clonage du dépôt temporaire dans : $TMP_DIR${NC}"
git clone "$REMOTE_REPO" "$TMP_DIR" || { echo -e "${RED}Échec du clonage.${NC}"; exit 1; }

cd "$TMP_DIR" || exit 1

# --- Détection automatique des conteneurs Docker ---
WP_CONTAINER=$(docker ps --format '{{.Names}}' | grep -E 'wordpress|wp' | head -n 1)
DB_CONTAINER=$(docker ps --format '{{.Names}}' | grep -E 'db|mysql' | head -n 1)
 
if [ -z "$WP_CONTAINER" ] || [ -z "$DB_CONTAINER" ]; then
    echo -e "${RED}Impossible de détecter les conteneurs WordPress ou MySQL. Vérifiez que Docker est lancé.${NC}"
    exit 1
fi
echo -e "${GREEN}Conteneurs détectés : WordPress=$WP_CONTAINER, MySQL=$DB_CONTAINER${NC}"

# --- Récupération des variables depuis .env ---
if [ -f ".env" ]; then
    set -a
    source .env
    set +a
else
    echo -e "${RED}Fichier .env introuvable dans le dépôt.${NC}"
    exit 1
fi

DB_NAME=${DB_NAME:-"wordpress"}
DB_USER=${DB_USER:-"root"}
DB_PASS=${DB_PASSWORD:-"root"}

# --- Import SQL via Docker ---
if [ ! -f "$DUMP_FILE" ]; then
    echo -e "${RED}Fichier SQL introuvable dans le dépôt.${NC}"
    exit 2
fi

echo -e "${YELLOW}Import de la base de données via Docker (${DB_CONTAINER})...${NC}"
cat "$DUMP_FILE" | docker exec -i "$DB_CONTAINER" mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME"
if [ $? -eq 0 ]; then
    echo -e "${GREEN}Base importée avec succès !${NC}"
else
    echo -e "${RED}Erreur lors de l'import de la base.${NC}"
    exit 3
fi

# --- Copie des fichiers ---
echo -e "${YELLOW}Copie de wp-content et des fichiers de configuration...${NC}"
[ -d "wp-content" ] && cp -r wp-content/. "$WP_CONTENT_TARGET/"
[ -d "wp-config" ] && cp -r wp-config ./wp-config
[ -f ".env" ] && cp .env ./
[ -f "uploads.ini" ] && cp uploads.ini ./

# --- Nettoyage du dépôt temporaire ---
echo -e "${YELLOW}Nettoyage du dépôt temporaire sur GitHub...${NC}"
git rm -r * >/dev/null 2>&1
git commit -m "Cleanup temporaire après import automatique" >/dev/null 2>&1
git push origin main --force >/dev/null 2>&1
echo -e "${GREEN}Dépôt Git temporaire vidé.${NC}"

# --- Nettoyage local temporaire ---
cd ..
rm -rf "$TMP_DIR"
echo -e "${GREEN}Import terminé. Dossier temporaire supprimé.${NC}"
