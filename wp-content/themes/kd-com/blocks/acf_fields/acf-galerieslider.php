<?php

// Définir les champs ACF pour le bloc galerie-slider
acf_add_local_field_group(array(
    'key' => 'group_galerieslider',
    'title' => 'Galerie Slider',
    'instruction' => "Affichage des actualités liées en selectionnant des articles",
    'fields' => array(
        array(
            'key' => 'field_nombre_dimages',
            'label' => 'Nombre d\'images',
            'name' => 'nombre_dimages',
            'type' => 'number',
            'default_value' => 5,
            'min' => 1,
            'max' => 20,
        ),
        array(
            'key' => 'field_images',
            'label' => 'Images',
            'name' => 'images',
            'type' => 'gallery',
            'instructions' => 'Ajouter des images pour le slider.',
            'required' => 1,
            'min' => 1,
            'max' => 20,
            'insert' => 'append',
            'library' => 'all',
            'min_width' => '',
            'min_height' => '',
            'min_size' => '',
            'max_width' => '',
            'max_height' => '',
            'max_size' => '',
            'mime_types' => '',
        ),
    ),
    'location' => array(
        array(
            array(
                'param' => 'block',
                'operator' => '==',
                'value' => 'acf/galerie-slider',
            ),
        ),
    ),
));

