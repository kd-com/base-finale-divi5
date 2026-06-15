

## Thème kd-com

### ⚠️ Avertissements & Prérequis
- Ce thème nécessite l'installation préalable des plugins suivants :
	- **Advanced Custom Fields Pro (ACF Pro)**
	- **Divi** (thème parent ou builder)
- Veillez à ce que ces extensions soient activées avant d'activer kd-com, sinon certaines fonctionnalités (blocks, modules, champs personnalisés) ne fonctionneront pas.


Ce thème WordPress est conçu pour une personnalisation avancée et une gestion modulaire via Gutenberg, Divi, ACF, SASS et de nombreux modules personnalisés.

### Fonctionnalités principales
- Child theme WordPress optimisé pour le développement
- Blocks Gutenberg et modules Divi personnalisés
- Gestion avancée des styles avec SASS
- Intégration ACF pour la création de blocks et de champs sur mesure
- Organisation claire des fichiers (modules, blocks, includes, assets, js, sass...)

### Compilation SASS
Pour générer les fichiers CSS du thème :
- Fichier principal :
```sh
sass --watch wp-content/themes/kd-com/sass/style.scss wp-content/themes/kd-com/style.css
```
- Fichier blocks Gutenberg :
```sh
sass --watch wp-content/themes/kd-com/sass/blocks/blocks.scss wp-content/themes/kd-com/css/blocks.css
```
- Fichier newsletter
```sh
sass --watch --no-source-map wp-content/themes/kd-com/sass/modules/newsletter.scss wp-content/themes/kd-com/newsletter/style.css
```
 

### Documentation complète
Retrouvez toute la documentation détaillée du thème dans le dossier `wiki/` :
- [Wiki du thème kd-com](./wiki/README.md)
	- Modules, blocks, SASS, JS, assets, includes, développement personnalisé, etc.

### Démarrage rapide
1. Installez le thème dans votre installation WordPress
2. Compilez les fichiers SASS pour générer le CSS
3. Consultez le wiki pour la prise en main, la personnalisation et le développement

---
**Note :** Si ACF Pro ou Divi ne sont pas installés/activés, le thème ne fonctionnera pas correctement.

---
Pour toute question ou évolution, consultez le wiki ou contactez l’équipe de développement.