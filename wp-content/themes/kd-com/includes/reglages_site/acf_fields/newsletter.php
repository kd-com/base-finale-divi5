<?php
if( function_exists('acf_add_local_field_group') ) {
    global $acf_module_fields;
    $acf_module_fields[] = array(
        'key' => 'group_evenenements',
        'title' => "Module d'affichage des événements",
        'fields' => array(
            array(
                'key' => 'field_newsletter_module',
                'label' => "newsletter",
                'name' => 'newsletter_module',
                'type' => 'true_false',
                'instructions' => "Pour l'utilisation du module ajouter le shortcode [newsletter_module] dans un module code dans le divi builder",
                'message' => "Activer le module newsletter",
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