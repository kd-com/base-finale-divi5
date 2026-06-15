<?php
/**
 * ========================================
 * GESTION NEWSLETTER BREVO - MODULE COMPLET v2.6
 * ========================================
 * 
 * Fonctionnalités :
 * - Sous-pages d'administration (Dashboard, Config, Auto, Libre, Stats, Abonnés)
 * - Newsletter automatique avec choix d'heure
 * - Newsletter libre avec éditeur de blocs
 * - Statistiques détaillées des campagnes
 * - Gestion des abonnés
 * - Formulaire d'inscription personnalisable
 * - Tracking des posts envoyés
 * - Images avec URLs absolues (compatible Gmail)
 * - Support des événements récurrents et multi-jours
 */

define('BREVO_NEWSLETTER_DIR', get_stylesheet_directory() . '/newsletter');
define('BREVO_NEWSLETTER_URL', get_stylesheet_directory_uri() . '/newsletter');

// Inclure les modules
require_once get_stylesheet_directory() . '/module_admin/newsletter_abonne.php';
// MIse en place du tache cron spécifique pour la newsletter
require_once get_stylesheet_directory() . '/module_admin/newsletter_pages/cron.php';


// ============================================
// MENU PRINCIPAL ET SOUS-PAGES
// ============================================

add_action('admin_menu', 'brevo_newsletter_admin_menu');

function brevo_newsletter_admin_menu() {
    // Capacité requise : manage_newsletter (pour éditeurs) OU manage_options (pour admin)
    $capability = current_user_can('manage_options') ? 'manage_options' : 'manage_newsletter';
    
    add_menu_page(
        'Newsletter',
        'Newsletter',
        $capability,
        'brevo-newsletter',
        'brevo_newsletter_dashboard_page',
        'dashicons-email-alt',
        30
    );
    
    add_submenu_page('brevo-newsletter', 'Tableau de bord', 'Tableau de bord', $capability, 'brevo-newsletter', 'brevo_newsletter_dashboard_page');
    add_submenu_page('brevo-newsletter', 'Configuration', 'Configuration', $capability, 'brevo-newsletter-config', 'brevo_newsletter_config_page');
    add_submenu_page('brevo-newsletter', 'Newsletter automatique', 'Newsletter auto', $capability, 'brevo-newsletter-auto', 'brevo_newsletter_auto_page');
    add_submenu_page('brevo-newsletter', 'Newsletter libre', 'Newsletter libre', $capability, 'brevo-newsletter-libre', 'brevo_newsletter_libre_page');
    add_submenu_page('brevo-newsletter', 'Statistiques', 'Statistiques', $capability, 'brevo-newsletter-stats', 'brevo_newsletter_stats_page');
    add_submenu_page('brevo-newsletter', 'Abonnés', 'Abonnés', $capability, 'brevo-newsletter-subscribers', 'brevo_newsletter_subscribers_page');

    add_submenu_page('brevo-newsletter', 'Cron autonome', 'Cron autonome', 'manage_options', 'brevo-newsletter-cron', 'brevo_newsletter_cron_page');


}

// Inclure les fichiers de pages
require_once get_stylesheet_directory() . '/module_admin/newsletter_pages/dashboard.php';
require_once get_stylesheet_directory() . '/module_admin/newsletter_pages/config.php';
require_once get_stylesheet_directory() . '/module_admin/newsletter_pages/auto.php';
require_once get_stylesheet_directory() . '/module_admin/newsletter_pages/libre.php';
require_once get_stylesheet_directory() . '/module_admin/newsletter_pages/stats.php';
require_once get_stylesheet_directory() . '/module_admin/newsletter_pages/newsletter_preview.php';
require_once get_stylesheet_directory() . '/module_admin/newsletter_pages/subscribers.php';

// ============================================
// 1. CONFIGURATION
// ============================================

function get_brevo_config() {
    return array(
        'api_key' => get_option('brevo_api_key', ''),
        'list_id' => get_option('brevo_list_id', ''),
        'list_name' => get_option('brevo_list_name', ''),
        'sender_email' => get_option('brevo_sender_email', ''),
        'sender_name' => get_option('brevo_sender_name', get_bloginfo('name'))
    );
}

function is_brevo_configured() {
    $config = get_brevo_config();
    return !empty($config['api_key']) && !empty($config['list_id']) && 
           !empty($config['sender_email']) && !empty($config['sender_name']);
}

// ============================================
// 2. TRACKING DES POSTS
// ============================================

function get_sent_posts() {
    $sent_posts = get_option('brevo_newsletter_sent_posts', array());
    return is_array($sent_posts) ? $sent_posts : array();
}

function update_sent_posts($post_ids) {
    update_option('brevo_newsletter_sent_posts', $post_ids);
}

function mark_posts_as_sent($post_ids) {
    $sent_posts = get_sent_posts();
    $sent_posts = array_merge($sent_posts, $post_ids);
    $sent_posts = array_unique($sent_posts);
    update_option('brevo_newsletter_sent_posts', $sent_posts);
}

function reset_sent_posts() {
    delete_option('brevo_newsletter_sent_posts');
}

function has_new_posts_to_send() {
    $sent_posts = get_sent_posts();
    
    $news_query = new WP_Query(array(
        'post_type' => 'post',
        'posts_per_page' => 10,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'post__not_in' => $sent_posts
    ));
    
    $has_news = $news_query->have_posts();
    wp_reset_postdata();
    
    $events_module_enabled = get_option('module_cpt_evenements', '0') === '1';
    $has_events = false;
    
    if ($events_module_enabled) {
        $today = date('Y-m-d');
        
        // Vérifier tous les types d'événements
        $all_events_query = new WP_Query(array(
            'post_type' => 'evenements',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            //'post__not_in' => $sent_posts
        ));
        
        if ($all_events_query->have_posts()) {
            while ($all_events_query->have_posts()) {
                $all_events_query->the_post();
                $post_id = get_the_ID();
                $event_type = get_field('event_type', $post_id);
                
                $is_upcoming = false;
                
                switch ($event_type) {
                    case 'multi_day':
                    case 'recurring':
                        $end_date = get_field('event_end_date', $post_id);
                        $is_upcoming = ($end_date >= $today);
                        break;
                        
                    case 'single':
                    default:
                        $event_date = get_field('event_date', $post_id);
                        $is_upcoming = ($event_date >= $today);
                        break;
                }
                
                if ($is_upcoming) {
                    $has_events = true;
                    break;
                }
            }
            wp_reset_postdata();
        }
    }
    
    $portfolio_module_enabled = get_option('module_cpt_portfolio', '0') === '1';
    $has_projects = false;

    if ($portfolio_module_enabled) {
        $projects_query = new WP_Query(array(
            'post_type'      => 'project',
            'posts_per_page' => 10,
            'post_status'    => 'publish',
            'post__not_in'   => $sent_posts
        ));
        $has_projects = $projects_query->have_posts();
        wp_reset_postdata();
    }

return $has_news || $has_events || $has_projects;
}

// ============================================
// 3. GESTION DES IMAGES (URLs absolues - compatible Gmail)
// ============================================

/**
 * Convertir URL relative en URL absolue
 * Compatible Gmail et tous les clients email
 */
function brevo_ensure_absolute_url($image_url) {
    // Si déjà une URL absolue, la retourner
    if (strpos($image_url, 'http://') === 0 || strpos($image_url, 'https://') === 0) {
        return $image_url;
    }
    
    // Si URL relative, la convertir en absolue
    $upload_dir = wp_upload_dir();
    $base_url = $upload_dir['baseurl'];
    
    // Si l'image est dans le dossier uploads
    if (strpos($image_url, $base_url) !== false) {
        return $image_url;
    }
    
    // Si c'est un chemin relatif au site
    if (strpos($image_url, '/') === 0) {
        return home_url($image_url);
    }
    
    // Sinon, ajouter l'URL du site
    return home_url('/' . ltrim($image_url, '/'));
}

// ============================================
// 4. GÉNÉRATION ET ENVOI
// ============================================

function generate_newsletter_html() {
    $sent_posts = get_sent_posts();
    $news_query = new WP_Query(array(
        'post_type' => 'post',
        'posts_per_page' => 3,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'post__not_in' => $sent_posts
    ));
    
    $events_module_enabled = get_option('module_cpt_evenements', '0') === '1';
    $events_query = null;
    $events_single = array();
    $events_multi_day = array();
    $events_recurring = array();
    
    if ($events_module_enabled) {
        $today = date('Y-m-d');
        
        // Récupérer tous les événements à venir
        $all_events_query = new WP_Query(array(
            'post_type' => 'evenements',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            //'post__not_in' => $sent_posts
        ));
        
        if ($all_events_query->have_posts()) {
            while ($all_events_query->have_posts()) {
                $all_events_query->the_post();
                $post_id = get_the_ID();
                $event_type = get_field('event_type', $post_id);
                
                $is_upcoming = false;
                
                switch ($event_type) {
                    case 'multi_day':
                        $end_date = get_field('event_end_date', $post_id);
                        if ($end_date >= $today) {
                            $events_multi_day[] = $post_id;
                            $is_upcoming = true;
                        }
                        break;
                        
                    case 'recurring':
                        $end_date = get_field('event_end_date', $post_id);
                        if ($end_date >= $today) {
                            $events_recurring[] = $post_id;
                            $is_upcoming = true;
                        }
                        break;
                        
                    case 'single':
                    default:
                        $event_date = get_field('event_date', $post_id);
                        if ($event_date >= $today) {
                            $events_single[] = $post_id;
                            $is_upcoming = true;
                        }
                        break;
                }
            }
            wp_reset_postdata();
        }
        
        // Limiter à 3 événements par catégorie
        $events_single = array_slice($events_single, 0, 3);
        $events_multi_day = array_slice($events_multi_day, 0, 3);
        $events_recurring = array_slice($events_recurring, 0, 3);
    }
    $portfolio_module_enabled = get_option('module_cpt_portfolio', '0') === '1';
    $projects_query = null;

    if ($portfolio_module_enabled) {
        $projects_query = new WP_Query(array(
            'post_type'      => 'project',
            'posts_per_page' => 5,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post__not_in'   => $sent_posts
        ));
    }


    
    // Logo en URL absolue (compatible Gmail)
    $logo_url = ( $user_logo = et_get_option('divi_logo')) && ! empty($user_logo) ? $user_logo : get_bloginfo('stylesheet_directory') .'/img/logo_admin.png';
    
    // Convertir en URL absolue si nécessaire
    if (strpos($logo_url, 'http') !== 0) {
        $logo_url = site_url($logo_url);
    }
    
    $template_data = array(
        'logo' => $logo_url,
        'news_query' => $news_query,
        'events_single' => $events_single,
        'events_multi_day' => $events_multi_day,
        'events_recurring' => $events_recurring,
        'events_module_enabled' => $events_module_enabled,
        'projects_query'           => $projects_query,           // ← NOUVEAU
        'portfolio_module_enabled' => $portfolio_module_enabled, // ← NOUVEAU
        'site_name' => get_bloginfo('name'),
        'current_month' => date_i18n('F Y'),
        'current_year' => date('Y')
    );
    
    $css_file = BREVO_NEWSLETTER_DIR . '/style.css';
    $css = file_exists($css_file) ? file_get_contents($css_file) : '';
    
    ob_start();
    extract($template_data);
    
    $template_file = BREVO_NEWSLETTER_DIR . '/template-email.php';
    if (file_exists($template_file)) {
        include $template_file;
    } else {
        echo '<p>Erreur : Template de newsletter introuvable</p>';
    }
    
    $html = ob_get_clean();
    wp_reset_postdata();
    
    return $html;
}

function send_newsletter_via_brevo($test_mode = false, $test_email = '') {
    $config = get_brevo_config();
    
    if (!$test_mode && !has_new_posts_to_send()) {
        return array(
            'success' => false,
            'message' => 'Aucun nouveau contenu à envoyer. Newsletter non envoyée.',
            'no_content' => true
        );
    }
    
    $html_content = generate_newsletter_html();
    
    $url = 'https://api.brevo.com/v3/emailCampaigns';
    
    $campaign_data = array(
        'name' => 'Newsletter ' . date_i18n('F Y'),
        'subject' => '📰 Newsletter ' . date_i18n('F Y') . ' - ' . get_bloginfo('name'),
        'sender' => array(
            'name' => $config['sender_name'],
            'email' => $config['sender_email']
        ),
        'htmlContent' => $html_content,
        'scheduledAt' => date('c', strtotime('+2 minutes')),
        'mirrorActive' => true
    );
    
    if ($test_mode && !empty($test_email)) {
        $url = 'https://api.brevo.com/v3/smtp/email';
        $campaign_data = array(
            'sender' => array(
                'name' => $config['sender_name'],
                'email' => $config['sender_email']
            ),
            'to' => array(
                array('email' => $test_email)
            ),
            'subject' => '[TEST] ' . $campaign_data['subject'],
            'htmlContent' => $html_content
        );
    } else {
        $campaign_data['recipients'] = array(
            'listIds' => array(intval($config['list_id']))
        );
    }
    
    $response = wp_remote_post($url, array(
        'headers' => array(
            'accept' => 'application/json',
            'api-key' => $config['api_key'],
            'content-type' => 'application/json'
        ),
        'body' => json_encode($campaign_data),
        'timeout' => 30
    ));
    
    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => 'Erreur : ' . $response->get_error_message()
        );
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    $status_code = wp_remote_retrieve_response_code($response);
    
    if ($status_code >= 200 && $status_code < 300) {
        if (!$test_mode) {
            update_option('brevo_newsletter_last_sent', current_time('mysql'));
            
            $sent_posts = get_sent_posts();
            $sent_post_ids = array();
            
            $news_query = new WP_Query(array(
                'post_type' => 'post',
                'posts_per_page' => 3,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
                'post__not_in' => $sent_posts,
                'fields' => 'ids'
            ));
            
            $sent_post_ids = array_merge($sent_post_ids, $news_query->posts);
            
            $events_module_enabled = get_option('module_cpt_evenements', '0') === '1';
            if ($events_module_enabled) {
                $today = date('Y-m-d');
                
                // Récupérer tous les événements envoyés
                $all_events_query = new WP_Query(array(
                    'post_type' => 'evenements',
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                    'post__not_in' => $sent_posts,
                    'fields' => 'ids'
                ));
                
                foreach ($all_events_query->posts as $event_id) {
                    $event_type = get_field('event_type', $event_id);
                    
                    $is_upcoming = false;
                    
                    switch ($event_type) {
                        case 'multi_day':
                        case 'recurring':
                            $end_date = get_field('event_end_date', $event_id);
                            $is_upcoming = ($end_date >= $today);
                            break;
                            
                        case 'single':
                        default:
                            $event_date = get_field('event_date', $event_id);
                            $is_upcoming = ($event_date >= $today);
                            break;
                    }
                    
                    if ($is_upcoming) {
                        $sent_post_ids[] = $event_id;
                    }
                }
            }
            $portfolio_module_enabled = get_option('module_cpt_portfolio', '0') === '1';
            if ($portfolio_module_enabled) {
                $projects_ids_query = new WP_Query(array(
                    'post_type'      => 'project',
                    'posts_per_page' => 5,
                    'post_status'    => 'publish',
                    'post__not_in'   => $sent_posts,
                    'fields'         => 'ids'
                ));
                $sent_post_ids = array_merge($sent_post_ids, $projects_ids_query->posts);
                wp_reset_postdata();
            }
            
            wp_reset_postdata();
            
            if (!empty($sent_post_ids)) {
                mark_posts_as_sent($sent_post_ids);
            }
        }
        
        return array(
            'success' => true,
            'message' => $test_mode ? 'Email de test envoyé avec succès !' : 'Newsletter programmée avec succès !',
            'data' => $body
        );
    } else {
        return array(
            'success' => false,
            'message' => 'Erreur API Brevo : ' . ($body['message'] ?? 'Erreur inconnue'),
            'data' => $body
        );
    }
}

// ============================================
// 5. WP CRON AVEC SUPPORT DE L'HEURE
// ============================================

add_filter('cron_schedules', 'brevo_add_hourly_cron_interval');

function brevo_add_hourly_cron_interval($schedules) {
    $schedules['brevo_hourly'] = array(
        'interval' => 3600,
        'display'  => __('Toutes les heures (Newsletter)')
    );
    return $schedules;
}

add_action('wp', 'brevo_newsletter_schedule_cron');

function brevo_newsletter_schedule_cron() {
    if (!wp_next_scheduled('brevo_newsletter_hourly_check')) {
        wp_schedule_event(time(), 'brevo_hourly', 'brevo_newsletter_hourly_check');
    }
}

add_action('brevo_newsletter_hourly_check', 'brevo_newsletter_check_and_send');

function brevo_newsletter_check_and_send() {
    if (!is_brevo_configured()) {
        return;
    }
    
    $auto_send = get_option('brevo_newsletter_auto_send', '0');
    if ($auto_send !== '1') {
        return;
    }
    
    $send_day = get_option('brevo_newsletter_send_day', 1);
    $send_hour = get_option('brevo_newsletter_send_hour', 9);
    $send_minute = get_option('brevo_newsletter_send_minute', 0);
    
    $current_day = date('j');
    if ($current_day != $send_day) {
        return;
    }
    
    $current_hour = date('G');
    if ($current_hour != $send_hour) {
        return;
    }
    
    $last_sent = get_option('brevo_newsletter_last_sent', '');
    if (!empty($last_sent)) {
        $last_sent_month = date('Y-m', strtotime($last_sent));
        $current_month = date('Y-m');
        
        if ($last_sent_month === $current_month) {
            return;
        }
    }
    
    if (!has_new_posts_to_send()) {
        error_log('Brevo Newsletter: Envoi automatique annulé - Aucun nouveau contenu disponible');
        return;
    }
    
    error_log('Brevo Newsletter: Envoi automatique déclenché à ' . date('Y-m-d H:i:s'));
    send_newsletter_via_brevo(false);
}

register_deactivation_hook(__FILE__, 'brevo_newsletter_deactivate');

function brevo_newsletter_deactivate() {
    $timestamp = wp_next_scheduled('brevo_newsletter_hourly_check');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'brevo_newsletter_hourly_check');
    }
}

/**
 * Calculer la date du prochain envoi
 */
function brevo_get_next_send_date($day, $hour, $minute) {
    $now = current_time('timestamp');
    $current_month = date('n', $now);
    $current_year = date('Y', $now);
    $current_day = date('j', $now);
    
    // Si on est avant le jour d'envoi ce mois-ci
    if ($current_day < $day) {
        $next_send = mktime($hour, $minute, 0, $current_month, $day, $current_year);
    } 
    // Si on est le jour d'envoi mais avant l'heure
    elseif ($current_day == $day) {
        $current_hour = date('G', $now);
        $current_minute = date('i', $now);
        
        if ($current_hour < $hour || ($current_hour == $hour && $current_minute < $minute)) {
            $next_send = mktime($hour, $minute, 0, $current_month, $day, $current_year);
        } else {
            // Mois prochain
            $next_month = $current_month + 1;
            $next_year = $current_year;
            
            if ($next_month > 12) {
                $next_month = 1;
                $next_year++;
            }
            
            $next_send = mktime($hour, $minute, 0, $next_month, $day, $next_year);
        }
    }
    else {
        // Sinon, mois prochain
        $next_month = $current_month + 1;
        $next_year = $current_year;
        
        if ($next_month > 12) {
            $next_month = 1;
            $next_year++;
        }
        
        $next_send = mktime($hour, $minute, 0, $next_month, $day, $next_year);
    }
    
    return $next_send;
}