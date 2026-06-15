<?php
if( function_exists('acf_add_local_field_group') ) {
    global $acf_module_fields;
    $acf_module_fields[] = array(
        'key' => 'group_slider_carrousel_page',
        'title' => "Module carrousel de page",
        'fields' => array(
            array(
                'key' => 'field_slider_carrousel_page',
                'label' => "Slider carrousel de page",
                'name' => 'slider_carrousel_page',
                'type' => 'true_false',
                'instructions' => "Affiche les pages enfants d'une page parente en ordre aléatoire. Pour l'utilisation du module ajouter le shortcode [slider_carousel_pages parent_id=\"123\"] dans un module code dans le divi builder",
                'message' => "Activer le module slider carrousel de page",
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