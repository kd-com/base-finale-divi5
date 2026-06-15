# Template Newsletter Mailpoet - Guide d'installation

## 📋 Ce que fait ce template

- **Section Actualités** : Affiche automatiquement les 3 derniers articles publiés (tous types confondus)
- **Section Événements** : Affiche automatiquement les **3 prochains événements** triés par date d'événement (champ `event_date`) UNIQUEMENT si le module événements est activé dans les réglages du site
  - Date et heure de l'événement
  - Lieu et adresse
  - Prix (gratuit ou payant avec tarifs)
  - Lien vers la billetterie
  - **Important** : Les événements sont triés par leur date d'événement réelle (pas par date de publication)
  - **Condition** : Le module `cpt_evenements` doit être activé dans **Réglages personnalisés > Gestion des modules**
- **Design responsive** : S'adapte aux mobiles et tablettes
- **Liens de désinscription** : Intégrés automatiquement par Mailpoet
- **Utilise les couleurs du thème** : Intégration SASS avec votre système de couleurs

## 🚀 Installation

### Étape 1 : Copier les fichiers

1. **Copiez le fichier PHP** dans votre thème :
   ```
   wp-content/themes/kd-com/mailpoet-templates/newsletter-template.php
   ```

2. **Copiez le fichier SASS** :
   ```
   wp-content/themes/kd-com/sass/modules/newsletter.scss
   ```

3. **GÉNÉRER le SASS** dans le fichier `/css/newsletter.css` :
   ```scss
   sass --no-source-map wp-content/themes/kd-com/sass/modules/newsletter.scss wp-content/themes/kd-com/css/newsletter.css
   ```
4. **COPIER le fichier généré** dans le fichier template mail

### Étape 2 : Configuration Mailpoet

1. Dans **Mailpoet > Modèles**, cliquez sur `Nouveau modèle`
2. Choisissez `Modèle vierge`
3. Basculez en mode `Code source` (icône `</>`)
4. Copiez-collez le contenu du fichier PHP
5. Sauvegardez le modèle

## ⚙️ Configuration des champs ACF

Le template utilise automatiquement tous les champs ACF de vos événements :

- `event_date` : Date de l'événement
- `event_time` : Heure de l'événement
- `event_location` : Lieu (nom)
- `event_address` : Adresse complète
- `event_ticket_link` : Lien vers la billetterie
- `event_price_type` : Type de tarif (free/paid)
- `event_prices` : Répéteur de tarifs avec :
  - `price_label` : Label du tarif
  - `price_amount` : Montant
  - `price_description` : Description

**Aucune modification nécessaire** si vous utilisez déjà ces champs dans votre CPT `evenements`.

### ✅ Activation du module événements

**IMPORTANT** : Pour que la section événements s'affiche dans la newsletter, vous devez activer le module dans votre administration WordPress :

1. Allez dans **Réglages personnalisés > Gestion des modules**
2. Cochez **"Activer le module cpt_evenements"**
3. Cliquez sur **Enregistrer**

Si le module n'est pas activé, seule la section Actualités sera affichée dans la newsletter.

## 🎨 Personnalisation des couleurs

Le template utilise automatiquement les couleurs définies dans votre `_theme-colors.scss` :

- `$couleur-titrage` : #2a9d8e (titres de section)
- `$couleur-texte` : #3e464b (texte principal)
- `$couleur-lien` : #e9c468 (boutons CTA)
- `$couleur-fond` : #264653 (header)
- `$couleur-fond2` : #424242 (footer)
- `$couleur-blanche` : #ffffff
- `$couleur-noire` : #000000

Pour modifier ces couleurs, éditez simplement votre fichier `_theme-colors.scss` et recompilez votre SASS.

## 🔧 Personnalisations avancées

### Nombre d'articles/événements affichés

Dans le template PHP, lignes 9 et 24 :
```php
function get_latest_news_for_newsletter($limit = 3) // Changez 3
function get_latest_events_for_newsletter($limit = 3) // Changez 3
```

### Longueur des extraits

Dans le template PHP, modifiez le nombre de mots :
```php
<?php echo wp_trim_words(get_the_excerpt(), 25, '...'); ?>
```

### Filtrer les actualités par catégorie

Si vous voulez filtrer les actualités par catégorie, ajoutez dans la fonction `get_latest_news_for_newsletter` :
```php
'category_name' => 'actualites' // Slug de votre catégorie
```

### Afficher uniquement les événements futurs

C'est déjà le cas par défaut ! Le template filtre et trie automatiquement :

```php
'orderby' => 'meta_value', // Tri par la valeur du champ
'meta_key' => 'event_date', // Utilise la date d'événement (pas la date de publication)
'order' => 'ASC', // Du plus proche au plus lointain
'meta_type' => 'DATE', // Pour un tri chronologique correct
'meta_query' => array(
    array(
        'key' => 'event_date',
        'value' => date('Y-m-d'), // Date d'aujourd'hui
        'compare' => '>=', // Seulement les événements futurs ou aujourd'hui
        'type' => 'DATE',
    ),
)
```

**Exemple** : Si aujourd'hui c'est le 29 janvier 2026 et que vous avez :
- Événement A le 1er février 2026
- Événement B le 15 mars 2026
- Événement C le 5 février 2026
- Événement D le 20 janvier 2026 (passé)

La newsletter affichera : **A (1er fév) → C (5 fév) → B (15 mars)**
L'événement D ne sera pas affiché car sa date est passée.

## 📧 Utilisation dans Mailpoet

1. Allez dans **Mailpoet > Emails > Nouveau email**
2. Choisissez **Newsletter standard**
3. Sélectionnez votre template personnalisé
4. Le template récupère automatiquement :
   - Les 3 derniers articles publiés
   - Les 3 prochains événements
5. Prévisualisez et envoyez !

## 🎯 Avantages de cette intégration

✅ **Automatique** : Récupère automatiquement les derniers contenus
✅ **Cohérent** : Utilise les mêmes couleurs que votre site
✅ **Complet** : Affiche tous les champs importants des événements
✅ **Maintenable** : Géré via SASS comme le reste du thème
✅ **Responsive** : S'adapte à tous les clients mail

## 🔍 Dépannage

### Les événements ne s'affichent pas
1. **Vérifiez que le module est activé** :
   - Allez dans **Réglages personnalisés > Gestion des modules**
   - Cochez **"Activer le module cpt_evenements"**
   - Cliquez sur **Enregistrer**
2. Vérifiez que le slug du CPT est bien `evenements`
3. Vérifiez qu'il y a des événements avec une date future dans le champ `event_date`

### Les actualités sont vides
- Vérifiez qu'il y a bien des articles publiés
- Le template prend TOUS les articles, pas seulement une catégorie

### Les couleurs ne sont pas bonnes
- Recompilez votre SASS
- Vérifiez que `newsletter.scss` est bien importé dans `style.scss`

### Les champs d'événement sont vides
- Vérifiez que les champs ACF utilisent bien les noms :
  - `event_date`, `event_time`, `event_location`, etc.
- Testez avec un événement qui a tous les champs remplis

## 💡 Astuces

### Ajouter un logo dans le header

Dans le template PHP, ligne 144 :
```php
<div class="newsletter-header">
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/logo-newsletter.png" 
         alt="Logo" style="max-width: 200px; margin-bottom: 15px;">
    <h1>📰 Newsletter - <?php echo date_i18n('F Y'); ?></h1>
</div>
```

### Personnaliser les emojis

Modifiez les emojis dans le template :
```php
<h2 class="section-title">🔥 Dernières Actualités</h2> // Changez 🔥
<h2 class="section-title">📅 Événements à venir</h2> // Changez 📅
```

### Ajouter une image à la une

Dans les boucles WordPress, ajoutez après le titre :
```php
<?php if (has_post_thumbnail()) : ?>
    <div style="margin-bottom: 15px;">
        <img src="<?php echo get_the_post_thumbnail_url(null, 'medium'); ?>" 
             alt="<?php echo get_the_title(); ?>" 
             style="max-width: 100%; height: auto; border-radius: 3px;">
    </div>
<?php endif; ?>
```

## 📝 Notes importantes

- Compatible avec la plupart des clients mail (Gmail, Outlook, Apple Mail, etc.)
- Les styles sont inline pour une meilleure compatibilité
- Testez toujours vos emails avant envoi (fonction de test de Mailpoet)
- Le template est optimisé pour les événements avec champs ACF complets

## 🆘 Support

Pour toute question sur l'intégration :
1. Vérifiez que tous les fichiers sont au bon endroit
2. Vérifiez que le SASS est bien compilé
3. Testez avec des contenus de test (articles et événements)
4. Vérifiez les champs ACF dans vos événements

