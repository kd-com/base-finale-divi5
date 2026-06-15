# Organisation et rôle des fichiers du dossier includes

Le dossier `includes/` contient des fichiers PHP qui étendent ou personnalisent le fonctionnement du thème kd-com.

## Structure courante
- `admin_editor.php` : personnalisation de l’éditeur WordPress en admin
- `dashboard_block.php` : ajout de widgets ou blocks au tableau de bord
- `no-results.php` : gestion de l’affichage en cas d’absence de résultats
- `reglages_site/` : fichiers pour la gestion des réglages du site (options, modules, etc.)

## Bonnes pratiques
- Regroupez les fonctions par thématique dans des fichiers dédiés.
- Documentez chaque fichier avec un commentaire en tête.
- Incluez les fichiers nécessaires dans `functions.php` ou via un autoloader.
