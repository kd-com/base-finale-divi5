<?php
// ajout de la faq
        function register_faq_post_type() {
          $args = array(
              'labels'    => array(
                  'name'               => __( 'FAQ', 'kd-faq' ),
                  'singular_name'      => __( 'FAQ', 'kd-faq' ),
                  'menu_name'          => __( 'FAQ', 'kd-faq' ),
                  'name_admin_bar'     => __( 'FAQ', 'kd-faq' ),
                  'add_new'            => __( 'Ajouter', 'kd-faq' ),
                  'add_new_item'       => __( 'Ajouter une question', 'kd-faq' ),
                  'new_item'           => __( 'Nouvelle question', 'kd-faq' ),
                  'edit_item'          => __( 'Éditer question', 'kd-faq' ),
                  'view_item'          => __( 'Voir question', 'kd-faq' ),
                  'all_items'          => __( 'Toutes les questions', 'kd-faq' ),
                  'search_items'       => __( 'Rechercher une question', 'kd-faq' ),
                  'parent_item_colon'  => __( 'Question parente :', 'kd-faq' ),
                  'not_found'          => __( 'Aucune question trouvée.', 'kd-faq' ),
                  'not_found_in_trash' => __( 'Aucune question trouvée dans la corbeille.', 'kd-faq' )
              ),
              "taxonomies" => array( 'faq_categorie' ),
              'query_var'              => 'kd_faq',
              'rewrite'=> [
                'slug' => 'faq',
                "with_front" => false
            ],
            "cptp_permalink_structure" => "/%faq-categorie%/%postname%/",
              'public'                 => true,  // If you don't want it to make public, make it false
              'publicly_queryable'     => true,  // you should be able to query it
              'show_ui'                => true,  // you should be able to edit it in wp-admin
              'show_in_rest'           => true,
              'has_archive'            => 'faq',    //true,
              'menu_position'          => 51,
              'supports'               => array( 'title','custom-fields'),
          );
          flush_rewrite_rules();
       
          register_post_type('kd_faq', $args);
      }
      add_action( 'init', 'register_faq_post_type' );
      // création d'une taxonomie pour la faq
          function taxonomies() {
              $taxonomies = array();

              $taxonomies['faq_categorie'] = array(
                  'hierarchical'  => true,
                  'query_var'     => 'faq-categorie',
                  'has_archive' => true,
                  'rewrite'=> [
                    'slug' => 'faq',
                    "with_front" => false
                ],
                "cptp_permalink_structure" => "/%faq-categorie%/%postname%/",
                  'show_in_rest'      => true,
                  'labels'            => array(
                      'name'          => 'FAQ catégorie',
                      'singular_name' => 'FAQ catégorie',
                      'edit_item'     => 'Éditer FAQ catégorie',
                      'update_item'   => 'Mettre à jour FAQ catégorie',
                      'add_new_item'  => 'Ajouter FAQ catégorie',
                      'new_item_name' => 'Ajouter nouvelle FAQ catégorie',
                      'all_items'     => 'Toutes les FAQ catégorie',
                      'search_items'  => 'Rechercher FAQ catégorie',
                      'popular_items' => 'Populaire FAQ catégorie',
                      'separate_items_with_commas' => 'Séparer une FAQ catégorie avec une virgule',
                      'add_or_remove_items' => 'Ajouter ou supprimer une FAQ catégorie',
                      'choose_from_most_used' => 'Choisir parmis les plus utilisé',
                  ),
                  'show_admin_column' => true
              );
            flush_rewrite_rules();


            foreach( $taxonomies as $name => $args ) {
                register_taxonomy( $name, array( 'kd_faq' ), $args );
            }
            
        }
        add_action( 'init', 'taxonomies' );

        function filter_post_type_link($link, $post)
        {
            if ($post->post_type != 'kd_faq')
                return $link;

            if ($cats = get_the_terms($post->ID, 'faq_categorie'))
                $link = str_replace('%faq_categorie%', array_pop($cats)->slug, $link);
            return $link;
        }
        add_filter('post_type_link', 'filter_post_type_link', 10, 2);
?>