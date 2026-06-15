<?php 
// CREATION DU MODULE SLIDER PAGE D'ACCUEIL ET RÉFÉRENCE CLIENTS
function create_slider_accueil() {       


        // SLIDER PAGE D'ACCUEIL

  $labels = array(
    'name' => 'Slider page d\'accueil'
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'exclude_from_search' => true, // the important line here!
    'has_archive' => false,
          'show_in_rest' => true, //important !
          'supports' => array('title'),
          'menu_position' => 2,
          'menu_icon' => 'dashicons-tagcloud',

        );
  register_post_type('slider', $args);
}
add_action('init', 'create_slider_accueil' );

?>

