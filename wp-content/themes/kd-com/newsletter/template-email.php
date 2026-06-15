<?php
/**
 * Template Email Newsletter - VERSION AVEC INLINE STYLES
 * Les styles sont générés dynamiquement en PHP pour être inline dans le HTML
 * Compatible avec tous les clients email
 * 
 * Variables disponibles:
 * - $logo_url: URL du logo (en URL absolue)
 * - $news_query: WP_Query des actualités
 * - $events_single: Array d'IDs d'événements ponctuels
 * - $events_multi_day: Array d'IDs d'événements multi-jours
 * - $events_recurring: Array d'IDs d'événements récurrents
 * - $events_module_enabled: bool
 * - $site_name: Nom du site
 * - $current_month: Mois actuel formaté
 * - $current_year: Année actuelle
 */

if (!defined('ABSPATH')) exit;

// Charger les styles inline
require_once(BREVO_NEWSLETTER_DIR . '/newsletter-styles-inline.php');

/**
 * Helper function pour formater la date selon le type d'événement
 */
function newsletter_format_event_date($post_id) {
    $event_type = get_field('event_type', $post_id);
    
    switch ($event_type) {
        case 'multi_day':
            $start_date = get_field('event_start_date', $post_id);
            $end_date = get_field('event_end_date', $post_id);
            
            $start_formatted = date_i18n('j F Y', strtotime($start_date));
            $end_formatted = date_i18n('j F Y', strtotime($end_date));
            
            if (date('Y-m', strtotime($start_date)) === date('Y-m', strtotime($end_date))) {
                $start_day = date_i18n('j', strtotime($start_date));
                return "Du $start_day au $end_formatted";
            }
            
            return "Du $start_formatted au $end_formatted";
            
        case 'recurring':
            $recurrence = get_field('event_recurrence', $post_id);
            $end_date = get_field('event_end_date', $post_id);
            
            $recurrence_labels = array(
                'daily' => 'Tous les jours',
                'weekly_monday' => 'Tous les lundis',
                'weekly_tuesday' => 'Tous les mardis',
                'weekly_wednesday' => 'Tous les mercredis',
                'weekly_thursday' => 'Tous les jeudis',
                'weekly_friday' => 'Tous les vendredis',
                'weekly_saturday' => 'Tous les samedis',
                'weekly_sunday' => 'Tous les dimanches',
                'monthly' => 'Tous les mois',
            );
            
            $recurrence_text = $recurrence_labels[$recurrence] ?? 'Récurrent';
            $end_formatted = date_i18n('j F Y', strtotime($end_date));
            
            return $recurrence_text . " (jusqu'au $end_formatted)";
            
        case 'single':
        default:
            $event_date = get_field('event_date', $post_id);
            return date_i18n('j F Y', strtotime($event_date));
    }
}

/**
 * Helper function pour calculer l'affichage du prix
 */
function newsletter_get_price_display($event_id) {
    $event_price_type = get_field('event_price_type', $event_id);
    $event_prices = get_field('event_prices', $event_id);
    
    if ($event_price_type === 'free') {
        return '🎫 Gratuit';
    }
    
    if ($event_price_type === 'paid' && !empty($event_prices)) {
        $amounts = array();
        foreach ($event_prices as $price) {
            $amount_str = preg_replace('/[^0-9,.]/', '', $price['price_amount']);
            $amount_str = str_replace(',', '.', $amount_str);
            if (is_numeric($amount_str)) {
                $amounts[] = floatval($amount_str);
            }
        }
        
        if (!empty($amounts)) {
            $min_price = min($amounts);
            $max_price = max($amounts);
            
            if ($min_price === $max_price) {
                return '🎫 ' . number_format($min_price, 2, ',', ' ') . ' €';
            } else {
                return '🎫 De ' . number_format($min_price, 2, ',', ' ') . ' € à ' . number_format($max_price, 2, ',', ' ') . ' €';
            }
        }
    }
    
    return '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Newsletter - <?php echo esc_html($current_month); ?></title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, Helvetica, sans-serif !important;}
    </style>
    <![endif]-->
</head>
<body<?php echo style('body'); ?>>
    
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"<?php echo style('email-wrapper'); ?>>
        <tr>
            <td<?php echo style('email-wrapper-td'); ?>>
                
                <!-- Container principal -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="600"<?php echo style('email-container'); ?>>
                    
                    <!-- Lien aperçu navigateur -->
                    <tr>
                        <td<?php echo style('browser-preview-link'); ?>>
                            <span<?php echo style('browser-preview-span'); ?>>
                                Problème d'affichage ? 
                                <a href="{{ mirror }}"<?php echo style('browser-preview-link-a'); ?>>
                                    Voir cette newsletter dans votre navigateur
                                </a>
                            </span>
                        </td>
                    </tr>
                    
                    <!-- Header avec logo -->
                    <tr>
                        <td<?php echo style('newsletter-header-blanc'); ?>>
                            <img src="<?php echo esc_attr($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>"<?php echo style('newsletter-header-blanc-img'); ?> />
                        </td>
                    </tr>
                    
                    <!-- Header titre -->
                    <tr>
                        <td<?php echo style('newsletter-header'); ?>>
                            <h1<?php echo style('newsletter-header-h1'); ?>>📰 Newsletter - <?php echo esc_html($current_month); ?></h1>
                        </td>
                    </tr>
                    
                    <!-- Contenu principal -->
                    <tr>
                        <td<?php echo style('newsletter-content'); ?>>
                            
                            <!-- Section Actualités -->
                            <?php if ($news_query && $news_query->have_posts()) : ?>
                                <h2<?php echo style('section-title'); ?>>🔥 Dernières Actualités</h2>
                                
                                <?php while ($news_query->have_posts()) : $news_query->the_post(); ?>
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"<?php echo style('post-item'); ?>>
                                        <tr>
                                            <td>
                                                <!-- Meta -->
                                                <div<?php echo style('post-meta'); ?>>
                                                    <?php echo get_the_date(); ?> • <?php echo strip_tags(get_the_category_list(', ')); ?>
                                                </div>
                                                
                                                <!-- Titre -->
                                                <div<?php echo style('post-title'); ?>>
                                                    <a href="<?php echo esc_url(get_permalink()); ?>"<?php echo style('post-title-a'); ?>>
                                                        <?php echo esc_html(get_the_title()); ?>
                                                    </a>
                                                </div>
                                                
                                                <!-- Image -->
                                                <?php if (has_post_thumbnail()) : ?>
                                                    <div<?php echo style('post-image'); ?>>
                                                        <img src="<?php echo esc_attr(get_the_post_thumbnail_url(get_the_ID(), 'large')); ?>" 
                                                             alt="<?php echo esc_attr(get_the_title()); ?>"<?php echo style('post-image-img'); ?> />
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <!-- Extrait -->
                                                <div<?php echo style('post-excerpt'); ?>>
                                                    <?php echo wp_trim_words(get_the_excerpt(), 25, '...'); ?>
                                                </div>
                                                
                                                <!-- Bouton -->
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                                    <tr>
                                                        <td<?php echo style('button-wrapper'); ?>>
                                                            <a href="<?php echo esc_url(get_permalink()); ?>"<?php echo style('read-more'); ?>>
                                                                Lire la suite →
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                <?php endwhile; ?>
                                
                            <?php endif; ?>

                            <!-- Section Événements Ponctuels -->
                            <?php if ($events_module_enabled && !empty($events_single)) : ?>
                                <h2<?php echo style('section-title'); ?>>📅 Événements à venir</h2>
                                
                                <?php foreach ($events_single as $event_id) : 
                                    $post = get_post($event_id);
                                    setup_postdata($post);
                                    
                                    $event_time = get_field('event_time', $event_id);
                                    $event_location = get_field('event_location', $event_id);
                                    $formatted_date = newsletter_format_event_date($event_id);
                                    $price_display = newsletter_get_price_display($event_id);
                                ?>
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"<?php echo style('post-item'); ?>>
                                        <tr>
                                            <td>
                                                <div<?php echo style('post-meta'); ?>>
                                                    📆 <?php echo esc_html($formatted_date); ?>
                                                    <?php if ($event_time) : ?>
                                                        • 🕐 <?php echo esc_html($event_time); ?>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div<?php echo style('post-title'); ?>>
                                                    <a href="<?php echo esc_url(get_permalink($event_id)); ?>"<?php echo style('post-title-a'); ?>>
                                                        <?php echo esc_html(get_the_title($event_id)); ?>
                                                    </a>
                                                </div>
                                                
                                                <?php if ($event_location) : ?>
                                                    <div<?php echo style('event-meta'); ?>>
                                                        📍 <?php echo esc_html($event_location); ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($price_display) : ?>
                                                    <div<?php echo style('event-meta'); ?>>
                                                        <?php echo esc_html($price_display); ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div<?php echo style('post-excerpt'); ?>>
                                                    <?php echo wp_trim_words(get_the_excerpt(), 25, '...'); ?>
                                                </div>
                                                
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                                    <tr>
                                                        <td<?php echo style('button-wrapper'); ?>>
                                                            <a href="<?php echo esc_url(get_permalink($event_id)); ?>"<?php echo style('read-more'); ?>>
                                                                Voir l'événement →
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                <?php endforeach; wp_reset_postdata(); ?>
                                
                            <?php endif; ?>

                            <!-- Section Événements Multi-jours -->
                            <?php if ($events_module_enabled && !empty($events_multi_day)) : ?>
                                <h2<?php echo style('section-title-multi-day'); ?>>📆 Événements sur plusieurs jours</h2>
                                
                                <?php foreach ($events_multi_day as $event_id) : 
                                    $post = get_post($event_id);
                                    setup_postdata($post);
                                    
                                    $event_time = get_field('event_time', $event_id);
                                    $event_location = get_field('event_location', $event_id);
                                    $formatted_date = newsletter_format_event_date($event_id);
                                    $price_display = newsletter_get_price_display($event_id);
                                ?>
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"<?php echo style('post-item'); ?>>
                                        <tr>
                                            <td>
                                                <div<?php echo style('post-meta'); ?>>
                                                    📆 <?php echo esc_html($formatted_date); ?>
                                                    
                                                    <?php if ($event_time) : ?>
                                                        <br>🕐 <?php echo esc_html($event_time); ?>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div<?php echo style('post-title'); ?>>
                                                    <a href="<?php echo esc_url(get_permalink($event_id)); ?>"<?php echo style('post-title-a-multi-day'); ?>>
                                                        <?php echo esc_html(get_the_title($event_id)); ?>
                                                    </a>
                                                </div>
                                                
                                                <?php if ($event_location) : ?>
                                                    <div<?php echo style('event-meta'); ?>>
                                                        📍 <?php echo esc_html($event_location); ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($price_display) : ?>
                                                    <div<?php echo style('event-meta'); ?>>
                                                        <?php echo esc_html($price_display); ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div<?php echo style('post-excerpt'); ?>>
                                                    <?php echo wp_trim_words(get_the_excerpt(), 25, '...'); ?>
                                                </div>
                                                
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                                    <tr>
                                                        <td<?php echo style('button-wrapper-multi-day'); ?>>
                                                            <a href="<?php echo esc_url(get_permalink($event_id)); ?>"<?php echo style('read-more-multi-day'); ?>>
                                                                Voir l'événement →
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                <?php endforeach; wp_reset_postdata(); ?>
                                
                            <?php endif; ?>

                            <!-- Section Événements Récurrents -->
                            <?php if ($events_module_enabled && !empty($events_recurring)) : ?>
                                <h2<?php echo style('section-title-recurring'); ?>>🔁 Événements récurrents</h2>
                                
                                <?php foreach ($events_recurring as $event_id) : 
                                    $post = get_post($event_id);
                                    setup_postdata($post);
                                    
                                    $event_time = get_field('event_time', $event_id);
                                    $event_location = get_field('event_location', $event_id);
                                    $formatted_date = newsletter_format_event_date($event_id);
                                    $price_display = newsletter_get_price_display($event_id);
                                ?>
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"<?php echo style('post-item'); ?>>
                                        <tr>
                                            <td>
                                                <div<?php echo style('post-meta'); ?>>
                                                    📆 <?php echo esc_html($formatted_date); ?>
                                                    
                                                    <?php if ($event_time) : ?>
                                                        <br>🕐 <?php echo esc_html($event_time); ?>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div<?php echo style('post-title'); ?>>
                                                    <a href="<?php echo esc_url(get_permalink($event_id)); ?>"<?php echo style('post-title-a-recurring'); ?>>
                                                        <?php echo esc_html(get_the_title($event_id)); ?>
                                                    </a>
                                                </div>
                                                
                                                <?php if ($event_location) : ?>
                                                    <div<?php echo style('event-meta'); ?>>
                                                        📍 <?php echo esc_html($event_location); ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($price_display) : ?>
                                                    <div<?php echo style('event-meta'); ?>>
                                                        <?php echo esc_html($price_display); ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div<?php echo style('post-excerpt'); ?>>
                                                    <?php echo wp_trim_words(get_the_excerpt(), 25, '...'); ?>
                                                </div>
                                                
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                                    <tr>
                                                        <td<?php echo style('button-wrapper-recurring'); ?>>
                                                            <a href="<?php echo esc_url(get_permalink($event_id)); ?>"<?php echo style('read-more-recurring'); ?>>
                                                                Voir l'événement →
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>

                                <?php endforeach; wp_reset_postdata(); ?>
                                
                            <?php endif; ?>
                            <!-- Section Projets / Réalisations -->
                            <?php if ($portfolio_module_enabled && $projects_query && $projects_query->have_posts()) : ?>
                                <h2<?php echo style('section-title'); ?>>🎨 Dernières réalisations</h2>
                                
                                <?php while ($projects_query->have_posts()) : $projects_query->the_post(); ?>
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"<?php echo style('post-item'); ?>>
                                        <tr>
                                            <td>
                                                <!-- Meta -->
                                                <div<?php echo style('post-meta'); ?>>
                                                    <?php echo get_the_date(); ?>
                                                    <?php
                                                    $cats = get_the_terms(get_the_ID(), 'project_category');
                                                    if ($cats && !is_wp_error($cats)) {
                                                        echo ' • ' . esc_html(implode(', ', wp_list_pluck($cats, 'name')));
                                                    }
                                                    ?>
                                                </div>
                                                
                                                <!-- Titre -->
                                                <div<?php echo style('post-title'); ?>>
                                                    <a href="<?php echo esc_url(get_permalink()); ?>"<?php echo style('post-title-a'); ?>>
                                                        <?php echo esc_html(get_the_title()); ?>
                                                    </a>
                                                </div>
                                                
                                                <!-- Image -->
                                                <?php if (has_post_thumbnail()) : ?>
                                                    <div<?php echo style('post-image'); ?>>
                                                        <img src="<?php echo esc_attr(get_the_post_thumbnail_url(get_the_ID(), 'large')); ?>"
                                                            alt="<?php echo esc_attr(get_the_title()); ?>"<?php echo style('post-image-img'); ?> />
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <!-- Extrait -->
                                                <div<?php echo style('post-excerpt'); ?>>
                                                    <?php echo wp_trim_words(get_the_excerpt(), 25, '...'); ?>
                                                </div>
                                                
                                                <!-- Bouton -->
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                                    <tr>
                                                        <td<?php echo style('button-wrapper'); ?>>
                                                            <a href="<?php echo esc_url(get_permalink()); ?>"<?php echo style('read-more'); ?>>
                                                                Voir la réalisation →
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                <?php endwhile; wp_reset_postdata(); ?>

                            <?php endif; ?>

                            <!-- Message si aucun contenu -->
                            <?php 
                            $has_news     = $news_query && $news_query->have_posts();
$has_events   = $events_module_enabled && (!empty($events_single) || !empty($events_multi_day) || !empty($events_recurring));
$has_projects = $portfolio_module_enabled && $projects_query && $projects_query->have_posts();
if (!$has_news && !$has_events && !$has_projects) :
                            ?>
                                <div<?php echo style('no-content'); ?>>
                                    Aucune actualité ou événement pour le moment. Restez connecté !
                                </div>
                            <?php endif; ?>

                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td<?php echo style('newsletter-footer'); ?>>
                            <p<?php echo style('newsletter-footer-p'); ?>>
                                Vous recevez cet email car vous êtes abonné à notre newsletter.
                            </p>
                            <p<?php echo style('newsletter-footer-p'); ?>>
                                <a href="<?php echo home_url('/?brevo_unsubscribe=1&email={{contact.EMAIL}}'); ?>"<?php echo style('newsletter-footer-a'); ?>>Se désabonner</a> | 
                                <a href="<?php echo home_url('/'); ?>"<?php echo style('newsletter-footer-a'); ?>>Voir le site web</a>
                            </p>
                            <p<?php echo style('newsletter-footer-p'); ?>>
                                &copy; <?php echo esc_html($current_year); ?> <?php echo esc_html($site_name); ?> - Tous droits réservés
                            </p>
                        </td>
                    </tr>
                    
                </table>
                
            </td>
        </tr>
    </table>

</body>
</html>