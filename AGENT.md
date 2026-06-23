# Migration Divi 5 — Child Theme kd-com

## 📋 Vue d'ensemble du projet

| Champ | Détail |
|---|---|
| Projet | Migration Child Theme kd-com vers Divi 5 |
| Objectif | Moderniser le code + accéder aux fonctionnalités Divi 5 |
| Approche | Refonte complète avec migration progressive des pages |
| Contexte | Gabarit git dupliqué par installation cliente — zéro action manuelle requise |
| Durée estimée | 6 semaines |
| Statut | À démarrer |

---

## 🗂️ Phases du projet

### Phase 1 — Préparation et environnement de staging
### Phase 2 — Corrections PHP critiques
### Phase 3 — Système de presets boutons Divi 5 (automatique)
### Phase 4 — Reconstruction des pages
### Phase 5 — Tests et mise en production

---

## ✅ Tâches détaillées

### Phase 1 — Préparation et staging (Semaine 1)

#### 1.1 Cloner le site en staging

- [ ] Créer un environnement de staging (sous-domaine ou local)
- [ ] Cloner la base de données et les fichiers
- [ ] Vérifier que le staging est inaccessible au public (noindex, htpasswd)
- [ ] Faire une sauvegarde complète du site de production avant toute modification
- **Priorité :** Critique
- **Fichier concerné :** Infrastructure
- **Durée estimée :** 2h

#### 1.2 Activer Divi 5 sur staging

- [ ] Mettre à jour Divi vers la version 5 sur le staging uniquement
- [ ] Vérifier que le site s'affiche correctement en front-end
- [ ] Consulter les logs PHP pour détecter les premières erreurs
- [ ] Noter toutes les erreurs et warnings remontés
- **Priorité :** Critique
- **Fichier concerné :** Infrastructure
- **Durée estimée :** 1h

#### 1.3 Audit initial post-activation

- [ ] Tester la page d'accueil
- [ ] Tester une page intérieure standard
- [ ] Tester une page avec shortcode `[display_events]`
- [ ] Tester une page avec shortcode `[faq_liste]`
- [ ] Tester une page avec shortcode `[slider_accueil]`
- [ ] Vérifier l'accès à l'administration WordPress
- [ ] Vérifier l'ouverture de l'éditeur Divi 5 sur une page
- [ ] Tester le tableau de bord kd-com (widget personnalisé)
- **Priorité :** Critique
- **Fichier concerné :** Ensemble du site
- **Durée estimée :** 2h

---

### Phase 2 — Corrections PHP critiques (Semaine 1-2)

#### 2.1 Supprimer divi-button-presets.php (Divi 4)

- [ ] Retirer le `require_once` de `divi-button-presets.php` dans `functions.php`
- [ ] Supprimer le fichier `wp-content/themes/kd-com/assets/buttons/divi-button-presets.php`
- [ ] Ajouter à la place le `require_once` du nouveau fichier Divi 5 (voir Phase 3)
- [ ] Vérifier qu'aucune erreur PHP n'est générée après suppression
- **Priorité :** Critique
- **Fichier concerné :** `assets/buttons/divi-button-presets.php` + `functions.php`
- **Durée estimée :** 15min
- **Raison :** Ce fichier utilise `et_core_is_fb_enabled()` et `et_builder_module_classes` qui sont dépréciés ou absents en Divi 5. Il génère des shortcodes Divi 4 pour une bibliothèque qui n'existe plus.

#### 2.2 Réécrire la synchronisation palette Divi

- [ ] Localiser et supprimer la fonction `kd_sync_divi_palette()` dans `functions.php`
- [ ] Supprimer le filtre `et_builder_custom_colors` dans `functions.php`
- [ ] Supprimer toutes les lignes `update_option('et_divi', ...)` dans `functions.php`
- [ ] Supprimer les appels `kd_sync_divi_palette()` dans les hooks `update_option_*` et `admin_init`
- [ ] Supprimer les lignes de flush cache Divi (`DELETE FROM wp_options WHERE option_name LIKE 'et_core_options_cache%'`)
- [ ] Ajouter la nouvelle fonction `kd_inject_theme_css_vars()` (voir code ci-dessous)
- [ ] Garder uniquement `kd_sync_theme_colors()` pour la palette Gutenberg

```php
// NOUVEAU CODE À AJOUTER dans functions.php
// Remplace kd_sync_divi_palette() et le bloc surcharge accent

function kd_inject_theme_css_vars() {
    $couleurs = [
        '--kd-couleur-titrage' => get_option('couleur_titrage', '#22282d'),
        '--kd-couleur-texte'   => get_option('couleur_texte',   '#3e464b'),
        '--kd-couleur-lien'    => get_option('couleur_lien',    '#e84448'),
        '--kd-couleur-fond'    => get_option('couleur_fond',    '#3db27c'),
        '--kd-couleur-fond2'   => get_option('couleur_fond2',   '#424242'),
        '--kd-couleur-blanche' => get_option('couleur_blanche', '#ffffff'),
        '--kd-couleur-noire'   => get_option('couleur_noire',   '#000000'),
    ];
    $css = ':root{';
    foreach ($couleurs as $var => $val) {
        $css .= $var . ':' . sanitize_hex_color($val) . ';';
    }
    $css .= '}';
    echo '<style id="kd-theme-vars">' . $css . '</style>';
}
add_action('wp_head',    'kd_inject_theme_css_vars', 1);
add_action('admin_head', 'kd_inject_theme_css_vars', 1);
```

- **Priorité :** Critique
- **Fichier concerné :** `functions.php`
- **Durée estimée :** 1h

#### 2.3 Supprimer l'ancienne surcharge CSS accent Divi 4

- [ ] Localiser le bloc `add_action('wp_head', function() { echo '<style id="divi-accent-override">...` dans `functions.php`
- [ ] Supprimer ce bloc entièrement (les sélecteurs `.et_pb_button`, `.et_pb_pricing_heading` etc. ne correspondent plus aux classes Divi 5)
- [ ] Ajouter la nouvelle surcharge accent Divi 5 (voir code ci-dessous)

```php
// Surcharge couleur accent Divi 5 — à ajouter dans functions.php
add_action('wp_head', function() {
    $accent = get_option('couleur_lien', '#e84448');
    echo '<style id="divi5-accent-override">
        :root { --divi-accent-color: ' . sanitize_hex_color($accent) . '; }
    </style>';
});
```

- **Priorité :** Haute
- **Fichier concerné :** `functions.php`
- **Durée estimée :** 15min

#### 2.4 Sécuriser et réécrire la synchronisation palette Divi

- [ ] Localiser `$logo_url = et_get_option('divi_logo')` dans `includes/dashboard_block.php`
- [ ] Remplacer par : `$logo_url = function_exists('et_get_option') ? et_get_option('divi_logo') : '';`
- [ ] Vérifier que le logo s'affiche toujours dans le widget dashboard
- **Priorité :** Moyenne
- **Fichier concerné :** `includes/dashboard_block.php`
- **Durée estimée :** 15min

#### 2.5 Mettre à jour les slugs Divi dans admin_editor.php

- [ ] Vérifier dans Divi 5 le nouveau slug de la page options (actuellement `et_divi_options`)
- [ ] Mettre à jour `remove_menu_page('et_divi_options')` avec le nouveau slug si nécessaire
- [ ] Mettre à jour `remove_cap('manage_divi_options')` si la capability a changé
- [ ] Tester que l'accès à l'admin est correct pour le rôle éditeur
- **Priorité :** Moyenne
- **Fichier concerné :** `includes/admin_editor.php`
- **Durée estimée :** 30min

#### 2.6 Adapter aos-divi.js aux nouvelles classes Divi 5

- [ ] Vérifier dans Divi 5 quelles classes CSS sont utilisées pour les modules (`.et_pb_module` existe encore en mode compatibilité mais les nouveaux blocs ont d'autres classes)
- [ ] Ajouter les nouveaux sélecteurs Divi 5 dans `js/aos-divi.js`
- [ ] Tester que les animations AOS se déclenchent sur les blocs Divi 5

```js
// Dans aos-divi.js — compléter le sélecteur existant
// Remplacer :
$('.et_pb_module, .et_pb_section, .et_pb_row').each(...)
// Par :
$('.et_pb_module, .et_pb_section, .et_pb_row, [data-divi-module], .divi-layout-block').each(...)
// ⚠️ Confirmer les noms de classes exacts dans la doc Divi 5 après installation
```

- **Priorité :** Moyenne
- **Fichier concerné :** `js/aos-divi.js`
- **Durée estimée :** 1h

---

### Phase 3 — Système de presets boutons Divi 5 automatique (Semaine 2)

> **Contexte :** Le thème est un gabarit git dupliqué par installation cliente. Les presets boutons doivent s'injecter automatiquement en base de données sans aucune action manuelle, aussi bien pour les nouvelles installations que pour les mises à jour du gabarit.
>
> Les boutons animés sont utilisés dans les deux contextes :
> - **Gutenberg** → déjà géré par `button-styles.php` via `register_block_style`, rien à changer
> - **Divi 5** → nouveau fichier `divi5-button-presets.php` à créer

#### 3.1 Créer le fichier divi5-button-presets.php

- [ ] Créer le fichier `assets/buttons/divi5-button-presets.php`
- [ ] Implémenter la fonction `kd_build_divi5_button_presets()` avec les 9 presets (voir code complet ci-dessous)
- [ ] Implémenter la fonction `kd_inject_divi5_button_presets()` avec le système de versioning
- [ ] Enregistrer les deux déclencheurs : `after_switch_theme` et `admin_init`
- **Priorité :** Critique
- **Fichier concerné :** `assets/buttons/divi5-button-presets.php` (nouveau fichier)
- **Durée estimée :** 1h

```php
<?php
/**
 * Presets de boutons Divi 5 — injection automatique
 *
 * S'injecte à l'activation du thème et se met à jour automatiquement
 * via un système de versioning quand le gabarit git évolue.
 *
 * Les couleurs sont gérées via les variables CSS kd-com
 * (--kd-couleur-lien, --kd-couleur-fond, etc.)
 * injectées par kd_inject_theme_css_vars() dans functions.php.
 * Les presets n'ont donc pas besoin de stocker les valeurs hex.
 *
 * Pour mettre à jour les presets sur tous les sites clones :
 * incrémenter $current_version ci-dessous.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function kd_build_divi5_button_presets() : array {
    return [
        'module' => [
            'et_pb_button' => [
                'presets' => [

                    'kd-btn-shine' => [
                        'name'       => '✨ Brillance (Shine)',
                        'created'    => '01-01-2025',
                        'updated'    => '01-01-2025',
                        'version'    => '5.0.0',
                        'is_default' => false,
                        'settings'   => [
                            'custom_button'        => 'on',
                            'button_text_color'    => 'var(--kd-couleur-blanche)',
                            'button_bg_color'      => 'var(--kd-couleur-fond)',
                            'button_border_width'  => '0px',
                            'button_border_radius' => '100px',
                            'button_use_icon'      => 'off',
                            'module_class'         => 'btn-shine',
                        ],
                    ],

                    'kd-btn-slide-right' => [
                        'name'       => '➡️ Remplissage Droite',
                        'created'    => '01-01-2025',
                        'updated'    => '01-01-2025',
                        'version'    => '5.0.0',
                        'is_default' => false,
                        'settings'   => [
                            'custom_button'        => 'on',
                            'button_text_color'    => 'var(--kd-couleur-lien)',
                            'button_bg_color'      => 'transparent',
                            'button_border_width'  => '1px',
                            'button_border_color'  => 'var(--kd-couleur-lien)',
                            'button_border_radius' => '0px',
                            'button_use_icon'      => 'off',
                            'module_class'         => 'btn-slide-right',
                        ],
                    ],

                    'kd-btn-circle-expand' => [
                        'name'       => '⭕ Cercle Expansif',
                        'created'    => '01-01-2025',
                        'updated'    => '01-01-2025',
                        'version'    => '5.0.0',
                        'is_default' => false,
                        'settings'   => [
                            'custom_button'        => 'on',
                            'button_text_color'    => 'var(--kd-couleur-lien)',
                            'button_bg_color'      => 'transparent',
                            'button_border_width'  => '0px',
                            'button_border_radius' => '100px',
                            'button_use_icon'      => 'off',
                            'module_class'         => 'btn-circle-expand',
                        ],
                    ],

                    'kd-btn-slide-up' => [
                        'name'       => '⬆️ Remplissage Haut',
                        'created'    => '01-01-2025',
                        'updated'    => '01-01-2025',
                        'version'    => '5.0.0',
                        'is_default' => false,
                        'settings'   => [
                            'custom_button'        => 'on',
                            'button_text_color'    => 'var(--kd-couleur-blanche)',
                            'button_bg_color'      => 'var(--kd-couleur-lien)',
                            'button_border_width'  => '1px',
                            'button_border_color'  => 'var(--kd-couleur-lien)',
                            'button_border_radius' => '0px',
                            'button_use_icon'      => 'off',
                            'module_class'         => 'btn-slide-up',
                        ],
                    ],

                    'kd-btn-arrow-tab' => [
                        'name'       => '🔖 Onglet Flèche',
                        'created'    => '01-01-2025',
                        'updated'    => '01-01-2025',
                        'version'    => '5.0.0',
                        'is_default' => false,
                        'settings'   => [
                            'custom_button'        => 'on',
                            'button_text_color'    => 'var(--kd-couleur-blanche)',
                            'button_bg_color'      => 'var(--kd-couleur-lien)',
                            'button_border_width'  => '0px',
                            'button_use_icon'      => 'off',
                            'module_class'         => 'btn-arrow-tab',
                        ],
                    ],

                    'kd-btn-shadow-lift' => [
                        'name'       => '🌑 Élévation Ombre',
                        'created'    => '01-01-2025',
                        'updated'    => '01-01-2025',
                        'version'    => '5.0.0',
                        'is_default' => false,
                        'settings'   => [
                            'custom_button'        => 'on',
                            'button_text_color'    => 'var(--kd-couleur-blanche)',
                            'button_bg_color'      => 'var(--kd-couleur-fond)',
                            'button_border_width'  => '0px',
                            'button_border_radius' => '0px',
                            'button_use_icon'      => 'off',
                            'module_class'         => 'btn-shadow-lift',
                        ],
                    ],

                    'kd-btn-border-grow' => [
                        'name'       => '📐 Bordure Croissante',
                        'created'    => '01-01-2025',
                        'updated'    => '01-01-2025',
                        'version'    => '5.0.0',
                        'is_default' => false,
                        'settings'   => [
                            'custom_button'        => 'on',
                            'button_text_color'    => 'var(--kd-couleur-lien)',
                            'button_bg_color'      => 'transparent',
                            'button_border_width'  => '0px',
                            'button_border_radius' => '0px',
                            'button_use_icon'      => 'off',
                            'module_class'         => 'btn-border-grow',
                        ],
                    ],

                    'kd-btn-fill-center' => [
                        'name'       => '🎯 Remplissage Central',
                        'created'    => '01-01-2025',
                        'updated'    => '01-01-2025',
                        'version'    => '5.0.0',
                        'is_default' => false,
                        'settings'   => [
                            'custom_button'        => 'on',
                            'button_text_color'    => 'var(--kd-couleur-lien)',
                            'button_bg_color'      => 'transparent',
                            'button_border_width'  => '2px',
                            'button_border_color'  => 'var(--kd-couleur-lien)',
                            'button_border_radius' => '0px',
                            'button_use_icon'      => 'off',
                            'module_class'         => 'btn-fill-center',
                        ],
                    ],

                    'kd-btn-u-link-arrow' => [
                        'name'       => '→ Lien avec flèche',
                        'created'    => '01-01-2025',
                        'updated'    => '01-01-2025',
                        'version'    => '5.0.0',
                        'is_default' => false,
                        'settings'   => [
                            'custom_button'        => 'on',
                            'button_text_color'    => 'var(--kd-couleur-lien)',
                            'button_bg_color'      => 'transparent',
                            'button_border_width'  => '0px',
                            'button_use_icon'      => 'off',
                            'module_class'         => 'u-link-arrow',
                        ],
                    ],

                ],
            ],
        ],
    ];
}

function kd_inject_divi5_button_presets() : void {

    if ( ! function_exists( 'et_get_option' ) ) {
        return;
    }

    $option_key      = 'et_divi_builder_global_presets';
    $version_key     = 'kd_divi5_presets_version';
    $current_version = '1.0.0';
    // ⚠️ Incrémenter cette version pour forcer la mise à jour
    // sur tous les sites clones lors d'un déploiement git.

    $installed_version = get_option( $version_key, '' );

    if ( $installed_version === $current_version ) {
        return; // Déjà à jour
    }

    $existing = get_option( $option_key, [] );
    $presets  = kd_build_divi5_button_presets();

    if ( empty( $existing ) ) {
        // Première installation : injecter tout
        update_option( $option_key, $presets );
    } else {
        // Installation existante : fusionner en préservant les presets client
        // Seuls les presets préfixés kd- sont mis à jour
        foreach ( $presets['module']['et_pb_button']['presets'] as $key => $preset ) {
            $existing['module']['et_pb_button']['presets'][ $key ] = $preset;
        }
        update_option( $option_key, $existing );
    }

    update_option( $version_key, $current_version );
}

// Déclencheur 1 : activation du thème (nouvelle installation cliente)
add_action( 'after_switch_theme', 'kd_inject_divi5_button_presets' );

// Déclencheur 2 : chargement admin (rattrape les déploiements git
// sans réactivation du thème sur les sites existants)
add_action( 'admin_init', 'kd_inject_divi5_button_presets' );
```

#### 3.2 Référencer le nouveau fichier dans functions.php

- [ ] Ouvrir `functions.php`
- [ ] Remplacer la ligne `require_once get_stylesheet_directory() . '/assets/buttons/divi-button-presets.php';`
- [ ] Par : `require_once get_stylesheet_directory() . '/assets/buttons/divi5-button-presets.php';`
- **Priorité :** Critique
- **Fichier concerné :** `functions.php`
- **Durée estimée :** 5min

#### 3.3 Valider l'injection des presets sur le staging

- [ ] Vider l'option `et_divi_builder_global_presets` en base (pour simuler une première installation)
- [ ] Charger une page admin et vérifier que les presets apparaissent dans l'éditeur Divi 5
- [ ] Ouvrir un module Bouton dans Divi 5 et vérifier que les 9 presets kd-com sont disponibles
- [ ] Vérifier que les couleurs s'appliquent correctement via les variables CSS
- [ ] Si Divi 5 n'accepte pas `var(--kd-couleur-lien)` dans les champs couleur : remplacer par les valeurs hex et ajouter `kd_inject_divi5_button_presets()` dans les hooks `update_option_couleur_*` existants
- **Priorité :** Critique
- **Durée estimée :** 1h

#### 3.4 Tester le système de versioning (mise à jour du gabarit)

- [ ] Modifier un preset dans `kd_build_divi5_button_presets()` (ex : changer un border-radius)
- [ ] Incrémenter `$current_version` de `1.0.0` à `1.1.0`
- [ ] Recharger une page admin et vérifier que le preset est mis à jour en base
- [ ] Vérifier qu'un preset créé manuellement par le "client" n'est pas supprimé
- **Priorité :** Haute
- **Durée estimée :** 30min

> **Note :** Les boutons Gutenberg sont déjà gérés par `assets/buttons/button-styles.php` via `register_block_style`. Ce fichier est inchangé et continue de fonctionner automatiquement — aucune action requise.

---

### Phase 4 — Reconstruction des pages dans Divi 5 (Semaines 3-5)

#### 4.1 Inventaire des pages existantes

- [ ] Exporter la liste complète des pages depuis WordPress (Pages > Tous)
- [ ] Identifier les pages construites avec Divi 4 (shortcodes `[et_pb_*]` dans le contenu)
- [ ] Classer par priorité : pages les plus visitées en premier
- [ ] Identifier les pages contenant des shortcodes custom à conserver
- **Priorité :** Haute
- **Durée estimée :** 1h

#### 4.2 Stratégie par page

Pour chaque page identifiée, choisir une stratégie :

| Stratégie | Quand l'utiliser |
|---|---|
| **Mode compatibilité** | Pages complexes, peu de trafic, pas de refonte prévue |
| **Migration complète** | Pages importantes, nouvelles pages, refontes prévues |

- [ ] Documenter la stratégie choisie pour chaque page
- **Durée estimée :** 30min

#### 4.3 Migration des pages — Lot 1 (pages simples)

- [ ] Identifier les 5-10 pages les plus simples à migrer
- [ ] Pour chaque page : ouvrir dans Divi 5, reconstruire la mise en page avec les blocs natifs Divi 5
- [ ] Pour les shortcodes custom (`[display_events]`, `[faq_liste]`, `[slider_accueil]`, etc.) : utiliser le bloc **Code** de Divi 5 — ils fonctionnent exactement comme avant, sans aucune modification
- [ ] Pour les boutons animés : utiliser les presets kd-com disponibles dans l'éditeur Divi 5
- [ ] Valider l'affichage sur desktop et mobile
- **Priorité :** Haute
- **Durée estimée :** 4-8h selon le nombre de pages

#### 4.4 Migration des pages — Lot 2 (pages principales)

- [ ] Page d'accueil (si refonte souhaitée)
- [ ] Pages de services / prestations
- [ ] Pages avec événements ou FAQ
- [ ] Pages portfolio / réalisations
- **Priorité :** Haute
- **Durée estimée :** 8-16h selon le nombre de pages

#### 4.5 Migration des pages — Lot 3 (pages complexes)

- [ ] Pages avec sliders personnalisés
- [ ] Pages avec mises en page très spécifiques
- [ ] Pages laissées en mode compatibilité Divi 4 si non prioritaires
- **Priorité :** Moyenne
- **Durée estimée :** 4-8h

---

### Phase 5 — Tests et mise en production (Semaine 6)

#### 5.1 Tests fonctionnels complets sur staging

- [ ] Tester tous les shortcodes custom sur les pages migrées
- [ ] Tester le formulaire d'inscription newsletter (`[brevo_newsletter_form]`)
- [ ] Tester l'affichage des événements (`[display_events]`)
- [ ] Tester la FAQ (`[faq_liste]`, `[faq_complete]`)
- [ ] Tester le portfolio et l'ordre d'affichage
- [ ] Tester les sliders (hero, contenu, carrousel)
- [ ] Tester Tarteaucitron (consentement cookies)
- [ ] Tester les animations AOS sur blocs Divi 5
- [ ] Tester les 9 presets de boutons kd-com dans l'éditeur Divi 5
- [ ] Tester les boutons animés en front-end (Divi 5 et Gutenberg)
- [ ] Tester la galerie lightbox et masonry
- [ ] Tester la synchronisation des couleurs (modifier une couleur dans les réglages et vérifier l'impact en front ET dans les presets boutons)
- [ ] Simuler une nouvelle installation (dupliquer le gabarit git, activer le thème) et vérifier l'injection automatique des presets
- [ ] Tester le système de versioning : incrémenter la version et vérifier la mise à jour automatique
- [ ] Tester le tableau de bord admin (widget kd-com)
- [ ] Tester les restrictions de rôle éditeur
- **Priorité :** Critique
- **Durée estimée :** 4-5h

#### 5.2 Tests de performance

- [ ] Mesurer les Core Web Vitals avant migration (production actuelle)
- [ ] Mesurer les Core Web Vitals après migration (staging)
- [ ] Comparer et valider l'amélioration attendue avec Divi 5
- [ ] Vérifier le chargement des assets (CSS/JS) dans les DevTools
- **Priorité :** Haute
- **Durée estimée :** 1h

#### 5.3 Tests cross-browser et responsive

- [ ] Chrome desktop
- [ ] Firefox desktop
- [ ] Safari desktop
- [ ] Chrome mobile (Android)
- [ ] Safari mobile (iOS)
- **Priorité :** Haute
- **Durée estimée :** 1h

#### 5.4 Nettoyage final du code

- [ ] Vérifier qu'il ne reste aucune référence à `divi-button-presets.php` dans `functions.php`
- [ ] Vérifier qu'il ne reste aucun appel à `kd_sync_divi_palette()` dans `functions.php`
- [ ] Vérifier qu'il ne reste aucun appel à `et_builder_custom_colors`
- [ ] Vérifier qu'il ne reste aucun appel à `update_option('et_divi', ...)`
- [ ] Supprimer les commentaires et code mort éventuel
- **Priorité :** Moyenne
- **Durée estimée :** 1h

#### 5.5 Mise en production

- [ ] Sauvegarde complète du site de production (base de données + fichiers)
- [ ] Activer le mode maintenance
- [ ] Appliquer toutes les modifications PHP sur la production
- [ ] Mettre à jour Divi vers la version 5 sur la production
- [ ] Vérifier l'affichage du site
- [ ] Vérifier l'injection automatique des presets boutons dans l'éditeur Divi 5
- [ ] Désactiver le mode maintenance
- [ ] Monitorer les logs PHP pendant 24h
- [ ] Valider le bon fonctionnement de tous les modules
- **Priorité :** Critique
- **Durée estimée :** 2h

---

## 📅 Rétro-planning

| Semaine | Dates | Phase | Tâches clés | Livrable |
|---|---|---|---|---|
| **Semaine 1** | J+0 à J+7 | Préparation + Corrections critiques | Staging, activation Divi 5, audit initial, corrections PHP 2.1 à 2.4 | Staging fonctionnel, fichiers PHP corrigés |
| **Semaine 2** | J+8 à J+14 | Corrections + Presets Divi 5 | Finir corrections PHP 2.5 à 2.6, créer `divi5-button-presets.php`, valider l'injection automatique | Tous les presets disponibles automatiquement à l'installation |
| **Semaine 3** | J+15 à J+21 | Migration pages Lot 1 | Inventaire pages, migrer les pages simples | 5-10 pages migrées dans Divi 5 |
| **Semaine 4** | J+22 à J+28 | Migration pages Lot 2 | Pages principales (accueil, services, événements, FAQ) | Pages principales migrées |
| **Semaine 5** | J+29 à J+35 | Migration pages Lot 3 | Pages complexes ou mode compatibilité | Toutes les pages traitées |
| **Semaine 6** | J+36 à J+42 | Tests + Production | Tests complets, performance, nettoyage, mise en prod | Site en production sous Divi 5 |

---

## 🗂️ Récapitulatif des fichiers impactés

| Fichier | Action | Priorité | Semaine |
|---|---|---|---|
| `assets/buttons/divi-button-presets.php` | ❌ Supprimer entièrement | Critique | S1 |
| `assets/buttons/divi5-button-presets.php` | ✅ Créer (nouveau fichier) | Critique | S2 |
| `functions.php` — `require_once divi-button-presets.php` | 🔄 Remplacer par `divi5-button-presets.php` | Critique | S2 |
| `functions.php` — bloc `kd_sync_divi_palette()` | ❌ Supprimer | Critique | S1 |
| `functions.php` — filtre `et_builder_custom_colors` | ❌ Supprimer | Critique | S1 |
| `functions.php` — `update_option('et_divi', ...)` | ❌ Supprimer | Critique | S1 |
| `functions.php` — surcharge CSS accent Divi 4 | ❌ Supprimer | Haute | S1 |
| `functions.php` | ✅ Ajouter `kd_inject_theme_css_vars()` | Critique | S1 |
| `functions.php` | ✅ Ajouter surcharge accent Divi 5 | Haute | S1 |
| `includes/dashboard_block.php` | 🔄 Sécuriser `et_get_option()` | Moyenne | S1 |
| `includes/admin_editor.php` | 🔄 Vérifier slugs Divi 5 | Moyenne | S2 |
| `js/aos-divi.js` | 🔄 Ajouter classes Divi 5 | Moyenne | S2 |
| Pages du site | 🔄 Migration progressive | Haute | S3-S5 |

---

## 🟢 Fichiers à ne pas toucher

Ces fichiers sont **100% indépendants de Divi** et ne nécessitent aucune modification :

- `assets/buttons/button-styles.php` — gère les boutons Gutenberg via `register_block_style`, déjà automatique
- `assets/buttons/button-style-editor.js` — aperçu Gutenberg, inchangé
- `module_admin/newsletter.php` et tous les fichiers newsletter
- `module_admin/cpt_evenements.php`
- `module_admin/cpt_faq.php`
- `module_admin/cpt_portfolio.php`
- `module_front/cpt_evenements.php`
- `module_front/cpt_faq.php`
- `module_front/cpt_portfolio.php`
- `includes/reglages_site/` (tous les fichiers)
- `includes/reglages_site/tarteaucitron/` (tous les fichiers)
- `includes/image_categorie.php`
- `includes/supp-medias.php`
- `js/gallery-lightbox.js`
- `js/gallery-masonry.js`
- `js/animation-sidebar.js`
- `js/aos-frontend.js`
- `module_admin/acf_fields/` (tous les fichiers)
- `module_admin/newsletter_abonne.php`
- `module_admin/galerie_lightbox.php`
- `module_admin/galerie_masonry.php`
- `module_admin/shortcode_*.php`
- `module_admin/slider_*.php`

---

## ⚠️ Points de vigilance

### Shortcodes custom — aucun changement nécessaire

Les shortcodes `[display_events]`, `[faq_liste]`, `[faq_complete]`, `[slider_accueil]`, `[slider_contenu]`, `[brevo_newsletter_form]`, `[show_childpages]`, `[affichage_page_spe]`, `[video_aleatoire]`, etc. **fonctionnent exactement comme avant** dans Divi 5 via le bloc Code. Ils n'alourdissent pas le site et ne nécessitent aucune réécriture.

### Système de versioning des presets — comment l'utiliser

Quand tu veux modifier ou ajouter un preset dans une future version du gabarit git :

1. Modifier `kd_build_divi5_button_presets()` dans `divi5-button-presets.php`
2. Incrémenter `$current_version` (ex : `1.0.0` → `1.1.0`)
3. Committer et pousser sur git
4. Au prochain chargement de l'admin sur chaque site clone, la mise à jour s'applique automatiquement sans toucher aux presets créés par les clients

### Variables CSS vs valeurs hex dans les presets

Les presets utilisent `var(--kd-couleur-lien)` au lieu de valeurs hex pour que les boutons se mettent à jour automatiquement quand un client change ses couleurs dans les réglages kd-com. Si Divi 5 n'accepte pas les variables CSS dans ses champs couleur, remplacer par les valeurs hex et ajouter `kd_inject_divi5_button_presets()` dans les hooks `update_option_couleur_*` de `functions.php`.

### Pages en mode compatibilité Divi 4

Les pages non migrées s'affichent correctement en front grâce au mode compatibilité de Divi 5. Il n'y a aucune urgence à les migrer toutes d'un coup.

### Sauvegarde obligatoire avant chaque étape critique

Ne jamais modifier la production sans sauvegarde préalable et validation sur staging.

### Tester la palette de couleurs après correction PHP

Après avoir remplacé `kd_sync_divi_palette()` par le nouveau système CSS vars, vérifier que les couleurs s'appliquent correctement en front-end en modifiant une couleur dans les réglages du thème.