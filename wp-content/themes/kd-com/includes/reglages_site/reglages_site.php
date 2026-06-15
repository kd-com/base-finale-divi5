<?php
if( function_exists('acf_add_local_field_group') ) {
    acf_add_local_field_group(array(
        'key' => 'group_65d470f50a7e1',
        'title' => 'réglages du site',
        'fields' => array(
            array(
                'key' => 'field_6603d37fe5237',
                'label' => 'Réglages du site',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_65d470f592938',
                'label' => "Choix de la page d'accueil",
                'name' => 'choix_de_la_page_daccueil',
                'type' => 'post_object',
                'post_type' => array('page'),
                'post_status' => array('publish'),
                'return_format' => 'id',
                'ui' => 1,
            ),
            array(
                'key' => 'field_65d4734daab0d',
                'label' => 'Téléphone',
                'name' => 'telephone',
                'type' => 'text',
            ),
            array(
                'key' => 'field_6708f85bca010',
                'label' => "horaires d'ouverture",
                'name' => 'horaires_douverture',
                'type' => 'wysiwyg',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
            ),
            array(
                'key' => 'field_65e5d7865a9b4',
                'label' => 'image par defaut',
                'name' => 'image_par_defaut',
                'type' => 'image',
                'return_format' => 'id',
                'library' => 'all',
                'preview_size' => 'medium',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'reglages-du-site',
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
        'description' => '',
        'show_in_rest' => 0,
    ));
}



// Inclusion des sous-pages personnalisées
require_once __DIR__ . '/pages/reglages_generaux.php';
require_once __DIR__ . '/pages/reglages_modules.php';
require_once __DIR__ . '/pages/reglages_couleurs.php';
require_once __DIR__ . '/pages/reglages_blocks_acf.php';
require_once __DIR__ . '/pages/reglages_tarteaucitron.php';
require_once __DIR__ . '/tarteaucitron/tarteaucitron-admin.php';

// Menu principal et sous-menus personnalisés pour les réglages du site
add_action('admin_menu', function() {
    add_menu_page(
        'Réglages personnalisés',
        'Réglages personnalisés',
        'manage_options',
        'reglages-site-main',
        function() { echo '<h1>Bienvenue dans les réglages personnalisés</h1><p>Utilisez les sous-menus pour configurer le site.</p>'; },
        'dashicons-admin-generic',
        2
    );
    add_submenu_page(
        'reglages-site-main',
        'Réglages généraux',
        'Réglages généraux',
        'manage_options',
        'reglages-site-generaux',
        'afficher_reglages_generaux'
    );
    add_submenu_page(
        'reglages-site-main',
        'Gestion des modules',
        'Gestion des modules',
        'manage_options',
        'reglages-site-modules',
        'afficher_reglages_modules'
    );
    add_submenu_page(
        'reglages-site-main',
        'Gestion des couleurs',
        'Gestion des couleurs',
        'manage_options',
        'reglages-site-couleurs',
        'afficher_reglages_couleurs'
    );
    add_submenu_page(
        'reglages-site-main',
        'Activation blocks ACF',
        'Activation blocks ACF',
        'manage_options',
        'reglages-site-blocks',
        'afficher_reglages_blocks_acf'
    );
    // Ajout de la sous-page Tarteaucitron
    add_submenu_page(
        'reglages-site-main',
        'Réglages Tarteaucitron',
        'Tarteaucitron',
        'manage_options',
        'reglages_tarteaucitron',
        'kd_reglages_tarteaucitron_page'
    );
});


// Enregistrement des réglages pour chaque sous-page
add_action('admin_init', function() {
    // Réglages généraux
        register_setting('reglages_site_generaux_group', 'telephone');
    add_settings_section('section_generaux', '', null, 'reglages-site-generaux');
    add_settings_field('telephone', 'Téléphone', function() {
        echo '<input type="text" name="telephone" value="' . esc_attr(get_option('telephone')) . '" class="regular-text">';
    }, 'reglages-site-generaux', 'section_generaux');
        // Le champ image par défaut est désormais géré uniquement via ACF

    // Gestion des couleurs
    register_setting('reglages_site_couleurs_group', 'couleur_personnalisee');
    add_settings_section('section_couleurs', '', null, 'reglages-site-couleurs');
    add_settings_field('couleur_personnalisee', 'Couleur personnalisée', function() {
        echo '<input type="text" name="couleur_personnalisee" value="' . esc_attr(get_option('couleur_personnalisee')) . '" class="regular-text">';
    }, 'reglages-site-couleurs', 'section_couleurs');
        // Gestion des modules
        register_setting('reglages_site_modules_group', 'slider_page_daccueil');
        register_setting('reglages_site_modules_group', 'slider_contenu');
        register_setting('reglages_site_modules_group', 'slider_reference_client');
        register_setting('reglages_site_modules_group', 'slider_carrousel_page');
        register_setting('reglages_site_modules_group', 'renommer_le_cpt_projet');
        register_setting('reglages_site_modules_group', 'faq');
        register_setting('reglages_site_modules_group', 'recrutement');
        register_setting('reglages_site_modules_group', 'sous_pages');
        register_setting('reglages_site_modules_group', 'videos_aleatoire');
        add_settings_section('section_modules', '', null, 'reglages-site-modules');
});