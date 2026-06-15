# 📚 Guide Complet - CI/CD WordPress avec GitHub Actions

## 📋 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Nouveautés et améliorations](#nouveautés-et-améliorations)
3. [Configuration initiale](#configuration-initiale)
4. [Synchronisation BDD et fichiers](#synchronisation-bdd-et-fichiers)
5. [Système de template Git](#système-de-template-git)
6. [Déploiement automatique](#déploiement-automatique)
7. [Exemples de workflows](#exemples-de-workflows)
8. [Dépannage](#dépannage)

---

## Vue d'ensemble

Ce système de CI/CD permet de gérer facilement plusieurs environnements WordPress (local, preprod, prod) avec :

- ✅ **Déploiement automatique** via GitHub Actions
- ✅ **Synchronisation BDD et fichiers** dans les deux sens
- ✅ **Support SSH et FTP** avec détection automatique
- ✅ **Whitelist IP automatique** pour o2switch
- ✅ **Synchronisation vers template Git** (optionnelle)

---

## Nouveautés et améliorations

### 🎯 Workflow de déploiement

**Avant :**
- ❌ Prod SSH sur o2switch échouait (pas de whitelist IP)
- ❌ Fallback FTP uniquement pour certains cas

**Après :**
- ✅ **Détection automatique** du type d'hébergeur
- ✅ **Whitelist IP automatique** si o2switch (preprod ET prod)
- ✅ **Test SSH puis fallback FTP** pour les autres hébergeurs
- ✅ Un seul workflow universel

### 📥 Scripts de synchronisation

**sync-db.sh** :
- ✅ Support PULL depuis prod (SSH ou dump PHP sécurisé)
- ✅ Support PUSH vers preprod/prod
- ✅ Search-replace automatique des URLs
- ✅ Détection auto des conteneurs Docker

**sync-file.sh** :
- ✅ Détection auto SSH/FTP
- ✅ Rsync optimisé en SSH
- ✅ Lftp en fallback FTP
- ✅ Exclusions automatiques (.git, node_modules, etc.)

### 🔄 Synchronisation template

- ✅ **Activation/désactivation** facile via menu interactif
- ✅ **Hook post-commit** automatique
- ✅ **Configuration JSON** flexible
- ✅ Possibilité de commit sans sync (`--no-verify`)

---

## Configuration initiale

### 1. Prérequis

```bash
# Installer les dépendances
brew install lftp rsync  # macOS
sudo apt install lftp rsync  # Linux

# Cloner le projet
git clone git@github.com:votre-compte/votre-projet.git
cd votre-projet

# Rendre les scripts exécutables
chmod +x scripts/*.sh
```

### 2. Configuration des environnements

```bash
./scripts/04_setup-remote-env.sh
```

Ce script interactif configure :
- ✅ Preprod (SSH o2switch)
- ✅ Prod (SSH ou FTP avec auto-détection)
- ✅ Token cPanel API (si o2switch)
- ✅ Synchronisation template (optionnelle)

**Exemple de configuration générée (`.env`) :**

```dotenv
###############################################
# 🔧 Préproduction
###############################################
PREPROD_USER="facb6870"
PREPROD_HOST="rabbit.o2switch.net"
PREPROD_PATH="/home/facb6870/site_clients/mon-site"
PREPROD_DB_USER="facb6870_kd-com"
PREPROD_DB_PASS="motdepasse"
PREPROD_DB_NAME="facb6870_monsite"
REMOTE_PREPROD_URL="https://monsite.kd-com.fr"

###############################################
# 🔑 cPanel API (o2switch)
###############################################
CPANEL_API_TOKEN="VOTRE_TOKEN_ICI"

###############################################
# 🛠️ Production (exemple SSH o2switch)
###############################################
PROD_USER="facb6870"
PROD_HOST="rabbit.o2switch.net"
PROD_PATH="/home/facb6870/site_clients/mon-site-prod"
PROD_DB_USER="facb6870_kd-com"
PROD_DB_PASS="motdepasse"
PROD_DB_NAME="facb6870_monsite_prod"
REMOTE_PROD_URL="https://www.monsite.com"

###############################################
# 🛠️ Production (exemple FTP OVH)
###############################################
# PROD_USER="ftpuser"
# PROD_HOST="ftp.cluster110.hosting.ovh.net"
# PROD_PATH="/www"
# PROD_PASS="motdepasseftp"
# REMOTE_PROD_URL="https://www.monsite.com"
# PROD_DB_DUMP_URL="https://www.monsite.com/dump_db.php"
# PROD_DB_DUMP_TOKEN="secret-token-2025"
```

### 3. Configuration GitHub Secrets

Allez dans **Settings → Secrets and variables → Actions** et ajoutez :

#### Secrets communs
| Secret | Valeur |
|--------|--------|
| `SSH_PRIVATE_KEY` | Contenu complet de `~/.ssh/id_rsa` |

#### Secrets preprod
| Secret | Valeur depuis .env |
|--------|-------------------|
| `PREPROD_USER` | `PREPROD_USER` |
| `PREPROD_HOST` | `PREPROD_HOST` |
| `PREPROD_PATH` | `PREPROD_PATH` |
| `REMOTE_PREPROD_URL` | `REMOTE_PREPROD_URL` |
| `CPANEL_API_TOKEN` | `CPANEL_API_TOKEN` |

#### Secrets prod (SSH o2switch)
| Secret | Valeur depuis .env |
|--------|-------------------|
| `PROD_USER` | `PROD_USER` |
| `PROD_HOST` | `PROD_HOST` |
| `PROD_PATH` | `PROD_PATH` |
| `REMOTE_PROD_URL` | `REMOTE_PROD_URL` |
| `CPANEL_API_TOKEN` | `CPANEL_API_TOKEN` (même que preprod) |

#### Secrets prod (FTP)
| Secret | Valeur depuis .env |
|--------|-------------------|
| `PROD_USER` | `PROD_USER` |
| `PROD_HOST` | `PROD_HOST` |
| `PROD_PATH` | `PROD_PATH` |
| `PROD_PASS` | `PROD_PASS` |
| `REMOTE_PROD_URL` | `REMOTE_PROD_URL` |

---

## Synchronisation BDD et fichiers

### Récupérer la BDD de prod en local

```bash
# Pull de la BDD prod
./scripts/sync-db.sh pull prod

# Le script fait automatiquement :
# 1. Dump de la BDD distante (SSH ou PHP dump)
# 2. Import dans le conteneur Docker local
# 3. Search-replace des URLs (prod → local)
```

**Résultat :**
```
✅ Dump réussi via SSH
✅ Import réussi
✅ URLs mises à jour
🎉 Synchronisation BDD terminée !
📍 Base locale : mon-site
🌐 URL locale : https://mon-site.localhost
```

### Récupérer les fichiers de prod en local

```bash
# Pull des fichiers prod
./scripts/sync-file.sh pull prod

# Le script fait automatiquement :
# 1. Test SSH
# 2. Rsync si SSH OK, sinon lftp (FTP)
# 3. Téléchargement de wp-content/
```

### Envoyer vers preprod/prod

```bash
# Envoyer la BDD vers preprod
./scripts/sync-db.sh push preprod

# Envoyer les fichiers vers preprod
./scripts/sync-file.sh push preprod
```

### Workflow complet : prod → local

```bash
# 1. Récupérer tout de prod
./scripts/sync-db.sh pull prod
./scripts/sync-file.sh pull prod

# 2. Travailler en local
# ...

# 3. Tester en preprod
./scripts/sync-db.sh push preprod
./scripts/sync-file.sh push preprod

# 4. Déployer en prod (via Git)
git add .
git commit -m "Mise à jour"
git push origin prod  # Déploiement auto via GitHub Actions
```

---

## Système de template Git

### Concept

Synchronise automatiquement vos modifications (scripts, thème) vers un dépôt "template" pour réutilisation sur d'autres projets.

### Activation

```bash
./scripts/install-sync.sh

# Choisir option 1 : Activer
# Renseigner :
#   - URL du template : git@github.com:kd-com/template-wordpress.git
#   - Branche : main
```

**Résultat :**
- ✅ Hook `post-commit` créé
- ✅ Configuration `.template-sync.json` générée
- ✅ À chaque commit, sync auto vers le template

### Configuration (`.template-sync.json`)

```json
{
  "template_repo": "git@github.com:kd-com/template-wordpress.git",
  "template_branch": "main",
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
```

### Utilisation

```bash
# Commit normal (avec sync auto)
git add .
git commit -m "Amélioration du thème"
# → Sync automatique vers le template

# Commit sans sync (temporaire)
git commit --no-verify -m "Config spécifique projet"

# Sync manuelle
./scripts/sync-to-template.sh


# Désactiver la sync
./scripts/install-sync.sh
# Choisir option 2 : Désactiver
```

---

## Déploiement automatique

### Workflow GitHub Actions

Le fichier `.github/workflows/deploy.yml` gère **tout automatiquement** :

#### Déploiement preprod (push sur branche `preprod`)

```bash
git checkout preprod
git merge main
git push origin preprod
```

**GitHub Actions fait :**
1. ✅ Récupère l'IP du runner
2. ✅ Configure whitelist cPanel (o2switch)
3. ✅ Déploie via SSH/rsync
4. ✅ Notification de succès

#### Déploiement prod (push sur branche `prod`)

```bash
git checkout prod
git merge preprod
git push origin prod
```

**GitHub Actions fait :**
1. ✅ Détecte si prod = o2switch
   - **Si oui** : whitelist IP automatique
   - **Si non** : test SSH puis fallback FTP
2. ✅ Déploie le thème `kd-com`
3. ✅ Notification de succès

### Déploiement manuel (workflow_dispatch)

Depuis GitHub :
1. **Actions** → **Deploy kd-com theme**
2. **Run workflow**
3. Choisir environnement : `preprod` ou `prod`

---

## Exemples de workflows

### Workflow 1 : Nouveau projet depuis zéro

```bash
# 1. Configuration initiale
./scripts/01_setup-project.sh
./scripts/02_create-db-user.sh  # Si shared-db
./scripts/03_init-wp.sh

# 2. Configuration environnements distants 
./scripts/04_setup-remote-env.sh

# 3. Activer sync template (optionnel)
./scripts/install-sync.sh

# 4. Premier déploiement
git add .
git commit -m "Init projet"
git push origin main

git checkout -b preprod
git push origin preprod  # Déploiement auto preprod
 
git checkout -b prod
git push origin prod  # Déploiement auto prod
```

### Workflow 2 : Récupérer un site existant

```bash
# 1. Configuration
./scripts/04_setup-remote-env.sh

# 2. Récupérer prod en local
./scripts/sync-db.sh pull prod
./scripts/sync-file.sh pull prod

# 3. Démarrer Docker
docker compose up -d

# 4. Accéder au site
open https://mon-site.localhost
```

### Workflow 3 : Développement quotidien

```bash
# 1. Travailler en local
# ... modifications du thème, plugins, etc.

# 2. Tester en preprod
git add .
git commit -m "Ajout fonctionnalité X"
git push origin preprod  # Déploiement auto

# 3. Valider en preprod
# → https://monsite.kd-com.fr

# 4. Déployer en prod
git checkout prod
git merge preprod
git push origin prod  # Déploiement auto
```

### Workflow 4 : Hotfix en prod

```bash
# 1. Récupérer prod en local
./scripts/sync-db.sh pull prod
./scripts/sync-file.sh pull prod

# 2. Corriger le bug en local
# ...

# 3. Envoyer directement en prod
git checkout prod
git add .
git commit -m "Hotfix: correction bug X"
git push origin prod

# 4. Merger dans preprod/main
git checkout preprod
git merge prod
git checkout main
git merge prod
```

---

## Dépannage

### Problème : SSH échoue sur o2switch prod

**Symptômes :**
```
❌ Permission denied (publickey)
```

**Solution :**
1. Vérifier que `CPANEL_API_TOKEN` est dans GitHub Secrets
2. Vérifier que `PROD_HOST` contient bien "o2switch.net"
3. Tester manuellement :
```bash
curl -H'Authorization: cpanel USER:TOKEN' \
  'https://HOST:2083/execute/SshWhitelist/list'
```

### Problème : FTP fallback ne fonctionne pas

**Symptômes :**
```
❌ Erreur lors de la synchronisation FTP
```

**Solution :**
1. Vérifier que `PROD_PASS` est défini dans GitHub Secrets
2. Tester manuellement :
```bash
lftp -u USER,PASS ftp://HOST
```

### Problème : Sync BDD prod échoue (FTP)

**Symptômes :**
```
❌ Échec du dump via PHP
```

**Solution :**
1. Uploader `scripts/sftp_acopiersurserveur/dump_db.php` sur le serveur prod
2. Modifier le token dans le fichier :
```php
$expectedToken = "VOTRE_TOKEN_ICI";  // Même que PROD_DB_DUMP_TOKEN
```
3. Tester l'URL :
```bash
curl "https://www.monsite.com/dump_db.php?token=VOTRE_TOKEN"
```

### Problème : Sync template échoue

**Symptômes :**
```
❌ Erreur lors du clone du template
```

**Solution :**
1. Vérifier l'URL du template dans `.template-sync.json`
2. Vérifier les droits SSH sur le dépôt template :
```bash
ssh -T git@github.com
```
3. Désactiver temporairement :
```bash
./scripts/install-sync.sh
# Option 2 : Désactiver
```

### Problème : Search-replace ne fonctionne pas

**Symptômes :**
```
⚠️ Conteneur WordPress non trouvé
```

**Solution :**
1. Vérifier le nom du conteneur :
```bash
docker ps --format '{{.Names}}'
```
2. Modifier `PROJECT_NAME` dans `.env` si nécessaire
3. Lancer le search-replace manuellement :
```bash
docker exec -u www-data NOM_CONTENEUR \
  wp search-replace 'https://old.com' 'https://new.com' --all-tables --allow-root
```

---

## Résumé des commandes

### Configuration
```bash
./scripts/04_setup-remote-env.sh     # Config preprod/prod
./scripts/install-sync.sh            # Gestion sync template
```

### Synchronisation
```bash
./scripts/sync-db.sh pull preprod    # Récupérer BDD preprod
./scripts/sync-db.sh pull prod       # Récupérer BDD prod
./scripts/sync-db.sh push preprod    # Envoyer BDD vers preprod

./scripts/sync-file.sh pull preprod  # Récupérer fichiers preprod
./scripts/sync-file.sh pull prod     # Récupérer fichiers prod
./scripts/sync-file.sh push preprod  # Envoyer fichiers vers preprod
```

### Déploiement
```bash
git push origin preprod              # Déploie automatiquement en preprod
git push origin prod                 # Déploie automatiquement en prod
git commit --no-verify               # Commit sans sync template
```

### Template
```bash
./scripts/sync-to-template.sh        # Sync manuelle vers template
git commit --no-verify               # Commit sans sync auto
```

---

## Support

En cas de problème non résolu :
1. Consultez les logs GitHub Actions
2. Testez les commandes manuellement en local
3. Vérifiez les secrets GitHub
4. Contactez le support de votre hébergeur (o2switch, OVH, etc.)