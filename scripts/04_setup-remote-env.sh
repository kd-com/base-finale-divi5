#!/usr/bin/env bash
# =====================================================
# 04_setup-remote-env.sh - Configuration avancée
# Gestion : preprod, prod, template sync
# =====================================================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

ENV_FILE=".env"
SYNC_CONFIG=".template-sync.json"

# =====================================================
# Fonctions utilitaires
# =====================================================
ask() {
    local prompt default reply
    prompt="$1"
    default="$2"
    read -p "${YELLOW}${prompt} [${default}]${NC}: " reply
    echo "${reply:-$default}"
}

ask_yes_no() {
    local prompt default reply
    prompt="$1"
    default="$2"
    while true; do
        read -p "${YELLOW}${prompt} (oui/non) [${default}]${NC}: " reply
        reply="${reply:-$default}"
        case "$reply" in
            oui|yes|y) echo "oui"; return;;
            non|no|n) echo "non"; return;;
            *) echo "${RED}Répondez par 'oui' ou 'non'${NC}";;
        esac
    done
}

sed_update() {
    local search="$1"
    local replace="$2"
    if [[ "$OSTYPE" == "darwin"* ]]; then
        sed -i '' "s|^$search=.*|$search=$replace|" "$ENV_FILE"
    else
        sed -i "s|^$search=.*|$search=$replace|" "$ENV_FILE"
    fi
}

update_or_add_var() {
    local key="$1"
    local value="$2"
    if grep -q "^$key=" "$ENV_FILE"; then
        sed_update "$key" "\"$value\""
    else
        echo "$key=\"$value\"" >> "$ENV_FILE"
    fi
}

remove_var() {
    local key="$1"
    if [[ "$OSTYPE" == "darwin"* ]]; then
        sed -i '' "/^$key=/d" "$ENV_FILE" 2>/dev/null || true
    else
        sed -i "/^$key=/d" "$ENV_FILE" 2>/dev/null || true
    fi
}

# =====================================================
# Initialisation .env si nécessaire
# =====================================================
if [ ! -f "$ENV_FILE" ]; then
    cat > "$ENV_FILE" <<EOL
###############################################
# 🌐 Configuration Projet WordPress
###############################################

###############################################
# 🗄️ Base de données
###############################################

###############################################
# 🔧 Préproduction
###############################################

###############################################
# 🛠️ Production
###############################################

###############################################
# 🔑 cPanel API (o2switch)
###############################################

###############################################
# 🔄 Dump PHP sécurisé (fallback SSH)
###############################################
EOL
fi

# Charger les variables existantes
while IFS='=' read -r key value; do
    if [[ -n "$key" && "$key" != \#* ]]; then
        value=$(echo "$value" | tr -d '"')
        declare "$key=$value"
    fi
done < "$ENV_FILE"

clear
echo -e "${GREEN}╔══════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  Configuration Environnements Distants      ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════╝${NC}"
echo ""

# =====================================================
# 1. PRÉPRODUCTION
# =====================================================
echo -e "${BLUE}═══ 1️⃣  PRÉPRODUCTION (SSH) ═══${NC}"
echo ""

PREPROD_USER_VAL=$(ask "Utilisateur SSH préprod" "${PREPROD_USER:-facb6870}")
PREPROD_HOST_VAL=$(ask "Hôte SSH préprod" "${PREPROD_HOST:-rabbit.o2switch.net}")
PREPROD_PATH_VAL=$(ask "Chemin du site préprod" "${PREPROD_PATH:-/home/facb6870/site_clients/XXX}")
PREPROD_DB_USER_VAL=$(ask "Utilisateur BDD préprod" "${PREPROD_DB_USER:-facb6870_kd-com}")
PREPROD_DB_PASS_VAL=$(ask "Mot de passe BDD préprod" "${PREPROD_DB_PASS:-}")
PREPROD_DB_NAME_VAL=$(ask "Nom de la BDD préprod" "${PREPROD_DB_NAME:-facb6870_XXX}")
REMOTE_PREPROD_URL_VAL=$(ask "URL publique préprod" "${REMOTE_PREPROD_URL:-https://site.kd-com.fr}")

update_or_add_var "PREPROD_USER" "$PREPROD_USER_VAL"
update_or_add_var "PREPROD_HOST" "$PREPROD_HOST_VAL"
update_or_add_var "PREPROD_PATH" "$PREPROD_PATH_VAL"
update_or_add_var "PREPROD_DB_USER" "$PREPROD_DB_USER_VAL"
update_or_add_var "PREPROD_DB_PASS" "$PREPROD_DB_PASS_VAL"
update_or_add_var "PREPROD_DB_NAME" "$PREPROD_DB_NAME_VAL"
update_or_add_var "REMOTE_PREPROD_URL" "$REMOTE_PREPROD_URL_VAL"

# =====================================================
# 2. cPanel API (pour o2switch)
# =====================================================
echo ""
echo -e "${BLUE}═══ 2️⃣  cPanel API (o2switch uniquement) ═══${NC}"
echo "ℹ️  Requis pour la whitelist SSH automatique"
echo ""

CPANEL_API_TOKEN_VAL=$(ask "Token API cPanel (laisser vide si non o2switch)" "${CPANEL_API_TOKEN:-}")
update_or_add_var "CPANEL_API_TOKEN" "$CPANEL_API_TOKEN_VAL"

# =====================================================
# 3. PRODUCTION
# =====================================================
echo ""
echo -e "${BLUE}═══ 3️⃣  PRODUCTION ═══${NC}"
echo ""
echo "Protocole de déploiement :"
echo "  1) SSH (o2switch ou autre hébergeur avec SSH)"
echo "  2) FTP (OVH ou hébergeur sans SSH)"
echo ""

PS3="Choisissez le protocole : "
options=("SSH" "FTP")
select opt in "${options[@]}"; do
    case $opt in
        "SSH")
            echo ""
            echo -e "${YELLOW}Configuration SSH pour la production${NC}"
            echo ""
            
            PROD_USER_VAL=$(ask "Utilisateur SSH prod" "${PROD_USER:-}")
            PROD_HOST_VAL=$(ask "Hôte SSH prod" "${PROD_HOST:-}")
            PROD_PATH_VAL=$(ask "Chemin du site prod" "${PROD_PATH:-}")
            PROD_DB_USER_VAL=$(ask "Utilisateur BDD prod" "${PROD_DB_USER:-}")
            PROD_DB_PASS_VAL=$(ask "Mot de passe BDD prod" "${PROD_DB_PASS:-}")
            PROD_DB_NAME_VAL=$(ask "Nom de la BDD prod" "${PROD_DB_NAME:-}")
            REMOTE_PROD_URL_VAL=$(ask "URL publique prod" "${REMOTE_PROD_URL:-https://www.site.com}")
            
            update_or_add_var "PROD_USER" "$PROD_USER_VAL"
            update_or_add_var "PROD_HOST" "$PROD_HOST_VAL"
            update_or_add_var "PROD_PATH" "$PROD_PATH_VAL"
            update_or_add_var "PROD_DB_USER" "$PROD_DB_USER_VAL"
            update_or_add_var "PROD_DB_PASS" "$PROD_DB_PASS_VAL"
            update_or_add_var "PROD_DB_NAME" "$PROD_DB_NAME_VAL"
            update_or_add_var "REMOTE_PROD_URL" "$REMOTE_PROD_URL_VAL"
            
            # Supprimer PROD_PASS si SSH
            remove_var "PROD_PASS"
            
            # Si o2switch, pas besoin de dump PHP
            if [[ "$PROD_HOST_VAL" == *"o2switch.net"* ]]; then
                echo ""
                echo "✅ Production sur o2switch détectée"
                echo "   La whitelist IP sera configurée automatiquement en CI/CD"
                update_or_add_var "PROD_DB_DUMP_URL" ""
                update_or_add_var "PROD_DB_DUMP_TOKEN" ""
            else
                echo ""
                echo "ℹ️  Production SSH (non o2switch)"
                echo "   Pas de whitelist automatique"
                update_or_add_var "PROD_DB_DUMP_URL" ""
                update_or_add_var "PROD_DB_DUMP_TOKEN" ""
            fi
            
            break
            ;;
        "FTP")
            echo ""
            echo -e "${YELLOW}Configuration FTP pour la production${NC}"
            echo ""
            
            PROD_USER_VAL=$(ask "Utilisateur FTP prod" "${PROD_USER:-}")
            PROD_PASS_VAL=$(ask "Mot de passe FTP prod" "${PROD_PASS:-}")
            PROD_HOST_VAL=$(ask "Hôte FTP prod" "${PROD_HOST:-ftp.cluster110.hosting.ovh.net}")
            PROD_PATH_VAL=$(ask "Chemin du site prod" "${PROD_PATH:-/www}")
            REMOTE_PROD_URL_VAL=$(ask "URL publique prod" "${REMOTE_PROD_URL:-https://www.site.com}")
            
            update_or_add_var "PROD_USER" "$PROD_USER_VAL"
            update_or_add_var "PROD_PASS" "$PROD_PASS_VAL"
            update_or_add_var "PROD_HOST" "$PROD_HOST_VAL"
            update_or_add_var "PROD_PATH" "$PROD_PATH_VAL"
            update_or_add_var "REMOTE_PROD_URL" "$REMOTE_PROD_URL_VAL"
            
            # Supprimer les vars BDD (inutiles pour FTP)
            remove_var "PROD_DB_USER"
            remove_var "PROD_DB_PASS"
            remove_var "PROD_DB_NAME"
            
            # Configuration dump PHP (fallback pour BDD)
            echo ""
            echo -e "${YELLOW}Dump PHP sécurisé (pour sync BDD)${NC}"
            echo "ℹ️  Permet de récupérer la BDD sans SSH"
            echo ""
            
            PROD_DB_DUMP_URL_VAL=$(ask "URL dump_db.php" "${PROD_DB_DUMP_URL:-https://www.site.com/dump_db.php}")
            PROD_DB_DUMP_TOKEN_VAL=$(ask "Token secret" "${PROD_DB_DUMP_TOKEN:-secret-token-2025}")
            
            update_or_add_var "PROD_DB_DUMP_URL" "$PROD_DB_DUMP_URL_VAL"
            update_or_add_var "PROD_DB_DUMP_TOKEN" "$PROD_DB_DUMP_TOKEN_VAL"
            
            break
            ;;
        *)
            echo -e "${RED}Option invalide${NC}"
            ;;
    esac
done

# =====================================================
# 4. SYNCHRONISATION TEMPLATE (optionnel)
# =====================================================
echo ""
echo -e "${BLUE}═══ 4️⃣  SYNCHRONISATION TEMPLATE GIT (optionnel) ═══${NC}"
echo "ℹ️  Permet de synchroniser automatiquement vos modifications"
echo "   vers un template Git (hook post-commit)"
echo ""

ENABLE_SYNC=$(ask_yes_no "Activer la synchronisation automatique vers un template" "non")

if [ "$ENABLE_SYNC" = "oui" ]; then
    echo ""
    echo -e "${YELLOW}Configuration du template${NC}"
    echo ""
    
    TEMPLATE_REPO=$(ask "URL du dépôt template" "${TEMPLATE_REPO:-git@github.com:kd-com/template-wordpress.git}")
    TEMPLATE_BRANCH=$(ask "Branche du template" "${TEMPLATE_BRANCH:-main}")
    
    # Créer/mettre à jour .template-sync.json
    cat > "$SYNC_CONFIG" << EOF
{
  "template_repo": "$TEMPLATE_REPO",
  "template_branch": "$TEMPLATE_BRANCH",
  "sync_folders": [
    "scripts",
    "wp-content/themes/kd-com"
  ],
  "exclude_patterns": [
    "*.env",
    "*.env.*",
    "*.local.*",
    "node_modules",
    ".git",
    "vendor",
    "wp-content/uploads",
    "wp-content/cache"
  ]
}
EOF
    
    # Installer le hook post-commit
    mkdir -p .git/hooks
    cat > .git/hooks/post-commit << 'HOOK_EOF'
#!/bin/bash
if [ -f "./scripts/sync-to-template.sh" ]; then
    echo "🔄 Synchronisation automatique vers le template..."
    bash ./scripts/sync-to-template.sh
fi
HOOK_EOF
    
    chmod +x .git/hooks/post-commit
    
    echo ""
    echo -e "${GREEN}✅ Synchronisation template activée${NC}"
    echo "📝 Configuration : .template-sync.json"
    echo "🔗 Template : $TEMPLATE_REPO"
    echo ""
    echo "Pour désactiver temporairement :"
    echo "  git commit --no-verify"
    
else
    # Supprimer le hook si existant
    rm -f .git/hooks/post-commit
    
    echo ""
    echo -e "${YELLOW}ℹ️  Synchronisation template désactivée${NC}"
    echo ""
    echo "Pour l'activer plus tard :"
    echo "  ./scripts/install-sync.sh"
fi

# =====================================================
# RÉSUMÉ
# =====================================================
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✅ Configuration terminée                   ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}📝 Fichiers mis à jour :${NC}"
echo "  • .env"
if [ "$ENABLE_SYNC" = "oui" ]; then
    echo "  • .template-sync.json"
    echo "  • .git/hooks/post-commit"
fi
echo ""
echo -e "${BLUE}🔑 Secrets GitHub à configurer :${NC}"
echo ""
echo "  Communs :"
echo "    • SSH_PRIVATE_KEY (contenu de la clé privée SSH)"
echo ""
echo "  Preprod :"
echo "    • PREPROD_USER, PREPROD_HOST, PREPROD_PATH"
echo "    • PREPROD_DB_USER, PREPROD_DB_PASS, PREPROD_DB_NAME"
echo "    • REMOTE_PREPROD_URL"
if [[ "$PREPROD_HOST_VAL" == *"o2switch.net"* ]] || [[ "$PROD_HOST_VAL" == *"o2switch.net"* ]]; then
    echo "    • CPANEL_API_TOKEN (pour whitelist SSH)"
fi
echo ""
echo "  Prod :"
echo "    • PROD_USER, PROD_HOST, PROD_PATH"
if [ -n "$PROD_PASS_VAL" ]; then
    echo "    • PROD_PASS (mot de passe FTP)"
else
    echo "    • PROD_DB_USER, PROD_DB_PASS, PROD_DB_NAME"
fi
echo "    • REMOTE_PROD_URL"
if [ -n "$PROD_DB_DUMP_URL_VAL" ]; then
    echo "    • PROD_DB_DUMP_URL, PROD_DB_DUMP_TOKEN (dump PHP)"
fi
echo ""
echo -e "${BLUE}📚 Prochaines étapes :${NC}"
echo "  1. Configurer les secrets dans GitHub Actions"
echo "  2. Tester la synchronisation :"
echo "     ./scripts/sync-db.sh pull preprod"
echo "     ./scripts/sync-file.sh pull preprod"
echo "  3. Commiter et pousser vers preprod/prod"
echo ""
 