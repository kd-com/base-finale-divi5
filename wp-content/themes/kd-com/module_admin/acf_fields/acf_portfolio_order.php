<?php
/**
 * Champ ACF - Ordre d'affichage des réalisations (CPT project / Divi)
 * 
 * À inclure depuis cpt_portfolio.php :
 * include get_stylesheet_directory() . '/module_admin/acf_fields/acf_portfolio_order.php';
 */

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
    return;
}

acf_add_local_field_group( array(
    'key'    => 'group_portfolio_order',
    'title'  => 'Ordre d\'affichage des réalisations',
    'fields' => array(

        // --- Champ principal : type d'ordre ---
        array(
            'key'           => 'field_portfolio_orderby',
            'label'         => 'Trier les réalisations par',
            'name'          => 'portfolio_orderby',
            'type'          => 'select',
            'instructions'  => 'Choisissez le critère de tri appliqué aux modules Divi affichant les réalisations (Portfolio, Filterable Portfolio, Post Slider…).',
            'choices'       => array(
                'date'     => '📅 Date de publication (plus récent en premier)',
                'date_asc' => '📅 Date de publication (plus ancien en premier)',
                'cat'      => '🗂️ Catégorie (ordre alphabétique)',
                'rand'     => '🔀 Aléatoire',
                'title'    => '🔤 Titre (A → Z)',
                'title_desc' => '🔤 Titre (Z → A)',
                'menu_order' => '🔢 Ordre manuel (drag & drop)',
            ),
            'default_value' => 'date',
            'allow_null'    => 0,
            'ui'            => 1,
            'return_format' => 'value',
            'wrapper'       => array( 'width' => '60' ),
        ),

        // --- Nombre de posts par page (optionnel) ---
        array(
            'key'           => 'field_portfolio_posts_per_page',
            'label'         => 'Nombre de réalisations à afficher',
            'name'          => 'portfolio_posts_per_page',
            'type'          => 'number',
            'instructions'  => 'Laissez vide ou à -1 pour tout afficher. Remplace la valeur saisie dans le module Divi si renseignée.',
            'default_value' => '',
            'min'           => -1,
            'max'           => 200,
            'placeholder'   => 'Valeur du module Divi',
            'wrapper'       => array( 'width' => '40' ),
        ),

    ),
    'location' => array(
        array(
            array(
                'param'    => 'options_page',
                'operator' => '==',
                'value'    => 'reglages-site-modules',
            ),
        ),
    ),
    'menu_order'         => 10,
    'position'           => 'normal',
    'style'              => 'default',
    'label_placement'    => 'top',
    'instruction_placement' => 'label',
    'active'             => true,
    // Ce groupe ne s'affiche que si le module portfolio est activé
    // (conditionnel géré côté affichage dans reglages_modules.php)
) );