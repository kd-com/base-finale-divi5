<?php 
// Dans functions.php
function enqueue_gallery_lightbox() {
  wp_enqueue_script(
    'gallery-lightbox',
    get_stylesheet_directory_uri() . '/js/gallery-lightbox.js',
    array(),
    '1.0',
    true // dans le footer
  );
}
add_action('wp_enqueue_scripts', 'enqueue_gallery_lightbox');