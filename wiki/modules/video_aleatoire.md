# Module Vidéo aléatoire (admin)

## Fonctionnement en admin
- Utilisation via le shortcode `[video_aleatoire]` avec différents paramètres :
  - `type` : 'cpt', 'posts', 'subpages'
  - `post_type` : nom du CPT (si type='cpt')
  - `category` : slug de la catégorie (si type='cpt' ou 'posts')
  - `parent_page` : ID de la page parente (si type='subpages')
- Le module récupère les vidéos depuis les blocs Gutenberg (core/video) ou les URLs YouTube dans le contenu des posts/pages.

### Ajouter ou modifier une vidéo pour le module
1. Selon le paramètre du shortcode, identifiez le post, la page ou le CPT concerné.
2. Éditez le contenu et ajoutez un block "Vidéo" ou collez une URL YouTube dans le contenu.
3. Enregistrez ou mettez à jour le contenu.
4. La vidéo sera automatiquement prise en compte par le module.

### Organisation et affichage
- Le module sélectionne une vidéo aléatoire parmi celles trouvées dans le contenu des posts/pages/CPT concernés.
- Activation/désactivation dans "Réglages kd-com > Modules du site".

## Conseils
- Ajoutez des vidéos dans le contenu des posts/pages pour enrichir le module.
- Testez les différents paramètres du shortcode pour varier l’affichage.
