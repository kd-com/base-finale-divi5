# UTILISATION DU MODULE
// Affiche uniquement les événements à venir
[display_events]

// Affiche les événements à venir + les événements passés (séparés en 2 sections)
[display_events show_past="true"]

// Limite à 6 événements à venir seulement
[display_events limit="6"]

// Limite à 3 événements à venir + 3 événements passés
[display_events limit="3" show_past="true"]

## AFFICHAGE DES TARIFS SUR UNE PAGE EVENEMENT
Dans une page d'événement (affiche les tarifs de l'événement actuel) :
[display_event_prices]
Pour afficher les tarifs d'un événement spécifique :
[display_event_prices event_id="123"]
Sans titre :
[display_event_prices show_title="false"]

## AFFICHAGE DE LA CARTE SUR LA PAGE EVENEMENT:
[display_event_map]

# Gestion des événements récurrents et multi-jours

## Nouveautés

Le système de gestion d'événements a été enrichi pour supporter :
- **Événements ponctuels** : événements d'une seule journée (comportement existant)
- **Événements sur plusieurs jours** : du 15 au 20 juin par exemple
- **Événements récurrents** : tous les samedis, tous les lundis, etc.

## Installation

### 1. Remplacer les fichiers

Remplacez les fichiers suivants par les nouvelles versions :

- `wp-content/themes/kd-com/module_admin/acf_fields/acf_evenements.php`
- `wp-content/themes/kd-com/module_front/cpt_evenements.php`

### 2. Ajouter les styles CSS

Ajoutez le contenu du fichier `events-recurring-styles.css` à votre feuille de styles principale ou incluez-le séparément dans votre thème.

### 3. Vider le cache

Après le remplacement des fichiers, videz le cache de WordPress si vous utilisez un plugin de cache.

## Utilisation dans l'admin WordPress

### Création d'un événement ponctuel (existant)

1. Sélectionnez **"Événement ponctuel (une seule date)"**
2. Choisissez la date de l'événement
3. Remplissez les autres champs normalement

**Affichage** : `5 février 2026`

### Création d'un événement sur plusieurs jours

1. Sélectionnez **"Événement sur plusieurs jours"**
2. Choisissez la **date de début**
3. Choisissez la **date de fin**
4. Remplissez les autres champs normalement

**Affichages possibles** :
- Même mois : `Du 15 au 20 février 2026`
- Mois différents : `Du 28 février au 5 mars 2026`

### Création d'un événement récurrent

1. Sélectionnez **"Événement récurrent"**
2. Choisissez la **date de début** (première occurrence)
3. Choisissez la **date de fin** (dernière occurrence)
4. Sélectionnez la **fréquence de récurrence** :
   - Tous les jours
   - Tous les lundis
   - Tous les mardis
   - Tous les mercredis
   - Tous les jeudis
   - Tous les vendredis
   - Tous les samedis
   - Tous les dimanches
   - Tous les mois (même jour)
5. Remplissez les autres champs normalement

**Affichage** : `Tous les samedis (jusqu'au 30 juin 2026)`

## Fonctionnement du filtrage

### Événements à venir

Un événement est considéré comme "à venir" si :
- **Ponctuel** : la date de l'événement >= aujourd'hui
- **Multi-jours** : la date de fin >= aujourd'hui
- **Récurrent** : la date de fin de récurrence >= aujourd'hui

**Exemple** : Un événement récurrent "tous les samedis" du 1er février au 30 juin 2026 restera affiché dans les événements à venir jusqu'au 30 juin 2026.

### Événements passés

Un événement est considéré comme "passé" si :
- **Ponctuel** : la date de l'événement < aujourd'hui
- **Multi-jours** : la date de fin < aujourd'hui
- **Récurrent** : la date de fin de récurrence < aujourd'hui

## Affichage visuel

### Badges

Des badges sont automatiquement ajoutés sur les cartes d'événements :
- Badge violet **"Récurrent"** avec icône de répétition pour les événements récurrents
- Badge rose **"Plusieurs jours"** avec icône de calendrier pour les événements multi-jours

### Classes CSS

Chaque type d'événement reçoit des classes CSS spécifiques :
- `.event-card.recurring` : événements récurrents
- `.event-card.multi-day` : événements sur plusieurs jours
- `.event-card.past` : événements passés

## Shortcode (inchangé)

Le shortcode `[display_events]` fonctionne exactement de la même manière :

```php
// Afficher tous les événements à venir
[display_events]

// Afficher 5 événements à venir maximum
[display_events limit="5"]

// Afficher les événements à venir + passés
[display_events show_past="true"]

// Afficher 3 événements à venir et 3 passés
[display_events limit="3" show_past="true"]
```

## Nouveau shortcode : Affichage de la date sur les pages individuelles

### `[display_event_date]`

Ce nouveau shortcode permet d'afficher la date de l'événement de manière formatée et adaptée selon le type d'événement (ponctuel, multi-jours ou récurrent).

### Utilisation de base

Sur une page single d'événement (single-evenements.php), utilisez simplement :

```php
[display_event_date]
```

**Résultats selon le type :**
- **Ponctuel** : "Samedi 15 février 2026" + heure
- **Multi-jours** : "Du 15 au 20 février 2026" + badge "Événement sur plusieurs jours" + heure
- **Récurrent** : "Tous les samedis" + "Du 1er février au 30 juin 2026" + badge "Événement récurrent" + heure

### Paramètres disponibles

```php
// Afficher pour un événement spécifique
[display_event_date event_id="123"]

// Sans icône
[display_event_date show_icon="false"]

// Sans l'heure
[display_event_date show_time="false"]

// Format court
[display_event_date format="short"]

// Format minimal
[display_event_date format="minimal"]
```

### Paramètres détaillés

| Paramètre | Valeurs | Défaut | Description |
|-----------|---------|--------|-------------|
| `event_id` | ID de l'événement | ID actuel | Spécifie quel événement afficher |
| `show_icon` | `true` / `false` | `true` | Afficher l'icône calendrier |
| `show_time` | `true` / `false` | `true` | Afficher l'heure de l'événement |
| `format` | `full` / `short` / `minimal` | `full` | Niveau de détail de l'affichage |

### Formats d'affichage

#### Format `full` (par défaut)
- **Ponctuel** : "📅 Samedi 15 février 2026" + 🕐 "20:30"
- **Multi-jours** : "📅 Du 15 au 20 février 2026" + Badge rose + 🕐 "14:00"
- **Récurrent** : "📅 Tous les samedis" + "Du 1er février au 30 juin" + Badge violet + 🕐 "18:00"

#### Format `short`
Identique au format full mais sans les badges de type d'événement.

#### Format `minimal`
- **Ponctuel** : "📅 15 février 2026"
- **Multi-jours** : "📅 15-20 février 2026"
- **Récurrent** : "📅 Tous les samedis"

### Exemples d'intégration dans un template

#### Dans single-evenements.php

```php
<div class="event-header">
    <h1><?php the_title(); ?></h1>
    
    <!-- Affichage de la date formatée -->
    <?php echo do_shortcode('[display_event_date]'); ?>
    
    <!-- Ou avec personnalisation -->
    <?php echo do_shortcode('[display_event_date format="full" show_time="true"]'); ?>
</div>
```

#### Dans un widget ou contenu

```php
<!-- Format complet avec tous les détails -->
[display_event_date]

<!-- Format minimal sans heure -->
[display_event_date format="minimal" show_time="false"]

<!-- Sans icône -->
[display_event_date show_icon="false"]
```

### Classes CSS pour personnalisation

Le shortcode génère les classes CSS suivantes :

```css
.event-date-display          /* Container principal */
.event-dates                 /* Container des dates */
.event-dates.single          /* Pour événements ponctuels */
.event-dates.multi-day       /* Pour événements multi-jours */
.event-dates.recurring       /* Pour événements récurrents */
.date-label                  /* Label de la date */
.date-range                  /* Plage de dates (récurrent) */
.event-type-badge           /* Badge de type */
.event-type-badge.recurring /* Badge récurrent (violet) */
.event-type-badge.multi-day /* Badge multi-jours (rose) */
.event-time-display         /* Affichage de l'heure */
```

Vous pouvez surcharger ces styles dans votre CSS personnalisé.

## Exemples d'utilisation

### Cours de yoga hebdomadaires

- Type : Événement récurrent
- Date de début : 1er septembre 2025
- Date de fin : 30 juin 2026
- Récurrence : Tous les lundis
- Heure : 18:00

**Résultat** : "Tous les lundis (jusqu'au 30 juin 2026)"

### Festival sur 3 jours

- Type : Événement sur plusieurs jours
- Date de début : 15 juillet 2026
- Date de fin : 17 juillet 2026
- Heure : 14:00

**Résultat** : "Du 15 au 17 juillet 2026"

### Concert unique

- Type : Événement ponctuel
- Date : 20 mars 2026
- Heure : 20:30

**Résultat** : "20 mars 2026"

## Recommandations

1. **Événements récurrents** : Utilisez-les pour les cours, ateliers ou activités qui se répètent régulièrement
2. **Événements multi-jours** : Parfaits pour les festivals, séminaires, ou événements qui s'étendent sur plusieurs jours consécutifs
3. **Événements ponctuels** : Pour tous les événements uniques (concerts, conférences, etc.)

## Compatibilité

✅ Compatible avec la gestion existante des tarifs
✅ Compatible avec la carte OpenStreetMap
✅ Compatible avec Tarteaucitron
✅ Responsive
✅ Rétrocompatible avec les événements ponctuels existants

## Support

En cas de problème, vérifiez :
1. Que les champs ACF ont bien été mis à jour
2. Que le cache WordPress a été vidé
3. Que les styles CSS ont été ajoutés
4. Que les anciennes données d'événements sont compatibles (événements ponctuels uniquement)