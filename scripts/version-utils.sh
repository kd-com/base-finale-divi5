#!/bin/bash
# =====================================================
# version-utils.sh - Utilitaires de gestion de version
# Sourcé par sync-to-template.sh et sync-from-template.sh
# =====================================================

# Chemins des fichiers de version (relatifs à la racine du dépôt)
THEME_JSON_PATH="wp-content/themes/kd-com/theme.json"
STYLE_SCSS_PATH="wp-content/themes/kd-com/sass/style.scss"
STYLE_CSS_PATH="wp-content/themes/kd-com/style.css"
CHANGELOG_PATH="CHANGELOG.md"

# Alias utilisé dans les scripts appelants pour les messages
VERSION_FILE_PATH="$THEME_JSON_PATH"

# =====================================================
# Lire la version actuelle
# Ordre de priorité : theme.json → style.scss → style.css
# $1 : chemin racine du dépôt
# =====================================================
get_version() {
    local root="$1"

    # 1. Lire dans theme.json
    local tjson="$root/$THEME_JSON_PATH"
    if [ -f "$tjson" ]; then
        local ver
        ver=$(jq -r '.version // empty' "$tjson" 2>/dev/null)
        if [ -n "$ver" ] && [[ "$ver" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
            echo "$ver"; return
        fi
    fi

    # 2. Fallback : lire dans style.scss
    local scss="$root/$STYLE_SCSS_PATH"
    if [ -f "$scss" ]; then
        local ver
        ver=$(grep -m1 'Version:' "$scss" | sed 's/[^0-9\.]//g' | tr -d '[:space:]')
        if [ -n "$ver" ] && [[ "$ver" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
            echo "$ver"; return
        fi
    fi

    # 3. Fallback : lire dans style.css
    local css="$root/$STYLE_CSS_PATH"
    if [ -f "$css" ]; then
        local ver
        ver=$(grep -m1 'Version:' "$css" | sed 's/[^0-9\.]//g' | tr -d '[:space:]')
        if [ -n "$ver" ] && [[ "$ver" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
            echo "$ver"; return
        fi
    fi

    # 4. Lire dans CHANGELOG.md (dernière version taguée)
    local changelog="$root/$CHANGELOG_PATH"
    if [ -f "$changelog" ]; then
        local ver
        ver=$(grep -m1 '^## \[' "$changelog" | sed 's/## \[\([0-9.]*\)\].*/\1/')
        if [ -n "$ver" ] && [[ "$ver" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
            echo "$ver"; return
        fi
    fi

    echo "1.0.0"
}

# =====================================================
# Incrémenter le PATCH d'une version sémantique
# $1 : version actuelle (ex: 1.4.2)
# =====================================================
increment_patch() {
    local version="$1"
    local major minor patch

    # Nettoyer la version (supprimer tout ce qui n'est pas chiffre ou point)
    version=$(echo "$version" | tr -cd '0-9.')

    IFS='.' read -r major minor patch <<< "$version"
    major=${major:-1}
    minor=${minor:-0}
    patch=${patch:-0}

    # Vérifier que ce sont bien des entiers
    [[ "$major" =~ ^[0-9]+$ ]] || major=1
    [[ "$minor" =~ ^[0-9]+$ ]] || minor=0
    [[ "$patch" =~ ^[0-9]+$ ]] || patch=0

    patch=$((patch + 1))
    echo "$major.$minor.$patch"
}

# =====================================================
# Écrire la version dans theme.json, style.scss et style.css
# Compatible macOS (sed -i '' ) et Linux (sed -i)
# $1 : chemin racine du dépôt
# $2 : nouvelle version
# =====================================================
write_version() {
    local root="$1"
    local new_version="$2"
    local sync_date
    sync_date=$(date '+%Y-%m-%d %H:%M')

    sed_inplace() {
        local pattern="$1" file="$2"
        if sed --version 2>/dev/null | grep -q GNU; then
            sed -i "$pattern" "$file"
        else
            sed -i '' "$pattern" "$file"
        fi
    }

    # --- theme.json ---
    local tjson="$root/$THEME_JSON_PATH"
    if [ -f "$tjson" ]; then
        local tmp
        tmp=$(mktemp)
        jq --arg v "$new_version" --arg d "$sync_date" \
            '. + {version: $v, version_updated: $d}' \
            "$tjson" > "$tmp" && mv "$tmp" "$tjson"
        echo -e "   ${GREEN}✓ theme.json${NC}  → version $new_version"
    else
        echo -e "   ${YELLOW}⚠️  theme.json introuvable${NC} ($tjson)"
    fi

    # --- style.scss ---
    local scss="$root/$STYLE_SCSS_PATH"
    if [ -f "$scss" ]; then
        if grep -q 'Version:' "$scss"; then
            sed_inplace "s/\( Version:[ \t]*\)[0-9][0-9.]*/\1$new_version/" "$scss"
            echo -e "   ${GREEN}✓ style.scss${NC}   → version $new_version"
        else
            echo -e "   ${YELLOW}⚠️  style.scss : ligne 'Version:' introuvable${NC}"
        fi
    else
        echo -e "   ${YELLOW}⚠️  style.scss introuvable${NC} ($scss)"
    fi

    # --- style.css (compilé) ---
    local css="$root/$STYLE_CSS_PATH"
    if [ -f "$css" ]; then
        if grep -q 'Version:' "$css"; then
            sed_inplace "s/\( Version:[ \t]*\)[0-9][0-9.]*/\1$new_version/" "$css"
            echo -e "   ${GREEN}✓ style.css${NC}    → version $new_version"
        else
            echo -e "   ${YELLOW}⚠️  style.css : ligne 'Version:' introuvable${NC}"
        fi
    else
        echo -e "   ${YELLOW}⚠️  style.css introuvable${NC} (optionnel, ignoré)"
    fi
}

# =====================================================
# Ajouter une entrée dans CHANGELOG.md
# $1 : chemin racine du dépôt
# $2 : nouvelle version
# $3 : type de sync ("push" = vers template, "pull" = depuis template)
# $4 : nom du projet source/cible
# $5 : liste des fichiers modifiés (un par ligne, passé entre guillemets)
# $6 : liste des PRs créées (format "url|fichier", un par ligne) — optionnel
# =====================================================
append_changelog() {
    local root="$1"
    local new_version="$2"
    local sync_type="$3"   # "push" | "pull"
    local project_name="$4"
    local files_changed="$5"
    local pr_list="${6:-}"

    local changelog="$root/$CHANGELOG_PATH"
    local entry_date
    entry_date=$(date '+%Y-%m-%d %H:%M')
    local day
    day=$(date '+%Y-%m-%d')

    # Construire l'en-tête de section
    local direction_label
    if [ "$sync_type" = "push" ]; then
        direction_label="↑ Push vers le template depuis \`${project_name}\`"
    else
        direction_label="↓ Pull depuis le template vers \`${project_name}\`"
    fi

    # Construire le bloc de la nouvelle entrée
    local new_entry
    new_entry="## [${new_version}] — ${day}

### ${direction_label}

**Date :** ${entry_date}
"

    # Fichiers scripts/directs
    local scripts_section=""
    local theme_section=""

    while IFS= read -r line; do
        [ -z "$line" ] && continue
        if [[ "$line" == scripts/* ]]; then
            scripts_section+="- \`${line}\`"$'\n'
        else
            theme_section+="- \`${line}\`"$'\n'
        fi
    done <<< "$files_changed"

    if [ -n "$scripts_section" ]; then
        new_entry+="
#### Scripts synchronisés automatiquement

${scripts_section}"
    fi

    if [ -n "$theme_section" ]; then
        new_entry+="
#### Fichiers thème (via Pull Request)

${theme_section}"
    fi

    # PRs créées
    if [ -n "$pr_list" ]; then
        new_entry+="
#### Pull Requests ouvertes

"
        while IFS= read -r pr_entry; do
            [ -z "$pr_entry" ] && continue
            IFS='|' read -r pr_url pr_file <<< "$pr_entry"
            if [[ "$pr_url" == "échec" ]]; then
                new_entry+="- ❌ \`${pr_file}\` — PR non créée"$'\n'
            else
                new_entry+="- ✅ [\`${pr_file}\`](${pr_url})"$'\n'
            fi
        done <<< "$pr_list"
    fi

    new_entry+="
---

"

    # Créer le fichier s'il n'existe pas
    if [ ! -f "$changelog" ]; then
        cat > "$changelog" << 'EOF'
# Changelog

Toutes les modifications notables sont documentées ici.
Format : [Semantic Versioning](https://semver.org/lang/fr/)

---

EOF
        echo -e "   ${GREEN}✓ CHANGELOG.md créé${NC}"
    fi

    # Insérer la nouvelle entrée après l'en-tête (ligne 6 après les 5 premières lignes d'intro)
    # On insère AVANT la première entrée ## existante, ou en fin de fichier
    local tmp
    tmp=$(mktemp)

    # Lire l'en-tête jusqu'au premier "## [" ou fin de fichier
    local header_done=false
    local inserted=false

    while IFS= read -r line; do
        if [ "$inserted" = false ] && [[ "$line" =~ ^##\ \[ ]]; then
            # Insérer notre entrée juste avant la première section existante
            printf '%s\n' "$new_entry" >> "$tmp"
            inserted=true
        fi
        echo "$line" >> "$tmp"
    done < "$changelog"

    # Si aucune section ## [ trouvée (fichier vide ou intro seule), ajouter à la fin
    if [ "$inserted" = false ]; then
        printf '%s\n' "$new_entry" >> "$tmp"
    fi

    mv "$tmp" "$changelog"
    echo -e "   ${GREEN}✓ CHANGELOG.md mis à jour${NC} (v${new_version})"
}

# =====================================================
# Récupérer le timestamp du dernier commit Git
# $1 : chemin racine du dépôt (git repo)
# $2 : chemin relatif du fichier dans le dépôt
# =====================================================
get_git_file_timestamp() {
    local repo_root="$1"
    local rel_file="$2"
    local ts
    ts=$(git -C "$repo_root" log -1 --format="%ct" -- "$rel_file" 2>/dev/null)
    echo "${ts:-0}"
}

# =====================================================
# Afficher la version actuelle d'un dépôt
# $1 : chemin racine du dépôt
# $2 : label d'affichage
# =====================================================
display_version() {
    local root="$1"
    local label="$2"
    local ver
    ver=$(get_version "$root")
    echo -e "  ${CYAN}$label${NC} → version ${YELLOW}$ver${NC}"
}