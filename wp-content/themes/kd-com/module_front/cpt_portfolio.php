<?php
/**
 * Ordre d'affichage des Réalisations (CPT project) pour Divi
 *
 * Emplacement : wp-content/themes/kd-com/module_front/cpt_portfolio.php
 * Inclus depuis : module_admin/cpt_portfolio.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─────────────────────────────────────────────────────────────
// Lecture de la config enregistrée par portfolio_order_settings.php
// Les clés correspondent exactement aux register_setting() :
//   'portfolio_orderby'        → get_option( 'portfolio_orderby' )
//   'portfolio_posts_per_page' → get_option( 'portfolio_posts_per_page' )
// ─────────────────────────────────────────────────────────────
function kd_portfolio_get_order_config() {

    $orderby_raw    = get_option( 'portfolio_orderby', 'date' );
    $posts_per_page = (int) get_option( 'portfolio_posts_per_page', 0 );

    $orderby = 'date';
    $order   = 'DESC';

    switch ( $orderby_raw ) {
        case 'date':      $orderby = 'date';       $order = 'DESC'; break;
        case 'date_asc':  $orderby = 'date';       $order = 'ASC';  break;
        case 'cat':       $orderby = 'cat';        $order = 'ASC';  break;
        case 'rand':      $orderby = 'rand';       $order = '';     break;
        case 'title':     $orderby = 'title';      $order = 'ASC';  break;
        case 'title_desc':$orderby = 'title';      $order = 'DESC'; break;
        case 'menu_order':$orderby = 'menu_order'; $order = 'ASC';  break;
        default:          $orderby = 'date';       $order = 'DESC';
    }

    return array(
        'orderby_raw'    => $orderby_raw,
        'orderby'        => $orderby,
        'order'          => $order,
        'posts_per_page' => $posts_per_page > 0 ? $posts_per_page : false,
    );
}

// ─────────────────────────────────────────────────────────────
// Filtre pre_get_posts
// ─────────────────────────────────────────────────────────────
function kd_portfolio_apply_order( WP_Query $query ) {

    if ( is_admin() ) {
        return;
    }

    // Vérifier que le module portfolio est activé.
    // Le nom de l'option suit la convention de reglages_modules.php :
    // 'module_' + basename du fichier acf_fields sans .php
    // → fichier cpt_portfolio.php → option 'module_cpt_portfolio'
    if ( get_option( 'module_cpt_portfolio', '0' ) !== '1' ) {
        return;
    }

    $post_type = $query->get( 'post_type' );

    $is_project_query = (
        $post_type === 'project' ||
        ( is_array( $post_type ) && in_array( 'project', $post_type, true ) )
    );

    if ( ! $is_project_query ) {
        if (
            $query->is_post_type_archive( 'project' ) ||
            $query->is_tax( 'project_category' ) ||
            $query->is_tax( 'project_tag' )
        ) {
            $is_project_query = true;
        }
    }

    if ( ! $is_project_query ) {
        return;
    }

    $config = kd_portfolio_get_order_config();

    if ( $config['orderby'] === 'cat' ) {
        $query->set( 'orderby', 'title' );
        $query->set( 'order', 'ASC' );
        add_filter( 'posts_join',    'kd_portfolio_join_by_cat',    10, 2 );
        add_filter( 'posts_orderby', 'kd_portfolio_orderby_cat',   10, 2 );
    } else {
        $query->set( 'orderby', $config['orderby'] );
        if ( $config['order'] ) {
            $query->set( 'order', $config['order'] );
        }
    }

    if ( $config['posts_per_page'] ) {
        $query->set( 'posts_per_page', $config['posts_per_page'] );
    }
}
add_action( 'pre_get_posts', 'kd_portfolio_apply_order', 20 );


// ─────────────────────────────────────────────────────────────
// JOIN + ORDER BY pour tri par catégorie
// ─────────────────────────────────────────────────────────────
function kd_portfolio_join_by_cat( $join, WP_Query $query ) {
    global $wpdb;

    $post_type  = $query->get( 'post_type' );
    $is_project = ( $post_type === 'project' || ( is_array( $post_type ) && in_array( 'project', $post_type, true ) ) );
    if ( ! $is_project ) {
        return $join;
    }

    $join .= "
        LEFT JOIN {$wpdb->term_relationships} AS kd_tr
            ON ({$wpdb->posts}.ID = kd_tr.object_id)
        LEFT JOIN {$wpdb->term_taxonomy} AS kd_tt
            ON (kd_tr.term_taxonomy_id = kd_tt.term_taxonomy_id AND kd_tt.taxonomy = 'project_category')
        LEFT JOIN {$wpdb->terms} AS kd_t
            ON (kd_tt.term_id = kd_t.term_id)
    ";

    return $join;
}

function kd_portfolio_orderby_cat( $orderby, WP_Query $query ) {
    global $wpdb;

    $post_type  = $query->get( 'post_type' );
    $is_project = ( $post_type === 'project' || ( is_array( $post_type ) && in_array( 'project', $post_type, true ) ) );
    if ( ! $is_project ) {
        return $orderby;
    }

    remove_filter( 'posts_join',    'kd_portfolio_join_by_cat',  10 );
    remove_filter( 'posts_orderby', 'kd_portfolio_orderby_cat', 10 );

    return "kd_t.name ASC, {$wpdb->posts}.post_title ASC";
}


// ─────────────────────────────────────────────────────────────
// Shortcode debug [portfolio_order_debug]
// Affiche la valeur lue en base — à retirer en production
// ─────────────────────────────────────────────────────────────
function kd_portfolio_order_debug_shortcode() {

    if ( ! current_user_can( 'manage_options' ) ) {
        return '';
    }

    $config = kd_portfolio_get_order_config();

    $labels = array(
        'date'        => '📅 Date (récent → ancien)',
        'date_asc'    => '📅 Date (ancien → récent)',
        'cat'         => '🗂️ Catégorie (A → Z)',
        'rand'        => '🔀 Aléatoire',
        'title'       => '🔤 Titre (A → Z)',
        'title_desc'  => '🔤 Titre (Z → A)',
        'menu_order'  => '🔢 Ordre manuel',
    );

    $current        = $labels[ $config['orderby_raw'] ] ?? $config['orderby_raw'];
    $module_active  = get_option( 'module_cpt_portfolio', '0' );
    $raw_option     = get_option( 'portfolio_orderby', '(vide)' );

    return '<div style="background:#fff3cd;padding:12px 16px;border-left:4px solid #ffc107;font-size:13px;line-height:1.8;">'
        . '<strong>🔧 Debug Portfolio</strong><br>'
        . 'Module actif : <code>' . esc_html( $module_active ) . '</code><br>'
        . 'Valeur brute en base (<code>portfolio_orderby</code>) : <code>' . esc_html( $raw_option ) . '</code><br>'
        . 'Tri interprété : <strong>' . esc_html( $current ) . '</strong>'
        . ( $config['posts_per_page'] ? '<br>Posts/page : <code>' . esc_html( $config['posts_per_page'] ) . '</code>' : '' )
        . '</div>';
}
add_shortcode( 'portfolio_order_debug', 'kd_portfolio_order_debug_shortcode' );