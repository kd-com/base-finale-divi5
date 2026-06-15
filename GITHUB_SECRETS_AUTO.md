# 🔐 Configuration Automatique des Secrets GitHub

## Méthode 1 : Script Automatique (Recommandé)

### Installation de GitHub CLI

```bash
# macOS
brew install gh

# Ubuntu/Debian
sudo apt install gh

# Windows
winget install --id GitHub.cli

# Ou télécharger : https://cli.github.com/
```

### Utilisation

```bash
# 1. Authentification (une seule fois)
gh auth login
# Suivre les instructions (choisir HTTPS ou SSH)

# 2. Dans votre projet
cd votre-projet

# 3. Configuration automatique
./scripts/setup-github-secrets.sh
```

**Le script fait automatiquement :**
- ✅ Détecte le repository GitHub
- ✅ Charge toutes les variables depuis `.env`
- ✅ Configure la clé SSH privée
- ✅ Crée/met à jour tous les secrets GitHub
- ✅ Affiche un résumé

### Exemple d'exécution

```
╔══════════════════════════════════════════════╗
║  Configuration Secrets GitHub automatique   ║
╚══════════════════════════════════════════════╝

✓ Repository détecté : kd-com/mon-projet
✓ Variables chargées

═══ Clé SSH Privée ═══

Clé SSH détectée : /home/user/.ssh/id_rsa
Utiliser cette clé ? (oui/non) [oui] : oui
  ✓ SSH_PRIVATE_KEY

═══ Secrets PREPROD ═══

  ✓ PREPROD_USER
  ✓ PREPROD_HOST
  ✓ PREPROD_PATH
  ✓ PREPROD_DB_USER
  ✓ PREPROD_DB_PASS
  ✓ PREPROD_DB_NAME
  ✓ REMOTE_PREPROD_URL

═══ cPanel API (o2switch) ═══

  ✓ CPANEL_API_TOKEN

═══ Secrets PROD ═══

  ✓ PROD_USER
  ✓ PROD_HOST
  ✓ PROD_PATH
  ⊘ PROD_PASS (vide, ignoré)
  ✓ PROD_DB_USER
  ✓ PROD_DB_PASS
  ✓ PROD_DB_NAME
  ✓ REMOTE_PROD_URL
  ⊘ PROD_DB_DUMP_URL (vide, ignoré)
  ⊘ PROD_DB_DUMP_TOKEN (vide, ignoré)

═══ Vérification ═══

Liste des secrets configurés :

CPANEL_API_TOKEN        Updated 2026-02-10
PREPROD_DB_NAME         Updated 2026-02-10
PREPROD_DB_PASS         Updated 2026-02-10
PREPROD_DB_USER         Updated 2026-02-10
PREPROD_HOST            Updated 2026-02-10
PREPROD_PATH            Updated 2026-02-10
PREPROD_USER            Updated 2026-02-10
PROD_DB_NAME            Updated 2026-02-10
PROD_DB_PASS            Updated 2026-02-10
PROD_DB_USER            Updated 2026-02-10
PROD_HOST               Updated 2026-02-10
PROD_PATH               Updated 2026-02-10
PROD_USER               Updated 2026-02-10
REMOTE_PREPROD_URL      Updated 2026-02-10
REMOTE_PROD_URL         Updated 2026-02-10
SSH_PRIVATE_KEY         Updated 2026-02-10

╔══════════════════════════════════════════════╗
║  ✅ Configuration terminée                   ║
╚══════════════════════════════════════════════╝
```

---

## Méthode 2 : Commandes Manuelles

### Configuration individuelle

```bash
# Authentification
gh auth login

# Créer un secret
echo "valeur_du_secret" | gh secret set NOM_SECRET

# Exemples
echo "facb6870" | gh secret set PREPROD_USER
echo "rabbit.o2switch.net" | gh secret set PREPROD_HOST

# Clé SSH (fichier entier)
gh secret set SSH_PRIVATE_KEY < ~/.ssh/id_rsa
```

### Script de configuration manuelle complet

```bash
#!/bin/bash

# Charger .env
source .env

# Secrets preprod
echo "$PREPROD_USER" | gh secret set PREPROD_USER
echo "$PREPROD_HOST" | gh secret set PREPROD_HOST
echo "$PREPROD_PATH" | gh secret set PREPROD_PATH
echo "$PREPROD_DB_USER" | gh secret set PREPROD_DB_USER
echo "$PREPROD_DB_PASS" | gh secret set PREPROD_DB_PASS
echo "$PREPROD_DB_NAME" | gh secret set PREPROD_DB_NAME
echo "$REMOTE_PREPROD_URL" | gh secret set REMOTE_PREPROD_URL

# cPanel
echo "$CPANEL_API_TOKEN" | gh secret set CPANEL_API_TOKEN

# Secrets prod
echo "$PROD_USER" | gh secret set PROD_USER
echo "$PROD_HOST" | gh secret set PROD_HOST
echo "$PROD_PATH" | gh secret set PROD_PATH
echo "$REMOTE_PROD_URL" | gh secret set REMOTE_PROD_URL

# Prod SSH
if [ -n "$PROD_DB_USER" ]; then
    echo "$PROD_DB_USER" | gh secret set PROD_DB_USER
    echo "$PROD_DB_PASS" | gh secret set PROD_DB_PASS
    echo "$PROD_DB_NAME" | gh secret set PROD_DB_NAME
fi

# Prod FTP
if [ -n "$PROD_PASS" ]; then
    echo "$PROD_PASS" | gh secret set PROD_PASS
fi

# Clé SSH
gh secret set SSH_PRIVATE_KEY < ~/.ssh/id_rsa

# Vérification
gh secret list
```

---

## Méthode 3 : Interface Web GitHub (Manuel)

Si vous ne voulez pas installer GitHub CLI :

### 1. Aller sur GitHub

```
https://github.com/VOTRE-USER/VOTRE-REPO/settings/secrets/actions
```

### 2. Cliquer sur "New repository secret"

### 3. Remplir pour chaque secret

**Secrets communs :**

| Name | Value (depuis .env) |
|------|---------------------|
| `SSH_PRIVATE_KEY` | Contenu complet de `~/.ssh/id_rsa` |
| `ACF_LICENSE_KEY` | Licence ACF Pro (déjà dans .env) |

**Preprod :**

| Name | Value |
|------|-------|
| `PREPROD_USER` | Valeur de `PREPROD_USER` dans .env |
| `PREPROD_HOST` | Valeur de `PREPROD_HOST` dans .env |
| `PREPROD_PATH` | Valeur de `PREPROD_PATH` dans .env |
| `PREPROD_DB_USER` | Valeur de `PREPROD_DB_USER` dans .env |
| `PREPROD_DB_PASS` | Valeur de `PREPROD_DB_PASS` dans .env |
| `PREPROD_DB_NAME` | Valeur de `PREPROD_DB_NAME` dans .env |
| `REMOTE_PREPROD_URL` | Valeur de `REMOTE_PREPROD_URL` dans .env |

**cPanel :**

| Name | Value |
|------|-------|
| `CPANEL_API_TOKEN` | Valeur de `CPANEL_API_TOKEN` dans .env |

**ACF Pro :**

| Name | Value |
|------|-------|
| `ACF_LICENSE_KEY` | `b3JkZXJfaWQ9MTYyNzY4fHR5cGU9ZGV2ZWxvcGVyfGRhdGU9MjAxOS0wNi0xMSAwNzowMDo1Ng==` |

**Prod (SSH) :**

| Name | Value |
|------|-------|
| `PROD_USER` | Valeur de `PROD_USER` dans .env |
| `PROD_HOST` | Valeur de `PROD_HOST` dans .env |
| `PROD_PATH` | Valeur de `PROD_PATH` dans .env |
| `PROD_DB_USER` | Valeur de `PROD_DB_USER` dans .env |
| `PROD_DB_PASS` | Valeur de `PROD_DB_PASS` dans .env |
| `PROD_DB_NAME` | Valeur de `PROD_DB_NAME` dans .env |
| `REMOTE_PROD_URL` | Valeur de `REMOTE_PROD_URL` dans .env |

**Prod (FTP) - en plus :**

| Name | Value |
|------|-------|
| `PROD_PASS` | Valeur de `PROD_PASS` dans .env |
| `PROD_DB_DUMP_URL` | Valeur de `PROD_DB_DUMP_URL` dans .env |
| `PROD_DB_DUMP_TOKEN` | Valeur de `PROD_DB_DUMP_TOKEN` dans .env |

### Astuce : Copier rapidement depuis .env

```bash
# Afficher une variable
grep PREPROD_USER .env

# Copier dans le presse-papier (macOS)
grep PREPROD_USER .env | cut -d= -f2 | tr -d '"' | pbcopy

# Linux
grep PREPROD_USER .env | cut -d= -f2 | tr -d '"' | xclip -selection clipboard
```

---

## Commandes Utiles GitHub CLI

### Lister les secrets

```bash
gh secret list
```

### Mettre à jour un secret

```bash
echo "nouvelle_valeur" | gh secret set NOM_SECRET
```

### Supprimer un secret

```bash
gh secret remove NOM_SECRET
```

### Voir l'aide

```bash
gh secret --help
```

---

## Sécurité

### ⚠️ Bonnes pratiques

- ✅ **Ne jamais** commit le fichier `.env`
- ✅ **Vérifier** que `.env` est dans `.gitignore`
- ✅ **Utiliser** des tokens API dédiés (pas votre mot de passe)
- ✅ **Regénérer** les tokens régulièrement
- ✅ **Limiter** les permissions des tokens au strict nécessaire

### Vérifier que .env n'est pas versionné

```bash
# Vérifier .gitignore
grep ".env" .gitignore

# Si absent, ajouter
echo ".env" >> .gitignore
echo ".env.*" >> .gitignore
echo "!.env.example" >> .gitignore

# Supprimer .env de Git si déjà commit
git rm --cached .env
git commit -m "Remove .env from version control"
```

---

## Workflow Complet Recommandé

### Setup initial (une fois)

```bash
# 1. Installer GitHub CLI
brew install gh  # ou apt/winget

# 2. Authentification
gh auth login

# 3. Vérifier
gh auth status
```

### Pour chaque projet

```bash
# 1. Configuration locale
./scripts/04_setup-remote-env.sh

# 2. Configuration GitHub automatique
./scripts/setup-github-secrets.sh

# 3. Vérification
gh secret list

# 4. Test déploiement
git push origin preprod
```

### Mise à jour des secrets

```bash
# Option 1 : Tout refaire
./scripts/04_setup-remote-env.sh  # Mettre à jour .env
./scripts/setup-github-secrets.sh # Mettre à jour GitHub

# Option 2 : Un seul secret
echo "nouvelle_valeur" | gh secret set NOM_SECRET

# Option 3 : Via l'interface web
# GitHub → Settings → Secrets → Actions → Edit
```

---

## Dépannage

### "gh: command not found"

```bash
# Installer GitHub CLI
brew install gh           # macOS
sudo apt install gh       # Ubuntu
winget install GitHub.cli # Windows
```

### "not logged in to any GitHub hosts"

```bash
gh auth login
# Suivre les instructions
```

### "HTTP 404: Not Found"

```bash
# Vérifier le repository
gh repo view

# Ou spécifier manuellement
gh secret set NOM_SECRET --repo user/repo
```

### "resource not accessible by integration"

Vérifier les permissions :
```bash
gh auth status
# Si nécessaire, se reconnecter avec plus de droits
gh auth login --scopes "repo,workflow"
```

---

## Comparaison des méthodes

| Méthode | Avantages | Inconvénients |
|---------|-----------|---------------|
| **Script auto** | ✅ Rapide (1 commande)<br>✅ Pas d'erreur de copie<br>✅ Répétable | ⚠️ Nécessite GitHub CLI |
| **CLI manuel** | ✅ Contrôle précis<br>✅ Scriptable | ⚠️ Plus long<br>⚠️ Nécessite GitHub CLI |
| **Interface web** | ✅ Pas d'installation<br>✅ Visuel | ⚠️ Très long<br>⚠️ Risque d'erreur |

**Recommandation** : Utiliser le script automatique (`setup-github-secrets.sh`)

---

## Résumé

### Pour commencer rapidement

```bash
# 1. Installer gh CLI
brew install gh

# 2. Se connecter
gh auth login

# 3. Tout configurer automatiquement
./scripts/setup-github-secrets.sh
```

C'est tout ! 🎉
