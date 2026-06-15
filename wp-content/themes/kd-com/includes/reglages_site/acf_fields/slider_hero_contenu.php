<?php
if( function_exists('acf_add_local_field_group') ) {
    global $acf_module_fields;
    $acf_module_fields[] = array(
        'key' => 'group_hero_slider_contenu',
        'title' => "Module Slider page d'accueil pour affichage du contenu",
        'fields' => array(
            array(
                'key' => 'field_slider_page_daccueil',
                'label' => "Slider contenu",
                'name' => 'slider_hero_contenu',
                'type' => 'true_false',
                'instructions' => "Pour l'utilisation du module ajouter le shortcode [slider_contenu] dans un module code dans le divi builder",
                'message' => "Activer le module slider page d'accueil",
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
