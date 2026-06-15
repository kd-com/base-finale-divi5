<?php
if( function_exists('acf_add_local_field_group') ) {
    $aos_enabled = get_option('kd_com_aos_enabled', '1');
    if ($aos_enabled === '1') {
        acf_add_local_field_group(array(
            'key' => 'group_animation_global',
            'title' => 'Animation globale (AOS)',
            'fields' => array(
                array(
                    'key' => 'field_animation_type_global',
                    'label' => "Type d'animation",
                    'name' => 'animation_type_global',
                    'type' => 'select',
                    'instructions' => "Choisissez l'effet d'apparition pour les blocks core.",
                    'choices' => array(
                        'fade-up' => 'Fondu montant',
                        'fade-down' => 'Fondu descendant',
                        'fade-left' => 'Fondu depuis la gauche',
                        'fade-right' => 'Fondu depuis la droite',
                        'zoom-in' => 'Zoom avant',
                        'zoom-out' => 'Zoom arrière',
                        'none' => 'Aucune animation',
                    ),
                    'default_value' => 'fade-up',
                ),
                array(
                    'key' => 'field_animation_delay_global',
                    'label' => 'Délai entre les animations',
                    'name' => 'animation_delay_global',
                    'type' => 'select',
                    'instructions' => 'Temps entre chaque animation',
                    'choices' => array(
                        '0' => 'Pas de délai',
                        '100' => 'Court (0.1s)',
                        '200' => 'Moyen (0.2s)',
                        '300' => 'Long (0.3s)',
                    ),
                    'default_value' => '100',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post'
                    )
                ),
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page'
                    )
                ),
            ),
        ));
    }
}
