#!/bin/bash
# ---------------------------------------------
# Script de transfert WordPress sur dépôt Git temporaire
# Inclut : wp-content, wp-config/, .env, uploads.ini et export SQL
# ---------------------------------------------

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

PROJECT_ROOT="$(pwd)"
TMP_DIR="$(mktemp -d)"
REMOTE_REPO="git@github.com:kd-com/transfert-temporaire.git"
DUMP_FILE="wp-db-dump.sql"

echo -e "${YELLOW}Création du dossier temporaire : $TMP_DIR${NC}"

# --- Export MySQL ---
echo -e "${YELLOW}Export de la base de données...${NC}"

WP_CONFIG=$(ls wp-config/*.php 2>/dev/null | grep -E 'wp-config(-local|-preprod|-prod)?\.php$' | head -n 1)
if [ -z "$WP_CONFIG" ]; then
    WP_CONFIG="wp-config/wp-config.php"
fi
 
# Lecture des identifiants
DB_NAME=$(grep DB_NAME $WP_CONFIG | grep -v getenv | sed -nE "s/define\s*\(\s*'DB_NAME'\s*,\s*'([^']*)'.*/\1/p")
DB_USER=$(grep DB_USER $WP_CONFIG | grep -v getenv | sed -nE "s/define\s*\(\s*'DB_USER'\s*,\s*'([^']*)'.*/\1/p")
DB_PASS=$(grep DB_PASSWORD $WP_CONFIG | grep -v getenv | sed -nE "s/define\s*\(\s*'DB_PASSWORD'\s*,\s*'([^']*)'.*/\1/p")
DB_HOST=$(grep DB_HOST $WP_CONFIG | grep -v getenv | sed -nE "s/define\s*\(\s*'DB_HOST'\s*,\s*'([^']*)'.*/\1/p")

# Fallback sur .env si nécessaire
[ -z "$DB_NAME" ] && [ -f ".env" ] && DB_NAME=$(grep DB_NAME .env | cut -d '=' -f2)
[ -z "$DB_USER" ] && [ -f ".env" ] && DB_USER=$(grep DB_USER .env | cut -d '=' -f2)
[ -z "$DB_PASS" ] && [ -f ".env" ] && DB_PASS=$(grep DB_PASSWORD .env | cut -d '=' -f2)
[ -z "$DB_HOST" ] && [ -f ".env" ] && DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)

# Export SQL
if command -v mysqldump >/dev/null 2>&1; then
    mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$TMP_DIR/$DUMP_FILE"
else
    MYSQL_CONTAINER=$(docker ps --format '{{.Names}}' | grep -E 'db|mysql' | head -n 1)
    docker exec -i "$MYSQL_CONTAINER" mysqldump -h "localhost" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$TMP_DIR/$DUMP_FILE"
fi
echo -e "${GREEN}Base exportée : $TMP_DIR/$DUMP_FILE${NC}"

# --- Copie des fichiers ---
echo -e "${YELLOW}Copie des fichiers du projet...${NC}"
cp -r wp-content "$TMP_DIR/"
cp -r wp-config "$TMP_DIR/"
[ -f uploads.ini ] && cp uploads.ini "$TMP_DIR/"
[ -f .env ] && cp .env "$TMP_DIR/"

# --- Initialisation Git ---
echo -e "${YELLOW}Initialisation du dépôt Git temporaire...${NC}"
cd "$TMP_DIR" || exit 1
git init
git add .
git commit -m "Transfert temporaire WordPress $(date +%Y%m%d_%H%M%S)"
git branch -M main

# --- Push vers le dépôt temporaire ---
git remote add origin "$REMOTE_REPO"
git push -u origin main --force
echo -e "${GREEN}✅ Projet WordPress transféré vers $REMOTE_REPO${NC}"

# --- Nettoyage ---
cd "$PROJECT_ROOT"
rm -rf "$TMP_DIR"
echo -e "${GREEN}Dossier temporaire supprimé.${NC}"
