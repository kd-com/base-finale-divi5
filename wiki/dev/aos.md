## Ajouter des effets AOS sur Divi avec le Visual Builder

1. Ouvrez la page dans le Visual Builder Divi.
2. Sélectionnez le module à animer (texte, image, bouton, etc.).
3. Allez dans l’onglet "Avancé" du module.
4. Dans la section "Classes CSS", ajoutez une ou plusieurs classes selon l’effet désiré :
  - Pour l’effet : `aos-fade-up`, `aos-zoom-in`, `aos-slide-left`, etc. (voir liste des effets plus haut)
  - Pour le délai : `aos-delay-200` (pour 200ms), `aos-delay-500`, etc.
5. Enregistrez le module.
6. Le script `js/aos-divi.js` détecte automatiquement ces classes et ajoute les attributs `data-aos` et `data-aos-delay` sur le module Divi correspondant.
7. Actualisez la page pour voir l’animation sur le module Divi.

Exemple : pour un module avec un effet "fade-up" et un délai de 300ms, ajoutez dans "Classes CSS" :
```
aos-fade-up aos-delay-300
```

Le script se charge de convertir ces classes en attributs AOS, inutile d’ajouter les attributs manuellement.

Vous pouvez répéter l’opération avec d’autres effets et délais en changeant les classes CSS.
# Fonctionnement d'AOS (Animate On Scroll) sur Gutenberg et Divi

AOS (Animate On Scroll) permet d’ajouter des animations lors du défilement de la page. Le thème kd-com intègre AOS pour les blocks Gutenberg et les modules Divi.

---

## 1. AOS sur Gutenberg

### Objectif
Ajouter des animations d’apparition sur les blocks lors du scroll pour dynamiser l’interface.

### Mise en place
- Les blocks personnalisés intègrent les attributs `data-aos`, `data-aos-delay`, `data-aos-duration` dans leur template PHP ou HTML.
- Exemple dans un block :
```php
<div class="block" data-aos="fade-up" data-aos-delay="100" data-aos-duration="600">
  ...contenu du block...
</div>
```
- Les options d’animation (type, délai, durée) sont configurables via des champs ACF ou des options globales du thème.
- Le script `js/aos-frontend.js` initialise AOS sur toutes les pages utilisant Gutenberg.

### Effets disponibles
Voici les principaux effets AOS utilisables :

| Effet         | data-aos="..."      | Description                          |
|---------------|---------------------|--------------------------------------|
| Fade          | fade                | Apparition progressive               |
| Fade-up       | fade-up             | Apparition en montant                |
| Fade-down     | fade-down           | Apparition en descendant             |
| Fade-left     | fade-left           | Apparition vers la gauche            |
| Fade-right    | fade-right          | Apparition vers la droite            |
| Flip-up       | flip-up             | Flip vertical vers le haut           |
| Flip-down     | flip-down           | Flip vertical vers le bas            |
| Flip-left     | flip-left           | Flip horizontal vers la gauche       |
| Flip-right    | flip-right          | Flip horizontal vers la droite       |
| Slide-up      | slide-up            | Glissement vers le haut              |
| Slide-down    | slide-down          | Glissement vers le bas               |
| Slide-left    | slide-left          | Glissement vers la gauche            |
| Slide-right   | slide-right         | Glissement vers la droite            |
| Zoom-in       | zoom-in             | Zoom avant                           |
| Zoom-in-up    | zoom-in-up          | Zoom avant en montant                |
| Zoom-in-down  | zoom-in-down        | Zoom avant en descendant             |
| Zoom-in-left  | zoom-in-left        | Zoom avant vers la gauche            |
| Zoom-in-right | zoom-in-right       | Zoom avant vers la droite            |
| Zoom-out      | zoom-out            | Zoom arrière                         |
| Zoom-out-up   | zoom-out-up         | Zoom arrière en montant              |
| Zoom-out-down | zoom-out-down       | Zoom arrière en descendant           |
| Zoom-out-left | zoom-out-left       | Zoom arrière vers la gauche          |
| Zoom-out-right| zoom-out-right      | Zoom arrière vers la droite          |

Retrouvez la liste complète et les démos sur [AOS documentation](https://michalsnik.github.io/aos/).

### Durée et délai
- `data-aos-delay` : délai avant le début de l’animation (en ms, ex : 100, 300, 500)
- `data-aos-duration` : durée de l’animation (en ms, ex : 400, 600, 1000)
- Exemple :
```html
<div data-aos="fade-up" data-aos-delay="200" data-aos-duration="800">...</div>
```

### Personnalisation
- Modifiez les valeurs des attributs pour adapter le type, le délai et la durée à chaque block.
- Utilisez des champs ACF pour permettre à l’admin de choisir l’effet et la durée directement dans l’interface.

### Exemple complet
```php
<div class="block" data-aos="zoom-in-right" data-aos-delay="300" data-aos-duration="1200">
  <h2>Mon block animé</h2>
</div>
```

---

## 2. AOS sur Divi

### Objectif
Appliquer des animations AOS sur les modules Divi pour enrichir l’expérience visuelle.

### Mise en place
- Le script `js/aos-divi.js` détecte les modules Divi et ajoute dynamiquement les attributs `data-aos`, `data-aos-delay`, `data-aos-duration`.
- Les modules Divi peuvent être configurés pour inclure des classes ou des attributs spécifiques via l’interface Divi ou des hooks PHP.
- Exemple d’intégration :
```html
<div class="et_pb_module" data-aos="slide-left" data-aos-delay="200" data-aos-duration="1000">
  ...contenu du module Divi...
</div>
```

### Effets disponibles
Tous les effets AOS listés ci-dessus sont utilisables sur les modules Divi.

### Durée et délai
- `data-aos-delay` : délai avant le début de l’animation (en ms)
- `data-aos-duration` : durée de l’animation (en ms)

### Personnalisation
- Utilisez les paramètres Divi ou ajoutez des classes personnalisées pour cibler les modules à animer.
- Adaptez le type et le délai d’animation selon le contexte visuel et la hiérarchie des modules.

### Exemple complet
```html
<div class="et_pb_module" data-aos="flip-up" data-aos-delay="400" data-aos-duration="900">
  <p>Module Divi animé</p>
</div>
```

---

## Bonnes pratiques
- N’abusez pas des animations pour préserver la lisibilité et la performance.
- Testez le rendu sur mobile et desktop.
- Centralisez la configuration des animations dans les options du thème ou via ACF pour faciliter la maintenance.
- Privilégiez les effets subtils et cohérents avec l’identité visuelle du site.
