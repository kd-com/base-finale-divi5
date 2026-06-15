# 🆕 NOUVELLES FONCTIONNALITÉS - Newsletter Brevo v2.1

## 📋 Résumé des Ajouts

Cette mise à jour ajoute trois fonctionnalités majeures :

1. ✅ **Tracking des posts envoyés** - Ne plus renvoyer les mêmes contenus
2. ✅ **Formulaire d'inscription** - Shortcode pour collecter des abonnés
3. ✅ **Page de désinscription** - Gestion automatique des désabonnements

---

## 🎯 1. TRACKING DES POSTS ENVOYÉS

### Comment ça fonctionne ?

Le module enregistre maintenant tous les posts (articles et événements) qui ont été envoyés dans une newsletter. 

**Avantages :**
- Chaque newsletter ne contient QUE du nouveau contenu
- Pas de doublons entre les envois
- L'envoi automatique ne se déclenche QUE s'il y a du nouveau contenu

### Dans l'interface admin

Vous verrez dans la section "Informations" :
- **Nouveaux posts disponibles** : Indique s'il y a du contenu à envoyer
- **Posts déjà envoyés** : Nombre de posts déjà envoyés avec un bouton de réinitialisation

### Réinitialiser le tracking

Si vous voulez renvoyer tous les posts (par exemple pour un nouvel envoi complet) :
1. Cliquez sur "🔄 Réinitialiser" dans la section Informations
2. Tous les posts redeviendront "non envoyés"

### Comportement automatique

**L'envoi automatique ne se déclenchera PAS si :**
- Tous les posts récents ont déjà été envoyés
- Il n'y a pas de nouveaux événements à venir

Un message sera enregistré dans les logs WordPress : `Brevo Newsletter: Envoi automatique annulé - Aucun nouveau contenu disponible`

---

## 📝 2. FORMULAIRE D'INSCRIPTION

### Utilisation basique

Ajoutez simplement ce shortcode dans n'importe quelle page, article ou widget :

```
[brevo_newsletter_form]
```

### Styles disponibles

#### Style par défaut
```
[brevo_newsletter_form]
```
Formulaire avec fond gris, champs verticaux

#### Style minimal
```
[brevo_newsletter_form style="minimal"]
```
Fond transparent, design épuré

#### Style inline
```
[brevo_newsletter_form style="inline"]
```
Champs en ligne (idéal pour les en-têtes ou barres latérales)

### Personnalisation

```
[brevo_newsletter_form 
    title="Rejoignez notre communauté"
    button_text="Je m'inscris"
    placeholder_email="votre@email.com"
    placeholder_name="Votre prénom"
    show_name="yes"
    style="default"]
```

#### Attributs disponibles :

| Attribut | Description | Valeur par défaut |
|----------|-------------|-------------------|
| `title` | Titre du formulaire | "Abonnez-vous à notre newsletter" |
| `button_text` | Texte du bouton | "S'abonner" |
| `placeholder_email` | Placeholder email | "Votre email" |
| `placeholder_name` | Placeholder nom | "Votre nom (optionnel)" |
| `show_name` | Afficher champ nom (yes/no) | "yes" |
| `style` | Style (default/minimal/inline) | "default" |

### Exemples d'intégration

#### Dans une page
Créez une page "Newsletter" et ajoutez le shortcode dans l'éditeur.

#### Dans un widget
Ajoutez un widget "Texte" ou "HTML personnalisé" et collez le shortcode.

#### Dans le thème
```php
<?php echo do_shortcode('[brevo_newsletter_form style="minimal"]'); ?>
```

#### Avec Elementor/Divi
Ajoutez un module "Shortcode" et collez le code.

### Gestion des données

**Ce qui est envoyé à Brevo :**
- Email (obligatoire)
- Nom complet (optionnel)
  - Divisé automatiquement en FIRSTNAME et LASTNAME
  - Exemple : "Jean Dupont" → FIRSTNAME: "Jean", LASTNAME: "Dupont"

**Gestion des doublons :**
- Si l'email existe déjà : Message "Vous êtes déjà abonné"
- Pas d'erreur, confirmation positive

---

## 🚫 3. PAGE DE DÉSINSCRIPTION

### Comment ça fonctionne ?

Le module crée automatiquement une page de désinscription accessible via une URL spéciale.

### URL de désinscription

```
https://votresite.com/?brevo_unsubscribe=1&email={{contact.EMAIL}}
```

**Important :** La variable `{{contact.EMAIL}}` est automatiquement remplacée par Brevo lors de l'envoi de la newsletter.

### Intégration dans vos emails

Le template `template-email.php` a été mis à jour avec le bon lien dans le footer :

```html
<a href="<?php echo home_url('/?brevo_unsubscribe=1&email={{contact.EMAIL}}'); ?>">
    Se désabonner
</a>
```

### Processus de désinscription

1. L'utilisateur clique sur "Se désabonner" dans l'email
2. Il arrive sur une page de confirmation avec son email
3. Il confirme la désinscription
4. Il est retiré de la liste Brevo
5. Message de confirmation affiché

### Personnalisation de la page

La page de désinscription est entièrement stylée et responsive. Pour la personnaliser, éditez le fichier `brevo-newsletter-subscribers.php` dans la fonction `brevo_handle_unsubscribe_page()`.

---

## 🔧 INSTALLATION DES NOUVELLES FONCTIONNALITÉS

### Si vous avez déjà la v2.0 installée

1. **Remplacez** `brevo-newsletter-functions.php` par la nouvelle version
2. **Ajoutez** le fichier `brevo-newsletter-subscribers.php` à la racine de votre thème
3. **Mettez à jour** `template-email.php` dans le dossier `brevo-newsletter/`

### Structure finale

```
votre-theme/
├── brevo-newsletter-functions.php       # NOUVEAU - Version 2.1
├── brevo-newsletter-subscribers.php     # NOUVEAU - Gestion abonnés
└── brevo-newsletter/
    ├── template-email.php               # MIS À JOUR - Liens désinscription
    ├── style.scss
    ├── style.css
    └── ...
```

---

## 📊 TABLEAU RÉCAPITULATIF

| Fonctionnalité | Avant (v2.0) | Après (v2.1) |
|----------------|--------------|--------------|
| **Gestion des posts** | Renvoie tous les posts | Ne renvoie que les nouveaux |
| **Envoi automatique** | Se déclenche toujours | Se déclenche seulement si nouveau contenu |
| **Inscription** | Via Brevo uniquement | Formulaire sur le site (shortcode) |
| **Désinscription** | Lien Brevo générique | Page personnalisée sur le site |
| **Tracking** | Non | Oui - historique des posts envoyés |

---

## 🧪 TESTS À EFFECTUER

### Test 1 : Formulaire d'inscription
1. Ajoutez `[brevo_newsletter_form]` dans une page
2. Testez l'inscription avec votre email
3. Vérifiez que l'email apparaît dans Brevo

### Test 2 : Tracking des posts
1. Envoyez un email de test
2. Vérifiez dans "Informations" que les posts sont marqués comme envoyés
3. Envoyez un nouveau test → Seuls les nouveaux posts apparaissent

### Test 3 : Désinscription
1. Envoyez-vous un email de test
2. Cliquez sur "Se désabonner" dans l'email
3. Confirmez la désinscription
4. Vérifiez dans Brevo que vous êtes désabonné

### Test 4 : Envoi automatique sans contenu
1. Activez l'envoi automatique
2. Assurez-vous qu'il n'y a pas de nouveau contenu
3. Le jour prévu, vérifiez que l'envoi n'a pas eu lieu
4. Consultez les logs WordPress pour voir le message d'annulation

---

## 💡 BONNES PRATIQUES

### Formulaire d'inscription

✅ **À faire :**
- Testez le formulaire avant de le publier
- Utilisez le style adapté à votre design
- Placez le formulaire à des endroits stratégiques (footer, sidebar, fin d'article)
- Personnalisez les textes selon votre audience

❌ **À éviter :**
- Ne dupliquez pas trop le formulaire sur une même page
- N'oubliez pas de tester sur mobile
- Ne collectez que les données nécessaires

### Désinscription

✅ **À faire :**
- Gardez le processus simple (2 clics maximum)
- Confirmez la désinscription clairement
- Proposez de revenir sur le site

❌ **À éviter :**
- Ne compliquez pas le processus
- Ne cachez pas le lien de désinscription
- Ne demandez pas de raison (sauf si vraiment nécessaire)

### Tracking des posts

✅ **À faire :**
- Réinitialisez le tracking si vous changez de stratégie éditoriale
- Surveillez la section "Nouveaux posts disponibles"
- Publiez régulièrement pour maintenir l'intérêt

❌ **À éviter :**
- Ne réinitialisez pas trop souvent (éviter les doublons)
- Ne forcez pas l'envoi s'il n'y a pas de contenu

---

## 🔍 DÉPANNAGE

### Le formulaire ne s'affiche pas
- Vérifiez que jQuery est chargé sur votre site
- Vérifiez la console du navigateur pour les erreurs JS
- Testez avec un autre shortcode pour confirmer que les shortcodes fonctionnent

### L'inscription ne fonctionne pas
- Vérifiez la configuration Brevo (API, liste)
- Consultez la console réseau (F12) pour voir les erreurs AJAX
- Vérifiez les logs PHP de WordPress

### La page de désinscription ne s'affiche pas
- Vérifiez que le fichier `brevo-newsletter-subscribers.php` est bien inclus
- Testez l'URL manuellement : `votresite.com/?brevo_unsubscribe=1&email=test@test.com`
- Vérifiez qu'il n'y a pas de conflit avec d'autres plugins

### Les posts continuent d'être renvoyés
- Vérifiez que l'envoi s'est bien terminé (pas d'erreur)
- Consultez la liste des posts envoyés dans l'admin
- Essayez de réinitialiser puis de renvoyer

---

## 📞 SUPPORT

Pour toute question ou problème :

1. Consultez cette documentation
2. Vérifiez le fichier README.md principal
3. Testez avec le fichier `test-installation.php`
4. Consultez les logs WordPress (`wp-content/debug.log`)

---

**Version actuelle : 2.1.0**  
**Date de mise à jour : 29 janvier 2026**

# Modifications Newsletter - Support Événements Récurrents et Multi-jours

## 🎯 Objectif

Adapter le système de newsletter Brevo pour gérer correctement les événements récurrents et sur plusieurs jours, en les séparant visuellement et en les affichant jusqu'à leur date de fin.

## 📦 Fichiers modifiés

### 1. `newsletter.php` (module_admin/newsletter.php)

**Modifications principales :**

#### Fonction `generate_newsletter_html()`
- Remplacement de la requête unique `$events_query` par trois tableaux séparés :
  - `$events_single` : Événements ponctuels
  - `$events_multi_day` : Événements sur plusieurs jours
  - `$events_recurring` : Événements récurrents

- Logique de filtrage améliorée :
  ```php
  // Pour événements multi-jours et récurrents :
  $end_date = get_field('event_end_date', $post_id);
  if ($end_date >= $today) {
      // L'événement est à venir
  }
  
  // Pour événements ponctuels :
  $event_date = get_field('event_date', $post_id);
  if ($event_date >= $today) {
      // L'événement est à venir
  }
  ```

#### Fonction `has_new_posts_to_send()`
- Mise à jour pour vérifier tous les types d'événements
- Basée sur la date de fin pour multi-jours et récurrents
- Basée sur la date unique pour les ponctuels

#### Fonction `send_newsletter_via_brevo()`
- Mise à jour du tracking des posts envoyés pour gérer tous les types d'événements
- Les événements sont marqués comme envoyés en fonction de leur date de fin

### 2. `template-email.php` (newsletter/template-email.php)

**Modifications principales :**

#### Nouvelle fonction helper
```php
function newsletter_format_event_date($post_id)
```
- Formate l'affichage de la date selon le type d'événement
- **Ponctuel** : "15 février 2026"
- **Multi-jours** : "Du 15 au 20 février 2026"
- **Récurrent** : "Tous les samedis (jusqu'au 30 juin 2026)"

#### Sections séparées pour chaque type d'événement

**Section 1 : Événements ponctuels** (📅)
- Titre en vert (#2a9d8e)
- Bouton standard jaune

**Section 2 : Événements sur plusieurs jours** (📆)
- Titre en rose (#f5576c)
- Badge "📅 Multi-jours" en dégradé rose
- Bouton rose
- Affichage de la période complète

**Section 3 : Événements récurrents** (🔁)
- Titre en violet (#667eea)
- Badge "🔁 Récurrent" en dégradé violet
- Bouton violet
- Affichage de la récurrence + période de validité

## 🎨 Hiérarchie visuelle

### Couleurs par type
- **Ponctuels** : Vert (#2a9d8e) - standard
- **Multi-jours** : Rose (#f5576c) - attention spéciale
- **Récurrents** : Violet (#667eea) - événements réguliers

### Badges
Les badges permettent d'identifier rapidement le type d'événement :
- Badge rose pour multi-jours
- Badge violet pour récurrents
- Pas de badge pour les ponctuels (standard)

## 📋 Exemple de rendu

```
📰 Newsletter - Février 2026

🔥 Dernières Actualités
- [Articles d'actualités...]

📅 Événements à venir
- Concert Jazz au Vieux Port
  📆 15 février 2026 • 🕐 20:30
  📍 Le Vieux Port
  [Voir l'événement →]

📆 Événements sur plusieurs jours
- Festival de Musique 📅 Multi-jours
  📆 Du 20 au 25 février 2026 • 🕐 14:00
  📍 Parc des Expositions
  [Voir l'événement →]

🔁 Événements récurrents
- Cours de Yoga 🔁 Récurrent
  📆 Tous les lundis (jusqu'au 30 juin 2026) • 🕐 18:00
  📍 Salle Polyvalente
  [Voir l'événement →]
```

## 🔄 Logique de filtrage

### Événements affichés dans la newsletter

Un événement est inclus si sa date de **fin de validité** n'est pas dépassée :

| Type | Critère d'inclusion |
|------|---------------------|
| Ponctuel | `event_date >= aujourd'hui` |
| Multi-jours | `event_end_date >= aujourd'hui` |
| Récurrent | `event_end_date >= aujourd'hui` |

### Exemple concret

**Aujourd'hui : 5 février 2026**

- ✅ Concert le 15 février → Affiché (dans le futur)
- ✅ Festival du 20 au 25 février → Affiché (se termine après aujourd'hui)
- ✅ Cours tous les lundis jusqu'au 30 juin → Affiché (se termine après aujourd'hui)
- ❌ Concert du 1er février → Non affiché (dans le passé)
- ❌ Festival du 1er au 3 février → Non affiché (terminé)
- ❌ Cours tous les mardis jusqu'au 4 février → Non affiché (série terminée)

## 📊 Tracking des posts envoyés

Les événements récurrents et multi-jours sont marqués comme "envoyés" une seule fois, mais continuent d'apparaître dans les newsletters suivantes tant que leur date de fin n'est pas dépassée.

**Comportement :**
1. Un événement récurrent créé le 1er février (jusqu'au 30 juin) apparaît dans la newsletter de février
2. Il est marqué comme "envoyé"
3. Il continue d'apparaître dans les newsletters de mars, avril, mai et juin
4. Il disparaît automatiquement après le 30 juin

## 🚀 Installation

1. Remplacer `wp-content/themes/kd-com/module_admin/newsletter.php`
2. Remplacer `wp-content/themes/kd-com/newsletter/template-email.php`
3. Tester avec l'envoi d'un email de test
4. Vérifier le rendu dans différents clients email

## ✅ Compatibilité

- ✅ Gmail
- ✅ Outlook
- ✅ Apple Mail
- ✅ Clients email mobiles
- ✅ Brevo (tous les templates conservent les URLs absolues)

## 🔍 Points de vigilance

1. **Limite de 3 événements par catégorie** : Pour éviter des newsletters trop longues
2. **URLs absolues** : Maintenues pour la compatibilité Gmail
3. **Styles inline** : Tous les styles restent inline pour la compatibilité email

## 📝 Notes techniques

### Variables passées au template

```php
$template_data = array(
    'logo' => $logo_url,                    // URL absolue du logo
    'news_query' => $news_query,            // WP_Query des actualités
    'events_single' => $events_single,      // Array d'IDs événements ponctuels
    'events_multi_day' => $events_multi_day,// Array d'IDs événements multi-jours
    'events_recurring' => $events_recurring,// Array d'IDs événements récurrents
    'events_module_enabled' => true/false,  // Module événements activé ?
    'site_name' => 'Nom du site',
    'current_month' => 'Février 2026',
    'current_year' => '2026'
);
```

### Fonction helper disponible dans le template

```php
newsletter_format_event_date($post_id)
```
Retourne la date formatée selon le type d'événement.

## 🆘 Dépannage

### Les événements récurrents n'apparaissent pas
- Vérifier que `event_end_date` est bien renseignée
- Vérifier que `event_end_date >= date du jour`
- Vérifier que le module événements est activé

### Les sections ne s'affichent pas avec les bonnes couleurs
- Vérifier que le CSS inline est bien présent dans le template
- Tester dans différents clients email

### Un événement apparaît dans plusieurs catégories
- Impossible : la logique sépare strictement par `event_type`
- Vérifier la valeur du champ ACF `event_type`

## 🎓 Ressources

- [Documentation ACF](https://www.advancedcustomfields.com/resources/)
- [Guide emails HTML](https://www.emailonacid.com/)
- [Documentation Brevo API](https://developers.brevo.com/)