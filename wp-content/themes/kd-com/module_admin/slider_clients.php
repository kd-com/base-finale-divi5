<?php
// slider logo client
function create_slider_client() {
    $labels = array(
        'name' => 'Partenaires'
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => false,
        'exclude_from_search' => true,
        'menu_icon' => 'dashicons-businessman',
        'supports' => array('title'),
        'show_in_rest' => true,
    );
    register_post_type('slider_client', $args);
}
add_action('init', 'create_slider_client');