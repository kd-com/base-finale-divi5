<?php
if( function_exists('acf_add_local_field_group') ) {
    global $acf_module_fields;
    $acf_module_fields[] = array(
        'key' => 'group_portfolio',
        'title' => 'Module Portfolio',
        'fields' => array(
            array(
                'key' => 'field_portfolio',
                'label' => 'Portfolio',
                'name' => 'cpt_portfolio',
                'type' => 'true_false',
                'instructions' => "Permet de renommer le Custom Post Type 'Projets' en 'Portfolio' et d'ajouter des fonctionnalités spécifiques pour la gestion de votre portfolio.",
                'message' => 'Activer le module portfolio',
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
