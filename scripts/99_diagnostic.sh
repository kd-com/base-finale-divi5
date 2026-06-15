#!/bin/bash

echo "🔍 === DIAGNOSTIC WORDPRESS 404 ==="
echo ""

echo "1️⃣ Vérification des fichiers WordPress :"
docker compose exec wordpress ls -la /var/www/html/ | head -15

echo ""
echo "2️⃣ Vérification du .htaccess :"
docker compose exec wordpress cat /var/www/html/.htaccess 2>/dev/null || echo "❌ .htaccess inexistant"

echo ""
echo "3️⃣ Vérification mod_rewrite :"
docker compose exec wordpress apache2ctl -M | grep rewrite

echo ""
echo "4️⃣ Vérification de la config Apache :"
docker compose exec wordpress grep -A 5 "Directory /var/www" /etc/apache2/apache2.conf | grep AllowOverride

echo ""
echo "5️⃣ Vérification des URLs WordPress :"
docker compose exec wordpress wp option get home --allow-root
docker compose exec wordpress wp option get siteurl --allow-root

echo ""
echo "6️⃣ Vérification des permaliens :"
docker compose exec wordpress wp rewrite list --allow-root

echo ""
echo "7️⃣ Test de connexion à index.php :"
docker compose exec wordpress test -f /var/www/html/index.php && echo "✅ index.php existe" || echo "❌ index.php manquant"

echo ""
echo "8️⃣ Permissions :"
docker compose exec wordpress ls -la /var/www/html/index.php
docker compose exec wordpress ls -la /var/www/html/.htaccess 2>/dev/null || echo "⚠️  .htaccess n'existe pas"