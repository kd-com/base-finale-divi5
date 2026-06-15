#!/bin/bash
# =====================================================
# install-sync.sh - Gestion synchronisation template
# Permet d'activer ou désactiver la sync auto
# =====================================================

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

CONFIG_FILE=".template-sync.json"
HOOK_FILE=".git/hooks/post-commit"

# =====================================================
# Détection de l'état actuel
# =====================================================
if [ -f "$HOOK_FILE" ] && [ -x "$HOOK_FILE" ]; then
    CURRENT_STATE="activé"
else
    CURRENT_STATE="désactivé"
fi

clear
echo -e "${BLUE}╔══════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Gestion Synchronisation Template          ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════╝${NC}"
echo ""
echo -e "État actuel : ${YELLOW}$CURRENT_STATE${NC}"
echo ""

# =====================================================
# Menu
# =====================================================
echo "Que voulez-vous faire ?"
echo ""
echo "  1) Activer la synchronisation automatique (sans confirmation)"
echo "  2) Activer la synchronisation avec confirmation à chaque commit"
echo "  3) Désactiver la synchronisation automatique"
echo "  4) Pousser vers le template (sync manuelle)"
echo "  5) Mettre à jour un projet client depuis le template"
echo "  6) Éditer la configuration (.template-sync.json)"
echo "  7) Quitter"
echo ""

read -p "Votre choix : " choice

case $choice in
    1)
        # ============================================
        # ACTIVATION AUTOMATIQUE (sans confirmation)
        # ============================================
        echo ""
        echo -e "${YELLOW}📦 Activation de la synchronisation automatique...${NC}"

        if [ ! -f "scripts/sync-to-template.sh" ]; then
            echo -e "${RED}❌ scripts/sync-to-template.sh introuvable${NC}"
            exit 1
        fi

        chmod +x scripts/sync-to-template.sh

        # Créer la configuration si elle n'existe pas
        if [ ! -f "$CONFIG_FILE" ]; then
            echo ""
            echo -e "${YELLOW}Création de la configuration...${NC}"

            read -p "URL du dépôt template (ex: git@github.com:user/template.git) : " template_repo
            read -p "Branche du template [main] : " template_branch
            template_branch=${template_branch:-main}
            read -p "Dossier parent de vos projets clients (ex: ~/sites) : " projects_dir
            projects_dir="${projects_dir/#\~/$HOME}"

            cat > "$CONFIG_FILE" << EOF
{
  "template_repo": "$template_repo",
  "template_branch": "$template_branch",
  "projects_dir": "$projects_dir",
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
            echo -e "${GREEN}✓ Configuration créée : $CONFIG_FILE${NC}"
        else
            echo -e "${GREEN}✓ Configuration existante : $CONFIG_FILE${NC}"
        fi

        mkdir -p .git/hooks

        cat > "$HOOK_FILE" << 'HOOK_EOF'
#!/bin/bash
# Ne pas déclencher le sync sur les commits internes
LAST_MSG=$(git log -1 --pretty=%B 2>/dev/null)
if echo "$LAST_MSG" | grep -qE "^\[(Version|Sync|Template sync|Auto-sync)"; then
    exit 0
fi
if [ -f "./scripts/sync-to-template.sh" ]; then
    echo "🔄 Synchronisation automatique vers le template..."
    bash ./scripts/sync-to-template.sh
fi
HOOK_EOF

        chmod +x "$HOOK_FILE"

        echo -e "${GREEN}✓ Hook post-commit installé (mode automatique)${NC}"
        echo ""
        echo -e "${GREEN}✅ Synchronisation automatique activée !${NC}"
        echo ""
        echo "À chaque commit, vos modifications seront automatiquement"
        echo "synchronisées vers le template."
        echo ""
        echo "Pour désactiver temporairement sur un commit :"
        echo "  ${BLUE}git commit --no-verify${NC}"
        ;;

    2)
        # ============================================
        # ACTIVATION AVEC CONFIRMATION
        # ============================================
        echo ""
        echo -e "${YELLOW}📦 Activation avec confirmation à chaque commit...${NC}"

        if [ ! -f "scripts/sync-to-template.sh" ]; then
            echo -e "${RED}❌ scripts/sync-to-template.sh introuvable${NC}"
            exit 1
        fi

        chmod +x scripts/sync-to-template.sh

        # Créer la configuration si elle n'existe pas
        if [ ! -f "$CONFIG_FILE" ]; then
            echo ""
            echo -e "${YELLOW}Création de la configuration...${NC}"

            read -p "URL du dépôt template (ex: git@github.com:user/template.git) : " template_repo
            read -p "Branche du template [main] : " template_branch
            template_branch=${template_branch:-main}
            read -p "Dossier parent de vos projets clients (ex: ~/sites) : " projects_dir
            projects_dir="${projects_dir/#\~/$HOME}"

            cat > "$CONFIG_FILE" << EOF
{
  "template_repo": "$template_repo",
  "template_branch": "$template_branch",
  "projects_dir": "$projects_dir",
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
            echo -e "${GREEN}✓ Configuration créée : $CONFIG_FILE${NC}"
        else
            echo -e "${GREEN}✓ Configuration existante : $CONFIG_FILE${NC}"
        fi

        mkdir -p .git/hooks

        cat > "$HOOK_FILE" << 'HOOK_EOF'
#!/bin/bash
# Ne pas déclencher le sync sur les commits internes
LAST_MSG=$(git log -1 --pretty=%B 2>/dev/null)
if echo "$LAST_MSG" | grep -qE "^\[(Version|Sync|Template sync|Auto-sync)"; then
    exit 0
fi
if [ -f "./scripts/sync-to-template.sh" ]; then
    echo ""
    read -p "🔄 Synchroniser vers le template ? (o/N) : " sync_choice </dev/tty
    if [[ "$sync_choice" =~ ^[Oo]$ ]]; then
        bash ./scripts/sync-to-template.sh
    else
        echo "  ~ Sync ignorée"
    fi
fi
HOOK_EOF

        chmod +x "$HOOK_FILE"

        echo -e "${GREEN}✓ Hook post-commit installé (mode confirmation)${NC}"
        echo ""
        echo -e "${GREEN}✅ Synchronisation avec confirmation activée !${NC}"
        echo ""
        echo "À chaque commit, il vous sera demandé si vous voulez"
        echo "synchroniser vers le template."
        echo ""
        echo "Pour bypasser complètement :"
        echo "  ${BLUE}git commit --no-verify${NC}"
        ;;

    3)
        # ============================================
        # DÉSACTIVATION
        # ============================================
        echo ""
        echo -e "${YELLOW}🔒 Désactivation de la synchronisation...${NC}"

        if [ -f "$HOOK_FILE" ]; then
            rm "$HOOK_FILE"
            echo -e "${GREEN}✓ Hook post-commit supprimé${NC}"
        fi

        echo ""
        echo -e "${GREEN}✅ Synchronisation désactivée !${NC}"
        echo ""
        echo "La configuration ($CONFIG_FILE) a été conservée."
        echo "Pour réactiver : ./scripts/install-sync.sh"
        ;;

    4)
        # ============================================
        # PUSH VERS LE TEMPLATE (test manuel)
        # ============================================
        echo ""
        echo -e "${YELLOW}🧪 Synchronisation manuelle vers le template...${NC}"
        echo ""

        if [ ! -f "scripts/sync-to-template.sh" ]; then
            echo -e "${RED}❌ scripts/sync-to-template.sh introuvable${NC}"
            exit 1
        fi

        if [ ! -f "$CONFIG_FILE" ]; then
            echo -e "${RED}❌ $CONFIG_FILE introuvable${NC}"
            echo "Lancez d'abord l'option 1 pour créer la configuration"
            exit 1
        fi

        bash ./scripts/sync-to-template.sh
        ;;

    5)
        # ============================================
        # PULL DEPUIS LE TEMPLATE VERS UN PROJET CLIENT
        # ============================================
        echo ""
        echo -e "${YELLOW}🔽 Mise à jour d'un projet client depuis le template...${NC}"
        echo ""

        if [ ! -f "scripts/sync-from-template.sh" ]; then
            echo -e "${RED}❌ scripts/sync-from-template.sh introuvable${NC}"
            exit 1
        fi

        if [ ! -f "$CONFIG_FILE" ]; then
            echo -e "${RED}❌ $CONFIG_FILE introuvable${NC}"
            echo "Lancez d'abord l'option 1 pour créer la configuration"
            exit 1
        fi

        bash ./scripts/sync-from-template.sh
        ;;

    6)
        # ============================================
        # ÉDITION CONFIG
        # ============================================
        echo ""
        echo -e "${YELLOW}📝 Édition de la configuration...${NC}"

        if [ ! -f "$CONFIG_FILE" ]; then
            echo -e "${RED}❌ $CONFIG_FILE introuvable${NC}"
            echo "Lancez d'abord l'option 1 pour créer la configuration"
            exit 1
        fi

        if command -v nano &> /dev/null; then
            nano "$CONFIG_FILE"
        elif command -v vi &> /dev/null; then
            vi "$CONFIG_FILE"
        elif command -v code &> /dev/null; then
            code "$CONFIG_FILE"
        else
            echo -e "${YELLOW}Ouvrez manuellement : $CONFIG_FILE${NC}"
        fi

        echo ""
        echo -e "${GREEN}✓ Configuration mise à jour${NC}"
        ;;

    7)
        # ============================================
        # QUITTER
        # ============================================
        echo ""
        echo "Au revoir !"
        exit 0
        ;;

    *)
        echo -e "${RED}❌ Option invalide${NC}"
        exit 1
        ;;
esac

echo ""
echo -e "${BLUE}═══════════════════════════════════════════════${NC}"
echo ""
echo "Commandes disponibles :"
echo -e "  ${GREEN}./scripts/install-sync.sh${NC}          - Ouvrir ce menu"
echo -e "  ${GREEN}./scripts/sync-to-template.sh${NC}      - Push vers le template"
echo -e "  ${GREEN}./scripts/sync-from-template.sh${NC}    - Pull depuis le template"
echo -e "  ${GREEN}git commit --no-verify${NC}             - Commit sans sync"
echo ""