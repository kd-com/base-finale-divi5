# INSTRUCTIONS D'INSTALLATION - MODULE APERÇU NEWSLETTERS

## Fichiers à ajouter

### 1. Nouveau fichier : `newsletter_preview.php`
📁 Emplacement : `/wp-content/themes/kd-com/module_admin/newsletter_preview.php`

Ce fichier contient :
- Page d'administration "Archives" 
- Fonction pour récupérer toutes les campagnes avec liens d'aperçu
- Shortcode `[newsletter_archives]` pour l'affichage front-end
- Système de cache pour optimiser les performances

---

## 2. Modification du fichier principal `newsletter.php`

📁 Fichier : `/wp-content/themes/kd-com/module_admin/newsletter.php`

**À la ligne 16**, après cette ligne :
```php
require_once get_stylesheet_directory() . '/module_admin/newsletter_abonne.php';
```

**Ajouter :**
```php
require_once get_stylesheet_directory() . '/module_admin/newsletter_preview.php';
```

Le code devrait ressembler à ceci :
```php
// Inclure les modules
require_once get_stylesheet_directory() . '/module_admin/newsletter_abonne.php';
require_once get_stylesheet_directory() . '/module_admin/newsletter_preview.php';
```

---

## 3. Modification du fichier `stats.php`

📁 Fichier : `/wp-content/themes/kd-com/module_admin/newsletter_pages/stats.php`

Remplacez entièrement le contenu par le fichier `stats_fixed.php` fourni.

---

## Résultat attendu

### En back-office (admin WordPress)

Nouveau menu dans **Newsletter > Archives** avec :
- ✅ Liste de toutes les newsletters envoyées
- ✅ Lien "Aperçu en ligne" pour chaque newsletter
- ✅ Bouton "Copier le lien" pour partager facilement
- ✅ Statistiques (destinataires, taux d'ouverture)
- ✅ Instructions pour utiliser le shortcode

### En front-end (sur le site public)

**Shortcode disponible :**
```
[newsletter_archives]
```

**Options du shortcode :**
```
[newsletter_archives limit="10"]                    → Limiter à 10 newsletters
[newsletter_archives show_stats="true"]             → Afficher les statistiques
[newsletter_archives title="Nos newsletters"]       → Titre personnalisé
```

**Design responsive inclus** avec :
- Cards élégantes avec effet hover
- Bouton avec dégradé violet
- Affichage adaptatif mobile/desktop
- Statistiques optionnelles

---

## Fonctionnalités bonus

### 🚀 Cache intelligent
- Les données sont mises en cache 1 heure
- Invalidation automatique lors de l'envoi d'une nouvelle newsletter
- Optimise les performances et réduit les appels API

### 🔗 Liens d'aperçu
La fonction récupère automatiquement les URLs d'aperçu depuis l'API Brevo :
- `onlineUrl` (prioritaire)
- `htmlUrl` (fallback)
- URL Brevo par défaut (si aucune URL disponible)

---

## Test après installation

1. **Vérifier le menu admin** : Newsletter > Archives
2. **Tester le shortcode** : Créer une page et ajouter `[newsletter_archives]`
3. **Vérifier les liens** : Cliquer sur "Aperçu en ligne" depuis le back-office
4. **Tester le responsive** : Voir la page sur mobile

---

## Dépannage

### Le menu "Archives" n'apparaît pas
→ Vérifier que `newsletter_preview.php` est bien inclus dans `newsletter.php`

### Les liens d'aperçu ne fonctionnent pas
→ Vérifier que les campagnes ont bien été envoyées (statut `sent` dans Brevo)
→ Vider le cache : `delete_transient('brevo_all_campaigns_...');`

### Le shortcode n'affiche rien
→ Vérifier la configuration Brevo (API configurée)
→ Vérifier qu'il y a des campagnes envoyées

---

## Support

Pour toute question, consultez les logs WordPress :
```
/wp-content/debug.log
```

Les erreurs sont préfixées par `Brevo Archives Error:`