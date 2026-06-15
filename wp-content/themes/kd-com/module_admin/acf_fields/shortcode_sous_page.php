<?php
/**
 * Champs ACF pour les pages avec sous-pages
 * Ce fichier ajoute le champ "chapeau" aux pages qui ont des enfants
 */

if( function_exists('acf_add_local_field_group') ) {
    acf_add_local_field_group(array(
        'key' => 'group_page_sous_pages',
        'title' => 'Configuration Sous-pages',
        'fields' => array(
            array(
                'key' => 'field_chapeau_de_la_page',
                'label' => 'Chapeau de la page',
                'name' => 'chapeau_de_la_page',
                'type' => 'wysiwyg',
                'instructions' => 'Si ce champ est rempli et que vous avez du contenu, les sous-pages seront affichées en dessous d\'un chapeau et avant le contenu de page, sinon le contenu est avant les sous-pages.',
                'required' => 0,
                'default_value' => '',
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
                'delay' => 0,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'page',
                ),
            ),
        ),
        'menu_order' => 5,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
    ));
}