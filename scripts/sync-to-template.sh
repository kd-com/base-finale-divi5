#!/bin/bash
# =====================================================
# sync-to-template.sh - Synchronisation vers template
# - scripts/          : push direct sur main du template
# - themes/kd-com/    : une PR par fichier sur le template
# Incrémente le numéro de version PATCH à chaque sync
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
    echo -e "${RED}❌ $CONFIG_FILE introuvable${NC}"
    echo "Lancez ./scripts/install-sync.sh pour configurer"; exit 1
fi
if [ ! -f "$SCRIPT_DIR/version-utils.sh" ]; then
    echo -e "${RED}❌ scripts/version-utils.sh introuvable${NC}"; exit 1
fi
source "$SCRIPT_DIR/version-utils.sh"

if ! gh auth status &>/dev/null; then
    echo -e "${RED}❌ GitHub CLI non authentifié. Lancez : gh auth login${NC}"; exit 1
fi

# =====================================================
# Lecture de la configuration
# =====================================================

TEMPLATE_REPO=$(jq -r '.template_repo' "$CONFIG_FILE")
TEMPLATE_BRANCH=$(jq -r '.template_branch' "$CONFIG_FILE")
SYNC_FOLDERS=$(jq -r '.sync_folders[]' "$CONFIG_FILE")

if [ "$TEMPLATE_REPO" = "null" ] || [ -z "$TEMPLATE_REPO" ]; then
    echo -e "${RED}❌ template_repo non configuré dans $CONFIG_FILE${NC}"; exit 1
fi

PROJECT_ROOT=$(git rev-parse --show-toplevel 2>/dev/null || pwd)
PROJECT_NAME=$(basename "$PROJECT_ROOT")

echo ""
echo -e "${BLUE}╔══════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Synchronisation vers le Template           ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════╝${NC}"
echo ""
echo -e "Projet   : ${CYAN}$PROJECT_NAME${NC}"
echo -e "Template : ${YELLOW}$TEMPLATE_REPO${NC}"
echo -e "Branche  : ${YELLOW}$TEMPLATE_BRANCH${NC}"
echo ""

# =====================================================
# Version
# =====================================================

CURRENT_VERSION=$(get_version "$PROJECT_ROOT")
NEW_VERSION=$(increment_patch "$CURRENT_VERSION")

echo -e "Version actuelle : ${YELLOW}$CURRENT_VERSION${NC}"
echo -e "Nouvelle version : ${GREEN}$NEW_VERSION${NC}"
echo ""

write_version "$PROJECT_ROOT" "$NEW_VERSION"
echo -e "${GREEN}✓ Version mise à jour dans le projet${NC}"

# Stager les fichiers de version dans le projet client
git add "$PROJECT_ROOT/$THEME_JSON_PATH" \
        "$PROJECT_ROOT/$STYLE_SCSS_PATH" \
        "$PROJECT_ROOT/$STYLE_CSS_PATH" 2>/dev/null

# =====================================================
# Clonage du template
# =====================================================

TEMP_DIR=$(mktemp -d)
trap "rm -rf $TEMP_DIR" EXIT

echo ""
echo -e "${YELLOW}📁 Clonage du template...${NC}"

if ! git clone --branch "$TEMPLATE_BRANCH" "$TEMPLATE_REPO" "$TEMP_DIR" 2>/dev/null; then
    echo -e "${RED}❌ Échec du clonage${NC}"
    echo "Vérifiez l'URL du dépôt, vos accès SSH, et que la branche $TEMPLATE_BRANCH existe"
    exit 1
fi

echo -e "${GREEN}✓ Template cloné${NC}"

# =====================================================
# Helpers diff
# =====================================================

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

is_theme_file()   { [[ "$1" == wp-content/themes/kd-com/* ]]; }
is_scripts_file() { [[ "$1" == scripts/* ]]; }

# =====================================================
# Construire la liste des fichiers à synchroniser
# =====================================================

EXCLUDE_ARGS=""
while IFS= read -r pattern; do
    [ -n "$pattern" ] && EXCLUDE_ARGS="$EXCLUDE_ARGS --exclude=$pattern"
done < <(jq -r '.exclude_patterns[]' "$PROJECT_ROOT/$CONFIG_FILE")

echo ""
echo -e "${BLUE}════════════════════════════════════════════════${NC}"
echo -e "${BLUE}  Analyse des modifications                    ${NC}"
echo -e "${BLUE}════════════════════════════════════════════════${NC}"

# Tableaux de résultats
SCRIPTS_DIRECT=()   # fichiers scripts → push direct
THEME_PR=()         # fichiers thème → PR (rel_path|statut)
THEME_SKIPPED=0
PR_URLS=()

for folder in $SYNC_FOLDERS; do
    folder="${folder%/}"
    SOURCE_FOLDER="$PROJECT_ROOT/$folder"
    DEST_FOLDER="$TEMP_DIR/$folder"

    if [ ! -e "$SOURCE_FOLDER" ]; then
        echo -e "\n${YELLOW}⚠️  $folder absent du projet (ignoré)${NC}"; continue
    fi

    echo ""
    if [[ "$folder" == scripts* ]]; then
        echo -e "${CYAN}📂 $folder${NC} ${BLUE}[push direct sur le template]${NC}"
    else
        echo -e "${CYAN}📂 $folder${NC} ${MAGENTA}[PR par fichier sur le template]${NC}"
    fi

    # Parcourir les fichiers du projet
    while IFS= read -r -d '' src_file; do
        rel_path="${src_file#$PROJECT_ROOT/}"
        rel_path=$(echo "$rel_path" | sed 's|//|/|g')
        dest_file="$TEMP_DIR/$rel_path"

        # Exclure style.scss et style.css (gérés par write_version)
        if [[ "$rel_path" == "$STYLE_SCSS_PATH" ]] || \
           [[ "$rel_path" == "$STYLE_CSS_PATH" ]]; then
            continue
        fi

        # Fichier NOUVEAU dans le template
        if [ ! -f "$dest_file" ]; then
            echo ""
            echo -e "  ${GREEN}[NOUVEAU]${NC} $rel_path"
            if is_scripts_file "$rel_path"; then
                SCRIPTS_DIRECT+=("$rel_path|nouveau")
                echo -e "  ${BLUE}→ Sera ajouté directement au template${NC}"
            else
                echo ""
                echo "  Envoyer ce nouveau fichier vers le template ?"
                echo "    1) Oui, créer une PR"
                echo "    2) Non, ignorer"
                read -p "  Votre choix [1/2] : " new_choice </dev/tty
                if [[ "$new_choice" == "1" ]]; then
                    THEME_PR+=("$rel_path|nouveau")
                else
                    echo -e "  ${YELLOW}~ Ignoré${NC}"
                    THEME_SKIPPED=$((THEME_SKIPPED+1))
                fi
            fi
            continue
        fi

        # Contenu identique → rien à faire
        if diff -q "$src_file" "$dest_file" > /dev/null 2>&1; then
            continue
        fi

        # Fichier modifié
        if is_scripts_file "$rel_path"; then
            echo ""
            echo -e "  ${BLUE}[MODIFIÉ]${NC} $rel_path"
            SCRIPTS_DIRECT+=("$rel_path|modifié")
            echo -e "  ${BLUE}→ Sera mis à jour directement dans le template${NC}"
            continue
        fi

        if is_theme_file "$rel_path"; then
            echo ""
            echo -e "  ${MAGENTA}[À valider]${NC} $rel_path"

            ts_proj=$(git -C "$PROJECT_ROOT" log -1 --format="%ct" -- "$rel_path" 2>/dev/null)
            ts_tpl=$(git -C "$TEMP_DIR" log -1 --format="%ct" -- "$rel_path" 2>/dev/null)
            dt_proj=$([ "${ts_proj:-0}" -gt 0 ] && (date -r "$ts_proj" '+%Y-%m-%d %H:%M' 2>/dev/null || date -d "@$ts_proj" '+%Y-%m-%d %H:%M' 2>/dev/null) || echo "jamais commité")
            dt_tpl=$([ "${ts_tpl:-0}" -gt 0 ] && (date -r "$ts_tpl" '+%Y-%m-%d %H:%M' 2>/dev/null || date -d "@$ts_tpl" '+%Y-%m-%d %H:%M' 2>/dev/null) || echo "jamais commité")

            echo -e "    Projet   : ${GREEN}$dt_proj${NC}  |  Template : ${YELLOW}$dt_tpl${NC}"
            echo ""
            echo -e "  ${BLUE}--- Différences (template → projet) ---${NC}"
            show_diff "$dest_file" "$src_file"
            echo ""
            echo "  Envoyer ce fichier vers le template via une PR ?"
            echo "    1) Oui, créer une PR"
            echo "    2) Non, ignorer"
            read -p "  Votre choix [1/2] : " pr_choice </dev/tty

            if [[ "$pr_choice" == "1" ]]; then
                THEME_PR+=("$rel_path|modifié|$dt_proj|$dt_tpl")
                echo -e "  ${MAGENTA}✓ Sera inclus dans une PR${NC}"
            else
                echo -e "  ${YELLOW}~ Ignoré${NC}"
                THEME_SKIPPED=$((THEME_SKIPPED+1))
            fi
        fi

    done < <(find "$SOURCE_FOLDER" -type f -print0 | sort -z)
done

# =====================================================
# Appliquer les fichiers scripts directement sur main
# =====================================================

SCRIPTS_APPLIED=0

if [ ${#SCRIPTS_DIRECT[@]} -gt 0 ]; then
    echo ""
    echo -e "${BLUE}════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}  Push scripts + version → template main       ${NC}"
    echo -e "${BLUE}════════════════════════════════════════════════${NC}"
    echo ""

    cd "$TEMP_DIR" || exit 1

    # Copier les fichiers scripts
    for entry in "${SCRIPTS_DIRECT[@]}"; do
        IFS='|' read -r rel_path statut <<< "$entry"
        dest="$TEMP_DIR/$rel_path"
        mkdir -p "$(dirname "$dest")"
        cp "$PROJECT_ROOT/$rel_path" "$dest"
        git add "$rel_path"
        echo -e "  ${BLUE}✓${NC} $rel_path  ${YELLOW}[$statut]${NC}"
        SCRIPTS_APPLIED=$((SCRIPTS_APPLIED+1))
    done

    # Écrire + stager la version dans le template
    write_version "$TEMP_DIR" "$NEW_VERSION"
    git add "$THEME_JSON_PATH" "$STYLE_SCSS_PATH" "$STYLE_CSS_PATH" 2>/dev/null

    # Annuler le staging des suppressions
    DELETED=$(git diff --cached --name-only --diff-filter=D 2>/dev/null)
    if [ -n "$DELETED" ]; then
        while IFS= read -r f; do
            git restore --staged "$f" 2>/dev/null
        done <<< "$DELETED"
    fi

    LAST_MSG=$(cd "$PROJECT_ROOT" && git log -1 --pretty=%B 2>/dev/null || echo "Sync depuis $PROJECT_NAME")
    git commit -m "[Sync v$NEW_VERSION] $LAST_MSG" --quiet
    echo ""
    echo -e "${YELLOW}📤 Push scripts + version sur le template...${NC}"
    if git push origin "$TEMPLATE_BRANCH" --quiet 2>&1; then
        echo -e "${GREEN}✓ Scripts et version poussés sur le template${NC}"
    else
        echo -e "${RED}❌ Échec du push — vérifiez vos droits sur le dépôt template${NC}"
        exit 1
    fi
fi

# =====================================================
# Créer une PR par fichier thème sur le template
# =====================================================

if [ ${#THEME_PR[@]} -gt 0 ]; then
    echo ""
    echo -e "${MAGENTA}════════════════════════════════════════════════${NC}"
    echo -e "${MAGENTA}  Création des Pull Requests sur le template   ${NC}"
    echo -e "${MAGENTA}════════════════════════════════════════════════${NC}"
    echo ""

    cd "$TEMP_DIR" || exit 1

    for entry in "${THEME_PR[@]}"; do
        IFS='|' read -r rel_path statut dt_proj dt_tpl <<< "$entry"

        safe_name=$(echo "$rel_path" | sed 's|[/.]|-|g')
        branch_name="project-sync/v${NEW_VERSION}/${PROJECT_NAME}/${safe_name}"
        pr_title="[Sync v${NEW_VERSION}] ${PROJECT_NAME} → ${rel_path}"

        echo -e "  ${MAGENTA}[PR]${NC} $rel_path"

        # Supprimer la branche si elle existe déjà
        if git show-ref --verify --quiet "refs/heads/$branch_name" 2>/dev/null || \
           git show-ref --verify --quiet "refs/remotes/origin/$branch_name" 2>/dev/null; then
            git branch -D "$branch_name" 2>/dev/null
            git push origin --delete "$branch_name" --quiet 2>/dev/null
        fi

        # Créer la branche depuis main du template
        if ! git checkout -b "$branch_name" 2>/dev/null; then
            echo -e "  ${RED}❌ Impossible de créer la branche${NC}"
            git checkout "$TEMPLATE_BRANCH" --quiet 2>/dev/null
            continue
        fi

        # Appliquer le fichier du projet
        mkdir -p "$(dirname "$TEMP_DIR/$rel_path")"
        cp "$PROJECT_ROOT/$rel_path" "$TEMP_DIR/$rel_path"
        git add "$rel_path"
        git commit -m "$pr_title" --quiet

        # Pousser la branche
        if ! git push origin "$branch_name" --quiet 2>&1; then
            echo -e "  ${RED}❌ Échec du push de la branche${NC}"
            git checkout "$TEMPLATE_BRANCH" --quiet 2>/dev/null
            PR_URLS+=("échec|$rel_path")
            continue
        fi

        # Corps de la PR
        pr_body="## 🔄 Proposition depuis le projet \`${PROJECT_NAME}\` v${NEW_VERSION}

**Fichier :** \`${rel_path}\`

| | Dernier commit |
|---|---|
| 🟡 Projet \`${PROJECT_NAME}\` | ${dt_proj:-inconnu} |
| 🔵 Template actuel | ${dt_tpl:-inconnu} |

### Différences (template → projet)
\`\`\`diff
$(diff --unified=5 "$TEMP_DIR/$rel_path" "$PROJECT_ROOT/$rel_path" 2>/dev/null | head -n 80)
\`\`\`

> ⚠️ Merger cette PR appliquera la version du projet \`${PROJECT_NAME}\` dans le template.
> Vérifiez que cette modification est générique et convient à tous les projets."

        pr_url=$(gh pr create \
            --title "$pr_title" \
            --body "$pr_body" \
            --base "$TEMPLATE_BRANCH" \
            --head "$branch_name" \
            --repo "$TEMPLATE_REPO" \
            2>/dev/null)

        git checkout "$TEMPLATE_BRANCH" --quiet 2>/dev/null

        if [ -n "$pr_url" ]; then
            echo -e "  ${GREEN}✓ PR créée${NC} → ${BLUE}$pr_url${NC}"
            PR_URLS+=("$pr_url|$rel_path")
        else
            echo -e "  ${RED}❌ PR non créée — branche poussée : $branch_name${NC}"
            PR_URLS+=("échec|$rel_path")
        fi
        echo ""
    done
fi

# =====================================================
# Mise à jour du CHANGELOG dans le projet client
# =====================================================

echo ""
echo -e "${YELLOW}📋 Mise à jour du changelog...${NC}"

# Construire la liste complète des fichiers modifiés pour le changelog
CHANGELOG_FILES=""
for entry in "${SCRIPTS_DIRECT[@]}"; do
    IFS='|' read -r rel_path _ <<< "$entry"
    CHANGELOG_FILES+="${rel_path}"$'\n'
done
for entry in "${THEME_PR[@]}"; do
    IFS='|' read -r rel_path _ _ _ <<< "$entry"
    CHANGELOG_FILES+="${rel_path}"$'\n'
done

# Construire la liste des PRs pour le changelog
CHANGELOG_PRS=""
for entry in "${PR_URLS[@]}"; do
    CHANGELOG_PRS+="${entry}"$'\n'
done

append_changelog \
    "$PROJECT_ROOT" \
    "$NEW_VERSION" \
    "push" \
    "$PROJECT_NAME" \
    "$CHANGELOG_FILES" \
    "$CHANGELOG_PRS"

# Stager le changelog
git -C "$PROJECT_ROOT" add "$CHANGELOG_PATH" 2>/dev/null

# =====================================================
# Commit + push version sur le dépôt client
# =====================================================

cd "$PROJECT_ROOT" || exit 1

if [ -n "$(git diff --cached --name-only 2>/dev/null)" ]; then
    git commit -m "[Version] $NEW_VERSION" --no-verify --quiet
    echo -e "${GREEN}✓ Commit version $NEW_VERSION créé dans le projet${NC}"
fi

echo ""
echo -e "${YELLOW}📤 Push de la version sur le dépôt client...${NC}"
if git push --quiet 2>&1; then
    echo -e "${GREEN}✓ Version $NEW_VERSION poussée sur le dépôt client${NC}"
else
    echo -e "${RED}❌ Échec du push client — lancez 'git push' manuellement${NC}"
fi

# =====================================================
# Résumé final
# =====================================================

echo ""
echo -e "${BLUE}════════════════════════════════════════════════${NC}"
echo -e "${BLUE}  Résumé                                       ${NC}"
echo -e "${BLUE}════════════════════════════════════════════════${NC}"
echo ""
echo -e "  ${CYAN}Scripts (push direct sur le template)${NC}"
echo -e "    ${GREEN}✓ Appliqués : $SCRIPTS_APPLIED${NC}"
echo ""
echo -e "  ${MAGENTA}Thème (Pull Requests sur le template)${NC}"
echo -e "    ${MAGENTA}✓ PRs créées : ${#THEME_PR[@]}${NC}"
echo -e "    ${YELLOW}~ Ignorés    : $THEME_SKIPPED${NC}"

if [ ${#PR_URLS[@]} -gt 0 ]; then
    echo ""
    echo -e "  ${MAGENTA}PRs ouvertes sur le template :${NC}"
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

echo ""
echo -e "  🏷️  Version : ${YELLOW}$CURRENT_VERSION${NC} → ${GREEN}$NEW_VERSION${NC}"
echo -e "  📋 Changelog : ${GREEN}$CHANGELOG_PATH${NC}"
echo ""
echo -e "${BLUE}══════════════════════════════════════════════${NC}"
echo -e "  ✅ ${CYAN}$PROJECT_NAME${NC} → template synchronisé"

if [ ${#PR_URLS[@]} -gt 0 ]; then
    echo ""
    echo -e "  ${MAGENTA}👉 ${#THEME_PR[@]} PR(s) en attente sur le dépôt template${NC}"
    echo -e "  ${MAGENTA}   Chaque PR = 1 fichier, mergeable indépendamment${NC}"
fi

echo -e "${BLUE}══════════════════════════════════════════════${NC}"
echo ""