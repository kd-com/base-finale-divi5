<?php
/**
 * Champs ACF pour le bloc YouTube Grid
 * Affichage de vidéos YouTube en grille avec système intelligent RGPD/Natif
 */

if( function_exists('acf_add_local_field_group') ):

// Fonction pour récupérer les CPT disponibles
function get_youtube_grid_cpt_choices() {
	$cpt_choices = array();
	
	// Récupérer tous les CPT publics (sauf les built-in)
	$post_types = get_post_types(array(
		'public' => true,
		'_builtin' => false
	), 'objects');
	
	foreach ($post_types as $post_type) {
		$cpt_choices[$post_type->name] = $post_type->label . ' (' . $post_type->name . ')';
	}
	
	// Ajouter quelques CPT communs si ils n'existent pas encore
	if (empty($cpt_choices)) {
		$cpt_choices = array(
			'video' => 'Vidéos',
			'product' => 'Produits',
			'portfolio' => 'Portfolio',
			'testimonial' => 'Témoignages',
		);
	}
	
	return $cpt_choices;
}

acf_add_local_field_group(array(
	'key' => 'group_youtube_grid',
	'title' => 'YouTube Grid - Configuration',
	'instruction' => 'Bloc ACF pour afficher une grille de vidéos YouTube avec options avancées',
	'fields' => array(
		array(
			'key' => 'field_youtube_grid_title',
			'label' => 'Titre de la section (optionnel)',
			'name' => 'youtube_grid_title',
			'type' => 'text',
			'instructions' => 'Titre affiché au-dessus de la grille de vidéos',
			'required' => 0,
			'wrapper' => array(
				'width' => '100%',
			),
		),
		array(
			'key' => 'field_youtube_grid_subtitle',
			'label' => 'Sous-titre (optionnel)',
			'name' => 'youtube_grid_subtitle',
			'type' => 'textarea',
			'instructions' => 'Description courte affichée sous le titre',
			'required' => 0,
			'rows' => 2,
			'wrapper' => array(
				'width' => '100%',
			),
		),
		array(
			'key' => 'field_youtube_grid_columns',
			'label' => 'Nombre de colonnes',
			'name' => 'youtube_grid_columns',
			'type' => 'select',
			'instructions' => 'Disposition des vidéos en grille (responsive automatique)',
			'required' => 1,
			'choices' => array(
				'1' => '1 colonne (pleine largeur)',
				'2' => '2 colonnes',
				'3' => '3 colonnes',
				'4' => '4 colonnes',
			),
			'default_value' => '3',
			'wrapper' => array(
				'width' => '100',
			),
		),
		array(
			'key' => 'field_youtube_grid_gap',
			'label' => 'Espacement',
			'name' => 'youtube_grid_gap',
			'type' => 'select',
			'instructions' => 'Espace entre les vidéos',
			'required' => 1,
			'choices' => array(
				'small' => 'Petit (10px)',
				'medium' => 'Moyen (20px)',
				'large' => 'Grand (30px)',
			),
			'default_value' => 'medium',
			'wrapper' => array(
				'width' => '100',
			),
		),
		array(
			'key' => 'field_youtube_grid_max_videos',
			'label' => 'Nombre max de vidéos',
			'name' => 'youtube_grid_max_videos',
			'type' => 'number',
			'instructions' => 'Limite le nombre de vidéos affichées (laissez vide pour toutes)',
			'required' => 0,
			'min' => 1,
			'max' => 20,
			'wrapper' => array(
				'width' => '100',
			),
		),
		array(
			'key' => 'field_youtube_grid_aspect_ratio',
			'label' => 'Ratio d\'aspect',
			'name' => 'youtube_grid_aspect_ratio',
			'type' => 'select',
			'instructions' => 'Format des vidéos dans la grille',
			'required' => 1,
			'choices' => array(
				'16:9' => '16:9 (Standard YouTube)',
				'4:3' => '4:3 (Format classique)',
				'1:1' => '1:1 (Carré)',
				'21:9' => '21:9 (Cinéma)',
			),
			'default_value' => '16:9',
			'wrapper' => array(
				'width' => '100',
			),
		),
		array(
			'key' => 'field_youtube_grid_source',
			'label' => 'Source des vidéos',
			'name' => 'youtube_grid_source',
			'type' => 'select',
			'instructions' => 'Choisissez d\'où récupérer automatiquement les vidéos YouTube',
			'required' => 1,
			'choices' => array(
				'category' => 'Catégorie de posts',
				'cpt' => 'Custom Post Type (CPT)',
				'subpages' => 'Sous-pages',
				'manual' => 'Sélection manuelle (repeater)',
			),
			'default_value' => 'category',
			'wrapper' => array(
				'width' => '100',
			),
		),
		array(
			'key' => 'field_youtube_grid_post_category',
			'label' => 'Catégorie de posts',
			'name' => 'youtube_grid_post_category',
			'type' => 'taxonomy',
			'instructions' => 'Sélectionnez la catégorie de posts contenant vos vidéos YouTube',
			'taxonomy' => 'category',
			'field_type' => 'select',
			'allow_null' => 0,
			'add_term' => 1,
			'save_terms' => 0,
			'load_terms' => 0,
			'return_format' => 'id',
			'multiple' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_youtube_grid_source',
						'operator' => '==',
						'value' => 'category',
					),
				),
			),
			'wrapper' => array(
				'width' => '100',
			),
		),
		array(
			'key' => 'field_youtube_grid_cpt_type',
			'label' => 'Type de CPT',
			'name' => 'youtube_grid_cpt_type',
			'type' => 'select',
			'instructions' => 'Sélectionnez le type de Custom Post Type',
			'choices' => get_youtube_grid_cpt_choices(),
			'allow_null' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_youtube_grid_source',
						'operator' => '==',
						'value' => 'cpt',
					),
				),
			),
			'wrapper' => array(
				'width' => '100',
			),
		),
		array(
			'key' => 'field_youtube_grid_cpt_category',
			'label' => 'Catégorie du CPT (optionnel)',
			'name' => 'youtube_grid_cpt_category',
			'type' => 'text',
			'instructions' => 'ID de la catégorie/taxonomie du CPT. Laissez vide pour récupérer tous les posts du CPT.',
			'required' => 0,
			'placeholder' => 'Ex: 5 (ID de la catégorie)',
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_youtube_grid_source',
						'operator' => '==',
						'value' => 'cpt',
					),
				),
			),
			'wrapper' => array(
				'width' => '100',
			),
		),
		array(
			'key' => 'field_youtube_grid_parent_page',
			'label' => 'Page parente',
			'name' => 'youtube_grid_parent_page',
			'type' => 'post_object',
			'instructions' => 'Sélectionnez la page dont vous voulez récupérer les sous-pages contenant des vidéos YouTube',
			'post_type' => array('page'),
			'allow_null' => 0,
			'multiple' => 0,
			'return_format' => 'id',
			'ui' => 1,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_youtube_grid_source',
						'operator' => '==',
						'value' => 'subpages',
					),
				),
			),
			'wrapper' => array(
				'width' => '100',
			),
		),
		array(
			'key' => 'field_youtube_grid_posts_limit',
			'label' => 'Nombre de posts/pages à récupérer',
			'name' => 'youtube_grid_posts_limit',
			'type' => 'number',
			'instructions' => 'Limite le nombre de posts/pages à récupérer (laissez vide pour tous)',
			'required' => 0,
			'min' => 1,
			'max' => 50,
			'default_value' => 12,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_youtube_grid_source',
						'operator' => '!=',
						'value' => 'manual',
					),
				),
			),
			'wrapper' => array(
				'width' => '100',
			),
		),
		array(
			'key' => 'field_youtube_grid_use_post_title',
			'label' => 'Utiliser le titre du post/page',
			'name' => 'youtube_grid_use_post_title',
			'type' => 'true_false',
			'instructions' => 'Utiliser le titre du post/page comme titre de la vidéo',
			'required' => 0,
			'default_value' => 1,
			'ui' => 1,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_youtube_grid_source',
						'operator' => '!=',
						'value' => 'manual',
					),
				),
			),
			'wrapper' => array(
				'width' => '100',
			),
		),
		array(
			'key' => 'field_youtube_grid_videos_manual',
			'label' => 'Vidéos YouTube (sélection manuelle)',
			'name' => 'youtube_grid_videos_manual',
			'type' => 'repeater',
			'instructions' => 'Ajoutez vos vidéos YouTube une par une manuellement',
			'required' => 0,
			'collapsed' => 'field_youtube_video_title_manual',
			'min' => 1,
			'max' => 20,
			'layout' => 'row',
			'button_label' => '+ Ajouter une vidéo YouTube',
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_youtube_grid_source',
						'operator' => '==',
						'value' => 'manual',
					),
				),
			),
			'sub_fields' => array(
				array(
					'key' => 'field_youtube_video_id_manual',
					'label' => 'URL ou ID Vidéo YouTube',
					'name' => 'video_id',
					'type' => 'text',
					'instructions' => 'Collez l\'URL complète YouTube ou juste l\'ID de la vidéo',
					'required' => 1,
					'placeholder' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ ou dQw4w9WgXcQ',
					'wrapper' => array(
						'width' => '100',
					),
				),
				array(
					'key' => 'field_youtube_video_title_manual',
					'label' => 'Titre de la vidéo',
					'name' => 'video_title',
					'type' => 'text',
					'instructions' => 'Titre personnalisé (optionnel)',
					'required' => 0,
					'wrapper' => array(
						'width' => '100',
					),
				),
				array(
					'key' => 'field_youtube_video_description_manual',
					'label' => 'Description',
					'name' => 'video_description',
					'type' => 'textarea',
					'instructions' => 'Description courte (optionnelle)',
					'required' => 0,
					'rows' => 2,
					'wrapper' => array(
						'width' => '100',
					),
				),
			),
		),
		array(
			'key' => 'field_youtube_grid_show_titles',
			'label' => 'Afficher les titres',
			'name' => 'youtube_grid_show_titles',
			'type' => 'true_false',
			'instructions' => 'Afficher le titre sous chaque vidéo',
			'required' => 0,
			'default_value' => 1,
			'ui' => 1,
			'wrapper' => array(
				'width' => '100',
			),
		),
		array(
			'key' => 'field_youtube_grid_show_descriptions',
			'label' => 'Afficher les descriptions',
			'name' => 'youtube_grid_show_descriptions',
			'type' => 'true_false',
			'instructions' => 'Afficher la description sous chaque vidéo',
			'required' => 0,
			'default_value' => 0,
			'ui' => 1,
			'wrapper' => array(
				'width' => '100',
			),
		),
		
	),
	'location' => array(
		array(
			array(
				'param' => 'block',
				'operator' => '==',
				'value' => 'acf/youtube-grid',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => 'Configuration du bloc YouTube Grid avec système intelligent RGPD/Natif',
));

endif;