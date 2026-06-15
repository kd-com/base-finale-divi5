<?php
 function capitaine_deregister_blocks($allowed_block_types, $editor_context) {
//   # Les blocs que je souhaite désactiver
$blocks_to_disable = [
      "core/rss",
      "core/search",
      "core/verse",
      "core/social-links",
      "core/social-link",
      "core/code",
      "wp-plugin-insert-php/winp-snippet",
      "divi/layout",
      "core/avatar",
      "core/comment-author-avatar",
      "core/comment-author-name",
      "core/comment-content",
      "core/comment-date",
      "core/comment-edit-link",
      "core/comment-reply-link",
      "core/comments",
      "core/comments-pagination",
      "core/comments-pagination-next",
      "core/comments-pagination-numbers",
      "core/comments-pagination-previous",
      "core/comments-title",
      "core/loginout",
      "core/navigation",
      "core/pattern",
      "core/site-logo",
      "core/site-title",
      "core/site-tagline",
      "core/query",
      "core/post-title",
      "core/post-featured-image",
      "core/read-more",
      "core/term-description",
      "core/query-title",
      "core/archives",
      "core/calendar",
      "core/categories",
      "core/latest-posts",
      "core/latest-comments",
      "core/html",
      "core/page-list",
      //"core/shortcode",
      "core/tag-cloud",
      "core/post-author",
      "core/post-author-biography",
      "core/post-author-name",
      "core/post-comment",
      "core/post-comments-count",
      "core/post-comments-form",
      "core/post-comments-link",
      "core/post-content",
      "core/post-date",
      "core/post-excerpt",
      "core/post-navigation-link",
      "core/post-template",
      "core/post-terms",
      "core/file",


  ];
  
# La liste des blocs actifs dans WordPress
  $active_blocks = array_keys(
      WP_Block_Type_Registry::get_instance()->get_all_registered()
);
  
# La nouvelle liste sans les blocs indésirables
  return array_values(array_diff($active_blocks, $blocks_to_disable));
}
add_filter("allowed_block_types_all", "capitaine_deregister_blocks", 10, 2);

// Restrictions et accès spécifiques pour le rôle éditeur

// Modification des capacités du rôle éditeur
add_action('init', function() {
  $role_object = get_role('editor');
  if ($role_object) {
    // Capacités de base
    $role_object->add_cap('forminator');
    $role_object->add_cap('manage_options');
    $role_object->add_cap('wp_block');
    
    // Capacités spécifiques pour menus et widgets uniquement
    $role_object->add_cap('edit_theme_options'); // Nécessaire pour menus et widgets
    
    // Capacité pour accéder au module Newsletter
    $role_object->add_cap('manage_newsletter');

    // Restaurer explicitement les caps de mise à jour sur le rôle
    // (elles ont pu être retirées en BDD par une version précédente du code)
    // Le blocage conditionnel est géré dynamiquement plus bas via user_has_cap
    $role_object->add_cap('update_plugins');
    $role_object->add_cap('update_themes');
    $role_object->add_cap('install_plugins');
    $role_object->add_cap('delete_plugins');
    $role_object->add_cap('activate_plugins');
    $role_object->add_cap('update_core');
    
    // Retirer les capacités non désirées
    // Note : update_plugins et update_themes sont gérés dynamiquement
    // par le bloc "BLOCAGE DES MISES À JOUR SI MAINTENANCE ACTIVÉE" ci-dessous
    $role_object->remove_cap('searchandfilter');
    $role_object->remove_cap('switch_themes'); // Empêche la sélection de thèmes
    $role_object->remove_cap('edit_themes'); // Empêche l'édition de thèmes
    $role_object->remove_cap('customize'); // Empêche l'accès au Customizer
    $role_object->remove_cap('delete_themes'); // Empêche la suppression de thèmes
    $role_object->remove_cap('install_themes'); // Empêche l'installation de thèmes
    $role_object->remove_cap('manage_divi_options'); // Empêche l'accès au menu Divi (et_divi_options)
    $role_object->remove_cap('custom_background'); // Empêche l'accès au sous-menu Arrière-plan
    //retirer accès à ACF
    $role_object->remove_cap('edit_acf');
    $role_object->remove_cap('edit_acf_field_groups');
    $role_object->remove_cap('delete_acf_field_groups');
    $role_object->remove_cap('manage_acf_field_groups');
    $role_object->remove_cap('export_acf');
    $role_object->remove_cap('import_acf');
  }
});

// Masquer les sous-menus de Apparence sauf Menus et Widgets
add_action('admin_menu', function() {
  if (current_user_can('editor')) {
    global $submenu;
    
    // Retirer tous les sous-menus d'Apparence sauf Menus et Widgets
    remove_submenu_page('themes.php', 'themes.php'); // Thèmes
    remove_submenu_page('themes.php', 'theme-editor.php'); // Éditeur de fichiers du thème
    remove_submenu_page('themes.php', 'site-editor.php'); // Éditeur de site
    remove_submenu_page('themes.php', 'custom-background.php'); // Arrière-plan
    remove_submenu_page('themes.php', 'custom-header.php'); // En-tête personnalisé
    //retirer le menu acf
    remove_submenu_page('themes.php', 'edit.php?post_type=acf-field-group');
    remove_menu_page('edit.php?post_type=acf-field-group');
    
    // Retirer le menu Divi
    remove_menu_page('et_divi_options');
    
    // Forcer le nettoyage du tableau global $submenu pour Apparence
    if (isset($submenu['themes.php'])) {
      foreach ($submenu['themes.php'] as $key => $item) {
        // Garder uniquement "Menus" (nav-menus.php) et "Widgets" (widgets.php)
        if (!in_array($item[2], array('nav-menus.php', 'widgets.php'))) {
          unset($submenu['themes.php'][$key]);
        }
      }
    }
  }
}, 9999);

// Bloquer l'accès direct à l'arrière-plan si l'utilisateur tente d'y accéder
add_action('admin_init', function() {
  if (current_user_can('editor')) {
    global $pagenow;
    if ($pagenow === 'themes.php' && isset($_GET['page']) && $_GET['page'] === 'custom-background.php') {
      wp_die('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.');
    }
  }
});

// Masquer le menu Réglages pour tous sauf kd-com
add_action('admin_menu', function() {
  $current_user = wp_get_current_user();
  if ($current_user->user_login !== 'kd-com') {
    // Masque le menu principal Réglages
    remove_menu_page('options-general.php');
    remove_menu_page('reglages-site-main');;
    // Masque les sous-menus de réglages
    remove_submenu_page('options-general.php', 'options-general.php');
    remove_submenu_page('options-general.php', 'options-writing.php');
    remove_submenu_page('options-general.php', 'options-reading.php');
    remove_submenu_page('options-general.php', 'options-discussion.php');
    remove_submenu_page('options-general.php', 'options-media.php');
    remove_submenu_page('options-general.php', 'options-permalink.php');
    remove_submenu_page('options-general.php', 'options-privacy.php');
  }
}, 99);

// Retirer la capacité manage_options pour tous sauf kd-com (sauf pour Newsletter)
add_action('admin_init', function() {
  $current_user = wp_get_current_user();
  
  // Vérifier si on est sur une page du module Newsletter
  $is_newsletter_page = isset($_GET['page']) && strpos($_GET['page'], 'brevo-newsletter') !== false;
  
  if ($current_user->user_login !== 'kd-com' && !$is_newsletter_page) {
    $roles = $current_user->roles;
    foreach ($roles as $role) {
      $role_object = get_role($role);
      if ($role_object && $role_object->has_cap('manage_options') && !current_user_can('manage_newsletter')) {
        // Temporairement retirer manage_options pour les pages hors Newsletter
        add_filter('user_has_cap', function($allcaps, $caps, $args, $user) {
          if (isset($_GET['page']) && strpos($_GET['page'], 'brevo-newsletter') === false) {
            unset($allcaps['manage_options']);
          }
          return $allcaps;
        }, 10, 4);
      }
    }
  }
});

// Empêcher l'accès direct aux pages de réglages pour tous sauf kd-com
add_action('admin_init', function() {
  $current_user = wp_get_current_user();
  $settings_pages = [
    'options-general.php',
    'options-writing.php',
    'options-reading.php',
    'options-discussion.php',
    'options-media.php',
    'options-permalink.php',
    'options-privacy.php',
  ];
  global $pagenow;
  if (in_array($pagenow, $settings_pages) && $current_user->user_login !== 'kd-com') {
    wp_die("Seul l'utilisateur kd-com peut accéder à cette page de réglages.");
  }
});

// ============================================
// RESTRICTION PAGE CRON NEWSLETTER À KD-COM
// ============================================

// Bloquer l'accès direct à la page cron pour tous sauf kd-com
add_action('admin_init', function() {
  $current_user = wp_get_current_user();
  if (
    isset($_GET['page']) &&
    $_GET['page'] === 'brevo-newsletter-cron' &&
    $current_user->user_login !== 'kd-com'
  ) {
    wp_die(
      "Seul l'utilisateur kd-com peut accéder à la page de configuration du Cron autonome.",
      'Accès refusé',
      ['response' => 403, 'back_link' => true]
    );
  }
});

// Masquer le sous-menu "Cron autonome" dans la navigation pour tous sauf kd-com
add_action('admin_menu', function() {
  $current_user = wp_get_current_user();
  if ($current_user->user_login !== 'kd-com') {
    remove_submenu_page('brevo-newsletter', 'brevo-newsletter-cron');
  }
}, 9999);

// ============================================
// BLOCAGE DES MISES À JOUR SI MAINTENANCE ACTIVÉE
// ============================================

add_action('admin_init', function() {
  // Ne s'applique que si l'option maintenance est activée
  if (get_option('maintenance_kd_com', '0') !== '1') {
    return;
  }

  $current_user = wp_get_current_user();

  // kd-com conserve tous ses droits, même en mode maintenance
  if ($current_user->user_login === 'kd-com') {
    return;
  }

  // Retirer dynamiquement les capacités de mise à jour via le filtre user_has_cap
  add_filter('user_has_cap', function($allcaps) {
    $caps_to_block = [
      'update_plugins',
      'install_plugins',
      'delete_plugins',
      'activate_plugins',
      'deactivate_plugins',
      'update_themes',
      'install_themes',
      'delete_themes',
      'switch_themes',
      'update_core',
    ];
    foreach ($caps_to_block as $cap) {
      $allcaps[$cap] = false;
    }
    return $allcaps;
  }, 10, 1);

  // Supprimer les notifications de mises à jour dans l'administration
  remove_action('admin_notices', 'update_nag', 3);
  remove_action('network_admin_notices', 'update_nag', 3);

  // Masquer le sous-menu "Mises à jour" dans Tableau de bord
  add_action('admin_menu', function() {
    remove_submenu_page('index.php', 'update-core.php');
  }, 999);

  // Bloquer les transients de mises à jour pour masquer les badges de notification
  add_filter('site_transient_update_plugins',     '__return_null');
  add_filter('site_transient_update_themes',      '__return_null');
  add_filter('site_transient_update_core',        '__return_null');
  add_filter('pre_site_transient_update_plugins', '__return_null');
  add_filter('pre_site_transient_update_themes',  '__return_null');
  add_filter('pre_site_transient_update_core',    '__return_null');

  // Bloquer l'accès direct à la page des mises à jour
  global $pagenow;
  if ($pagenow === 'update-core.php') {
    wp_die(
      "Les mises à jour sont gérées par kd-com dans le cadre du contrat de maintenance. Contactez votre prestataire.",
      'Mises à jour désactivées',
      ['response' => 403, 'back_link' => true]
    );
  }
});