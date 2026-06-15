<?php
if( function_exists('acf_add_local_field_group') ) {
    global $acf_module_fields;
    $acf_module_fields[] = array(
        'key' => 'group_galery_masonry',
        'title' => 'Module Galerie Masonry',
        'fields' => array(
            array(
                'key' => 'field_galerie_masonry_module',
                'label' => 'Galerie Masonry',
                'name' => 'galerie_masonry',
                'type' => 'true_false',
                'instructions' => "Active l'option masonry sur les galerie gutenberg",
                'message' => 'Activer le module galerie masonry',
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