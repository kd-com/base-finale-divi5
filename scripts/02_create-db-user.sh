#!/bin/bash
# ---------------------------------------------------------
# 🎯 Script : create-db-user.sh
# Objectif : Créer automatiquement la base + utilisateur pour shared-db
# ---------------------------------------------------------

set -e 

# Charger les variables du .env
if [ ! -f .env ]; then
  echo "❌ Fichier .env introuvable !"
  exit 1
fi

echo "🔍 Chargement des variables..."
set -o allexport
source .env
set +o allexport

# Vérifier DB_MODE
if [ "$DB_MODE" != "external" ]; then
  echo "⚠️ DB_MODE n'est pas 'external'."
  echo "Ce script ne doit être utilisé que si tu utilises une base MySQL partagée (shared-db)."
  exit 1
fi

# Vérifier existence du conteneur
if ! docker ps --format '{{.Names}}' | grep -q "^shared-db$"; then
  echo "❌ Le conteneur MySQL 'shared-db' est introuvable."
  echo "Assure-toi qu'il tourne via : docker ps"
  exit 1
fi

echo "🗄️ Connexion à MySQL..."
MYSQL_CMD="docker exec -i shared-db mysql -u root -prootpassword"

echo "📦 Vérification / création de la base : $DB_NAME"
$MYSQL_CMD <<EOF
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
EOF

# Création de l'utilisateur pour les deux hosts les plus courants :
#  - '%'         → connexions depuis un autre container (ex: WordPress -> shared-db)
#  - 'localhost' → connexions directes depuis un script lancé sur la machine hôte
# Sans l'entrée 'localhost', un script de migration/restauration qui se
# connecte en spécifiant -h localhost échoue avec "Access denied" même si
# le mot de passe est correct, car MySQL traite 'user'@'%' et
# 'user'@'localhost' comme deux comptes distincts (vécu sur equitasun).
echo "👤 Vérification / création de l'utilisateur : $DB_USER (hosts % et localhost)"
$MYSQL_CMD <<EOF
CREATE USER IF NOT EXISTS '$DB_USER'@'%' IDENTIFIED BY '$DB_PASSWORD';
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
EOF

echo "🔐 Attribution des privilèges..."
$MYSQL_CMD <<EOF
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'%';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

echo ""
echo "🎉 Base et utilisateur MySQL correctement configurés !"
echo "📌 Base      : $DB_NAME"
echo "📌 Utilisateur : $DB_USER (hosts % et localhost)"
echo "📌 Mot de passe : (défini via .env)"
echo ""
echo "Tu peux maintenant relancer :"
echo "👉 ./scripts/03_init-wp.sh"