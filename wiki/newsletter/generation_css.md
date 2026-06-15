# Newsletter Templates - Version INLINE STYLES (Compatible Email)

## ⚠️ IMPORTANT : Pourquoi cette version ?

Les emails **ne peuvent PAS charger de CSS externe**. Les clients email (Gmail, Outlook, Yahoo, etc.) bloquent :
- Les fichiers CSS externes (`<link rel="stylesheet">`)
- Les balises `<style>` dans le `<head>`
- Les imports CSS

**Solution** : Tous les styles doivent être **inline** dans les attributs `style=""` de chaque élément HTML.

## 🎯 Cette version vs la version précédente

### ❌ Version précédente (NE FONCTIONNE PAS pour les emails)
```php
<div class="post-title">
    <a href="..." class="read-more">Lire la suite</a>
</div>
```
→ Nécessite un fichier CSS externe → **Bloqué par les clients email**

### ✅ Cette version (FONCTIONNE pour les emails)
```php
<div style="font-size: 18px; font-weight: bold; margin-bottom: 10px;">
    <a href="..." style="display: inline-block; padding: 8px 16px; background-color: #e9c468;">Lire la suite</a>
</div>
```
→ Styles inline → **Compatible tous clients email**

## 📁 Fichiers fournis

1. **newsletter-styles-inline.php** - Définition centralisée des styles en PHP
2. **template-email-inline.php** - Template automatique avec styles inline
3. **template-email-free-inline.php** - Template personnalisé avec styles inline

## 🚀 Installation

### Étape 1 : Copier les fichiers

```bash
cp newsletter-styles-inline.php wp-content/themes/votre-theme/newsletter/
cp template-email-inline.php wp-content/themes/votre-theme/newsletter/template-email.php
cp template-email-free-inline.php wp-content/themes/votre-theme/newsletter/template-email-free.php
```

### Étape 2 : Personnaliser les couleurs

Éditez `newsletter-styles-inline.php` et modifiez les variables en haut de la fonction `brevo_get_newsletter_styles()` :

```php
function brevo_get_newsletter_styles() {
    // 👇 PERSONNALISEZ VOS COULEURS ICI
    $couleur_blanche = '#ffffff';
    $couleur_fond = '#264653';        // Header background
    $couleur_fond2 = '#424242';       // Footer background
    $couleur_titrage = '#2a9d8e';     // Titres et liens principaux
    $couleur_lien = '#e9c468';        // Boutons et liens secondaires
    $couleur_texte = '#3e464b';       // Texte principal
    
    $event_multi_day_color = '#f5576c';
    $event_recurring_color = '#667eea';
    
    // ... reste du code
}
```

### Étape 3 : Tester

Envoyez une newsletter de test et vérifiez le rendu dans :
- Gmail (web + mobile)
- Outlook
- Apple Mail
- Yahoo Mail

## 🎨 Comment ça fonctionne ?

### Le système de styles centralisés

**1. Définition des styles** (dans `newsletter-styles-inline.php`)

```php
return array(
    'post-title' => 'font-size: 18px; font-weight: bold; margin-bottom: 10px;',
    'read-more' => 'display: inline-block; padding: 8px 16px; background-color: #e9c468;',
    // etc.
);
```

**2. Utilisation dans les templates**

```php
<div<?php echo style('post-title'); ?>>
    <a href="..."<?php echo style('read-more'); ?>>Lire la suite</a>
</div>
```

**3. Rendu HTML final**

```html
<div style="font-size: 18px; font-weight: bold; margin-bottom: 10px;">
    <a href="..." style="display: inline-block; padding: 8px 16px; background-color: #e9c468;">Lire la suite</a>
</div>
```

### Fonction helper `style()`

```php
function style($class_name) {
    $inline = get_inline_style($class_name);
    return $inline ? ' style="' . esc_attr($inline) . '"' : '';
}
```

Cette fonction :
1. Récupère le style correspondant à la "classe"
2. Le formate en attribut `style="..."`
3. Échappe les caractères spéciaux pour la sécurité

## 🔧 Personnalisation avancée

### Ajouter un nouveau style

**1. Dans `newsletter-styles-inline.php`**, ajoutez une entrée :

```php
return array(
    // ... styles existants
    'mon-nouveau-style' => 'color: #ff0000; font-size: 16px; font-weight: bold;',
);
```

**2. Dans votre template**, utilisez-le :

```php
<div<?php echo style('mon-nouveau-style'); ?>>
    Mon contenu personnalisé
</div>
```

### Variantes conditionnelles

Vous pouvez créer des variantes selon le contexte :

```php
// Dans le template
$style_class = $is_special ? 'button-special' : 'button-normal';
<a href="..."<?php echo style($style_class); ?>>Cliquez ici</a>
```

```php
// Dans newsletter-styles-inline.php
'button-normal' => 'background-color: #e9c468; padding: 8px 16px;',
'button-special' => 'background-color: #ff0000; padding: 12px 24px; font-weight: bold;',
```

## 📋 Liste des styles disponibles

### Structure
- `body`
- `email-wrapper`
- `email-wrapper-td`
- `email-container`

### Navigation
- `browser-preview-link`
- `browser-preview-span`
- `browser-preview-link-a`

### Headers
- `newsletter-header`
- `newsletter-header-h1`
- `newsletter-header-blanc`
- `newsletter-header-blanc-img`

### Contenu
- `newsletter-content`
- `section-title`
- `section-title-multi-day`
- `section-title-recurring`

### Posts/Événements
- `post-item`
- `post-meta`
- `event-meta`
- `post-title`
- `post-title-a`
- `post-title-a-multi-day`
- `post-title-a-recurring`
- `post-image`
- `post-image-img`
- `post-excerpt`

### Badges
- `event-type-badge-multi-day`
- `event-type-badge-recurring`

### Boutons
- `button-wrapper`
- `button-wrapper-multi-day`
- `button-wrapper-recurring`
- `read-more`
- `read-more-multi-day`
- `read-more-recurring`

### Blocs personnalisés
- `custom-text-block`
- `custom-image-block`
- `custom-image-block-img`
- `custom-button-block-left`
- `custom-button-block-center`
- `custom-button-block-right`
- `custom-button-block-a`

### Footer
- `newsletter-footer`
- `newsletter-footer-p`
- `newsletter-footer-a`

### Utilitaires
- `no-content`

## 🐛 Troubleshooting

### Les styles ne s'appliquent toujours pas

**Vérifiez que :**
1. Le fichier `newsletter-styles-inline.php` est bien chargé
2. La fonction `style()` est appelée avec `<?php echo style('...'); ?>`
3. Les noms de styles correspondent exactement (sensible à la casse)

**Debug :**
```php
// Affichez le style généré pour vérifier
<?php 
$generated_style = style('post-title');
echo '<!-- DEBUG: ' . esc_html($generated_style) . ' -->';
?>
```

### Problème avec Outlook

Outlook utilise Word pour le rendu HTML. Quelques limitations :
- Pas de `border-radius` en version < 2016
- Pas de `box-shadow`
- Pas de `transform`

**Solution** : Les styles fournis sont déjà compatibles Outlook.

### Images ne s'affichent pas

**Vérifiez :**
1. Les URLs sont absolues (`https://...`)
2. Les images sont accessibles publiquement
3. Pas de redirection HTTPS sur les images

```php
// Bon ✅
$image_url = 'https://example.com/image.jpg';

// Mauvais ❌
$image_url = '/wp-content/uploads/image.jpg';
```

## ✅ Compatibilité testée

| Client Email | Support | Notes |
|--------------|---------|-------|
| Gmail Web | ✅ | Full support |
| Gmail Mobile | ✅ | Full support |
| Outlook 2010-2021 | ✅ | Sans border-radius sur boutons |
| Outlook Office 365 | ✅ | Full support |
| Apple Mail | ✅ | Full support |
| Yahoo Mail | ✅ | Full support |
| Thunderbird | ✅ | Full support |

## 🚀 Réutilisation sur d'autres projets

### Temps de setup : ~10 minutes

1. Copier `newsletter-styles-inline.php`
2. Modifier les 6 variables de couleurs
3. Copier les templates
4. Tester

C'est tout ! Les styles s'adaptent automatiquement à vos couleurs.

## 📚 Ressources

- [Email Client CSS Support](https://www.caniemail.com/)
- [Litmus Email Testing](https://www.litmus.com/)
- [Email on Acid](https://www.emailonacid.com/)

## 💡 Pourquoi ne pas utiliser un CSS inliner automatique ?

Il existe des outils comme [Juice](https://github.com/Automattic/juice) ou [Premailer](https://premailer.dialect.ca/) qui convertissent automatiquement le CSS en inline.

**Avantages de notre approche PHP :**
- ✅ Pas de dépendance externe
- ✅ Contrôle total sur le HTML généré
- ✅ Performance (pas de parsing HTML/CSS à chaque envoi)
- ✅ Facile à debugger
- ✅ Réutilisable instantanément

## 🤝 Support

Si vous rencontrez un problème de rendu sur un client email spécifique :

1. Testez sur [Litmus](https://www.litmus.com/) ou [Email on Acid](https://www.emailonacid.com/)
2. Identifiez le style problématique
3. Créez une variante compatible dans `newsletter-styles-inline.php`
4. Appliquez conditionnellement selon le client email (si nécessaire)