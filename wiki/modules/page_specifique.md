# Module : Page spécifique

Ce module permet d’afficher dynamiquement le contenu d’une page WordPress à l’aide d’un shortcode.

## Fonctionnalités
- Affichage d’une page précise via son ID
- Utilisation simple dans l’éditeur ou dans un template
- Activation/désactivation via l’admin (réglages modules)
- Personnalisation du style via SASS

## Activation
- Rendez-vous dans l’admin, section Réglages > Modules
- Cochez la case « Activer le module page spécifique »

## Utilisation du shortcode
```
[page_specifique id="123"]
```
- Remplacez `123` par l’ID de la page à afficher
- Le contenu de la page sera inséré à l’endroit du shortcode

## Paramètres disponibles
- `id` : (obligatoire) ID de la page à afficher

## Fichiers liés
- PHP : `module_admin/affichage_page_specifique.php`
- SASS : `sass/modules/_affichage_page_specifique.scss`

## Bonnes pratiques
- Vérifiez que la page existe et que l’ID est correct
- Personnalisez le style via le fichier SASS dédié
- Utilisez le shortcode dans n’importe quel contenu WordPress

## Exemple d’intégration dans un template PHP
```php
if (shortcode_exists('page_specifique')) {
    echo do_shortcode('[page_specifique id="123"]');
}
```

## Gestion dynamique
- Le module est chargé automatiquement si l’option est activée
- Respectez la convention de nommage pour l’automatisation

---
Pour toute personnalisation avancée, modifiez le fichier PHP du module ou le SASS associé.