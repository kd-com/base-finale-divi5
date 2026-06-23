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