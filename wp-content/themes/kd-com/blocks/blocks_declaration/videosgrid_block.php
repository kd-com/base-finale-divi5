<?php
 // Inclure les champs ACF pour le bloc
  require_once get_stylesheet_directory() . '/blocks/acf_fields/acf-videosgrid.php';
  
  // Enregistrer le bloc ACF directement
  acf_register_block_type( array(
    'name'            => 'youtube-grid',
    'title'           => __('Vidéos grid'),
    'description'     => 'Affichage de vidéos en grille avec système intelligent RGPD/Natif',
    'instructions' => 'Bloc ACF pour afficher une grille de vidéos YouTube avec options avancées',
    'render_callback' => function($block, $content = '', $is_preview = false) {
      // Inclure le template
      include get_stylesheet_directory() . '/blocks/my_block/videosgrid.php';
    },
    'category'        => 'media',
    'icon'            => 'video',
    'keywords'        => array( 'youtube', 'video', 'grid', 'grille', 'rgpd', 'tarteaucitron' ),
    'enqueue_assets'    => function() {
        wp_enqueue_style( 
          'capitaine-blocks', 
          get_bloginfo( 'stylesheet_directory' ) . '/css/blocks.css' 
        );
      },
    'supports'        => array(
      'align' => array( 'wide', 'full' ),
      'anchor' => true,
      'customClassName' => true,
    ),
    'example'         => array(
      'attributes' => array(
        'mode' => 'preview',
        'data' => array(
          'youtube_grid_title' => 'Nos vidéos YouTube',
          'youtube_grid_columns' => '3',
          'youtube_grid_videos' => array(
            array(
              'video_id' => 'dQw4w9WgXcQ',
              'video_title' => 'Vidéo exemple 1',
            ),
            array(
              'video_id' => 'dQw4w9WgXcQ',
              'video_title' => 'Vidéo exemple 2',
            ),
            array(
              'video_id' => 'dQw4w9WgXcQ',
              'video_title' => 'Vidéo exemple 3',
            ),
          ),
        ),
      ),
    ),
  ) );