<?php
/**
 * Enregistrement des champs ACF pour le CPT Slider Contenu
 * Permet de sélectionner une page ou un article et personnaliser l'affichage
 */

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
    'key' => 'group_slider_contenu',
    'title' => 'Slider contenu - Configuration',
    'description' => 'Shortcode : [acf_block_slider_contenu]',
    'fields' => array(
        array(
            'key' => 'field_slider_contenu_page',
            'label' => 'Page liée',
            'name' => 'page_liee',
            'type' => 'post_object',
            'instructions' => 'Sélectionnez une page à afficher (ou un article ci-dessous)',
            'required' => 0,
            'conditional_logic' => 0,
            'post_type' => array('page'),
            'taxonomy' => '',
            'allow_null' => 1,
            'multiple' => 0,
            'return_format' => 'object',
            'ui' => 1,
        ),
        array(
            'key' => 'field_slider_contenu_article',
            'label' => 'Article lié',
            'name' => 'article_lie',
            'type' => 'post_object',
            'instructions' => 'Ou sélectionnez un article à afficher',
            'required' => 0,
            'conditional_logic' => 0,
            'post_type' => array('post'),
            'taxonomy' => '',
            'allow_null' => 1,
            'multiple' => 0,
            'return_format' => 'object',
            'ui' => 1,
        )
    ),
    'location' => array(
        array(
            array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'slider_contenu',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => 'Configuration du slider de contenu (pages et articles)',
));

endif;