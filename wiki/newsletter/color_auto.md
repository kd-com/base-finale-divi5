# Newsletter avec Couleurs Automatiques depuis SCSS

## 🎨 Fonctionnalité Principale

Cette version **lit automatiquement** les couleurs depuis votre fichier `_theme-colors.scss` et les applique aux newsletters. Plus besoin de modifier manuellement les couleurs !

## 🚀 Installation

### Étape 1 : Remplacer le fichier de styles

```bash
cp newsletter-styles-inline-auto.php wp-content/themes/votre-theme/newsletter/newsletter-styles-inline.php
```

### Étape 2 : Vérifier votre fichier SCSS

Le fichier `sass/_theme-colors.scss` doit contenir vos couleurs :

```scss
$couleur-titrage: #2a9d8e;
$couleur-texte: #3e464b;
$couleur-lien: #e9c468;
$couleur-fond: #264653;
$couleur-fond2: #424242;
$couleur-blanche: #ffffff;
```

### Étape 3 : C'est tout !

Les newsletters utiliseront automatiquement ces couleurs. ✨

## ✅ Avantages

### Avant (version manuelle)
```php
// Dans newsletter-styles-inline.php
$couleur_titrage = '#2a9d8e';  // ❌ À modifier manuellement
$couleur_lien = '#e9c468';     // ❌ À modifier manuellement
```

**Problème** : Si vous changez les couleurs dans votre thème, vous devez les changer aussi dans les newsletters.

### Maintenant (version auto)
```php
// Lit automatiquement depuis _theme-colors.scss
$theme_colors = brevo_parse_theme_colors();
$couleur_titrage = $theme_colors['couleur-titrage'];  // ✅ Toujours synchronisé
```

**Avantage** : Un seul endroit à modifier = cohérence totale !

## 🎯 Comment ça fonctionne

### 1. Parsing du fichier SCSS

La fonction `brevo_parse_theme_colors()` :
1. Lit le fichier `sass/_theme-colors.scss`
2. Parse les variables SCSS avec regex : `$nom-variable: #couleur;`
3. Retourne un tableau PHP avec toutes les couleurs
4. Met en cache le résultat pour la performance

### 2. Application automatique

```php
function brevo_get_newsletter_styles() {
    // Récupération automatique
    $theme_colors = brevo_parse_theme_colors();
    
    // Utilisation directe
    $couleur_titrage = $theme_colors['couleur-titrage'];
    
    return array(
        'post-title-a' => 'color: ' . $couleur_titrage . ';',
        // etc.
    );
}
```

## 🔧 Personnalisation avancée

### Ajouter des couleurs spécifiques aux événements

Dans votre `_theme-colors.scss`, ajoutez :

```scss
// Couleurs standards
$couleur-titrage: #2a9d8e;
$couleur-lien: #e9c468;

// Couleurs spécifiques newsletters (optionnel)
$event-multi-day: #f5576c;
$event-recurring: #667eea;
```

Le système les détectera automatiquement !

### Valeurs par défaut

Si une couleur n'est pas trouvée dans le SCSS, le système utilise des valeurs par défaut :

```php
$default_colors = array(
    'couleur-titrage' => '#2a9d8e',
    'couleur-texte' => '#3e464b',
    'couleur-lien' => '#e9c468',
    'couleur-fond' => '#264653',
    'couleur-fond2' => '#424242',
    'couleur-blanche' => '#ffffff',
    'couleur-noire' => '#000000',
    'divi-accent' => '#e9c468',
);
```

## 🐛 Debug / Vérification

### Afficher les couleurs détectées

Dans `newsletter-styles-inline-auto.php`, décommentez la dernière ligne :

```php
// Décommenter cette ligne pour debug
add_action('admin_notices', 'brevo_debug_theme_colors');
```

Vous verrez alors dans votre admin WordPress un encadré avec toutes les couleurs détectées :

```
Couleurs détectées depuis _theme-colors.scss :

🟦 $couleur-titrage: #2a9d8e
🟫 $couleur-texte: #3e464b
🟨 $couleur-lien: #e9c468
...
```

### Tester une newsletter

1. Activez le debug ci-dessus
2. Allez dans l'admin WordPress
3. Vérifiez que les couleurs affichées sont correctes
4. Envoyez une newsletter de test
5. Vérifiez le rendu dans Gmail/Outlook

## 📋 Variables SCSS supportées

Le parser détecte automatiquement :

### Format standard
```scss
$nom-variable: #123456;
$autre-couleur: #fff;
```

### Format RGB/RGBA
```scss
$couleur-custom: rgb(255, 0, 0);
$couleur-transparente: rgba(0, 0, 0, 0.5);
```

### Format court HEX
```scss
$couleur-courte: #fff;  // Détecté comme #ffffff
```

## 🔄 Synchronisation automatique

### Workflow complet

1. **Designer change les couleurs dans le thème**
   ```scss
   // _theme-colors.scss
   $couleur-lien: #ff5733;  // Nouvelle couleur
   ```

2. **Les newsletters s'adaptent automatiquement**
   - Aucune modification de code nécessaire
   - Les prochaines newsletters auront la nouvelle couleur
   - Cohérence garantie avec le site

### Cas d'usage

**Scénario** : Votre client veut changer sa charte graphique

**Sans auto** :
1. Modifier `_theme-colors.scss` ✏️
2. Recompiler les CSS du site 🔄
3. Modifier `newsletter-styles-inline.php` ✏️ (on oublie souvent !)
4. Tester les newsletters 📧

**Avec auto** :
1. Modifier `_theme-colors.scss` ✏️
2. Recompiler les CSS du site 🔄
3. ✅ C'est tout !

## 💡 Compatibilité

### Chemins supportés

Le système cherche automatiquement :
```php
get_template_directory() . '/sass/_theme-colors.scss'
```

Si vous avez un thème enfant, vous pouvez adapter :
```php
// Dans newsletter-styles-inline-auto.php, ligne ~30
$scss_file = get_stylesheet_directory() . '/sass/_theme-colors.scss';
```

### Formats SCSS supportés

✅ Variables simples : `$couleur: #123456;`
✅ Variables avec tirets : `$couleur-primaire: #123456;`
✅ HEX court : `#fff`
✅ HEX long : `#ffffff`
✅ RGB : `rgb(255, 0, 0)`
✅ RGBA : `rgba(0, 0, 0, 0.5)`

❌ Variables SCSS référencées : `$couleur2: $couleur1;` (pas supporté)
❌ Fonctions SCSS : `lighten($couleur, 10%)` (pas supporté)

## 🎯 Migration depuis version manuelle

### Si vous avez déjà la version manuelle

1. **Sauvegardez** votre fichier actuel
   ```bash
   cp newsletter-styles-inline.php newsletter-styles-inline-backup.php
   ```

2. **Remplacez** par la version auto
   ```bash
   cp newsletter-styles-inline-auto.php newsletter-styles-inline.php
   ```

3. **Testez** avec le debug activé
   ```php
   add_action('admin_notices', 'brevo_debug_theme_colors');
   ```

4. **Vérifiez** que les couleurs sont identiques

5. **Envoyez** une newsletter de test

### Rollback si nécessaire

```bash
cp newsletter-styles-inline-backup.php newsletter-styles-inline.php
```

## 🚀 Utilisation sur nouveaux projets

### Temps de setup : ~2 minutes

1. Copier le fichier auto
2. Vérifier que `_theme-colors.scss` existe
3. Tester

Les couleurs sont **toujours synchronisées** automatiquement !

## 📚 Structure de fichiers recommandée

```
votre-theme/
├── sass/
│   └── _theme-colors.scss           ← Source unique de vérité
├── newsletter/
│   ├── newsletter-styles-inline.php ← Version auto (ce fichier)
│   ├── template-email.php
│   └── template-email-free.php
└── style.css
```

## ⚡ Performance

### Cache intégré

Les couleurs sont parsées **une seule fois** par requête grâce au cache statique :

```php
static $colors_cache = null;

if ($colors_cache !== null) {
    return $colors_cache;  // Retour instantané
}
```

**Impact** : Négligeable (~0.1ms pour parser le fichier SCSS)

## 🎓 Pour aller plus loin

### Variables SCSS avancées (optionnel)

Vous pouvez ajouter des variables spécifiques newsletters :

```scss
// _theme-colors.scss

// Couleurs standard du site
$couleur-titrage: #2a9d8e;
$couleur-lien: #e9c468;

// Couleurs spécifiques newsletters
$newsletter-header-bg: #264653;
$newsletter-footer-bg: #424242;
$event-multi-day: #f5576c;
$event-recurring: #667eea;
```

Puis dans `newsletter-styles-inline-auto.php` :

```php
// Utiliser les couleurs spécifiques si disponibles
$header_bg = isset($theme_colors['newsletter-header-bg']) 
    ? $theme_colors['newsletter-header-bg'] 
    : $theme_colors['couleur-fond'];
```

## ✅ Checklist finale

- [ ] `_theme-colors.scss` existe et contient les couleurs
- [ ] `newsletter-styles-inline-auto.php` copié dans `/newsletter/`
- [ ] Debug activé et couleurs vérifiées
- [ ] Newsletter de test envoyée et vérifiée
- [ ] Rendu validé sur Gmail, Outlook, Apple Mail
- [ ] Debug désactivé pour la production

## 🎉 Résultat

Vous avez maintenant des newsletters qui :
- ✅ Se synchronisent automatiquement avec votre thème
- ✅ Ne nécessitent aucune maintenance des couleurs
- ✅ Restent cohérentes avec votre charte graphique
- ✅ Sont faciles à déployer sur d'autres projets