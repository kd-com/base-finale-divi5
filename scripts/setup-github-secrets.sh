#!/usr/bin/env bash
# =====================================================
# setup-github-secrets.sh
# Configure automatiquement les secrets GitHub depuis .env
# =====================================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# =====================================================
# Vérifications préalables
# =====================================================
echo -e "${BLUE}╔══════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Configuration Secrets GitHub automatique   ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════╝${NC}"
echo ""

# Vérifier que .env existe
if [ ! -f ".env" ]; then
    echo -e "${RED}❌ Fichier .env introuvable${NC}"
    echo "Lancez d'abord : ./scripts/04_setup-remote-env.sh"
    exit 1
fi
 
# Vérifier que gh CLI est installé
if ! command -v gh &> /dev/null; then
    echo -e "${RED}❌ GitHub CLI (gh) n'est pas installé${NC}"
    echo ""
    echo "Installation :"
    echo "  • macOS   : brew install gh"
    echo "  • Ubuntu  : sudo apt install gh"
    echo "  • Windows : winget install --id GitHub.cli"
    echo ""
    echo "Puis authentifiez-vous : gh auth login"
    exit 1
fi

# Vérifier que l'utilisateur est authentifié
if ! gh auth status &> /dev/null; then
    echo -e "${YELLOW}⚠️  Vous n'êtes pas authentifié sur GitHub${NC}"
    echo ""
    echo "Authentification..."
    gh auth login
fi

# =====================================================
# Détection du repository
# =====================================================
REPO=$(gh repo view --json nameWithOwner -q .nameWithOwner 2>/dev/null || echo "")

if [ -z "$REPO" ]; then
    echo -e "${RED}❌ Impossible de détecter le repository GitHub${NC}"
    echo "Assurez-vous d'être dans un dépôt Git avec une remote GitHub"
    echo ""
    read -p "Entrez manuellement (format: user/repo) : " REPO
    
    if [ -z "$REPO" ]; then
        echo -e "${RED}❌ Repository non spécifié${NC}"
        exit 1
    fi
fi

echo -e "${GREEN}✓ Repository détecté : $REPO${NC}"
echo ""

# =====================================================
# Chargement des variables .env
# =====================================================
echo -e "${YELLOW}📂 Chargement des variables depuis .env...${NC}"

set -a
source .env
set +a

echo -e "${GREEN}✓ Variables chargées${NC}"
echo ""

# =====================================================
# Fonction pour créer/mettre à jour un secret
# =====================================================
set_secret() {
    local name="$1"
    local value="$2"
    
    if [ -z "$value" ]; then
        echo -e "${YELLOW}  ⊘ $name (vide, ignoré)${NC}"
        return
    fi
    
    echo "$value" | gh secret set "$name" --repo "$REPO" 2>&1
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}  ✓ $name${NC}"
    else
        echo -e "${RED}  ✗ $name (erreur)${NC}"
    fi
}

# =====================================================
# Gestion de la clé SSH privée
# =====================================================
echo -e "${BLUE}═══ Clé SSH Privée ═══${NC}"
echo ""

if [ -f "$HOME/.ssh/id_rsa" ]; then
    echo "Clé SSH détectée : $HOME/.ssh/id_rsa"
    echo ""
    
    read -p "Utiliser cette clé ? (oui/non) [oui] : " use_key
    use_key=${use_key:-oui}
    
    if [ "$use_key" = "oui" ]; then
        SSH_KEY_CONTENT=$(cat "$HOME/.ssh/id_rsa")
        set_secret "SSH_PRIVATE_KEY" "$SSH_KEY_CONTENT"
    else
        echo ""
        read -p "Chemin vers la clé SSH privée : " ssh_key_path
        if [ -f "$ssh_key_path" ]; then
            SSH_KEY_CONTENT=$(cat "$ssh_key_path")
            set_secret "SSH_PRIVATE_KEY" "$SSH_KEY_CONTENT"
        else
            echo -e "${RED}  ✗ Fichier introuvable : $ssh_key_path${NC}"
        fi
    fi
else
    echo -e "${YELLOW}⚠️  Aucune clé SSH détectée dans ~/.ssh/id_rsa${NC}"
    echo ""
    read -p "Chemin vers la clé SSH privée (ou laisser vide pour ignorer) : " ssh_key_path
    
    if [ -n "$ssh_key_path" ] && [ -f "$ssh_key_path" ]; then
        SSH_KEY_CONTENT=$(cat "$ssh_key_path")
        set_secret "SSH_PRIVATE_KEY" "$SSH_KEY_CONTENT"
    else
        echo -e "${YELLOW}  ⊘ SSH_PRIVATE_KEY (ignoré)${NC}"
    fi
fi

echo ""

# =====================================================
# Secrets PREPROD
# =====================================================
echo -e "${BLUE}═══ Secrets PREPROD ═══${NC}"
echo ""

set_secret "PREPROD_USER" "$PREPROD_USER"
set_secret "PREPROD_HOST" "$PREPROD_HOST"
set_secret "PREPROD_PATH" "$PREPROD_PATH"
set_secret "PREPROD_DB_USER" "$PREPROD_DB_USER"
set_secret "PREPROD_DB_PASS" "$PREPROD_DB_PASS"
set_secret "PREPROD_DB_NAME" "$PREPROD_DB_NAME"
set_secret "REMOTE_PREPROD_URL" "$REMOTE_PREPROD_URL"

echo ""

# =====================================================
# Secret cPanel API
# =====================================================
echo -e "${BLUE}═══ cPanel API (o2switch) ═══${NC}"
echo ""

set_secret "CPANEL_API_TOKEN" "$CPANEL_API_TOKEN"

echo ""

# =====================================================
# Secrets PROD
# =====================================================
echo -e "${BLUE}═══ Secrets PROD ═══${NC}"
echo ""

set_secret "PROD_USER" "$PROD_USER"
set_secret "PROD_HOST" "$PROD_HOST"
set_secret "PROD_PATH" "$PROD_PATH"
set_secret "PROD_PASS" "$PROD_PASS"
set_secret "PROD_DB_USER" "$PROD_DB_USER"
set_secret "PROD_DB_PASS" "$PROD_DB_PASS"
set_secret "PROD_DB_NAME" "$PROD_DB_NAME"
set_secret "REMOTE_PROD_URL" "$REMOTE_PROD_URL"
set_secret "PROD_DB_DUMP_URL" "$PROD_DB_DUMP_URL"
set_secret "PROD_DB_DUMP_TOKEN" "$PROD_DB_DUMP_TOKEN"

echo ""

# =====================================================
# Secret ACF Pro
# =====================================================
echo -e "${BLUE}═══ ACF Pro License ═══${NC}"
echo ""

# Valeur par défaut si non définie dans .env
ACF_LICENSE_KEY="${ACF_LICENSE_KEY:-b3JkZXJfaWQ9MTYyNzY4fHR5cGU9ZGV2ZWxvcGVyfGRhdGU9MjAxOS0wNi0xMSAwNzowMDo1Ng==}"
set_secret "ACF_LICENSE_KEY" "$ACF_LICENSE_KEY"

echo ""

# =====================================================
# Secret GH_PAT (Personal Access Token)
# =====================================================
echo -e "${BLUE}═══ GitHub Personal Access Token (GH_PAT) ═══${NC}"
echo ""

# Priorité : 1) variable dans .env  2) variable globale ~/.zshrc  3) saisie manuelle
if [ -n "$GH_PAT" ]; then
    echo -e "${GREEN}  ✓ GH_PAT trouvé dans .env${NC}"
    set_secret "GH_PAT" "$GH_PAT"
elif [ -n "$GH_PAT_GLOBAL" ]; then
    # Nom alternatif possible dans ~/.zshrc pour éviter les conflits
    echo -e "${GREEN}  ✓ GH_PAT trouvé dans les variables globales (GH_PAT_GLOBAL)${NC}"
    set_secret "GH_PAT" "$GH_PAT_GLOBAL"
else
    echo -e "${YELLOW}  ⚠️  GH_PAT non trouvé dans .env ni dans les variables globales${NC}"
    echo ""
    echo "  💡 Pour ne plus avoir à le saisir, ajoutez cette ligne dans ~/.zshrc :"
    echo "     export GH_PAT=\"ghp_xxxxxxxxxxxxxxxxxxxx\""
    echo "     source ~/.zshrc"
    echo ""
    read -p "  Entrez votre GH_PAT manuellement (ou laisser vide pour ignorer) : " manual_pat
    if [ -n "$manual_pat" ]; then
        set_secret "GH_PAT" "$manual_pat"

        # Proposer de sauvegarder dans ~/.zshrc pour les prochaines fois
        echo ""
        read -p "  Sauvegarder ce token dans ~/.zshrc pour les prochains projets ? (oui/non) [oui] : " save_pat
        save_pat=${save_pat:-oui}
        if [ "$save_pat" = "oui" ]; then
            echo "export GH_PAT=\"$manual_pat\"" >> ~/.zshrc
            source ~/.zshrc
            echo -e "${GREEN}  ✓ GH_PAT sauvegardé dans ~/.zshrc${NC}"
        fi
    else
        echo -e "${YELLOW}  ⊘ GH_PAT (ignoré)${NC}"
    fi
fi

echo ""

# =====================================================
# Vérification
# =====================================================
echo -e "${BLUE}═══ Vérification ═══${NC}"
echo ""
echo "Liste des secrets configurés :"
echo ""

gh secret list --repo "$REPO"

echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✅ Configuration terminée                   ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════╝${NC}"
echo ""
echo "Vous pouvez maintenant :"
echo "  1. Vérifier sur GitHub : Settings → Secrets and variables → Actions"
echo "  2. Tester un déploiement : git push origin preprod"
echo ""