<?php
/**
 * Champ ACF Galerie + désactivation conditionnelle de l'éditeur (CPT project)
 * 
 * Champ ACF :
 *  - projet_galerie (gallery) : galerie d'images utilisée par la lightbox
 *                                du shortcode [projets_galerie]
 * 
 * Comportement lié aux réglages (portfolio_order_settings.php) :
 *  - Si "Activer la galerie lightbox" est coché (option portfolio_lightbox_active = 1) :
 *      → l'éditeur de contenu (Gutenberg + TinyMCE) est retiré de la fiche projet
 *      → ne restent que : titre + image à la une + champs ACF (galerie, lien site)
 *  - Si la case est décochée :
 *      → l'éditeur de contenu classique redevient disponible normalement
 * 
 * Emplacement : wp-content/themes/kd-com/module_admin/acf_fields/acf_portfolio_galerie.php
 * 
 * Inclure depuis cpt_portfolio.php :
 *   include get_stylesheet_directory() . '/module_admin/acf_fields/acf_portfolio_galerie.php';
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─────────────────────────────────────────────
// 1. Champ ACF Galerie
// ─────────────────────────────────────────────
if ( function_exists( 'acf_add_local_field_group' ) ) :

    acf_add_local_field_group( array(
        'key'   => 'group_portfolio_galerie',
        'title' => 'Galerie photo',
        'fields' => array(

            array(
                'key'           => 'field_portfolio_galerie',
                'label'         => 'Galerie du projet',
                'name'          => 'projet_galerie',
                'type'          => 'gallery',
                'instructions'  => 'Ajoutez les images qui s\'afficheront dans la lightbox au clic sur ce projet.',
                'required'      => 0,
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'library'       => 'all',
                'min'           => '',
                'max'           => '',
                'mime_types'    => 'jpg,jpeg,png,webp',
            ),

        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'project', // CPT Divi Portfolio (renommé "Réalisations" dans cpt_portfolio.php)
                ),
            ),
        ),
        'menu_order'            => 11,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
        'description'           => 'Gère la galerie d\'images affichée en lightbox pour le shortcode [projets_galerie].',
    ) );

endif;


// ─────────────────────────────────────────────
// 2. Désactivation conditionnelle de l'éditeur
//    (uniquement si la lightbox est activée)
// ─────────────────────────────────────────────

// Gutenberg
add_filter( 'use_block_editor_for_post_type', function ( $use_block_editor, $post_type ) {
    if ( $post_type === 'project' && (int) get_option( 'portfolio_lightbox_active', 0 ) === 1 ) {
        return false;
    }
    return $use_block_editor;
}, 10, 2 );

// Éditeur classique (TinyMCE) — retire complètement le champ contenu
add_action( 'init', function () {
    if ( (int) get_option( 'portfolio_lightbox_active', 0 ) === 1 ) {
        remove_post_type_support( 'project', 'editor' );
    }
}, 20 );