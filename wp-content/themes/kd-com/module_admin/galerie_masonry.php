<?php
function enqueue_gallery_masonry() {
  wp_enqueue_script(
    'gallery-masonry',
    get_stylesheet_directory_uri() . '/js/gallery-masonry.js',
    array(),
    '1.0',
    true
  );
}
add_action('wp_enqueue_scripts', 'enqueue_gallery_masonry');