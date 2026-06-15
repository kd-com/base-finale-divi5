# 🔄 Guide de Migration - Nouvelle Version CI/CD

## ⚡ Migration rapide (15 minutes)

### Étape 1 : Backup (2 min)

```bash
# Sauvegarder les fichiers actuels
cp .env .env.backup
cp -r .github/workflows .github/workflows.backup

# Sauvegarder la BDD locale
./scripts/sync-db.sh pull local > /tmp/backup_local.sql 2>/dev/null || \
  docker exec shared-db mysqldump -uroot -prootpassword nom_bdd > /tmp/backup_local.sql
```

### Étape 2 : Mise à jour des fichiers (5 min) 

```bash
# Télécharger les nouveaux fichiers
# Depuis le ZIP fourni ou git pull

# Copier les nouveaux scripts
cp scripts_nouveaux/*.sh scripts/
chmod +x scripts/*.sh

# Copier le nouveau workflow
cp deploy.yml .github/workflows/deploy.yml

# Copier les fichiers de documentation
cp GUIDE_COMPLET.md .
cp README.md .
cp SECRETS_CONFIGURATION.md .
cp MIGRATION_GUIDE.md .
```
 
### Étape 3 : Configuration (5 min)

```bash
# Lancer la configuration interactive
./scripts/04_setup-remote-env.sh

# Suivre les instructions :
# 1. Preprod : renseigner les infos SSH
# 2. cPanel API : copier le token depuis cPanel
# 3. Prod : choisir SSH ou FTP
# 4. Template : activer ou non (optionnel)
```

### Étape 4 : GitHub Secrets (3 min)

Aller dans **Settings → Secrets and variables → Actions** :

#### Ajouter/vérifier les secrets :

**Communs :**
- [ ] `SSH_PRIVATE_KEY` (contenu de `~/.ssh/id_rsa`)

**Preprod :**
- [ ] `PREPROD_USER`
- [ ] `PREPROD_HOST`
- [ ] `PREPROD_PATH`
- [ ] `REMOTE_PREPROD_URL`

**cPanel (si o2switch) :**
- [ ] `CPANEL_API_TOKEN`

**Prod (choisir selon config) :**

SSH :
- [ ] `PROD_USER`
- [ ] `PROD_HOST`
- [ ] `PROD_PATH`
- [ ] `REMOTE_PROD_URL`

FTP (en plus ou à la place) :
- [ ] `PROD_PASS`
- [ ] `PROD_DB_DUMP_URL` (optionnel)
- [ ] `PROD_DB_DUMP_TOKEN` (optionnel)

### Étape 5 : Test (2 min)

```bash
# Test sync preprod
./scripts/sync-db.sh pull preprod
./scripts/sync-file.sh pull preprod

# Test déploiement preprod
git add .
git commit -m "Migration nouvelle version CI/CD"
git push origin preprod

# Vérifier dans GitHub Actions que ça passe ✅
```

---

## 🔍 Différences clés

### Avant vs Après

| Fonctionnalité | Avant | Après |
|----------------|-------|-------|
| **Prod SSH o2switch** | ❌ Échoue (pas de whitelist) | ✅ Whitelist auto |
| **Prod FTP** | ✅ Marche | ✅ Marche (amélioré) |
| **Pull BDD prod** | ⚠️ Limité | ✅ SSH + FTP + PHP dump |
| **Pull fichiers prod** | ⚠️ SSH uniquement | ✅ SSH + FTP auto |
| **Sync template** | ❌ N'existe pas | ✅ Hook auto optionnel |

### Nouveaux fichiers

```
scripts/
├── sync-db.sh              ← AMÉLIORÉ (pull prod, push, auto-detect)
├── sync-file.sh            ← AMÉLIORÉ (auto SSH/FTP)
├── 04_setup-remote-env.sh  ← NOUVEAU (config interactive)
├── install-sync.sh         ← NOUVEAU (gestion template)
└── sync-to-template.sh     ← NOUVEAU (sync template)

.github/workflows/
└── deploy.yml              ← AMÉLIORÉ (auto-detect o2switch, whitelist)

Documentation/
├── GUIDE_COMPLET.md        ← NOUVEAU (guide détaillé)
├── SECRETS_CONFIGURATION.md← NOUVEAU (config secrets)
├── MIGRATION_GUIDE.md      ← NOUVEAU (avant/après)
└── README.md               ← MIS À JOUR
```

---

## 🎯 Cas d'usage spécifiques

### Cas 1 : J'ai preprod et prod sur o2switch

**Avant :** Prod SSH échouait

**Migration :**
```bash
# 1. Configuration
./scripts/04_setup-remote-env.sh
# → Preprod : SSH o2switch
# → Prod : SSH o2switch
# → cPanel API : renseigner le token

# 2. GitHub Secrets
# Ajouter CPANEL_API_TOKEN (un seul pour preprod ET prod)

# 3. Test
git push origin prod
# ✅ Déploiement SSH réussit avec whitelist auto
```

### Cas 2 : J'ai preprod o2switch et prod OVH/FTP

**Avant :** Fallback FTP marche

**Migration :**
```bash
# 1. Configuration
./scripts/04_setup-remote-env.sh
# → Preprod : SSH o2switch
# → Prod : FTP
# → cPanel API : renseigner le token (pour preprod)

# 2. GitHub Secrets
# Ajouter :
#   - CPANEL_API_TOKEN (pour preprod)
#   - PROD_PASS (pour prod FTP)

# 3. Test
git push origin prod
# ✅ Déploiement FTP fonctionne comme avant (amélioré)
```

### Cas 3 : Je veux récupérer prod en local

**Avant :** Pas supporté facilement

**Migration :**
```bash
# 1. Configuration (si pas déjà fait)
./scripts/04_setup-remote-env.sh

# 2. Pull BDD et fichiers
./scripts/sync-db.sh pull prod      # SSH ou PHP dump
./scripts/sync-file.sh pull prod    # SSH ou FTP

# 3. Accéder en local
open https://mon-site.localhost
```

### Cas 4 : Je veux un template Git réutilisable

**Avant :** Pas supporté

**Migration :**
```bash
# 1. Créer un dépôt template sur GitHub
# Exemple : git@github.com:kd-com/template-wordpress.git

# 2. Activer la sync
./scripts/install-sync.sh
# → Option 1 : Activer
# → URL : git@github.com:kd-com/template-wordpress.git

# 3. Commit
git add .
git commit -m "Test sync template"
# → Sync automatique vers le template

# 4. Désactiver temporairement
git commit --no-verify -m "Config spécifique projet"
```

---

## 🔧 Personnalisation post-migration

### Modifier les dossiers synchronisés (template)

Éditer `.template-sync.json` :

```json
{
  "sync_folders": [
    "scripts",
    "wp-content/themes/kd-com",
    "wp-content/plugins/mon-plugin"  ← Ajouter
  ]
}
```

### Ajouter des exclusions de synchronisation

Éditer `.template-sync.json` :

```json
{
  "exclude_patterns": [
    "*.env",
    "mon-dossier-specifique",  ← Ajouter
    "*.backup"
  ]
}
```

### Changer l'environnement prod de SSH vers FTP

```bash
# 1. Reconfigurer
./scripts/04_setup-remote-env.sh
# → Prod : choisir FTP au lieu de SSH

# 2. Mettre à jour GitHub Secrets
# Supprimer : PROD_DB_USER, PROD_DB_PASS, PROD_DB_NAME
# Ajouter : PROD_PASS

# 3. Optionnel : dump PHP pour BDD
# Ajouter : PROD_DB_DUMP_URL, PROD_DB_DUMP_TOKEN
# Uploader dump_db.php sur le serveur
```

---

## ⚠️ Points d'attention

### 1. Token cPanel API

**Pour o2switch uniquement** :
- Un seul token pour preprod ET prod (si même serveur)
- Obtenir dans cPanel → **Manage API Tokens**
- Permissions requises : SshWhitelist (add, remove_all, list)

### 2. Clé SSH privée

**Format dans GitHub Secrets :**
```
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA...
...
-----END RSA PRIVATE KEY-----
```

**Copier TOUT le contenu** de `~/.ssh/id_rsa`, y compris les lignes BEGIN/END.

### 3. Chemins distants

**Vérifier les chemins** :
- o2switch : `/home/USER/site_clients/PROJET`
- OVH : généralement `/www` ou `/html`

**Test SSH :**
```bash
ssh user@host "ls -la /chemin/vers/site"
```

### 4. URLs avec/sans HTTPS

**Important :** Utiliser le bon protocole dans `.env` :
```dotenv
REMOTE_PREPROD_URL="https://site.kd-com.fr"   ✅
REMOTE_PREPROD_URL="http://site.kd-com.fr"    ❌ (si HTTPS)
```

---

## 🆘 Problèmes courants

### "CPANEL_API_TOKEN introuvable"

```bash
# Vérifier dans GitHub Secrets
# Settings → Secrets → CPANEL_API_TOKEN

# Tester localement
curl -H'Authorization: cpanel USER:TOKEN' \
  'https://HOST:2083/execute/SshWhitelist/list'
```

### "SSH échoue après migration"

```bash
# 1. Vérifier la clé SSH
cat ~/.ssh/id_rsa
# → Copier TOUT dans GitHub Secrets SSH_PRIVATE_KEY

# 2. Vérifier que PROD_HOST contient "o2switch.net"
# Si oui → whitelist auto
# Si non → test SSH puis FTP

# 3. Logs GitHub Actions
# Actions → Dernier run → "Check if prod uses o2switch"
```

### "sync-db.sh : commande introuvable"

```bash
# Rendre exécutable
chmod +x scripts/*.sh

# Vérifier
ls -la scripts/sync-db.sh
# → -rwxr-xr-x (le 'x' est important)
```

### "Template sync échoue"

```bash
# 1. Vérifier l'URL dans .template-sync.json
cat .template-sync.json

# 2. Tester l'accès SSH au template
ssh -T git@github.com

# 3. Désactiver temporairement
./scripts/install-sync.sh
# → Option 2 : Désactiver
```

---

## ✅ Checklist finale

Après migration, vérifier :

- [ ] `.env` contient toutes les variables preprod/prod
- [ ] GitHub Secrets sont configurés
- [ ] `./scripts/sync-db.sh pull preprod` fonctionne
- [ ] `./scripts/sync-file.sh pull preprod` fonctionne
- [ ] `git push origin preprod` déclenche le déploiement
- [ ] Déploiement preprod réussit dans GitHub Actions
- [ ] `git push origin prod` déclenche le déploiement
- [ ] Déploiement prod réussit dans GitHub Actions
- [ ] Sites preprod et prod sont accessibles
- [ ] Template sync activé (si désiré)

---

## 📞 Support

**Documentation :**
- [Guide complet](./GUIDE_COMPLET.md)
- [Configuration secrets](./SECRETS_CONFIGURATION.md)
- [README](./README.md)

**En cas de blocage :**
1. Vérifier les logs GitHub Actions
2. Tester les commandes en local
3. Consulter la documentation complète
4. Contacter le support KD-COM

---

**Bonne migration ! 🚀**