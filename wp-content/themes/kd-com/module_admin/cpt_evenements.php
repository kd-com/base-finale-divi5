<?php
/**
 * Registers a custom post type 'evenements'.
 *
 * @since 1.0.0
 *
 * @return void
 */
function kd_register_evenements() : void {
	$labels = [
		'name' => _x( 'Evénements', 'Post Type General Name', 'kd' ),
		'singular_name' => _x( 'Evénement', 'Post Type Singular Name', 'kd' ),
		'menu_name' => __( 'Evénements', 'kd' ),
		'name_admin_bar' => __( 'Evénements', 'kd' ),
		'archives' => __( 'Archive des événements', 'kd' ),
		'attributes' => __( 'Attributs d\'événements', 'kd' ),
		'parent_item_colon' => __( 'Evénement parent :', 'kd' ),
		'all_items' => __( 'Tous les événements', 'kd' ),
		'add_new_item' => __( 'Ajouter un nouvel événement', 'kd' ),
		'add_new' => __( 'Nouveau', 'kd' ),
		'new_item' => __( 'Nouvel événement', 'kd' ),
		'edit_item' => __( 'Editer l\'événement', 'kd' ),
		'update_item' => __( 'mettre à jour l\'événement', 'kd' ),
		'view_item' => __( 'voir l\'événement', 'kd' ),
		'view_items' => __( 'Voir les événements', 'kd' ),
		'search_items' => __( 'Search Evénements', 'kd' ),
		'not_found' => __( 'Aucun événement trouvé', 'kd' ),
		'not_found_in_trash' => __( 'Evénement Not Found in Trash', 'kd' ),
		'featured_image' => __( 'Image principale', 'kd' ),
		'set_featured_image' => __( 'Configurer l\'image principale', 'kd' ),
		'remove_featured_image' => __( 'Effacer l\'image principale', 'kd' ),
		'use_featured_image' => __( 'Utiliser en image principale', 'kd' ),
		'insert_into_item' => __( 'Insert into Evénement', 'kd' ),
		'uploaded_to_this_item' => __( 'Uploaded to this Evénement', 'kd' ),
		'items_list' => __( 'Evénements List', 'kd' ),
		'items_list_navigation' => __( 'Evénements List Navigation', 'kd' ),
		'filter_items_list' => __( 'Filter Evénements List', 'kd' ),
	];
	$labels = apply_filters( 'evenements-labels', $labels );

	$args = [
		'label' => __( 'Evénement', 'kd' ),
		'description' => __( 'Gestion des évènements', 'kd' ),
		'labels' => $labels,
		'supports' => [
			'title',
			'editor',
			'thumbnail',
			'revisions',
			'publicize'
		],
		'taxonomies' => [
			'type_evenements',
		],
		'hierarchical' => false,
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 5,
		'menu_icon' => 'dashicons-calendar',
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => true,
		'exclude_from_search' => false,
		'has_archive' => false,
		'can_export' => false,
		'capability_type' => 'post',
		'show_in_rest' => true,
	];
	$args = apply_filters( 'evenements-args', $args );

	register_post_type( 'evenements', $args );
}
add_action( 'init', 'kd_register_evenements', 0 );

/**
 * Registers the 'type_evenements' taxonomy.
 * 
 * @return void
 */
function kd_register_type_evenements() : void {
	$labels = [
		'name' => _x( 'Catégorie d\'événements', 'Taxonomy Name', 'kd' ),
		'singular_name' => _x( 'Catégorie d\'événement', 'Taxonomy Singular Name', 'kd' ),
		'menu_name' => __( 'Catégorie d\'événements ', 'kd' ),
		'all_items' => __( 'Toutes les catégories d\'événements ', 'kd' ),
		'parent_item' => __( 'Catégorie d\'événement parente ', 'kd' ),
		'parent_item_colon' => __( 'Catégorie d\'événement parente : ', 'kd' ),
		'new_item_name' => __( 'Nouvelle catégorie d\'événement ', 'kd' ),
		'add_new_item' => __( 'Ajouter une nouvelle catégorie d\'événement ', 'kd' ),
		'edit_item' => __( 'Editer la catégorie d\'événement ', 'kd' ),
		'update_item' => __( 'Mettre à jour la catégorie d\'événement ', 'kd' ),
		'view_item' => __( 'Voir la catégorie d\'événement ', 'kd' ),
		'add_or_remove_items' => __( 'Ajouter ou supprimer catégorie d\'événements ', 'kd' ),
		'choose_from_most_used' => __( 'Choisissez parmi les catégories d\'événements les plus utilisées ', 'kd' ),
		'popular_items' => __( 'Catégories d\'événements populaires ', 'kd' ),
		'search_items' => __( 'Rechercher des catégories d\'événements ', 'kd' ),
		'not_found' => __( 'Non trouvé ', 'kd' ),
		'no_terms' => __( 'Aucune catégorie d\'événements ', 'kd' ),
		'items_list' => __( 'Liste des catégories d\'événements ', 'kd' ),
		'items_list_navigation' => __( 'Navigation dans la liste des catégories d\'événements ', 'kd' ),
	];

	$args = [
		'labels' => $labels,
		'hierarchical' => false,
		'public' => true,
		'show_ui' => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud' => true,
		'show_in_rest' => true,
		'rest_base' => 'type_evenements',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	];

	register_taxonomy( 'type_evenements', ['evenements'], $args );
}
add_action( 'init', 'kd_register_type_evenements' );