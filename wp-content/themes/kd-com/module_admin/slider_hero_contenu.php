<?php
/**
 * CPT Slider Contenu
 * Enregistrement du Custom Post Type pour le slider de contenu (pages/articles)
 */

function create_slider_contenu() {
  $labels = array(
    'name' => 'Slider contenu',
    'singular_name' => 'Slide contenu',
    'add_new' => 'Ajouter une slide',
    'add_new_item' => 'Ajouter une nouvelle slide',
    'edit_item' => 'Modifier la slide',
    'new_item' => 'Nouvelle slide',
    'view_item' => 'Voir la slide',
    'search_items' => 'Rechercher des slides',
    'not_found' => 'Aucune slide trouvée',
    'not_found_in_trash' => 'Aucune slide dans la corbeille',
    'all_items' => 'Toutes les slides',
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'exclude_from_search' => true,
    'has_archive' => false,
    'show_in_rest' => true,
    'supports' => array('title'),
    'menu_position' => 3,
    'menu_icon' => 'dashicons-slides',
    'capability_type' => 'post',
    'hierarchical' => false,
    'rewrite' => array('slug' => 'slider-contenu'),
  );
  
  register_post_type('slider_contenu', $args);
}
add_action('init', 'create_slider_contenu');

/**
 * Colonnes personnalisées dans la liste admin
 */
function slider_contenu_admin_columns($columns) {
  $new_columns = array(
    'cb' => $columns['cb'],
    'title' => 'Titre',
    'slider_content_type' => 'Type',
    'slider_content_linked' => 'Contenu lié',
    'slider_content_image' => 'Image',
    'date' => 'Date',
  );
  return $new_columns;
}
add_filter('manage_slider_contenu_posts_columns', 'slider_contenu_admin_columns');

/**
 * Contenu des colonnes personnalisées
 */
function slider_contenu_admin_column_content($column, $post_id) {
  switch ($column) {
    case 'slider_content_type':
      $page = get_field('page_liee', $post_id);
      $article = get_field('article_lie', $post_id);
      
      if ($page) {
        echo '<span class="dashicons dashicons-admin-page"></span> Page';
      } elseif ($article) {
        echo '<span class="dashicons dashicons-admin-post"></span> Article';
      } else {
        echo '—';
      }
      break;
      
    case 'slider_content_linked':
      $page = get_field('page_liee', $post_id);
      $article = get_field('article_lie', $post_id);
      
      if (is_object($page) && property_exists($page, 'ID') && property_exists($page, 'post_title')) {
        echo '<a href="' . get_edit_post_link($page->ID) . '" target="_blank">' . esc_html($page->post_title) . '</a>';
      } elseif (is_object($article) && property_exists($article, 'ID') && property_exists($article, 'post_title')) {
        echo '<a href="' . get_edit_post_link($article->ID) . '" target="_blank">' . esc_html($article->post_title) . '</a>';
      } else {
        echo '—';
      }
      break;
      
    case 'slider_content_image':
      $image = get_field('image_personnalisee', $post_id);
      $page = get_field('page_liee', $post_id);
      $article = get_field('article_lie', $post_id);
      
      if ($image) {
        echo '<img src="' . esc_url($image) . '" style="max-width:60px;height:auto;" />';
      } elseif (is_object($page) && property_exists($page, 'ID') && has_post_thumbnail($page->ID)) {
        echo get_the_post_thumbnail($page->ID, 'thumbnail', array('style' => 'max-width:60px;height:auto;'));
      } elseif (is_object($article) && property_exists($article, 'ID') && has_post_thumbnail($article->ID)) {
        echo get_the_post_thumbnail($article->ID, 'thumbnail', array('style' => 'max-width:60px;height:auto;'));
      } else {
        echo '<span class="dashicons dashicons-format-image" style="color:#ccc;font-size:40px;"></span>';
      }
      break;
  }
}
add_action('manage_slider_contenu_posts_custom_column', 'slider_contenu_admin_column_content', 10, 2);

/**
 * Rendre les colonnes triables
 */
function slider_contenu_sortable_columns($columns) {
  $columns['slider_content_type'] = 'content_type';
  return $columns;
}
add_filter('manage_edit-slider_contenu_sortable_columns', 'slider_contenu_sortable_columns');
