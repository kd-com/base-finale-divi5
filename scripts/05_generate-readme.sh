#!/bin/bash
# scripts/generate-readme.sh
# ---------------------------------------------
# Objectif : Générer automatiquement le README.md à jour
# ---------------------------------------------

# Chemin vers le README (à la racine du projet)
README_FILE="$(dirname "$0")/../README.md"

cat > "$README_FILE" <<'EOF'
# Projet WordPress + Docker + Git + CI/CD

---

## 📌 Sommaire
- Présentation du projet
- Prérequis
- Configuration initiale
- Gestion des environnements & synchronisation
- Structure des branches Git
- Personnalisation
- CI/CD & Déploiement automatique
- Dépannage
- Sécurité & bonnes pratiques
- Traefik
- Duplication machine à machine
- Documentation du thème kd-com

---

## Présentation du projet
Ce projet permet de créer, configurer et déployer un site WordPress avec Docker, Traefik et Git, en utilisant des scripts automatisés pour gérer facilement les environnements (local, préprod, prod).

- Installation et configuration WordPress automatisée  
- Gestion des bases de données via conteneur Docker ou service externe  
- Préfixe de table personnalisable via `.env`  
- Configuration centralisée et versionnée  

---

## Prérequis
- Docker et Docker Compose installés  
- Git installé  
- Accès sudo pour modifier `/etc/hosts`  

---

## Configuration initiale
1. Cloner le dépôt :
\`\`\`bash
git clone https://github.com/votre-compte/wp-docker-git-project.git
cd wp-docker-git-project
\`\`\`

2. Rendre les scripts exécutables :
\`\`\`bash
chmod +x scripts/*.sh
\`\`\`

3. Configurer le projet :
\`\`\`bash
./scripts/01_setup-project.sh
\`\`\`

4. Démarrer Docker :
\`\`\`bash
docker compose up -d
\`\`\`

5. Installer WordPress automatiquement :
\`\`\`bash
./scripts/02_init-wp.sh
\`\`\`

Le script affichera un récapitulatif des identifiants admin, base de données, préfixe de table et chemins de configuration.

---

## Gestion des environnements & synchronisation

### Variables à renseigner dans `.env`
\`\`\`dotenv
# Préprod
PREPROD_USER=
PREPROD_HOST=
PREPROD_PATH=
PREPROD_DB_USER=
PREPROD_DB_PASS=
PREPROD_DB_NAME=
CPANEL_API_TOKEN=

# Prod
PROD_USER=
PROD_HOST=
PROD_PATH=
PROD_DB_USER=
PROD_DB_PASS=
PROD_DB_NAME=
\`\`\`

### Synchronisation multi-environnements
Les scripts `sync-db.sh` et `sync-file.sh` permettent de synchroniser la base de données et les fichiers avec le serveur distant.

**Exemples d’utilisation :**  

- **Préprod :**
\`\`\`bash
./scripts/sync-db.sh pull preprod
./scripts/sync-db.sh push preprod
./scripts/sync-file.sh pull preprod
./scripts/sync-file.sh push preprod
\`\`\`

- **Prod :**
\`\`\`bash
./scripts/sync-db.sh pull prod
./scripts/sync-db.sh push prod
./scripts/sync-file.sh pull prod
./scripts/sync-file.sh push prod
\`\`\`

---

## Structure des branches Git
| Branche    | Environnement   | Déclenchement CI/CD                |
|------------|-----------------|------------------------------------|
| prod       | Production      | Déploiement automatique sur prod   |
| preprod    | Préproduction   | Déploiement automatique sur préprod|
| main       | Dev/Template    | Pas de déploiement automatique     |
| feature/*  | Local           | Pas de déploiement automatique     |

---

## Personnalisation
- **Thème enfant** : `wp-content/themes/mon-child-theme/`  
- **Configuration WordPress** : `wp-config.php` généré automatiquement depuis `.env` avec le préfixe de table défini.  
- Modifications locales possibles dans `.env` ou directement dans `wp-config.php`.  

---

## CI/CD & Déploiement automatique

### Variables à renseigner dans GitHub (Secrets)

#### Préprod (o2switch)
| Nom du secret         | Description                                 |
|----------------------|---------------------------------------------|
| PREPROD_USER         | Utilisateur cPanel/SSH                      |
| PREPROD_HOST         | Adresse du serveur                          |
| PREPROD_PATH         | Chemin cible sur le serveur                 |
| SSH_PRIVATE_KEY      | Clé privée SSH (contenu, pas le chemin)     |
| CPANEL_API_TOKEN     | Jeton API cPanel pour whitelist SSH         |

#### Prod (classique)
| Nom du secret         | Description                                 |
|----------------------|---------------------------------------------|
| PROD_USER            | Utilisateur SSH                             |
| PROD_HOST            | Adresse du serveur                          |
| PROD_PATH            | Chemin cible sur le serveur                 |
| SSH_PRIVATE_KEY      | Clé privée SSH (contenu, pas le chemin)     |

### Fonctionnement du workflow
- **Préprod** : Ajout IP à la whitelist SSH via l’API cPanel, installation de la clé SSH, synchronisation du dossier `kd-com` avec `rsync`.  
- **Prod** : Installation de la clé SSH et synchronisation du dossier `kd-com` sans whitelist.  

---

## Dépannage
| Problème                        | Solution                        |
|---------------------------------|--------------------------------|
| Docker ne démarre pas            | `docker compose logs`           |
| Erreur de connexion à la BDD     | Vérifiez `.env` et `docker compose ps` |
| /etc/hosts non modifié           | `sudo nano /etc/hosts`          |
| Erreur de permissions            | `chmod +x scripts/*.sh`         |
| Port Docker déjà utilisé         | Changez le port dans `docker-compose.yml` |

---

## Sécurité & bonnes pratiques
- Ne versionnez jamais les fichiers contenant des secrets ou des accès : `.env`, `wp-config.php`, clés privées, etc.  
- Vérifiez que ces fichiers sont bien exclus via `.gitignore`.  
- Utilisez les secrets GitHub pour la CI/CD (jamais de mot de passe ou clé dans le code ou les logs).  
- Changez régulièrement les mots de passe et clés sensibles.  
- Documentez le code, modules et blocs personnalisés.  
- Mettez à jour régulièrement les dépendances (images Docker, plugins WP, etc.).  
- Vérifiez la sécurité des plugins et thèmes utilisés.  

---

## Traefik
- `traefik/traefik.yml` : Configuration du reverse proxy, dashboard et réseau Docker.  
- `traefik/dynamic_conf/middleware.yml` : Définit les middlewares, ex. redirection HTTP → HTTPS.  

Exemple de middleware :
\`\`\`yaml
http:
  middlewares:
    redirect-https:
      redirectScheme:
        scheme: https
        permanent: true
\`\`\`

---

## Duplication machine à machine
Transfert complet de WordPress vers une autre machine locale (Mac ou Windows).

**Étapes :**

1. **Générer une archive de transfert** depuis la machine source :  
\`\`\`bash
./scripts/duplicate-machine-to-machine.sh
\`\`\`
- Crée un dossier `wp-archive-YYYYMMDD_HHMMSS` contenant :  
  - la base de données (`wp-db-dump.sql`)  
  - le dossier `wp-content`  

2. **Transférer l’archive** sur la nouvelle machine.  

3. **Importer l’archive** sur la nouvelle machine :  
\`\`\`bash
./scripts/import-machine.sh
\`\`\`
- Importe la base de données dans le conteneur MySQL  
- Copie le contenu de `wp-content` dans le projet local  

---

## Documentation du thème kd-com
- [README du thème kd-com](./theme.md)  
- [Wiki complet du thème kd-com](./wiki/presentation.md)  
  - Modules, blocks, SASS, JS, assets, includes, développement personnalisé, etc.  

---
EOF

echo "README.md généré avec succès à la racine du projet."
