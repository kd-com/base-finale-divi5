# Module Slider contenu (admin)

## Fonctionnement en admin
- Module basé sur le Custom Post Type `slider_contenu`.
- Les slides sont gérées dans le menu "Slider contenu" de l’admin WordPress.

### Créer une nouvelle slide
1. Dans le menu WordPress, cliquez sur "Slider contenu".
2. Cliquez sur "Ajouter une slide".
3. Remplissez les champs :
   - **Titre** : le titre de la slide.
   - **Type** : choisissez le type de contenu (page, article, etc.).
   - **Contenu lié** : sélectionnez la page ou l’article à lier via le champ ACF.
   - **Image** : ajoutez une image pour la slide.
   - **Date** : renseignez la date si nécessaire.
4. Cliquez sur "Publier" pour enregistrer la slide.

### Modifier ou supprimer une slide
1. Dans la liste des slides, cliquez sur le titre de la slide à modifier.
2. Modifiez les champs souhaités puis cliquez sur "Mettre à jour".
3. Pour supprimer une slide, cliquez sur "Déplacer dans la corbeille".

### Organisation et affichage
- Les slides sont affichées dans l’ordre défini (menu_order). Vous pouvez réorganiser l’ordre via l’interface d’administration.
- Pour afficher le slider en front, utilisez le shortcode `[slider_contenu]` ou le template dédié.

## Conseils
- Ajoutez des images et titres personnalisés pour chaque slide.
- Utilisez le champ "contenu lié" pour pointer vers une page ou un article précis.
