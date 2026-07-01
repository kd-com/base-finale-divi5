<?php
// Renommer le cpt projet
function projects_to_teams( $args, $post_type ) {
    if ( $post_type == 'project' ) {
        $args['rewrite']['slug'] = 'realisations';
        $args['menu_icon']       = 'dashicons-portfolio';
        $args['labels']          = array(
            'name'          => _x( 'Réalisations', 'Post Type General Name', 'textdomain' ),
            'singular_name' => _x( 'Réalisation',  'Post Type Singular Name', 'textdomain' ),
        );
    }
    return $args;
}
add_filter( 'register_post_type_args', 'projects_to_teams', 10, 2 );

add_filter( 'register_taxonomy_args', 'change_taxonomies_slug', 10, 2 );
function change_taxonomies_slug( $args, $taxonomy ) {
    if ( 'project_category' === $taxonomy ) {
        $args['rewrite']['slug'] = 'type-de-realisation';
    }
    if ( 'project_tag' === $taxonomy ) {
        $args['rewrite']['slug'] = 'filtre-de-realisation';
    }
    return $args;
}

// Champs ACF lien site internet (single project)
include get_stylesheet_directory() . '/module_admin/acf_fields/acf_portfolio_lien.php';

// Shortcode [portfolio_bouton_site] + body class
include get_stylesheet_directory() . '/module_front/portfolio_lien.php';

// Réglages ordre d'affichage (register_setting + injection JS dans la page modules)
include get_stylesheet_directory() . '/module_admin/portfolio_order_settings.php';

// Filtre pre_get_posts — applique l'ordre en front-end
include get_stylesheet_directory() . '/module_front/cpt_portfolio.php';

// Shortcode [projets_galerie] — grille + lightbox (piloté par les réglages)
include get_stylesheet_directory() . '/module_front/projets_galerie.php';