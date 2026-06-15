# Utilisation des CPT (Custom Post Types) dans kd-com

Ce guide explique comment gérer chaque type de contenu personnalisé (CPT) du thème kd-com en tant qu’utilisateur.

---

## 1. Slider contenu (`slider_contenu`)
- Accessible dans le menu "Slider contenu" de l’admin WordPress.
- Pour ajouter une slide : cliquez sur "Ajouter une slide".
- Remplissez les champs : titre, type, contenu lié (page ou article), image, date.
- Les slides sont affichées dans l’ordre défini (menu_order).
- Pour modifier une slide, cliquez sur son titre dans la liste et éditez les champs.
- Supprimez une slide en la déplaçant dans la corbeille.

## 2. Slider accueil (`slider`)
- Accessible dans le menu "Slider page d’accueil".
- Pour ajouter une slide : cliquez sur "Ajouter une slide".
- Remplissez les champs : titre, image, vidéo (iframe YouTube si disponible).
- Les slides sont affichées dans l’ordre défini.
- Modifiez ou supprimez une slide comme pour tout contenu WordPress.

## 3. Réalisation (`project` renommé en `réalisation`)
- Accessible dans le menu "Réalisation".
- Pour ajouter une réalisation : cliquez sur "Ajouter une réalisation".
- Remplissez les champs : titre, description, image, catégories (type-de-realisation), tags (filtre-de-realisation).
- Les réalisations sont affichées dans l’ordre défini.
- Modifiez ou supprimez une réalisation comme pour tout contenu WordPress.


## Module : Page spécifique

Ce module permet d’afficher dynamiquement une page spécifique à l’aide d’un shortcode.

**Activation** :
- Rendez-vous dans l’admin, section Réglages > Modules.
- Cochez la case « Activer le module page spécifique ».

**Utilisation du shortcode** :

```
[page_specifique id="123"]
```
- Remplacez `123` par l’ID de la page à afficher.
- Le contenu de la page sera inséré à l’endroit du shortcode.

**Fichiers liés** :
- PHP : `module_admin/affichage_page_specifique.php`
- SASS : `sass/modules/_affichage_page_specifique.scss`

**Bonnes pratiques** :
- Vérifiez que la page existe et que l’ID est correct.
- Personnalisez le style via le fichier SASS dédié.

---
