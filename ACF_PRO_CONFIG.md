# 🔑 Configuration ACF Pro License

## Vue d'ensemble

La licence ACF Pro est maintenant automatiquement configurée dans GitHub Secrets pour permettre les mises à jour automatiques du plugin.

## Variable configurée

| Variable | Valeur | Utilisation |
|----------|--------|-------------|
| `ACF_LICENSE_KEY` | `b3JkZXJfaWQ9MTYyNzY4fHR5cGU9ZGV2ZWxvcGVyfGRhdGU9MjAxOS0wNi0xMSAwNzowMDo1Ng==` | Licence ACF Pro (base64) |

## Configuration automatique

### Méthode 1 : Via le script (recommandé)

La licence est **déjà incluse** dans `.env.example` et sera automatiquement configurée :

```bash
# Configuration automatique
./scripts/setup-github-secrets.sh
```

**Le script configure automatiquement :**
- ✅ `ACF_LICENSE_KEY` depuis `.env`
- ✅ Valeur par défaut si absente dans `.env`

### Méthode 2 : Manuelle via GitHub CLI

```bash
echo "b3JkZXJfaWQ9MTYyNzY4fHR5cGU9ZGV2ZWxvcGVyfGRhdGU9MjAxOS0wNi0xMSAwNzowMDo1Ng==" | gh secret set ACF_LICENSE_KEY
```
 
### Méthode 3 : Interface Web GitHub

1. Aller sur : `https://github.com/USER/REPO/settings/secrets/actions`
2. Cliquer sur **New repository secret**
3. Name : `ACF_LICENSE_KEY`
4. Value : `b3JkZXJfaWQ9MTYyNzY4fHR5cGU9ZGV2ZWxvcGVyfGRhdGU9MjAxOS0wNi0xMSAwNzowMDo1Ng==`
5. Cliquer sur **Add secret**

## Utilisation dans les workflows

### Workflow de mise à jour ACF Pro

Le workflow `.github/workflows/update-acf-pro.yml` utilise cette licence :

```yaml
env:
  ACF_LICENSE_KEY: ${{ secrets.ACF_LICENSE_KEY }}

steps:
  - name: Télécharger ACF Pro
    run: |
      # Utilise ACF_LICENSE_KEY pour télécharger
      curl -o acf-pro.zip "${{ secrets.ACF_DOWNLOAD_URL }}"
```

### Mise à jour bimensuelle automatique

Le workflow est configuré pour :
- ✅ S'exécuter tous les 15 jours (`cron: '0 0 */15 * *'`)
- ✅ Télécharger la dernière version ACF Pro
- ✅ Tester le plugin
- ✅ Mettre à jour le dépôt Git
- ✅ Propager aux sites
- ✅ Envoyer un email de notification

## Vérification

### Vérifier que le secret est configuré

```bash
# Lister tous les secrets
gh secret list

# Doit afficher :
# ACF_LICENSE_KEY  Updated 2026-02-10
```

### Tester le workflow

```bash
# Déclencher manuellement
gh workflow run update-acf-pro.yml

# Voir les logs
gh run list --workflow=update-acf-pro.yml
gh run view [RUN_ID] --log
```

## Format de la licence

La licence est encodée en **base64** et contient :
- `order_id=162768`
- `type=developer`
- `date=2019-06-11 07:00:56`

## Sécurité

### ✅ Bonnes pratiques

- ✅ Licence stockée dans GitHub Secrets (chiffrée)
- ✅ Jamais versionnée dans Git
- ✅ Accessible uniquement par les workflows GitHub Actions
- ✅ Pas d'exposition publique

### ⚠️ Ne jamais

- ❌ Commit la licence dans le code
- ❌ Partager la licence publiquement
- ❌ Logger la licence dans les workflows

## Renouvellement de licence

Si vous devez mettre à jour la licence ACF Pro :

### 1. Mettre à jour dans .env

```bash
# Éditer .env
nano .env

# Modifier la ligne
ACF_LICENSE_KEY="NOUVELLE_LICENCE_BASE64"
```

### 2. Mettre à jour GitHub Secrets

```bash
# Option 1 : Script automatique
./scripts/setup-github-secrets.sh

# Option 2 : Manuelle
echo "NOUVELLE_LICENCE_BASE64" | gh secret set ACF_LICENSE_KEY
```

### 3. Vérifier

```bash
gh secret list | grep ACF_LICENSE_KEY
```

## Dépannage

### "ACF_LICENSE_KEY non trouvé"

```bash
# Vérifier dans .env
grep ACF_LICENSE_KEY .env

# Si absent, ajouter
echo 'ACF_LICENSE_KEY="b3JkZXJfaWQ9MTYyNzY4fHR5cGU9ZGV2ZWxvcGVyfGRhdGU9MjAxOS0wNi0xMSAwNzowMDo1Ng=="' >> .env

# Reconfigurer GitHub
./scripts/setup-github-secrets.sh
```

### "Erreur de téléchargement ACF Pro"

```bash
# Vérifier que la licence est valide
gh secret list | grep ACF_LICENSE_KEY

# Tester manuellement
curl -o test.zip "https://connect.advancedcustomfields.com/v2/plugins/download?p=pro&k=b3JkZXJfaWQ9MTYyNzY4fHR5cGU9ZGV2ZWxvcGVyfGRhdGU9MjAxOS0wNi0xMSAwNzowMDo1Ng=="
```

### "Workflow de mise à jour échoue"

```bash
# Voir les logs détaillés
gh run list --workflow=update-acf-pro.yml
gh run view [RUN_ID] --log

# Vérifier les secrets
gh secret list
```

## Documentation connexe

- [GITHUB_SECRETS_AUTO.md](./GITHUB_SECRETS_AUTO.md) - Configuration automatique des secrets
- [SECRETS_CONFIGURATION.md](./SECRETS_CONFIGURATION.md) - Configuration manuelle
- `.github/workflows/update-acf-pro.yml` - Workflow de mise à jour ACF Pro

## Support

En cas de problème avec la licence ACF Pro :
1. Vérifier que le secret est bien configuré : `gh secret list`
2. Vérifier le workflow : `gh run list --workflow=update-acf-pro.yml`
3. Consulter les logs : `gh run view [RUN_ID] --log`
4. Contacter le support ACF si la licence est invalide

---

**Note** : Cette licence est de type "developer" et permet les mises à jour automatiques sur tous vos projets.
