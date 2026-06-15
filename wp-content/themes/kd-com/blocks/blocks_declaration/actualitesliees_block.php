<?php
  // Inclure les champs ACF pour le bloc
    require_once get_stylesheet_directory() . '/blocks/acf_fields/acf-actualitesliees.php';
  
  //register ACF bloc type
  acf_register_block_type(array(
      'name'              => 'actualite-liees',
      'title'             => 'Actualités liées',
      'description'       => "Affiche les actualités liées",
      'render_callback'   => function($block, $content = '', $is_preview = false) {
          // Inclure le template
            include get_stylesheet_directory() . '/blocks/my_block/actualitesliees.php';
      },
      'category'          => 'formatting',
      'icon'              => 'grid-view',
      'keywords'          => array('actualités', 'articles liés', 'actualités liées'),
      'enqueue_assets'    => function() {
          wp_enqueue_style( 
                'capitaine-blocks', 
                get_bloginfo( 'stylesheet_directory' ) . '/css/blocks.css' 
            );
        },
          'supports'          => array(
            'align'           => array( 'full' ),
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
                  'nombre_darticles' => 3 // Nombre d'articles par défaut
              )
          )
      )
  ));