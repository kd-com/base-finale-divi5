<?php
if( function_exists('acf_add_local_field_group') ) {
    global $acf_module_fields;
    $acf_module_fields[] = array(
        'key' => 'group_sous_pages',
        'title' => 'Module Sous-pages',
        'fields' => array(
            array(
                'key' => 'field_sous_pages',
                'label' => 'Sous-pages',
                'name' => 'shortcode_sous_pages',
                'type' => 'true_false',
                'instructions' => "Module de gestion de l'affichage des sous pages avec deux shortcodes disponible : [show_childpages] pour afficher les sous pages en bas de page et [show_childpages_chapeau] pour afficher les sous pages avec chapeau en haut de page",
                'message' => "module de gestion de l'affichage des sous pages",
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
