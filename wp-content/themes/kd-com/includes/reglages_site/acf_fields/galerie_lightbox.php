<?php
if( function_exists('acf_add_local_field_group') ) {
    global $acf_module_fields;
    $acf_module_fields[] = array(
        'key' => 'group_galery_lightbox',
        'title' => 'Module Galerie lightbox',
        'fields' => array(
            array(
                'key' => 'field_galerie_lightbox_module',
                'label' => 'Galerie lightbox',
                'name' => 'galerie_lightbox',
                'type' => 'true_false',
                'instructions' => "Active l'option lightbox sur les galerie gutenberg",
                'message' => 'Activer le module galerie lightbox',
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