<?php
// Inclure les champs ACF pour le bloc
require_once get_stylesheet_directory() . '/blocks/acf_fields/acf-galerieslider.php';

// register ACF bloc type
acf_register_block_type(array(
    'name'              => 'galerie-slider',
    'title'             => 'Galerie Slider',
    'description'       => "Affiche une galerie d'images sous forme de slider",
    'render_callback'   => function($block, $content = '', $is_preview = false) {
        // Inclure le template
        include get_stylesheet_directory() . '/blocks/my_block/galerieslider.php';
    },
    'category'          => 'formatting',
    'icon'              => 'images-alt2',
    'keywords'          => array('galerie', 'slider', 'images'),
    'enqueue_assets'    => function() {
        wp_enqueue_style(
            'capitaine-blocks',
            get_bloginfo('stylesheet_directory') . '/css/blocks.css'
        );
        wp_enqueue_script(
            'capitaine-blocks-slider',
            get_bloginfo('stylesheet_directory') . '/js/sliderblock.js',
            array('jquery'),
            null,
            true
        );
    },
    'supports'          => array(
        'align'           => array('full'),
        'jsx'             => true,
        'color'           => array(
            'background' => false,
            'gradients'  => false,
            'text'       => true,
            'link'       => true,
        ),
    ),
    'example'           => array(
        'attributes' => array(
            'mode' => 'preview',
            'data' => array(
                'nombre_dimages' => 5 // Nombre d'images par défaut
            )
        )
    )
));

