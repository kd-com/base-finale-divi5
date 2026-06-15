# Organisation et rôle des fichiers du dossier assets

Le dossier `assets/` contient les ressources diverses utilisées par le thème kd-com (icônes, polices, fichiers JSON, etc.).

## Structure courante
`icons_custom.json` : configuration des icônes personnalisées

## Gestion des boutons
`buttons/` : gestion des styles et presets de boutons
	- `button-styles.php` : enregistre des styles personnalisés pour les boutons Gutenberg (ex : Shine, Slide Right, Circle Expand, Slide Up)
	- `divi-button-presets.php` : ajoute des presets animés pour les boutons Divi, utilisables dans le Visual Builder

### Apparence en front
Les boutons personnalisés bénéficient de styles avancés :
- Effets de brillance, remplissage animé, expansion circulaire, etc.
- Animations CSS et transitions pour un rendu moderne
- Compatibles Gutenberg et Divi
Exemple visuel :
```
<button class="wp-block-button__link btn-shine">Brillance</button>
<button class="wp-block-button__link btn-slide-right">Remplissage droite</button>
```
Les presets Divi sont accessibles dans le Visual Builder et proposent des couleurs et effets dynamiques.

## Bonnes pratiques
- Rangez chaque type de ressource dans un sous-dossier dédié si besoin.
- Documentez le format et l’utilisation de chaque fichier.
