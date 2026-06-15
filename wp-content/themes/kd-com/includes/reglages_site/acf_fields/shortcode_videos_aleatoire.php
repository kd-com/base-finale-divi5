<?php
if( function_exists('acf_add_local_field_group') ) {
    global $acf_module_fields;
    $acf_module_fields[] = array(
        'key' => 'group_sous_pages',
        'title' => 'Module Vidéo aléatoire',
        'fields' => array(
            array(
                'key' => 'field_videos_aleatoire',
                'label' => 'Vidéo aléatoire',
                'name' => 'shortcode_videos_aleatoire',
                'type' => 'true_false',
                'instructions' => "Module de gestion de l'affichage des vidéos aléatoires. Soit par catégorie, soit par CPT, soit par sous page. Exemple d'utilisation
                [video_aleatoire type=\"cpt\" post_type=\"films\" category=\"action\"] [video_aleatoire type=\"posts\" category=\"actualites\"] [video_aleatoire type=\"subpages\" parent_page=\"123\"]",
                'message' => "module de gestion de l'affichage des vidéos aléatoires",
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