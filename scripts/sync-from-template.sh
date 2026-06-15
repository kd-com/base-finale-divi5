#!/bin/bash
# =====================================================
# sync-from-template.sh - Mise à jour projet client
# - scripts/          : sync automatique (commun)
# - themes/kd-com/    : une PR GitHub par fichier
# =====================================================

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
NC='\033[0m'

CONFIG_FILE=".template-sync.json"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# =====================================================
# Vérifications préalables
# =====================================================

if ! command -v jq &> /dev/null; then
    echo -e "${RED}❌ jq n'est pas installé${NC}"
    echo "  → brew install jq"; exit 1
fi
if ! command -v gh &> /dev/null; then
    echo -e "${RED}❌ GitHub CLI (gh) n'est pas installé${NC}"
    echo "  → brew install gh  puis  gh auth login"; exit 1
fi
if [ ! -f "$CONFIG_FILE" ]; then
    echo -e "${RED}❌ $CONFIG_FILE introuvable${NC}"; exit 1
fi
if [ ! -f "$SCRIPT_DIR/version-utils.sh" ]; then
    echo -e "${RED}❌ scripts/version-utils.sh introuvable${NC}"; exit 1
fi
source "$SCRIPT_DIR/version-utils.sh"

# =====================================================
# Fichiers du thème à ne JAMAIS modifier
# =====================================================

PROTECTED_FILES=(
    "$THEME_JSON_PATH"
    "wp-content/themes/kd-com/sass/_theme-colors.scss"
)

is_protected() {
    local file="$1"
    for p in "${PROTECTED_FILES[@]}"; do
        [[ "$file" == "$p" ]] && return 0
    done
    return 1
}

is_theme_file()   { [[ "$1" == wp-content/themes/kd-com/* ]]; }
is_scripts_file() { [[ "$1" == scripts/* ]]; }

# =====================================================
# Lecture de la configuration
# =====================================================

TEMPLATE_REPO=$(jq -r '.template_repo' "$CONFIG_FILE")
TEMPLATE_BRANCH=$(jq -r '.template_branch' "$CONFIG_FILE")
SYNC_FOLDERS=$(jq -r '.sync_folders[]' "$CONFIG_FILE")
PROJECTS_DIR=$(jq -r '.projects_dir // empty' "$CONFIG_FILE")

if [ "$TEMPLATE_REPO" = "null" ] || [ -z "$TEMPLATE_REPO" ]; then
    echo -e "${RED}❌ template_repo non configuré${NC}"; exit 1
fi

PROJECT_ROOT=$(git rev-parse --show-toplevel 2>/dev/null || pwd)

# =====================================================
# Interface principale
# =====================================================

clear
echo -e "${BLUE}╔══════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Mise à jour depuis le Template             ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════╝${NC}"
echo ""
echo -e "Template source : ${YELLOW}$TEMPLATE_REPO${NC}"
echo -e "Branche         : ${YELLOW}$TEMPLATE_BRANCH${NC}"
echo ""

if [ -z "$PROJECTS_DIR" ]; then
    read -p "📁 Chemin du dossier contenant vos projets clients : " PROJECTS_DIR
    PROJECTS_DIR="${PROJECTS_DIR/#\~/$HOME}"
    read -p "Sauvegarder ce chemin dans la config ? (o/N) : " save_dir
    if [[ "$save_dir" =~ ^[Oo]$ ]]; then
        tmp=$(mktemp)
        jq --arg dir "$PROJECTS_DIR" '. + {projects_dir: $dir}' "$CONFIG_FILE" > "$tmp" \
            && mv "$tmp" "$CONFIG_FILE"
        echo -e "${GREEN}✓ Chemin sauvegardé${NC}"
    fi
fi

if [ ! -d "$PROJECTS_DIR" ]; then
    echo -e "${RED}❌ Dossier introuvable : $PROJECTS_DIR${NC}"; exit 1
fi

# =====================================================
# Clonage du template
# =====================================================

TEMP_DIR=$(mktemp -d)
trap "rm -rf $TEMP_DIR" EXIT

echo -e "${YELLOW}📁 Récupération du template...${NC}"
if ! git clone --depth 1 --branch "$TEMPLATE_BRANCH" "$TEMPLATE_REPO" "$TEMP_DIR" 2>/dev/null; then
    echo -e "${RED}❌ Échec du clonage${NC}"; exit 1
fi

TEMPLATE_VERSION=$(get_version "$TEMP_DIR")
echo -e "${GREEN}✓ Template récupéré — version ${YELLOW}$TEMPLATE_VERSION${NC}"
echo ""

# =====================================================
# Sélection du projet client
# =====================================================

echo -e "${YELLOW}Projets disponibles dans $PROJECTS_DIR :${NC}"
echo ""

PROJECTS=()
INDEX=1
while IFS= read -r -d '' dir; do
    PROJECTS+=("$dir")
    project_version=$(get_version "$dir")
    if [ "$project_version" = "$TEMPLATE_VERSION" ]; then
        version_label="${GREEN}$project_version ✓ à jour${NC}"
    else
        version_label="${YELLOW}$project_version${NC} → ${GREEN}$TEMPLATE_VERSION${NC}"
    fi
    echo -e "  $INDEX) $(basename "$dir")  [$version_label]"
    ((INDEX++))
done < <(find "$PROJECTS_DIR" -maxdepth 1 -mindepth 1 -type d \
    -name "*.git" -prune -o -type d -print0 | sort -z)

if [ ${#PROJECTS[@]} -eq 0 ]; then
    echo -e "${RED}❌ Aucun projet trouvé dans $PROJECTS_DIR${NC}"; exit 1
fi

echo ""
read -p "Numéro du projet à mettre à jour : " project_choice

if ! [[ "$project_choice" =~ ^[0-9]+$ ]] || \
   [ "$project_choice" -lt 1 ] || \
   [ "$project_choice" -gt ${#PROJECTS[@]} ]; then
    echo -e "${RED}❌ Choix invalide${NC}"; exit 1
fi

TARGET_PROJECT="${PROJECTS[$((project_choice-1))]}"
TARGET_NAME=$(basename "$TARGET_PROJECT")
TARGET_VERSION=$(get_version "$TARGET_PROJECT")

echo ""
echo -e "Projet sélectionné : ${CYAN}$TARGET_NAME${NC}"
echo -e "Version actuelle   : ${YELLOW}$TARGET_VERSION${NC}"
echo -e "Version template   : ${GREEN}$TEMPLATE_VERSION${NC}"
echo ""

if [ ! -d "$TARGET_PROJECT/.git" ]; then
    echo -e "${RED}❌ Ce dossier n'est pas un dépôt Git${NC}"; exit 1
fi

if ! gh auth status &>/dev/null; then
    echo -e "${RED}❌ GitHub CLI non authentifié. Lancez : gh auth login${NC}"; exit 1
fi

# =====================================================
# Helpers
# =====================================================

get_ts() {
    local ts
    ts=$(git -C "$1" log -1 --format="%ct" -- "$2" 2>/dev/null)
    echo "${ts:-0}"
}

format_date() {
    local ts="$1"
    if [ "$ts" -gt 0 ]; then
        date -r "$ts" '+%Y-%m-%d %H:%M' 2>/dev/null || \
        date -d "@$ts" '+%Y-%m-%d %H:%M' 2>/dev/null || echo "inconnu"
    else
        echo "jamais commité"
    fi
}

show_diff() {
    local a="$1" b="$2" max="${3:-40}"
    local out n
    out=$(diff --unified=3 "$a" "$b" 2>/dev/null)
    n=$(echo "$out" | wc -l)
    echo "$out" | head -n "$max" | while IFS= read -r line; do
        if   [[ "$line" == "+"* ]] && [[ "$line" != "+++"* ]]; then echo -e "    ${GREEN}$line${NC}"
        elif [[ "$line" == "-"* ]] && [[ "$line" != "---"* ]]; then echo -e "    ${RED}$line${NC}"
        else echo "    $line"; fi
    done
    [ "$n" -gt "$max" ] && echo -e "    ${YELLOW}... ($((n - max)) lignes supplémentaires)${NC}"
}

# =====================================================
# Fonction : créer une PR pour UN fichier
# =====================================================

create_pr() {
    local rel_path="$1"
    local template_file="$2"
    local dt_tpl="$3"
    local dt_cli="$4"

    local safe_name
    safe_name=$(echo "$rel_path" | sed 's|[/.]|-|g')
    local branch_name="template-sync/v${TEMPLATE_VERSION}/${safe_name}"
    local pr_title="[Template sync v${TEMPLATE_VERSION}] ${rel_path}"

    pushd "$TARGET_PROJECT" > /dev/null || return 1

    local main_branch
    main_branch=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "main")

    # Supprimer la branche si elle existe déjà
    if git show-ref --verify --quiet "refs/heads/$branch_name" 2>/dev/null || \
       git show-ref --verify --quiet "refs/remotes/origin/$branch_name" 2>/dev/null; then
        git branch -D "$branch_name" 2>/dev/null
        git push origin --delete "$branch_name" --quiet 2>/dev/null
    fi

    # Créer la branche
    if ! git checkout -b "$branch_name" 2>/dev/null; then
        echo -e "  ${RED}❌ Impossible de créer la branche${NC}"
        popd > /dev/null; return 1
    fi

    # Appliquer le fichier
    mkdir -p "$(dirname "$TARGET_PROJECT/$rel_path")"
    cp "$template_file" "$TARGET_PROJECT/$rel_path"
    git add "$rel_path"
    git commit -m "$pr_title" --quiet

    # Pousser
    if ! git push origin "$branch_name" --quiet 2>&1; then
        echo -e "  ${RED}❌ Échec du push${NC}"
        git checkout "$main_branch" --quiet 2>/dev/null
        popd > /dev/null; return 1
    fi

    # Corps de la PR avec diff
    local pr_body
    pr_body="## 🔄 Sync depuis le template v${TEMPLATE_VERSION}

**Fichier :** \`${rel_path}\`

| | Dernier commit |
|---|---|
| 🔵 Template | ${dt_tpl} |
| 🟡 Projet   | ${dt_cli} |

### Différences
\`\`\`diff
$(diff --unified=5 "$TARGET_PROJECT/$rel_path" "$template_file" 2>/dev/null | head -n 80)
\`\`\`

> ⚠️ Vérifiez que cette modification ne casse pas le thème avant de merger."

    local pr_url
    pr_url=$(gh pr create \
        --title "$pr_title" \
        --body "$pr_body" \
        --base "$main_branch" \
        --head "$branch_name" \
        2>/dev/null)

    git checkout "$main_branch" --quiet 2>/dev/null
    popd > /dev/null

    if [ -n "$pr_url" ]; then
        echo -e "  ${GREEN}✓ PR créée${NC} → ${BLUE}$pr_url${NC}"
        PR_URLS+=("$pr_url|$rel_path")
        return 0
    else
        echo -e "  ${RED}❌ PR non créée — branche poussée : $branch_name${NC}"
        PR_URLS+=("échec|$rel_path")
        return 1
    fi
}

# =====================================================
# Compteurs & résultats
# =====================================================

SCRIPTS_APPLIED=0; SCRIPTS_ADDED=0
THEME_PR=0; THEME_ADDED=0; THEME_SKIPPED=0
PROTECTED_SKIPPED=()
SCRIPTS_MODIFIED=()
PR_URLS=()
CHANGELOG_FILES=""   # accumulateur pour le changelog

# =====================================================
# BOUCLE PRINCIPALE
# =====================================================

echo -e "${BLUE}════════════════════════════════════════════════${NC}"
echo -e "${BLUE}  Analyse et synchronisation                   ${NC}"
echo -e "${BLUE}════════════════════════════════════════════════${NC}"

for folder in $SYNC_FOLDERS; do
    folder="${folder%/}"
    TEMPLATE_FOLDER="$TEMP_DIR/$folder"
    if [ ! -e "$TEMPLATE_FOLDER" ]; then
        echo -e "\n${YELLOW}⚠️  $folder absent du template (ignoré)${NC}"; continue
    fi

    echo ""
    if [[ "$folder" == scripts* ]]; then
        echo -e "${CYAN}📂 $folder${NC} ${BLUE}[sync automatique]${NC}"
    else
        echo -e "${CYAN}📂 $folder${NC} ${MAGENTA}[PR par fichier]${NC}"
    fi

    while IFS= read -r -d '' template_file; do
        rel_path="${template_file#$TEMP_DIR/}"
        rel_path=$(echo "$rel_path" | sed 's|//|/|g')
        target_file="$TARGET_PROJECT/$rel_path"

        # ── Protégés → ignorer silencieusement ──
        if is_protected "$rel_path"; then
            PROTECTED_SKIPPED+=("$rel_path")
            continue
        fi

        # ── style.scss et style.css → gérés par write_version ──
        if [[ "$rel_path" == "$STYLE_SCSS_PATH" ]] || \
           [[ "$rel_path" == "$STYLE_CSS_PATH" ]]; then
            continue
        fi

        # ── Fichier NOUVEAU → proposer l'ajout direct ──
        if [ ! -f "$target_file" ]; then
            echo ""
            echo -e "  ${GREEN}[NOUVEAU]${NC} $rel_path"
            read -p "  Ajouter ce fichier au projet ? (o/N) : " add_choice </dev/tty
            if [[ "$add_choice" =~ ^[Oo]$ ]]; then
                mkdir -p "$(dirname "$target_file")"
                cp "$template_file" "$target_file"
                echo -e "  ${GREEN}✓ Ajouté${NC}"
                CHANGELOG_FILES+="${rel_path}"$'\n'
                if is_scripts_file "$rel_path"; then
                    SCRIPTS_MODIFIED+=("$rel_path")
                    SCRIPTS_ADDED=$((SCRIPTS_ADDED+1))
                else
                    THEME_ADDED=$((THEME_ADDED+1))
                fi
            else
                echo -e "  ${YELLOW}~ Ignoré${NC}"
                THEME_SKIPPED=$((THEME_SKIPPED+1))
            fi
            continue
        fi

        # ── Contenu identique → rien à faire ──
        if diff -q "$template_file" "$target_file" > /dev/null 2>&1; then
            continue
        fi

        # ── Timestamps ──
        ts_tpl=$(get_ts "$TEMP_DIR" "$rel_path")
        ts_cli=$(get_ts "$TARGET_PROJECT" "$rel_path")
        dt_tpl=$(format_date "$ts_tpl")
        dt_cli=$(format_date "$ts_cli")

        # ════════════════════════════════
        # MODE SCRIPTS : sync automatique
        # ════════════════════════════════
        if is_scripts_file "$rel_path"; then
            echo ""
            echo -e "  ${BLUE}[SYNC]${NC} $rel_path"
            echo -e "    Template : ${GREEN}$dt_tpl${NC}  |  Projet : ${YELLOW}$dt_cli${NC}"
            cp "$template_file" "$target_file"
            SCRIPTS_MODIFIED+=("$rel_path")
            CHANGELOG_FILES+="${rel_path}"$'\n'
            echo -e "  ${GREEN}✓ Mis à jour${NC}"
            SCRIPTS_APPLIED=$((SCRIPTS_APPLIED+1))
            continue
        fi

        # ════════════════════════════════════════
        # MODE THÈME : PR par fichier
        # ════════════════════════════════════════
        if is_theme_file "$rel_path"; then
            echo ""
            echo -e "  ${MAGENTA}[À valider]${NC} $rel_path"
            echo -e "    Template : ${GREEN}$dt_tpl${NC}  |  Projet : ${YELLOW}$dt_cli${NC}"

            if   [ "$ts_tpl" -gt "$ts_cli" ]; then echo -e "    ${BLUE}→ Template plus récent${NC}"
            elif [ "$ts_cli" -gt "$ts_tpl" ]; then echo -e "    ${YELLOW}→ Projet plus récent${NC}"
            else                                    echo -e "    ${YELLOW}→ Timestamps identiques ou inconnus${NC}"
            fi

            echo ""
            echo -e "  ${BLUE}--- Différences ---${NC}"
            show_diff "$target_file" "$template_file"
            echo ""
            echo "  Créer une PR GitHub pour ce fichier ?"
            echo "    1) Oui, créer la PR"
            echo "    2) Non, ignorer"
            read -p "  Votre choix [1/2] : " pr_choice </dev/tty

            if [[ "$pr_choice" == "1" ]]; then
                create_pr "$rel_path" "$template_file" "$dt_tpl" "$dt_cli"
                CHANGELOG_FILES+="${rel_path}"$'\n'
                THEME_PR=$((THEME_PR+1))
            else
                echo -e "  ${YELLOW}~ Ignoré${NC}"
                THEME_SKIPPED=$((THEME_SKIPPED+1))
            fi
        fi

    done < <(find "$TEMPLATE_FOLDER" -type f -print0 | sort -z)
done

# =====================================================
# Commit scripts + version sur la branche principale
# =====================================================

echo ""
echo -e "${YELLOW}🏷️  Mise à jour de la version...${NC}"
write_version "$TARGET_PROJECT" "$TEMPLATE_VERSION"

cd "$TARGET_PROJECT" || exit 1

for _sf in "${SCRIPTS_MODIFIED[@]}"; do
    git add "$_sf" 2>/dev/null
done
git add "$THEME_JSON_PATH" "$STYLE_SCSS_PATH" "$STYLE_CSS_PATH" 2>/dev/null

# =====================================================
# Mise à jour du CHANGELOG dans le projet client
# =====================================================

echo ""
echo -e "${YELLOW}📋 Mise à jour du changelog...${NC}"

# Construire la liste des PRs pour le changelog
CHANGELOG_PRS=""
for entry in "${PR_URLS[@]}"; do
    CHANGELOG_PRS+="${entry}"$'\n'
done

append_changelog \
    "$TARGET_PROJECT" \
    "$TEMPLATE_VERSION" \
    "pull" \
    "$TARGET_NAME" \
    "$CHANGELOG_FILES" \
    "$CHANGELOG_PRS"

git add "$CHANGELOG_PATH" 2>/dev/null

# =====================================================
# Commit final
# =====================================================

if [ -n "$(git diff --cached --name-only 2>/dev/null)" ]; then
    git commit -m "[Template sync v$TEMPLATE_VERSION] scripts + version" --quiet
    echo -e "${GREEN}✓ Commit scripts & version créé${NC}"
    read -p "Pousser ce commit ? (O/n) : " do_push
    if [[ ! "$do_push" =~ ^[Nn]$ ]]; then
        git push --quiet && echo -e "${GREEN}✓ Push effectué${NC}" || \
        echo -e "${RED}❌ Échec du push${NC}"
    fi
fi

# =====================================================
# Résumé final
# =====================================================

echo ""
echo -e "${BLUE}════════════════════════════════════════════════${NC}"
echo -e "${BLUE}  Résumé                                       ${NC}"
echo -e "${BLUE}════════════════════════════════════════════════${NC}"
echo ""
echo -e "  ${CYAN}Scripts (sync automatique)${NC}"
echo -e "    ${GREEN}✓ Mis à jour : $SCRIPTS_APPLIED${NC}"
echo -e "    ${GREEN}✓ Ajoutés    : $SCRIPTS_ADDED${NC}"
echo ""
echo -e "  ${MAGENTA}Thème (Pull Requests)${NC}"
echo -e "    ${MAGENTA}✓ PRs créées : $THEME_PR${NC}"
echo -e "    ${GREEN}✓ Ajoutés    : $THEME_ADDED${NC}"
echo -e "    ${YELLOW}~ Ignorés    : $THEME_SKIPPED${NC}"

if [ ${#PR_URLS[@]} -gt 0 ]; then
    echo ""
    echo -e "  ${MAGENTA}PRs ouvertes sur GitHub :${NC}"
    for entry in "${PR_URLS[@]}"; do
        IFS='|' read -r url file <<< "$entry"
        if [[ "$url" == "échec" ]]; then
            echo -e "    ${RED}✗${NC} $file  ${RED}(PR non créée)${NC}"
        else
            echo -e "    ${GREEN}✓${NC} $file"
            echo -e "      ${BLUE}$url${NC}"
        fi
    done
fi

if [ ${#PROTECTED_SKIPPED[@]} -gt 0 ]; then
    echo ""
    echo -e "  ${YELLOW}🔒 Protégés (intacts) :${NC}"
    for f in "${PROTECTED_SKIPPED[@]}"; do
        echo -e "     ${YELLOW}- $f${NC}"
    done
fi

echo ""
echo -e "  🏷️  Version : ${YELLOW}$TARGET_VERSION${NC} → ${GREEN}$TEMPLATE_VERSION${NC}"
echo -e "  📋 Changelog : ${GREEN}$CHANGELOG_PATH${NC}"
echo ""
echo -e "${BLUE}══════════════════════════════════════════════${NC}"
echo -e "  ✅ ${CYAN}$TARGET_NAME${NC} traité"

if [ ${#PR_URLS[@]} -gt 0 ]; then
    echo ""
    echo -e "  ${MAGENTA}👉 $THEME_PR PR(s) en attente sur GitHub${NC}"
    echo -e "  ${MAGENTA}   Chaque PR = 1 fichier, mergeable indépendamment${NC}"
fi

echo -e "${BLUE}══════════════════════════════════════════════${NC}"
echo ""