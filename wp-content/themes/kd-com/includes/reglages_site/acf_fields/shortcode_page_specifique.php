<?php
if( function_exists('acf_add_local_field_group') ) {
    global $acf_module_fields;
    $acf_module_fields[] = array(
        'key' => 'group_sous_pages',
        'title' => 'Module page sépcifique',
        'fields' => array(
            array(
                'key' => 'field_page_specifique',
                'label' => 'Page spécifique',
                'name' => 'shortcode_page_specifique',
                'type' => 'true_false',
                'instructions' => "Module de gestion de l'affichage des pages spécifiques. Exemple d'utilisation
                [affichage_page_spe id=\"123\"]",
                'message' => "module de gestion de l'affichage de page spécifique",
                'default_value' => 0,
                'ui' => 1,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'reglages-site-modules',
                ),
            ),
        ),
    );
    acf_add_local_field_group($acf_module_fields[count($acf_module_fields)-1]);
}