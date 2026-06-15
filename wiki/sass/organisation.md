# Organisation des fichiers SASS

## Structure du dossier SASS
- `sass/blocks/` : styles des blocks Gutenberg
- `sass/components/` : composants réutilisables (boutons, cards, etc.)
- `sass/core/` : styles de base, variables, mixins
- `sass/modules/` : styles des modules (carrousel, sliders, etc.)
- `_theme-colors.scss` : variables de couleurs globales
- `style.scss` : point d’entrée principal pour la compilation


## Compilation
Pour compiler les styles SASS en CSS, utilisez les commandes suivantes :

### Compilation du fichier principal
```sh
sass --watch wp-content/themes/kd-com/sass/style.scss wp-content/themes/kd-com/css/style.css
```

### Compilation des blocks
```sh
sass --watch wp-content/themes/kd-com/sass/blocks/blocks.scss wp-content/themes/kd-com/css/blocks.css
```

## Bonnes pratiques
- Organisez chaque composant ou module dans un fichier dédié.
- Utilisez les variables et mixins pour la cohérence des styles.
- Documentez chaque fichier SASS avec un commentaire en tête.
