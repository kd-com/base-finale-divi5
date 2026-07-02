<?php
/**
 * Module "Galerie lightbox" — pour les galeries Gutenberg (wp-block-gallery)
 * 
 * Module indépendant : ajoute l'ouverture en lightbox sur les galeries
 * d'images Gutenberg du site (articles, pages...).
 * Peut être activé seul, avec le module portfolio, ou pas du tout.
 * 
 * Toggle : case "module_galerie_lightbox" dans la page "reglages-site-modules"
 * (même mécanisme que le module portfolio : activation/désactivation centralisée
 * gérée par reglages_modules.php, lue ici via get_option — PAS via get_field,
 * cette page n'étant pas une page d'options ACF).
 * 
 * Emplacement : wp-content/themes/kd-com/module_admin/galerie_lightbox.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─────────────────────────────────────────────
// 1. Enregistrement + chargement conditionnel du script
// ─────────────────────────────────────────────
function kd_enqueue_gallery_lightbox_gutenberg() {

    // wp_register_script est sans risque même si déjà enregistré ailleurs :
    // WordPress ignore silencieusement les appels suivants sur le même handle.
    if ( ! wp_script_is( 'gallery-lightbox', 'registered' ) ) {
        wp_register_script(
            'gallery-lightbox',
            get_stylesheet_directory_uri() . '/js/gallery-lightbox.js',
            array(),
            '1.1',
            true // dans le footer
        );
    }

    // Chargement automatique en front uniquement si le module est activé
    // (case à cocher gérée par reglages_modules.php, nom : module_galerie_lightbox)
    if ( get_option( 'module_galerie_lightbox', '0' ) === '1' ) {
        wp_enqueue_script( 'gallery-lightbox' );
    }
}
add_action( 'wp_enqueue_scripts', 'kd_enqueue_gallery_lightbox_gutenberg' );