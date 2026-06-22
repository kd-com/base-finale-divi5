#!/bin/bash
# ---------------------------------------------
# Script de récupération WordPress depuis le dépôt Git temporaire
# Contrepartie de duplicate-machine-to-machine.sh
#
# Usage : ./restore-machine-to-machine.sh
# (à lancer depuis la racine du projet, même nom de dossier que sur le Mac source)
# ---------------------------------------------

set -uo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

PROJECT_ROOT="$(pwd)"
TMP_DIR="$(mktemp -d)"
REMOTE_REPO="git@github.com:kd-com/transfert-temporaire.git"
DUMP_FILE="wp-db-dump.sql.gz"

PROJECT_NAME="$(basename "$PROJECT_ROOT" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9]/-/g')"
BRANCH="${1:-transfert-${PROJECT_NAME}}"

echo -e "${YELLOW}Récupération de la branche ${BRANCH} depuis $REMOTE_REPO...${NC}"

if ! git clone -q --depth 1 --branch "$BRANCH" "$REMOTE_REPO" "$TMP_DIR" 2>"$TMP_DIR.log"; then
    echo -e "${RED}❌ Impossible de récupérer la branche $BRANCH. Vérifie le nom du dossier projet ou passe la branche en argument :${NC}"
    echo -e "   ./restore-machine-to-machine.sh transfert-nom-du-projet"
    rm -rf "$TMP_DIR" "$TMP_DIR.log"
    exit 1
fi

# --- Sauvegarde locale avant écrasement ---
BACKUP_DIR="$PROJECT_ROOT/.backup-$(date +%Y%m%d_%H%M%S)"
NEED_BACKUP=0
for item in wp-content wp-config .env uploads.ini; do
    [ -e "$PROJECT_ROOT/$item" ] && NEED_BACKUP=1
done

if [ "$NEED_BACKUP" -eq 1 ]; then
    echo -e "${YELLOW}Sauvegarde des fichiers locaux existants dans $BACKUP_DIR${NC}"
    mkdir -p "$BACKUP_DIR"
    for item in wp-content wp-config .env uploads.ini; do
        [ -e "$PROJECT_ROOT/$item" ] && mv "$PROJECT_ROOT/$item" "$BACKUP_DIR/"
    done
fi

# --- Copie des fichiers récupérés ---
echo -e "${YELLOW}Restauration des fichiers du projet...${NC}"
[ -d "$TMP_DIR/wp-content" ] && cp -r "$TMP_DIR/wp-content" "$PROJECT_ROOT/"
[ -d "$TMP_DIR/wp-config" ] && cp -r "$TMP_DIR/wp-config" "$PROJECT_ROOT/"
[ -f "$TMP_DIR/uploads.ini" ] && cp "$TMP_DIR/uploads.ini" "$PROJECT_ROOT/"
[ -f "$TMP_DIR/.env" ] && cp "$TMP_DIR/.env" "$PROJECT_ROOT/"

# --- Nettoyage des valeurs lues depuis un .env ---
# Retire les guillemets simples/doubles en début et fin de valeur, et les
# espaces parasites. Corrige le bug "Access denied for user '\"x\"'@'host'"
# causé par un .env écrit avec DB_USER="valeur" au lieu de DB_USER=valeur.
clean_env_value() {
    local val="$1"
    val="$(echo "$val" | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//')"
    if [[ "$val" =~ ^\"(.*)\"$ ]]; then
        val="${BASH_REMATCH[1]}"
    elif [[ "$val" =~ ^\'(.*)\'$ ]]; then
        val="${BASH_REMATCH[1]}"
    fi
    echo "$val"
}

read_env_value() {
    local key="$1"
    local file="$2"
    local raw
    raw=$(grep -E "^${key}=" "$file" 2>/dev/null | head -n 1 | cut -d '=' -f2-)
    clean_env_value "$raw"
}

# --- Lecture des identifiants (mêmes règles que le script de push) ---
WP_CONFIG=$(ls "$PROJECT_ROOT"/wp-config/*.php 2>/dev/null | grep -E 'wp-config(-local|-preprod|-prod)?\.php$' | head -n 1)
[ -z "$WP_CONFIG" ] && WP_CONFIG="$PROJECT_ROOT/wp-config/wp-config.php"

DB_NAME=$(grep DB_NAME "$WP_CONFIG" 2>/dev/null | grep -v getenv | sed -nE "s/define\s*\(\s*'DB_NAME'\s*,\s*'([^']*)'.*/\1/p")
DB_USER=$(grep DB_USER "$WP_CONFIG" 2>/dev/null | grep -v getenv | sed -nE "s/define\s*\(\s*'DB_USER'\s*,\s*'([^']*)'.*/\1/p")
DB_PASS=$(grep DB_PASSWORD "$WP_CONFIG" 2>/dev/null | grep -v getenv | sed -nE "s/define\s*\(\s*'DB_PASSWORD'\s*,\s*'([^']*)'.*/\1/p")
DB_HOST=$(grep DB_HOST "$WP_CONFIG" 2>/dev/null | grep -v getenv | sed -nE "s/define\s*\(\s*'DB_HOST'\s*,\s*'([^']*)'.*/\1/p")

# Fallback .env : valeurs systématiquement nettoyées des guillemets/espaces parasites
[ -z "$DB_NAME" ] && [ -f "$PROJECT_ROOT/.env" ] && DB_NAME=$(read_env_value DB_NAME "$PROJECT_ROOT/.env")
[ -z "$DB_USER" ] && [ -f "$PROJECT_ROOT/.env" ] && DB_USER=$(read_env_value DB_USER "$PROJECT_ROOT/.env")
[ -z "$DB_PASS" ] && [ -f "$PROJECT_ROOT/.env" ] && DB_PASS=$(read_env_value DB_PASSWORD "$PROJECT_ROOT/.env")
[ -z "$DB_HOST" ] && [ -f "$PROJECT_ROOT/.env" ] && DB_HOST=$(read_env_value DB_HOST "$PROJECT_ROOT/.env")

if [ -z "$DB_NAME" ] || [ -z "$DB_USER" ]; then
    echo -e "${RED}❌ Impossible de lire les identifiants BDD après restauration. Import SQL annulé.${NC}"
    echo -e "${GREEN}Les fichiers ont quand même été restaurés.${NC}"
    rm -rf "$TMP_DIR"
    exit 1
fi

# --- Garde-fou : .env mal formé détecté avant même de tenter l'import ---
for pair in "DB_NAME:$DB_NAME" "DB_USER:$DB_USER" "DB_PASS:$DB_PASS" "DB_HOST:$DB_HOST"; do
    key="${pair%%:*}"
    val="${pair#*:}"
    if [[ "$val" == \"*\" ]] || [[ "$val" == \'*\' ]]; then
        echo -e "${RED}⚠️  Valeur ${key} suspecte (guillemets non retirés) : ${val}${NC}"
        echo -e "${YELLOW}   Vérifie le .env restauré — il ne doit pas y avoir de guillemets autour des valeurs.${NC}"
    fi
done

# --- Import MySQL ---
echo -e "${YELLOW}Import de la base de données...${NC}"
if [ ! -f "$TMP_DIR/$DUMP_FILE" ]; then
    echo -e "${RED}❌ Dump SQL introuvable dans le dépôt récupéré.${NC}"
    rm -rf "$TMP_DIR"
    exit 1
fi
gunzip -k "$TMP_DIR/$DUMP_FILE"
SQL_PATH="${TMP_DIR}/${DUMP_FILE%.gz}"

IMPORT_OK=0
MYSQL_CONTAINER=""
if command -v mysql >/dev/null 2>&1; then
    if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SQL_PATH" 2>"$TMP_DIR/mysql-import.log"; then
        IMPORT_OK=1
    fi
else
    MYSQL_CONTAINER=$(docker compose ps --format '{{.Name}}' 2>/dev/null | grep -E 'db|mysql' | head -n 1)
    [ -z "$MYSQL_CONTAINER" ] && MYSQL_CONTAINER=$(docker ps --format '{{.Names}}' | grep -E 'db|mysql' | head -n 1)

    if [ -n "$MYSQL_CONTAINER" ]; then
        echo -e "${YELLOW}Container détecté : $MYSQL_CONTAINER${NC}"
        if docker exec -i "$MYSQL_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SQL_PATH" 2>"$TMP_DIR/mysql-import.log"; then
            IMPORT_OK=1
        fi
    else
        echo -e "${RED}❌ Aucun container MySQL/DB trouvé pour ce projet.${NC}"
    fi
fi

if [ "$IMPORT_OK" -ne 1 ]; then
    echo -e "${RED}❌ Import SQL échoué. Voir $TMP_DIR/mysql-import.log${NC}"
    cat "$TMP_DIR/mysql-import.log" 2>/dev/null

    # Diagnostic ciblé "Access denied" : cause la plus fréquente après migration
    # de Mac, liée à un mismatch d'host MySQL (user créé en @% mais pas @localhost,
    # ou inversement) plutôt qu'à un mauvais mot de passe.
    if grep -qi "access denied" "$TMP_DIR/mysql-import.log" 2>/dev/null; then
        echo -e "${YELLOW}💡 Piste : l'utilisateur '${DB_USER}' existe peut-être en base mais pas pour cet host.${NC}"
        if [ -n "$MYSQL_CONTAINER" ]; then
            echo -e "${YELLOW}   Vérifie avec :${NC}"
            echo -e "   docker exec -it $MYSQL_CONTAINER mysql -u root -p -e \"SELECT user, host FROM mysql.user WHERE user='${DB_USER}';\""
            echo -e "${YELLOW}   Si seul l'host '%' apparaît (pas 'localhost'), crée l'entrée manquante :${NC}"
            echo -e "   docker exec -it $MYSQL_CONTAINER mysql -u root -p -e \"CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}'; GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;\""
        fi
    fi

    rm -rf "$TMP_DIR"
    exit 1
fi

echo -e "${GREEN}✅ Fichiers et base de données restaurés depuis la branche $BRANCH${NC}"

# --- Nettoyage local ---
rm -rf "$TMP_DIR"

# --- Suppression automatique de la branche distante (secrets ne doivent pas traîner) ---
echo -e "${YELLOW}Suppression de la branche distante $BRANCH...${NC}"
if git push "$REMOTE_REPO" --delete "$BRANCH" 2>"$TMP_DIR.deletelog"; then
    echo -e "${GREEN}Branche distante supprimée du dépôt temporaire.${NC}"
else
    echo -e "${RED}⚠️  Suppression de la branche échouée, à faire manuellement :${NC}"
    echo -e "   git push $REMOTE_REPO --delete $BRANCH"
    cat "$TMP_DIR.deletelog" 2>/dev/null
fi
rm -f "$TMP_DIR.deletelog"