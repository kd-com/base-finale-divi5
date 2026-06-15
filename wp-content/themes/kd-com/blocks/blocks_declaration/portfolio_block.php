<?php
/**
 * Déclaration du bloc ACF Portfolio
 * Style inspiré d'Olena Blog #6
 */

// Inclure les champs ACF pour le bloc
require_once get_stylesheet_directory() . '/blocks/acf_fields/acf-portfolio.php';

// Enregistrer le bloc ACF
function kd_portfolio_acf_block_types() {
    acf_register_block_type(array(
        'name'            => 'portfolio',
        'title'           => 'Portfolio / Réalisations',
        'description'     => 'Affiche les réalisations d\'une catégorie en grille 3 colonnes',
        'render_callback' => function($block, $content = '', $is_preview = false) {
            include get_stylesheet_directory() . '/blocks/my_block/portfolio.php';
        },
        'category'        => 'formatting',
        'icon'            => 'portfolio',
        'keywords'        => array('portfolio', 'réalisations', 'projets', 'galerie'),
        'enqueue_assets'  => function() {
            wp_enqueue_style(
                'capitaine-blocks',
                get_bloginfo('stylesheet_directory') . '/css/blocks.css'
            );
        },
        'supports'        => array(
            'align'           => array('wide', 'full'),
            'jsx'             => true,
            'color'           => array(
                'background' => false,
                'gradients'  => false,
                'text'       => false,
            ),
        ),
        'example'         => array(
            'attributes' => array(
                'mode' => 'preview',
                'data' => array(
                    'portfolio_titre_section' => 'Nos Réalisations',
                    'portfolio_nombre'        => 3,
                ),
            ),
        ),
    ));
}

if (function_exists('acf_register_block_type')) {
    kd_portfolio_acf_block_types();
} else {
    add_action('acf/init', 'kd_portfolio_acf_block_types');
}