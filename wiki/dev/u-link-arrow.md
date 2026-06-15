# Fonctionnement du composant personnalisé `u-link-arrow`

Ce composant permet d’ajouter un effet de flèche animée sur les liens du site kd-com.

## Objectif
- Améliorer l’expérience utilisateur en ajoutant une animation visuelle sur les liens importants.
- Utilisé pour les boutons, liens d’action, call-to-action, etc.

## Structure du composant
- **HTML** :
```html
<a href="#" class="u-link-arrow">Texte du lien</a>
```
- **SASS** :
Le style est généralement défini dans `sass/components/_u-link-arrow.scss`.
- **JS** :
Si une animation JavaScript est nécessaire, elle peut être ajoutée dans `js/u-link-arrow.js`.

## Fonctionnement
- Le composant ajoute une flèche à droite du texte du lien.
- Au survol, la flèche s’anime (translation, rotation, couleur, etc.).
- Les styles sont personnalisables via les variables SASS du thème.

## Exemple d’utilisation
```html
<a href="/contact" class="u-link-arrow">Contactez-nous</a>
```

## Bonnes pratiques
- Utilisez ce composant pour les liens d’action ou de navigation principale.
- Vérifiez la cohérence des styles avec le reste du site.
- Documentez toute modification ou extension du composant dans ce dossier.
