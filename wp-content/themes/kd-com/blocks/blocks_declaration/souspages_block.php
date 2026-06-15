<?php
// Charger les champs ACF (local field group) et enregistrer le bloc de façon robuste
  require_once get_stylesheet_directory() . '/blocks/acf_fields/acf-souspages.php';

  function kd_sous_pages_acf_block_types() {
    acf_register_block_type(array(
      'name'              => 'sous-pages',
      'title'             => 'Sous-pages',
      'description'       => 'Affiche les sous-pages d\'une page sélectionnée',
      'render_callback'   => function($block, $content = '', $is_preview = false) {
  // Inclure le template
  include get_stylesheet_directory() . '/blocks/my_block/souspages.php';
      },
      'category'          => 'formatting',
      'icon'              => 'grid-view',
      'keywords'          => array('sous-pages', 'pages-enfant', 'navigation'),
      'enqueue_assets'    => function() {
        wp_enqueue_style( 
          'capitaine-blocks', 
          get_bloginfo( 'stylesheet_directory' ) . '/css/blocks.css' 
        );
      },
        'supports'          => array(
        'align'         => array( 'wide', 'full' ),
        'mode'          => false,
        'jsx'           => true,
        'color'         => array(
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
            'page_parente' => get_option('page_on_front') // Page d'accueil par défaut
          )
        )
      )
    ));
  }

  if ( function_exists( 'acf_register_block_type' ) ) {
    kd_sous_pages_acf_block_types();
  } else {
    add_action( 'acf/init', 'kd_sous_pages_acf_block_types' );
  }