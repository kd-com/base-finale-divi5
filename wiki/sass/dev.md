# Créer et modifier un composant SASS

## Étapes pour créer un composant ou module SASS
1. Créez un fichier dans le dossier approprié (`blocks/`, `components/`, `modules/`).
2. Ajoutez un commentaire en tête pour décrire le composant/module.
3. Utilisez les variables du thème pour les couleurs et espacements.
4. Importez le fichier dans `style.scss` ou le fichier principal du dossier.
5. Compilez le SASS pour générer le CSS.

## Exemple de structure
```scss
// sass/components/_bouton.scss
// Composant bouton principal
.btn {
  background: $primary-color;
  padding: 1rem 2rem;
  border-radius: 4px;
}
```

## Bonnes pratiques
- Privilégiez la modularité et la réutilisation.
- Documentez les variables et mixins utilisées.
