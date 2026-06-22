#!/bin/bash
# ---------------------------------------------
# Script de transfert WordPress sur dépôt Git temporaire
# Inclut : wp-content, wp-config/, .env, uploads.ini et export SQL
#
# Usage : ./duplicate-machine-to-machine.sh
# (à lancer depuis la racine du projet, comme avant)
# ---------------------------------------------

set -uo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

PROJECT_ROOT="$(pwd)"
TMP_DIR="$(mktemp -d)"
REMOTE_REPO="git@github.com:kd-com/transfert-temporaire.git"
DUMP_FILE="wp-db-dump.sql"

# Une branche dédiée par projet, basée sur le nom du dossier courant.
# Evite que deux transferts de clients différents s'écrasent l'un l'autre.
PROJECT_NAME="$(basename "$PROJECT_ROOT" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9]/-/g')"
BRANCH="transfert-${PROJECT_NAME}"

echo -e "${YELLOW}Projet détecté : ${PROJECT_NAME} → branche ${BRANCH}${NC}"
echo -e "${YELLOW}Création du dossier temporaire : $TMP_DIR${NC}"

# --- Nettoyage des valeurs lues depuis un .env ---
# Retire les guillemets simples/doubles en début et fin de valeur, et les
# espaces parasites. Corrige le bug "Access denied for user '\"x\"'@'host'"
# causé par un .env écrit avec DB_USER="valeur" au lieu de DB_USER=valeur.
clean_env_value() {
    local val="$1"
    # trim espaces de début/fin
    val="$(echo "$val" | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//')"
    # retire une paire de guillemets doubles ou simples qui encadre toute la valeur
    if [[ "$val" =~ ^\"(.*)\"$ ]]; then
        val="${BASH_REMATCH[1]}"
    elif [[ "$val" =~ ^\'(.*)\'$ ]]; then
        val="${BASH_REMATCH[1]}"
    fi
    echo "$val"
}

# Lit une clé donnée dans un fichier .env et retourne une valeur nettoyée.
# Ignore les lignes commentées et ne garde que la première occurrence.
read_env_value() {
    local key="$1"
    local file="$2"
    local raw
    raw=$(grep -E "^${key}=" "$file" 2>/dev/null | head -n 1 | cut -d '=' -f2-)
    clean_env_value "$raw"
}

# --- Lecture des identifiants ---
WP_CONFIG=$(ls wp-config/*.php 2>/dev/null | grep -E 'wp-config(-local|-preprod|-prod)?\.php$' | head -n 1)
if [ -z "$WP_CONFIG" ]; then
    WP_CONFIG="wp-config/wp-config.php"
fi

DB_NAME=$(grep DB_NAME "$WP_CONFIG" 2>/dev/null | grep -v getenv | sed -nE "s/define\s*\(\s*'DB_NAME'\s*,\s*'([^']*)'.*/\1/p")
DB_USER=$(grep DB_USER "$WP_CONFIG" 2>/dev/null | grep -v getenv | sed -nE "s/define\s*\(\s*'DB_USER'\s*,\s*'([^']*)'.*/\1/p")
DB_PASS=$(grep DB_PASSWORD "$WP_CONFIG" 2>/dev/null | grep -v getenv | sed -nE "s/define\s*\(\s*'DB_PASSWORD'\s*,\s*'([^']*)'.*/\1/p")
DB_HOST=$(grep DB_HOST "$WP_CONFIG" 2>/dev/null | grep -v getenv | sed -nE "s/define\s*\(\s*'DB_HOST'\s*,\s*'([^']*)'.*/\1/p")

# Fallback .env : valeurs systématiquement nettoyées des guillemets/espaces parasites
[ -z "$DB_NAME" ] && [ -f ".env" ] && DB_NAME=$(read_env_value DB_NAME ".env")
[ -z "$DB_USER" ] && [ -f ".env" ] && DB_USER=$(read_env_value DB_USER ".env")
[ -z "$DB_PASS" ] && [ -f ".env" ] && DB_PASS=$(read_env_value DB_PASSWORD ".env")
[ -z "$DB_HOST" ] && [ -f ".env" ] && DB_HOST=$(read_env_value DB_HOST ".env")

if [ -z "$DB_NAME" ] || [ -z "$DB_USER" ]; then
    echo -e "${RED}❌ Impossible de lire les identifiants BDD (DB_NAME/DB_USER). Abandon.${NC}"
    rm -rf "$TMP_DIR"
    exit 1
fi

# --- Garde-fou : .env mal formé détecté avant même de tenter l'export ---
# Si une valeur lue dans wp-config.php contient encore des guillemets,
# c'est que le .env source était mal écrit (ex: DB_USER="user").
for pair in "DB_NAME:$DB_NAME" "DB_USER:$DB_USER" "DB_PASS:$DB_PASS" "DB_HOST:$DB_HOST"; do
    key="${pair%%:*}"
    val="${pair#*:}"
    if [[ "$val" == \"*\" ]] || [[ "$val" == \'*\' ]]; then
        echo -e "${RED}⚠️  Valeur ${key} suspecte (guillemets non retirés) : ${val}${NC}"
        echo -e "${YELLOW}   Vérifie le .env source — il ne doit pas y avoir de guillemets autour des valeurs.${NC}"
    fi
done

# --- Export MySQL ---
echo -e "${YELLOW}Export de la base de données...${NC}"

DUMP_OK=0
if command -v mysqldump >/dev/null 2>&1; then
    if mysqldump --single-transaction --quick --no-tablespaces \
        -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$TMP_DIR/$DUMP_FILE" 2>"$TMP_DIR/mysqldump.log"; then
        DUMP_OK=1
    fi
else
    # Détection scopée au projet courant (docker compose), pas à tous les containers du Mac
    MYSQL_CONTAINER=$(docker compose ps --format '{{.Name}}' 2>/dev/null | grep -E 'db|mysql' | head -n 1)
    [ -z "$MYSQL_CONTAINER" ] && MYSQL_CONTAINER=$(docker ps --format '{{.Names}}' | grep -E 'db|mysql' | head -n 1)

    if [ -n "$MYSQL_CONTAINER" ]; then
        echo -e "${YELLOW}Container détecté : $MYSQL_CONTAINER${NC}"
        if docker exec -i "$MYSQL_CONTAINER" mysqldump --single-transaction --quick --no-tablespaces \
            -h "localhost" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$TMP_DIR/$DUMP_FILE" 2>"$TMP_DIR/mysqldump.log"; then
            DUMP_OK=1
        fi
    else
        echo -e "${RED}❌ Aucun container MySQL/DB trouvé pour ce projet.${NC}"
    fi
fi

if [ "$DUMP_OK" -ne 1 ] || [ ! -s "$TMP_DIR/$DUMP_FILE" ]; then
    echo -e "${RED}❌ Export SQL échoué ou vide. Voir $TMP_DIR/mysqldump.log${NC}"
    cat "$TMP_DIR/mysqldump.log" 2>/dev/null
    # Diagnostic ciblé : message MySQL classique en cas de mismatch host/guillemets
    if grep -qi "access denied" "$TMP_DIR/mysqldump.log" 2>/dev/null; then
        echo -e "${YELLOW}💡 Piste : vérifie que l'utilisateur MySQL existe pour le bon host (localhost vs %)${NC}"
        echo -e "${YELLOW}   et que le .env ne contient pas de guillemets autour de DB_USER/DB_PASSWORD.${NC}"
    fi
    rm -rf "$TMP_DIR"
    exit 1
fi

gzip "$TMP_DIR/$DUMP_FILE"
echo -e "${GREEN}Base exportée et compressée : $TMP_DIR/$DUMP_FILE.gz${NC}"

# --- Copie des fichiers ---
echo -e "${YELLOW}Copie des fichiers du projet...${NC}"
[ -d wp-content ] && cp -r wp-content "$TMP_DIR/"
[ -d wp-config ] && cp -r wp-config "$TMP_DIR/"
[ -f uploads.ini ] && cp uploads.ini "$TMP_DIR/"
[ -f .env ] && cp .env "$TMP_DIR/"

echo -e "${YELLOW}⚠️  Rappel : .env / wp-config contiennent des identifiants. Repo distant à garder privé.${NC}"

# --- Initialisation Git ---
echo -e "${YELLOW}Initialisation du dépôt Git temporaire (branche ${BRANCH})...${NC}"
cd "$TMP_DIR" || exit 1
git init -q
git checkout -q -b "$BRANCH"
git add .
git commit -q -m "Transfert ${PROJECT_NAME} $(date +%Y%m%d_%H%M%S)"

git remote add origin "$REMOTE_REPO"
git push -q -f origin "$BRANCH"
echo -e "${GREEN}✅ Projet transféré vers $REMOTE_REPO (branche $BRANCH)${NC}"

# --- Nettoyage ---
cd "$PROJECT_ROOT"
rm -rf "$TMP_DIR"
echo -e "${GREEN}Dossier temporaire supprimé.${NC}"
echo -e "${YELLOW}Sur l'autre Mac, lance : ./restore-machine-to-machine.sh${NC}"