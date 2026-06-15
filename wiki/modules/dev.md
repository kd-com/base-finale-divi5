
# Présentation développeur : créer un module kd-com

## Structure recommandée
- Placez vos modules dans `module_admin/` (pour l’admin) ou `module_front/` (pour le front).
- Un module est généralement un fichier PHP qui peut contenir un shortcode, un CPT, ou une logique personnalisée.
- Pour les modules complexes, créez un dossier dédié avec plusieurs fichiers (template, logique, assets).

## Convention de nommage
- Le nom du fichier doit suivre le format `[categorie]_[nom_module].php` (ex : `slider_carrousel_page.php`, `affichage_page_specifique.php`).
- Le nom du champ principal ACF doit être identique au nom du fichier (sans `.php`).
- L’option WordPress associée est `module_[slug]` (ex : `module_slider_carrousel_page`).

## Gestion dynamique des modules
- Le thème détecte automatiquement tous les modules présents dans `acf_fields`.
- Si l’option `module_[slug]` est activée, les fichiers correspondants sont inclus automatiquement :
  - `module_admin/[slug].php`
  - `module_front/[slug].php`
- Plus besoin d’ajouter manuellement les conditions dans `functions.php`.

## Exemple : Module page spécifique
- Fichier : `module_admin/affichage_page_specifique.php`
- Option : `module_affichage_page_specifique`
- Pour activer le module, cochez la case dans l’admin (réglages modules).
- Le chargement et l’affichage sont alors automatiques.

## Étapes pour créer un module
1. **Créer le fichier** : Ajoutez un fichier PHP dans le dossier approprié.
2. **Déclarer le module** :
   - Pour un shortcode, utilisez `add_shortcode('nom_shortcode', 'ma_fonction')`.
   - Pour un CPT, utilisez `register_post_type()`.
3. **Ajouter la logique** :
   - Récupérez les données nécessaires (posts, pages, ACF...)
   - Affichez le contenu avec HTML/PHP.
4. **Intégrer dans le thème** :
   - Utilisez le shortcode dans une page ou un template.
   - Activez le module via l’admin, il sera chargé automatiquement.
5. **Documenter** :
   - Ajoutez une page dans ce dossier wiki pour expliquer le fonctionnement à l’utilisateur.

## Bonnes pratiques
- Utilisez des noms explicites pour les fichiers et shortcodes.
- Respectez la convention de nommage pour l’automatisation.
- Documentez chaque paramètre et option.
- Prévoyez des messages d’erreur clairs pour l’utilisateur.
- Testez le module en admin et en front.
