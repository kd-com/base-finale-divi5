# Documentation détaillée des boutons

Ce document présente chaque style de bouton disponible dans le thème kd-com, avec exemples d’utilisation pour Gutenberg et Divi.

---

## 1. Bouton Shine (Brillance)

### Description
Effet de brillance animé sur le bouton, idéal pour attirer l’attention.

### Utilisation avec Gutenberg
Dans l’éditeur, sélectionnez un bloc "Bouton" puis choisissez le style "Brillance (Shine)".

```html
<button class="wp-block-button__link btn-shine">Brillance</button>
```

### Utilisation avec Divi (développeur)
Ajoutez le preset correspondant dans le Visual Builder ou via shortcode :

```
[et_pb_button button_text="Cliquez ici" custom_button="on" button_text_color="#fff" button_bg_use_color_gradient="on" button_bg_color_gradient_start="#3db27c" button_bg_color_gradient_end="#e84448" button_border_radius="100px" box_shadow_style="preset6" ... css_class="btn-shine" ]
```
**Classe à renseigner :** `btn-shine`

---

## 2. Bouton Slide Right (Remplissage droite)

### Description
Remplissage animé du bouton de gauche à droite lors du survol.

### Utilisation avec Gutenberg
Sélectionnez le style "Remplissage Droite" dans le bloc bouton.

```html
<button class="wp-block-button__link btn-slide-right">Remplissage droite</button>
```

### Utilisation avec Divi
Utilisez le preset Divi correspondant ou adaptez le CSS :

```
[et_pb_button button_text="Remplissage droite" custom_button="on" ... css_class="btn-slide-right" ]
```
**Classe à renseigner :** `btn-slide-right`

---

## 3. Bouton Circle Expand (Cercle expansif)

### Description
Effet d’expansion circulaire au survol du bouton.

### Utilisation avec Gutenberg
Sélectionnez le style "Cercle Expansif".

```html
<button class="wp-block-button__link btn-circle-expand">Cercle expansif</button>
```

### Utilisation avec Divi
Preset Divi ou adaptation CSS :

```
[et_pb_button button_text="Cercle expansif" custom_button="on" ... css_class="btn-circle-expand" ]
```
**Classe à renseigner :** `btn-circle-expand`

---

## 4. Bouton Slide Up (Remplissage haut)

### Description
Remplissage animé du bouton du bas vers le haut.

### Utilisation avec Gutenberg
Sélectionnez le style "Remplissage haut".

```html
<button class="wp-block-button__link btn-slide-up">Remplissage haut</button>
```

### Utilisation avec Divi
Preset Divi ou adaptation CSS :

```
[et_pb_button button_text="Remplissage haut" custom_button="on" ... css_class="btn-slide-up" ]
```
**Classe à renseigner :** `btn-slide-up`

---

## Astuces et recommandations
- Les styles sont compatibles avec l’éditeur Gutenberg et le Visual Builder Divi.
- Pour personnaliser les couleurs, modifiez les variables CSS ou les options du preset Divi.
- Les animations sont gérées par le CSS du thème (voir `sass/components/buttons_animations.scss`).
- Pour ajouter un nouveau style, créez-le dans `button-styles.php` et ajoutez le CSS correspondant.

---

Pour toute question ou ajout de style, contactez l’équipe de développement kd-com.
