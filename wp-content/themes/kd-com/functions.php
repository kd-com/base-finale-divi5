<?php
// sécurité : empêcher l'accès direct au fichier

// ACTIVTATION DU THEME ENFANT
function theme_enqueue_styles() {
    // Vérifier que le parent style existe
    $parent_style = 'parent-style';
    $parent_style_file = get_template_directory() . '/style.css';
    
    if (file_exists($parent_style_file)) {
        wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
    } else {
        error_log('Le fichier style.css du thème parent est introuvable');
    }
}
add_action('wp_enqueue_scripts', 'theme_enqueue_styles');

// AJOUT DU SCRIPT DU THEME
add_action('wp_enqueue_scripts', 'custom_enqueue_script');
function custom_enqueue_script() {
    // Vérifier que le script existe
    $script_path = get_stylesheet_directory() . '/script.js';
    
    if (file_exists($script_path)) {
        wp_enqueue_script('script', get_stylesheet_directory_uri() . '/script.js',
            array('jquery'), filemtime($script_path), true);
    } else {
        error_log('Le fichier script.js est introuvable');
    }

    // Passer l’option AOS à JS de manière sécurisée
    $aos_enabled = get_option('kd_com_aos_enabled', '1') === '1';
    wp_localize_script('script', 'kdComOptions', [
        'aosEnabled' => wp_json_encode($aos_enabled)
    ]);

}

// acitvation shortcode acf
add_action( 'acf/init', 'set_acf_settings' );
function set_acf_settings() {
    acf_update_setting( 'enable_shortcode', true );
}

// Chargement de AOS pour les animations
// Chargement conditionnel de AOS selon l'option WordPress
// Limitation des blocs Gutenberg dans l'éditeur
if (is_admin()) {
  require_once get_stylesheet_directory() . '/includes/admin_editor.php';
  // Restriction des pages de réglages du site à l'utilisateur kd-com
  add_action('admin_init', function() {
    // Liste des pages de réglages à restreindre
    $settings_pages = [
      'options-general.php', // Réglages généraux
      'options-writing.php',
      'options-reading.php',
      'options-discussion.php',
      'options-media.php',
      'options-permalink.php',
      'options-privacy.php',
    ];
    global $pagenow;
    $current_user = wp_get_current_user();
    if (in_array($pagenow, $settings_pages) && $current_user->user_login !== 'kd-com') {
      wp_die("Seul l'utilisateur kd-com peut accéder à cette page de réglages.");
    }
  });
}

// Inclusion des styles de boutons pour Gutenberg (front + admin)
require_once get_stylesheet_directory() . '/assets/buttons/button-styles.php';
// Inclusion des presets de boutons Divi 5
require_once get_stylesheet_directory() . '/assets/buttons/divi5-button-presets.php';
function kd_enqueue_aos() {
  if (get_option('kd_com_aos_enabled', '1') === '1') {
    wp_enqueue_script( 'aos', 'https://unpkg.com/aos@2.3.1/dist/aos.js', [] , '2.3.1', true );
    wp_enqueue_style('aos', 'https://unpkg.com/aos@2.3.1/dist/aos.css', [], '2.3.1');
    wp_enqueue_script( 'aos-frontend', get_stylesheet_directory_uri() . '/js/aos-frontend.js', array('aos'), null, true );
    wp_enqueue_script( 'aos-divi', get_stylesheet_directory_uri() . '/js/aos-divi.js', array('jquery', 'aos'), null, true );
  }
}
add_action('wp_enqueue_scripts', 'kd_enqueue_aos', 100);
// Injecte le SVG flèche dans l'éditeur Gutenberg pour le style u-link-arrow
add_action('enqueue_block_editor_assets', function() {
    wp_enqueue_script(
        'kd-com-button-style-editor',
        get_stylesheet_directory_uri() . '/assets/buttons/button-style-editor.js',
        array('wp-dom-ready'),
        null,
        true
    );
});

// Masque les contrôles de couleur du bloc bouton dans l'éditeur Gutenberg
add_action('enqueue_block_editor_assets', function() {
    wp_enqueue_script(
        'kd-com-hide-button-colors',
        get_stylesheet_directory_uri() . '/assets/buttons/hide-button-colors.js',
        array('wp-dom-ready'),
        null,
        true
    );
});


// Ajout du panneau Animation dans l'éditeur Gutenberg pour blocks core
add_action('enqueue_block_editor_assets', function() {
  if (get_option('kd_com_aos_enabled', '1') === '1') {
    wp_enqueue_script(
      'animation-sidebar',
      get_stylesheet_directory_uri() . '/js/animation-sidebar.js',
      array(
        'wp-blocks',
        'wp-i18n',
        'wp-element',
        'wp-components',
        'wp-edit-post',
        'wp-plugins',
        'wp-data',
        'wp-compose',
        'wp-block-editor'
      ),
      null,
      true
    );
  }
});



// Chargement conditionnel de Swiper.js selon l'activation d'un module slider
function kd_enqueue_swiper_if_slider_active() {
  $slider_page_daccueil = get_option('module_slider_page_daccueil');
  $slider_contenu = get_option('module_slider_contenu');
  $slider_reference_client = get_option('module_slider_reference_client');
  $slider_carrousel_page = get_option('module_slider_carrousel_page');
  if ($slider_page_daccueil || $slider_contenu || $slider_reference_client || $slider_carrousel_page) {
    wp_enqueue_script( 'your-swiper-js-slug', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [] , '11.0.0', true );
    wp_enqueue_style( 'your-swiper-css-slug', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [] , '11.0.0');
  }
}
add_action('wp_enqueue_scripts', 'kd_enqueue_swiper_if_slider_active', 99);

// Désactivation complète de l'éditeur sur la page d'accueil
add_filter('use_block_editor_for_post', function($use_block_editor, $post) {
    if (!$post) return $use_block_editor;

    $homepage_id = get_option('page_on_front');

    if ($homepage_id && $post->ID == $homepage_id) {
        return false;
    }

    return $use_block_editor;
}, 10, 2);

add_action('admin_init', function() {
    $homepage_id = get_option('page_on_front');

    if ($homepage_id && isset($_GET['post']) && $_GET['post'] == $homepage_id) {
        remove_post_type_support('page', 'editor');
    }
});


/**
 * Returns the theme color definitions used across the site.
 */
function kd_get_theme_color_settings() {
    return [
        'couleur_titrage' => [ 'name' => 'Titrage', 'default' => '#22282d' ],
        'couleur_texte'   => [ 'name' => 'Texte',   'default' => '#3e464b' ],
        'couleur_lien'    => [ 'name' => 'Lien',    'default' => '#e84448' ],
        'couleur_fond'    => [ 'name' => 'Fond',    'default' => '#3db27c' ],
        'couleur_fond2'   => [ 'name' => 'Fond 2',  'default' => '#424242' ],
        'couleur_blanche' => [ 'name' => 'Blanc',   'default' => '#ffffff' ],
        'couleur_noire'   => [ 'name' => 'Noir',    'default' => '#000000' ],
    ];
}

function kd_get_theme_colors() {
    $colors = [];
    foreach ( kd_get_theme_color_settings() as $slug => $data ) {
        $colors[] = [
            'name'  => $data['name'],
            'slug'  => $slug,
            'color' => sanitize_hex_color( get_option( $slug, $data['default'] ) ) ?: $data['default'],
        ];
    }
    return $colors;
}

function kd_get_theme_color_palette() {
    return array_map(
        function( $color ) {
            return strtolower( $color['color'] );
        },
        kd_get_theme_colors()
    );
}

function kd_sync_theme_colors() {
    $colors = kd_get_theme_colors();
    add_theme_support( 'editor-color-palette', $colors );

    $scss = "// Ce fichier est généré automatiquement par le thème\n";
    foreach ( $colors as $color ) {
        $scss .= '$' . $color['slug'] . ': ' . $color['color'] . ";\n";
    }

    $accent = sanitize_hex_color( get_option( 'couleur_lien', '#e84448' ) ) ?: '#e84448';
    $scss .= '$divi-accent: ' . $accent . ";\n";

    $file_path = get_stylesheet_directory() . '/sass/_theme-colors.scss';
    $dir_path  = dirname( $file_path );

    if ( is_dir( $dir_path ) && is_writable( $dir_path ) ) {
        file_put_contents( $file_path, $scss );
    } else {
        error_log( 'KD-COM Theme: Cannot write to _theme-colors.scss - directory not writable' );
    }
}
add_action( 'after_setup_theme', 'kd_sync_theme_colors' );

function kd_sync_divi_palette() {
    $palette = kd_get_theme_color_palette();
    $accent  = sanitize_hex_color( get_option( 'couleur_lien', '#e84448' ) ) ?: '#e84448';

    $et_divi = get_option( 'et_divi', [] );
    if ( ! is_array( $et_divi ) ) {
        $et_divi = [];
    }

    $et_divi['color_pickers_default_palette'] = $palette;
    $et_divi['divi_color_palette']           = implode( '|', $palette );
    $et_divi['accent_color']                 = $accent;

    update_option( 'et_divi', $et_divi, true );

    if ( function_exists( 'et_update_option' ) ) {
        et_update_option( 'divi_color_palette', implode( '|', $palette ) );
        et_update_option( 'accent_color', $accent );
    } else {
        update_option( 'divi_color_palette', implode( '|', $palette ) );
        update_option( 'accent_color', $accent );
    }

    global $wpdb;
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'et_core_options_cache%' OR option_name LIKE '_transient_et_core_options_cache%'" );
}

add_action( 'admin_init', 'kd_sync_divi_palette' );
add_action( 'updated_option', function( $option_name ) {
    if ( array_key_exists( $option_name, kd_get_theme_color_settings() ) ) {
        kd_sync_divi_palette();
    }
}, 10, 1 );

function kd_inject_theme_css_vars() {
    $colors = kd_get_theme_colors();
    $accent = sanitize_hex_color( get_option( 'couleur_lien', '#e84448' ) ) ?: '#e84448';

    $css = ':root {';
    foreach ( $colors as $color ) {
        $css .= '--kd-' . $color['slug'] . ':' . $color['color'] . ';';
    }
    $css .= '--divi-accent-color:' . $accent . ';}';

    echo '<style id="kd-theme-vars">' . $css . '</style>';
}
add_action( 'wp_head', 'kd_inject_theme_css_vars', 1 );
add_action( 'admin_head', 'kd_inject_theme_css_vars', 1 );

add_action( 'wp_head', function() {
    $accent = sanitize_hex_color( get_option( 'couleur_lien', '#e84448' ) ) ?: '#e84448';
    echo '<style id="divi5-accent-override">:root{ --divi-accent-color: ' . $accent . '; }</style>';
} );


// synchro des couleurs du thème avec ACF
add_filter('acf/load_field/name=choix_couleurs', 'wd_acf_dynamic_colors_load');
add_filter('acf/load_field/name=choix_couleurs_copier', 'wd_acf_dynamic_colors_load');
add_filter('acf/load_field/name=couleur_de_licon', 'wd_acf_dynamic_colors_load');
add_filter('acf/load_field/name=couleur_de_fond_du_bloc', 'wd_acf_dynamic_colors_load');
add_filter('acf/load_field/name=couleur_fond', 'wd_acf_dynamic_colors_load');
add_filter('acf/load_field/name=couleur_texte', 'wd_acf_dynamic_colors_load');
add_filter('acf/load_field/name=couleur_titre_block', 'wd_acf_dynamic_colors_load');
add_filter('acf/load_field/name=couleur_titre', 'wd_acf_dynamic_colors_load');
add_filter('acf/load_field/name=couleur_liens', 'wd_acf_dynamic_colors_load');
add_filter('acf/load_field/name=couleur_fond_lien', 'wd_acf_dynamic_colors_load');

function wd_acf_dynamic_colors_load( $field ) {

     // get array of colors created using editor-color-palette
     $colors = get_theme_support( 'editor-color-palette' );

     // if this array is empty, continue
     if( ! empty( $colors ) ) {

          // loop over each color and create option
          foreach( $colors[0] as $color ) {
               $field['choices'][ $color['slug'] ] = $color['name'];
          }
     }

     return $field;
}

function wd_admin_style() {
  wp_enqueue_style( 'admin-styles', get_stylesheet_directory_uri().'/css/blocks.css' );
}
add_action( 'admin_enqueue_scripts', 'wd_admin_style' );
add_action('acf/input/admin_footer', 'prefix_acf_color_picker_from_theme_palette');
function prefix_acf_color_picker_from_theme_palette() {

    $colors = '';
    // Get colors palette registerd in theme support
	$color_palette = get_theme_support( 'editor-color-palette' );
    if ( ! empty( $color_palette ) ) {
		// Get each 'color' value (hex code)
		$colors = array_column( $color_palette[ 0 ], 'color' );
    }

    // Try to get color palette from theme.json
    if ( false === $color_palette && class_exists( 'WP_Theme_JSON_Resolver' ) ) {
        $settings = WP_Theme_JSON_Resolver::get_theme_data()->get_settings();
        if ( isset( $settings['color']['palette']['theme'] ) ) {
            $color_palette = $settings['color']['palette']['theme'];
            if ( ! empty( $color_palette ) ) {
                // Get each 'color' value (hex code)
                $colors = array_column( $color_palette, 'color' );
            }
        }
    }

    if ( ! empty( $colors ) ) {
		?>
		<script type="text/javascript">
		acf.add_filter('color_picker_args', function( args, field ){
		    // do something to args
		    args.palettes = <?php echo json_encode( $colors ); ?>;
		    // return
		    return args;
		});
		</script>
		<?php
	}

}


// Image mise en avant par défaut si aucune n'est définie
function default_post_metadata__thumbnail_id( $value, $object_id, $meta_key, $single, $meta_type ) {
  if ( '_thumbnail_id' == $meta_key && empty($value) ) {
    $default_image = get_option('image_par_defaut');
    if ( $default_image ) {
      $value = $default_image;
    }
  }
  return $value;
}
add_filter( 'default_post_metadata', 'default_post_metadata__thumbnail_id', 10, 5 );

// LOGO PERSO SUR PAGE CONNEXION ADMIN
  function my_custom_login_logo() {
    $logo = ( $user_logo = et_get_option('divi_logo')) && ! empty($user_logo) ? $user_logo : get_bloginfo('stylesheet_directory') .'/img/logo_admin.png';
    echo '<style type="text/css">
    h1 a { background-image:url('.esc_attr($logo).') !important; background-size:contain !important; width:100% !important; }
    </style>';
  }
  add_action('login_head', 'my_custom_login_logo');

// SUPPRESSION DU NUMERO DE VERSION DE WORDPRESS

  function kd_delete_version() {
    return '';
  }
  add_filter('the_generator', 'kd_delete_version');


// AJOUT DES ICONES FONT AWESOME

  function kd_load_fontawesome() {
  wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css', null, '6.4.2' );
  }
  add_action('wp_enqueue_scripts', 'kd_load_fontawesome');
  // Charger Font Awesome dans l'administration pour l'aperçu des icônes
add_action('admin_enqueue_scripts', function() {
  wp_enqueue_style( 'font-awesome-admin', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css', null, '6.4.2' );
});


// MASQUER LES ERREURS DE CONNEXION A L'ADMINISTRATION

  function wpm_hide_errors() {
   return "L'identifiant ou le mot de passe est incorrect";
 }
 add_filter('login_errors', 'wpm_hide_errors');

// gestion de l'affichage random ajouter id random-posts
 function kd_random_posts($query, $args) {
  if (isset($args['module_id']) && $args['module_id'] === 'random-posts') {
    $query->query_vars['orderby'] = 'rand';
    $query->query_vars['order'] = 'ASC';
    $query = new WP_Query( $query->query_vars );
  }
  return $query;
}
add_filter('et_builder_blog_query', 'kd_random_posts', 10, 2);

// ajout bloc sur dashboard wp
require_once get_stylesheet_directory() . '/includes/dashboard_block.php';

/**
 * Disable the custom color picker.
 */
add_theme_support('disable-custom-colors');
// -- Disable Gradients
add_theme_support( 'disable-custom-gradients' );
add_theme_support( 'editor-gradient-presets', array() );


// AJOUT DES BLOCKS ACF
// Inclusion dynamique des blocks ACF selon les réglages
require_once get_stylesheet_directory() . '/blocks/gestion_blocks.php';

// AJOUT DES MODULES - Chargement automatique
$acf_fields_dir = get_stylesheet_directory() . '/includes/reglages_site/acf_fields/';
$module_admin_acf_dir = get_stylesheet_directory() . '/module_admin/acf_fields/';
$acf_files = glob($acf_fields_dir . '*.php');
$module_admin_acf_files = glob($module_admin_acf_dir . '*.php');

// 1️⃣ TOUJOURS charger les fichiers ACF des réglages site (pour la page de gestion des modules)
foreach ($acf_files as $acf_file) {
    require_once $acf_file;
}

// 2️⃣ TOUJOURS charger les fichiers ACF des modules admin (pour l'édition des CPT)
foreach ($module_admin_acf_files as $acf_file) {
    require_once $acf_file;
}

// 3️⃣ Charger les fichiers fonctionnels admin/front SEULEMENT si le module est activé
foreach ($acf_files as $acf_file) {
    $module_slug = basename($acf_file, '.php');
    $option_key = 'module_' . $module_slug;

    if (get_option($option_key)) {
        // Inclusion des fichiers admin
        $admin_file = get_stylesheet_directory() . '/module_admin/' . $module_slug . '.php';
        if (file_exists($admin_file)) {
            include_once $admin_file;
        }
        // Inclusion des fichiers front
        $front_file = get_stylesheet_directory() . '/module_front/' . $module_slug . '.php';
        if (file_exists($front_file)) {
            include_once $front_file;
        }
    }
}

// images catégories
require_once get_stylesheet_directory() . '/includes/image_categorie.php';


/* -------------------------------------------------------------------------------------*/
/* WP DASHBOARD - Change nom Articles pour Actualités */
/* -------------------------------------------------------------------------------------*/

function actu_change_post_menu_label() {
  global $menu;
  global $submenu;
  $menu[5][0] = 'Actualités';
  $submenu['edit.php'][5][0] = 'Toutes les Actualités';
  $submenu['edit.php'][10][0] = 'Ajouter';
  $submenu['edit.php'][16][0] = 'Actualité Tags';
  echo '';
}
function actu_change_post_object_label() {
  global $wp_post_types;
  $labels = &$wp_post_types['post']->labels;
  $labels->name = 'Actualités';
  $labels->singular_name = 'Actualité';
  $labels->add_new = 'Ajouter une Actualité';
  $labels->add_new_item = 'Ajouter une Actualité';
  $labels->edit_item = 'Modifier l\'Actualité';
  $labels->new_item = 'Nouvelle Actualité';
  $labels->view_item = 'Voir les Actualités';
  $labels->search_items = 'Rechercher une Actualité';
  $labels->not_found = 'Aucune Actualité correspondante.';
  $labels->not_found_in_trash = 'Aucune Actualité dans la corbeille.';
}
add_action( 'admin_menu', 'actu_change_post_menu_label' );
add_action( 'init', 'actu_change_post_object_label' );


// limit excerpt
function custom_excerpt_length( $length ) {
  return 25;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );

// sauvegarde auto des champs acf
function kd_acf_export_json( $path ) {
	$path = get_stylesheet_directory() . '/acf-json';
	return $path;
}
add_filter( 'acf/settings/save_json', 'kd_acf_export_json' );

// désactivation de AOS dans l'édition des contenus
function disable_aos_in_gutenberg() {
  // Vérifie si nous sommes sur la page d'édition Gutenberg
  if ( is_admin() && function_exists('get_current_screen') ) {
      $screen = get_current_screen();

      // Vérifie si nous sommes dans l'éditeur Gutenberg
      if ( $screen->base === 'post' && isset($_GET['post']) && is_numeric($_GET['post']) ) {
          ?>
          <script>
              // Désactiver AOS dans l'éditeur Gutenberg
              if (document.body.classList.contains('block-editor-page')) {
                  // Supprimer ou désactiver l'initialisation d'AOS
                  if (typeof AOS !== 'undefined') {
                      AOS.init = function() {}; // Redéfinir la fonction AOS.init pour ne rien faire
                  }
              }
          </script>
          <?php
      }
  }
}
add_action('admin_head', 'disable_aos_in_gutenberg');

// Vérifie si Tarteaucitron est activé (option RGPD)
function kd_is_tarteaucitron_enabled() {
  $options = get_option('kd_tarteaucitron_settings', array());
  return !empty($options['enabled']);
}

// Chargement des champs ACF pour les réglages du site
require_once get_stylesheet_directory() . '/includes/reglages_site/reglages_site.php';
require_once get_stylesheet_directory() . '/includes/reglages_site/tarteaucitron/tarteaucitron-filters.php';

// Ajout d'une option pour activer/désactiver AOS
add_action('admin_init', function() {
  add_settings_section('kd_com_aos_section', 'Réglages AOS', null, 'general');
  add_settings_field('kd_com_aos_enabled', 'Activer AOS', function() {
    $value = get_option('kd_com_aos_enabled', '1');
    echo '<input type="checkbox" name="kd_com_aos_enabled" value="1"' . checked($value, '1', false) . ' />';
  }, 'general', 'kd_com_aos_section');
  register_setting('general', 'kd_com_aos_enabled', [
    'type' => 'string',
    'sanitize_callback' => function($v) { return $v === '1' ? '1' : '0'; }
  ]);
});
// Shortcode pour afficher l'adresse des réglages généraux
function kd_com_shortcode_adresse() {
    $contentw = get_option('adresse_du_site_w');
    return wpautop(wp_kses_post($contentw));
}
add_shortcode('adresse', 'kd_com_shortcode_adresse');

// Shortcode pour afficher le téléphone des réglages généraux
function kd_com_shortcode_telephone() {
    $telephone = get_option('telephone'); // Remplacez 'telephone' par le nom exact de l'option si besoin
    return esc_html($telephone);
}
add_shortcode('telephone', 'kd_com_shortcode_telephone');

// Shortcode pour afficher les horaires d'ouverture avec la mise en page WYSIWYG
function kd_com_shortcode_horaires_ouverture() {
  $content = get_option('horaire_ouverture');
  return wpautop(wp_kses_post($content));
}
add_shortcode('horaires_ouverture', 'kd_com_shortcode_horaires_ouverture');


// Masquer le CPT "project" de Divi si le module portfolio n'est pas activé (doit être après l'inclusion des modules)
add_action('admin_menu', function() {
  $portfolio_active = get_option('module_cpt_portfolio');
  if (!$portfolio_active) {
    remove_menu_page('edit.php?post_type=project');
  }
}, 99);

// supprimer les images non utilisées automatiquement.
include_once get_stylesheet_directory() . '/includes/supp-medias.php';

function generer_contenu_newsletter() {
    $template_path = get_stylesheet_directory() . '/newsletter/template-newsletter.php';
    if (file_exists($template_path)) {
        ob_start();
        include $template_path;
        return ob_get_clean();
    } else {
        return '<p>Erreur : Le fichier de template est introuvable.</p>';
    }
}

function shortcode_test_newsletter() {
    return generer_contenu_newsletter();
}
add_shortcode('test_newsletter', 'shortcode_test_newsletter');

// création du cpt produits
add_action('init', function() {
    register_post_type('produit', [
        'labels' => [
            'name'          => 'Produits',
            'singular_name' => 'Produit',
        ],
        'public'       => true,
        'has_archive'  => true,
        'show_in_rest' => true,
        'supports'     => ['title', 'editor', 'thumbnail'],
        'rewrite'      => ['slug' => 'produits'],
        'menu_icon'    => 'dashicons-products', // 👈 icône ici
    ]);
});
// gestion du formulaire par produit
add_filter('forminator_custom_form_submit_field_data', function($field_data, $form_id) {
    $referer = wp_get_referer();

    if ($referer) {
        $post_id = url_to_postid($referer);
        if ($post_id && get_post_type($post_id) === 'produit') {
            $post_title = get_the_title($post_id);
            $field_data[] = [
                'name'  => 'hidden-1',
                'value' => $post_title . ' - ' . $referer,
            ];
        }
    }

    return $field_data;
}, 10, 2);
