<?php

/**
 * Formate une heure au format "10h25"
 * Accepte "10:25", "10:25:00", "10h25"
 */
function format_event_time($time) {
    $formatted = preg_replace('/^(\d{1,2}):(\d{2})(:\d{2})?$/', '$1h$2', $time);
    return $formatted ?: $time;
}

/**
 * Fonction helper pour obtenir la date de référence d'un événement
 * (pour le tri et le filtrage)
 * Retourne la date la plus proche de "aujourd'hui" pour un affichage pertinent
 */
function get_event_reference_date($post_id) {
    $event_type = get_field('event_type', $post_id);
    $today = date('Y-m-d');
    
    switch ($event_type) {
        case 'multi_day':
            $start_date = get_field('event_start_date', $post_id);
            $end_date = get_field('event_end_date', $post_id);
            
            // Si l'événement est en cours, on retourne aujourd'hui
            if ($start_date <= $today && $end_date >= $today) {
                return $today;
            }
            // Si l'événement n'a pas encore commencé, on retourne la date de début
            if ($start_date > $today) {
                return $start_date;
            }
            // Sinon, c'est un événement passé, on retourne la date de fin
            return $end_date;
            
        case 'recurring':
            $start_date = get_field('event_start_date', $post_id);
            $end_date = get_field('event_end_date', $post_id);
            $recurrence = get_field('event_recurrence', $post_id);
            
            // Si l'événement récurrent n'a pas encore commencé
            if ($start_date > $today) {
                return $start_date;
            }
            
            // Si l'événement récurrent est terminé
            if ($end_date < $today) {
                return $end_date;
            }
            
            // L'événement est en cours : calculer la prochaine occurrence
            return get_next_occurrence_date($start_date, $end_date, $recurrence, $today);
            
        case 'single':
        default:
            return get_field('event_date', $post_id);
    }
}

/**
 * Calcule la prochaine occurrence d'un événement récurrent
 * 
 * @param string $start_date Date de début de la période
 * @param string $end_date Date de fin de la période
 * @param string $recurrence Type de récurrence
 * @param string $today Date du jour
 * @return string Date de la prochaine occurrence
 */
function get_next_occurrence_date($start_date, $end_date, $recurrence, $today) {
    $start = strtotime($start_date);
    $end = strtotime($end_date);
    $current = strtotime($today);
    
    // Si aujourd'hui est avant le début, retourner la date de début
    if ($current < $start) {
        return $start_date;
    }
    
    // Si aujourd'hui est après la fin, retourner la date de fin
    if ($current > $end) {
        return $end_date;
    }
    
    switch ($recurrence) {
        case 'daily':
            // Pour un événement quotidien en cours, retourner aujourd'hui
            return $today;
            
        case 'weekly_monday':
        case 'weekly_tuesday':
        case 'weekly_wednesday':
        case 'weekly_thursday':
        case 'weekly_friday':
        case 'weekly_saturday':
        case 'weekly_sunday':
            // Extraire le jour de la semaine (0=dimanche, 1=lundi, ..., 6=samedi)
            $target_day_map = array(
                'weekly_sunday' => 0,
                'weekly_monday' => 1,
                'weekly_tuesday' => 2,
                'weekly_wednesday' => 3,
                'weekly_thursday' => 4,
                'weekly_friday' => 5,
                'weekly_saturday' => 6,
            );
            
            $target_day = $target_day_map[$recurrence];
            $current_day = (int)date('w', $current);
            
            // Si aujourd'hui est le jour de l'événement
            if ($current_day === $target_day) {
                return $today;
            }
            
            // Calculer le prochain jour correspondant
            $days_until_next = ($target_day - $current_day + 7) % 7;
            if ($days_until_next === 0) {
                $days_until_next = 7;
            }
            
            $next_occurrence = strtotime("+{$days_until_next} days", $current);
            
            // Vérifier que la prochaine occurrence est bien dans la période
            if ($next_occurrence > $end) {
                return $end_date;
            }
            
            return date('Y-m-d', $next_occurrence);
            
        case 'monthly':
            // Obtenir le jour du mois de la date de début
            $target_day_of_month = (int)date('j', $start);
            $current_day_of_month = (int)date('j', $current);
            $current_month = date('Y-m', $current);
            
            // Si aujourd'hui est le jour de l'événement
            if ($current_day_of_month === $target_day_of_month) {
                return $today;
            }
            
            // Si on n'a pas encore atteint le jour ce mois-ci
            if ($current_day_of_month < $target_day_of_month) {
                $next_occurrence = strtotime($current_month . '-' . str_pad($target_day_of_month, 2, '0', STR_PAD_LEFT));
            } else {
                // Aller au mois prochain
                $next_month = strtotime('first day of next month', $current);
                $next_occurrence = strtotime(date('Y-m', $next_month) . '-' . str_pad($target_day_of_month, 2, '0', STR_PAD_LEFT));
            }
            
            // Vérifier que la prochaine occurrence est bien dans la période
            if ($next_occurrence > $end) {
                return $end_date;
            }
            
            return date('Y-m-d', $next_occurrence);
            
        default:
            return $today;
    }
}

/**
 * Fonction helper pour formater l'affichage de la date selon le type d'événement
 */
function format_event_date_display($post_id) {
    $event_type = get_field('event_type', $post_id);
    
    switch ($event_type) {
        case 'multi_day':
            $start_date = get_field('event_start_date', $post_id);
            $end_date = get_field('event_end_date', $post_id);
            
            // Si même mois et année : "Du mardi 15 au vendredi 20 mars 2026"
            if (date('Y-m', strtotime($start_date)) === date('Y-m', strtotime($end_date))) {
                $start_formatted = date_i18n('l j', strtotime($start_date));
                $end_formatted = date_i18n('l j F Y', strtotime($end_date));
                return "Du $start_formatted au $end_formatted";
            }

            // Mois différents : "Du mardi 15 mars au vendredi 20 avril 2026"
            $start_formatted = date_i18n('l j F Y', strtotime($start_date));
            $end_formatted = date_i18n('l j F Y', strtotime($end_date));
            return "Du $start_formatted au $end_formatted";
            
        case 'recurring':
            $recurrence = get_field('event_recurrence', $post_id);
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
            
            $end_date = get_field('event_end_date', $post_id);
            $end_formatted = date_i18n('l j F Y', strtotime($end_date));
            
            return ($recurrence_labels[$recurrence] ?? 'Récurrent') . " (jusqu'au $end_formatted)";
            
        case 'single':
        default:
            $event_date = get_field('event_date', $post_id);
            return date_i18n('l j F Y', strtotime($event_date));
    }
}

function display_events_shortcode($atts) {
    // Attributs du shortcode
    $atts = shortcode_atts(
        array(
            'limit' => -1,
            'show_past' => 'false',
        ),
        $atts,
        'display_events'
    );

    $today = date('Y-m-d');
    // Initialisation de la variable output
    $output = '';
    
    // Requête pour récupérer tous les événements
    $all_events_args = array(
        'post_type' => 'evenements',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    $all_events = new WP_Query($all_events_args);
    $upcoming_events_array = array();
    $past_events_array = array();
    
    // Filtrage et préparation des événements avec leur date de référence
    if ($all_events->have_posts()) {
        while ($all_events->have_posts()) {
            $all_events->the_post();
            $post_id = get_the_ID();
            $event_type = get_field('event_type', $post_id);
            
            // Obtenir la date de référence pour ce post
            $reference_date = get_event_reference_date($post_id);
            
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
                $upcoming_events_array[] = array(
                    'id' => $post_id,
                    'reference_date' => $reference_date,
                );
            } else {
                $past_events_array[] = array(
                    'id' => $post_id,
                    'reference_date' => $reference_date,
                );
            }
        }
        wp_reset_postdata();
    }
    
    // Trier les événements à venir par date de référence (du plus proche au plus loin)
    usort($upcoming_events_array, function($a, $b) {
        return strcmp($a['reference_date'], $b['reference_date']);
    });
    
    // Trier les événements passés par date de référence (du plus récent au plus ancien)
    usort($past_events_array, function($a, $b) {
        return strcmp($b['reference_date'], $a['reference_date']);
    });
    
    // Appliquer la limite si spécifiée
    if ($atts['limit'] != -1) {
        $upcoming_events_array = array_slice($upcoming_events_array, 0, (int)$atts['limit']);
        $past_events_array = array_slice($past_events_array, 0, (int)$atts['limit']);
    }

    // Début de la structure HTML
    $output .= '<div class="events-container">';

    // Affichage des événements à venir
    if (!empty($upcoming_events_array)) {
        $output .= '<div class="events-grid">';
        
        foreach ($upcoming_events_array as $event) {
            $output .= generate_event_card($event['id']);
        }
        
        $output .= '</div>'; // Fin events-grid
    } else {
        $output .= '<div class="no-events"><p>Aucun événement à venir pour le moment.</p></div>';
    }
    
    // Affichage des événements passés (si demandé)
    if ($atts['show_past'] === 'true' && !empty($past_events_array)) {
        $output .= '<h2 class="events-section-title">Événements passés</h2>';
        $output .= '<div class="events-grid">';
        
        foreach ($past_events_array as $event) {
            $output .= generate_event_card($event['id'], true);
        }
        
        $output .= '</div>'; // Fin events-grid
    }

    $output .= '</div>'; // Fin events-container

    return $output;
}

// Fonction pour générer une carte d'événement
function generate_event_card($post_id, $is_past = false) {
    // Récupération des champs ACF
    $event_type = get_field('event_type', $post_id);
    $event_time = get_field('event_time', $post_id);
    $event_location = get_field('event_location', $post_id);
    $event_ticket_link = get_field('event_ticket_link', $post_id);
    $event_image = get_the_post_thumbnail_url($post_id, 'medium');
    $event_price_type = get_field('event_price_type', $post_id);
    $event_prices = get_field('event_prices', $post_id);

    // Formatage de la date selon le type
    $formatted_date = format_event_date_display($post_id);
    
    // Date pour l'attribut datetime
    $datetime_value = '';
    switch ($event_type) {
        case 'multi_day':
        case 'recurring':
            $datetime_value = get_field('event_start_date', $post_id);
            break;
        case 'single':
        default:
            $datetime_value = get_field('event_date', $post_id);
            break;
    }
    
    // Formatage du prix
    $price_display = '';
    if ($event_price_type === 'free') {
        $price_display = '<div class="event-price free"><span><i class="fa-solid fa-ticket"></i> Gratuit</span></div>';
    } elseif ($event_price_type === 'paid' && !empty($event_prices)) {
        // Extraire les montants pour trouver min et max
        $amounts = array();
        foreach ($event_prices as $price) {
            // Extraire le nombre du montant (ex: "20€" -> 20)
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
                $price_display = '<div class="event-price"><span><i class="fa-solid fa-ticket"></i> ' . number_format($min_price, 2, ',', ' ') . ' €</span></div>';
            } else {
                $price_display = '<div class="event-price"><span><i class="fa-solid fa-ticket"></i> De ' . number_format($min_price, 2, ',', ' ') . ' € à ' . number_format($max_price, 2, ',', ' ') . ' €</span></div>';
            }
        }
    }

    // Classe CSS pour les événements passés et récurrents
    $card_class = 'event-card';
    if ($is_past) {
        $card_class .= ' past';
    }
    if ($event_type === 'recurring') {
        $card_class .= ' recurring';
    } elseif ($event_type === 'multi_day') {
        $card_class .= ' multi-day';
    }

    // Structure HTML pour la carte événement
    $output = '
    <div class="' . $card_class . '">
        <div class="event-image">
            <img src="' . ($event_image ? esc_url($event_image) : 'https://via.placeholder.com/400x200') . '" alt="' . esc_attr(get_the_title($post_id)) . '" />';
    
    // Badge pour événements récurrents ou multi-jours
    if ($event_type === 'recurring') {
        $output .= '<span class="event-badge recurring-badge"><i class="fa-solid fa-repeat"></i> Récurrent</span>';
    } elseif ($event_type === 'multi_day') {
        $output .= '<span class="event-badge multi-day-badge"><i class="fa-solid fa-calendar-days"></i> Plusieurs jours</span>';
    }
    
    $output .= '
        </div>
        <div class="event-content">
            <h2 class="event-title">
                <a href="' . esc_url(get_permalink($post_id)) . '">' . get_the_title($post_id) . '</a>
            </h2>
            <div class="event-meta">
                <div class="event-date">
                    <i class="fa-regular fa-calendar"></i> <time datetime="' . esc_attr($datetime_value) . '">' . $formatted_date . '</time>
                </div>';
    
    if ($event_time) {
        $output .= '
                <div class="event-time">
                    <i class="fa-regular fa-clock"></i> <time datetime="' . esc_attr($event_time) . '">' . esc_html(format_event_time($event_time)) . '</time>
                </div>';
    }
    
    if ($event_location) {
        $output .= '
                <div class="event-location">
                    <span><i class="fa-regular fa-map"></i> ' . esc_html($event_location) . '</span>
                </div>';
    }
    
    // Affichage du prix
    $output .= $price_display;
    
    $output .= '
            </div>
            <div class="event-links">
                <a class="link-details" href="' . esc_url(get_permalink($post_id)) . '" aria-label="En savoir plus sur ' . esc_attr(get_the_title($post_id)) . '">Détails</a>';
    
    if ($event_ticket_link && !$is_past) {
        $output .= '
                <a class="link-tickets" href="' . esc_url($event_ticket_link) . '" aria-label="Acheter des billets pour ' . esc_attr(get_the_title($post_id)) . '" target="_blank" rel="noopener">Billetterie</a>';
    }
    
    $output .= '
            </div>
        </div>
    </div>';
    
    return $output;
}
add_shortcode('display_events', 'display_events_shortcode');


// affichage des tarifs sur la page de chaque événement
function display_event_prices_shortcode($atts) {
    // Attributs du shortcode
    $atts = shortcode_atts(
        array(
            'event_id' => get_the_ID(), // Par défaut, l'événement actuel
            'show_title' => 'true',
        ),
        $atts,
        'display_event_prices'
    );

    // Récupération des champs ACF
    $event_price_type = get_field('event_price_type', $atts['event_id']);
    $event_prices = get_field('event_prices', $atts['event_id']);
    
    // Début de la structure HTML
    $output = '<div class="event-prices-container">';
    
    // Titre optionnel
    if ($atts['show_title'] === 'true') {
        $output .= '<h3 class="prices-title">Tarifs</h3>';
    }
    
    // Vérification du type de tarif
    if ($event_price_type === 'free') {
        // Événement gratuit
        $output .= '<div class="price-item free">';
        $output .= '<div class="price-icon"><i class="fa-solid fa-ticket"></i></div>';
        $output .= '<div class="price-details">';
        $output .= '<p class="price-label">Entrée gratuite</p>';
        $output .= '</div>';
        $output .= '</div>';
        
    } elseif ($event_price_type === 'paid' && !empty($event_prices)) {
        // Événement payant avec tarifs multiples
        $output .= '<div class="prices-list">';
        
        foreach ($event_prices as $price) {
            $price_label = isset($price['price_label']) ? $price['price_label'] : '';
            $price_amount = isset($price['price_amount']) ? $price['price_amount'] : '';
            $price_description = isset($price['price_description']) ? $price['price_description'] : '';
            
            $output .= '<div class="price-item">';
            $output .= '<div class="price-icon"><i class="fa-solid fa-ticket"></i></div>';
            $output .= '<div class="price-details">';
            
            if ($price_label) {
                $output .= '<p class="price-label">' . esc_html($price_label) . '</p>';
            }
            
            if ($price_amount) {
                // Formatage du montant
                $amount_str = preg_replace('/[^0-9,.]/', '', $price_amount);
                $amount_str = str_replace(',', '.', $amount_str);
                
                if (is_numeric($amount_str)) {
                    $formatted_amount = number_format(floatval($amount_str), 2, ',', ' ') . ' €';
                } else {
                    $formatted_amount = esc_html($price_amount);
                }
                
                $output .= '<p class="price-amount">' . $formatted_amount . '</p>';
            }
            
            if ($price_description) {
                $output .= '<p class="price-description">' . esc_html($price_description) . '</p>';
            }
            
            $output .= '</div>'; // Fin price-details
            $output .= '</div>'; // Fin price-item
        }
        
        $output .= '</div>'; // Fin prices-list
        
    } else {
        // Aucun tarif défini
        $output .= '<div class="no-prices">';
        $output .= '<p>Tarifs non disponibles pour le moment.</p>';
        $output .= '</div>';
    }

    // Bouton de billetterie (si disponible)
    $event_ticket_link = get_field('event_ticket_link', $atts['event_id']);
    if ($event_ticket_link) {
        $event_title = get_the_title($atts['event_id']);
        $output .= '<div class="event-ticket-button">';
        $output .= '<a class="u-link-arrow" href="' . esc_url($event_ticket_link) . '" aria-label="Acheter des billets pour ' . esc_attr($event_title) . '" target="_blank" rel="noopener">';
        $output .= '<i class="fa-solid fa-ticket"></i> Réserver / Billetterie';
        $output .= '</a>';
        $output .= '</div>';
    }
    
    $output .= '</div>'; // Fin event-prices-container
    
    return $output;
}

add_shortcode('display_event_prices', 'display_event_prices_shortcode');



// affichage de la carte sur les événements
function display_event_map_shortcode($atts) {
    // Attributs du shortcode
    $atts = shortcode_atts(
        array(
            'event_id' => get_the_ID(), // Par défaut, l'événement actuel
            'height' => '400px',
            'zoom' => '15',
            'marker_color' => '#e74c3c', // Couleur du marqueur par défaut (rouge)
        ),
        $atts,
        'display_event_map'
    );

    // Récupération des champs ACF
    $event_show_map = get_field('event_show_map', $atts['event_id']);
    $event_address = get_field('event_address', $atts['event_id']);
    $event_location = get_field('event_location', $atts['event_id']);
    
    // Vérifier si la carte doit être affichée
    if (!$event_show_map) {
        return ''; // Ne rien afficher si l'option n'est pas cochée
    }
    
    // Vérifier si une adresse est disponible
    if (empty($event_address)) {
        return '<div class="event-map-error"><p>Aucune adresse n\'a été renseignée pour cet événement.</p></div>';
    }
    
    // Générer un ID unique pour la carte
    $map_id = 'event-map-' . $atts['event_id'];
    
    // Encoder l'adresse pour l'URL de géocodage
    $encoded_address = urlencode($event_address);
    
    // Vérifier si Tarteaucitron est activé
    $tac_options = get_option('kd_tarteaucitron_settings', array());
    $tac_enabled = !empty($tac_options['enabled']) && !empty($tac_options['services']['openstreetmap']['enabled']);
    
    // Début de la structure HTML
    $output = '<div class="event-map-container">';
    $output .= '<h3 class="map-title">Localisation</h3>';
    
    if ($event_location) {
        $output .= '<h4 class="map-location"><i class="fa-solid fa-location-dot"></i> ' . esc_html($event_location) . '</h4>';
        $output .= '<p class="map-address"><i class="fa-solid fa-location-dot"></i> ' . esc_html($event_address) . '</p>';
    }
    
    if ($tac_enabled) {
        // Version compatible Tarteaucitron
        // On va d'abord géocoder pour avoir les coordonnées, puis construire l'URL
        $output .= '<div class="openstreetmap-wrapper" data-address="' . esc_attr($encoded_address) . '" data-location="' . esc_attr($event_location) . '" data-zoom="' . esc_attr($atts['zoom']) . '" data-height="' . esc_attr($atts['height']) . '" data-marker-color="' . esc_attr($atts['marker_color']) . '"></div>';
    } else {
        // Version sans Tarteaucitron (Leaflet.js)
        $output .= '<div id="' . esc_attr($map_id) . '" class="event-map" data-address="' . esc_attr($encoded_address) . '" data-location="' . esc_attr($event_location) . '" data-zoom="' . esc_attr($atts['zoom']) . '" data-marker-color="' . esc_attr($atts['marker_color']) . '" style="height: ' . esc_attr($atts['height']) . '; width: 100%;"></div>';
    }
    
    $output .= '</div>';
    
    // Script pour initialiser la carte uniquement si Tarteaucitron n'est pas activé
    if (!$tac_enabled) {
        $output .= "
        <script>
        (function() {
            var mapId = '" . esc_js($map_id) . "';
            var mapElement = document.getElementById(mapId);
            
            if (!mapElement) return;
            
            var address = mapElement.getAttribute('data-address');
            var location = mapElement.getAttribute('data-location');
            var zoom = parseInt(mapElement.getAttribute('data-zoom')) || 15;
            var markerColor = mapElement.getAttribute('data-marker-color') || '#e74c3c';
            
            // Charger Leaflet.js si ce n'est pas déjà fait
            if (typeof L === 'undefined') {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(link);
                
                var script = document.createElement('script');
                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                script.onload = initMap;
                document.head.appendChild(script);
            } else {
                initMap();
            }
            
            function createColoredMarkerIcon(color) {
                // Créer un marqueur SVG personnalisé avec la couleur choisie
                var svgIcon = '<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 36\" width=\"32\" height=\"48\">' +
                    '<path fill=\"' + color + '\" stroke=\"#fff\" stroke-width=\"1.5\" d=\"M12 0C5.4 0 0 5.4 0 12c0 7.2 12 24 12 24s12-16.8 12-24c0-6.6-5.4-12-12-12z\"/>' +
                    '<circle cx=\"12\" cy=\"12\" r=\"4\" fill=\"#fff\"/>' +
                    '</svg>';
                
                return L.divIcon({
                    html: svgIcon,
                    className: 'custom-marker-icon',
                    iconSize: [32, 48],
                    iconAnchor: [16, 48],
                    popupAnchor: [0, -48]
                });
            }
            
            function initMap() {
                // Géocodage avec Nominatim (OpenStreetMap)
                fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + address)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            var lat = parseFloat(data[0].lat);
                            var lon = parseFloat(data[0].lon);
                            
                            // Initialiser la carte
                            var map = L.map(mapId).setView([lat, lon], zoom);
                            
                            // Ajouter les tuiles OpenStreetMap
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors',
                                maxZoom: 19
                            }).addTo(map);
                            
                            // Ajouter un marqueur avec l'icône personnalisée
                            var markerIcon = createColoredMarkerIcon(markerColor);
                            var marker = L.marker([lat, lon], { icon: markerIcon }).addTo(map);
                            " . (!empty($event_location) ? "marker.bindPopup('" . esc_js($event_location) . "').openPopup();" : "") . "
                        } else {
                            mapElement.innerHTML = '<p style=\"padding: 2rem; text-align: center; color: #666;\">Impossible de localiser l\\'adresse sur la carte.</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Erreur de géocodage:', error);
                        mapElement.innerHTML = '<p style=\"padding: 2rem; text-align: center; color: #666;\">Erreur lors du chargement de la carte.</p>';
                    });
            }
        })();
        </script>";
    } else {
        // Script pour gérer le géocodage avec Tarteaucitron
        $output .= "
        <script>
        (function() {
            var mapWrapper = document.querySelector('.openstreetmap-wrapper[data-address]');
            if (!mapWrapper) return;
            
            var address = mapWrapper.getAttribute('data-address');
            var location = mapWrapper.getAttribute('data-location');
            var zoom = parseInt(mapWrapper.getAttribute('data-zoom')) || 15;
            var height = mapWrapper.getAttribute('data-height') || '400px';
            var markerColor = mapWrapper.getAttribute('data-marker-color') || '#e74c3c';
            
            // Fonction pour convertir la couleur hex en format compatible URL
            function encodeColor(color) {
                return color.replace('#', '%23');
            }
            
            // Géocoder l'adresse pour obtenir les coordonnées
            fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + address)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        var lat = parseFloat(data[0].lat);
                        var lon = parseFloat(data[0].lon);
                        
                        // Calculer le bbox pour le niveau de zoom
                        var scale = Math.pow(2, 18 - zoom);
                        var deltaLat = 0.005 * scale;
                        var deltaLon = 0.005 * scale;
                        var bbox = (lon - deltaLon) + ',' + (lat - deltaLat) + ',' + (lon + deltaLon) + ',' + (lat + deltaLat);
                        
                        // Construire l'URL OpenStreetMap avec le marqueur personnalisé
                        var osmUrl = 'https://www.openstreetmap.org/export/embed.html?bbox=' + bbox + '&layer=mapnik&marker=' + lat + ',' + lon;
                        
                        // Créer le div avec la classe openstreetmap pour Tarteaucitron
                        var osmDiv = document.createElement('div');
                        osmDiv.className = 'openstreetmap';
                        osmDiv.setAttribute('data-url', osmUrl);
                        osmDiv.style.height = height;
                        osmDiv.style.width = '100%';
                        osmDiv.style.position = 'relative';
                        
                        // Remplacer le wrapper par le div Tarteaucitron
                        mapWrapper.parentNode.replaceChild(osmDiv, mapWrapper);
                        
                        // Forcer Tarteaucitron à traiter ce nouvel élément
                        if (typeof tarteaucitron !== 'undefined') {
                            // Vérifier si l'utilisateur a déjà accepté
                            if (tarteaucitron.state && tarteaucitron.state.openstreetmap === true) {
                                // Déclencher manuellement le chargement
                                if (tarteaucitron.services && tarteaucitron.services.openstreetmap) {
                                    tarteaucitron.services.openstreetmap.js();
                                }
                            } else {
                                // Afficher le fallback
                                if (tarteaucitron.services && tarteaucitron.services.openstreetmap && tarteaucitron.services.openstreetmap.fallback) {
                                    tarteaucitron.services.openstreetmap.fallback();
                                }
                            }
                        }
                    } else {
                        mapWrapper.innerHTML = '<p style=\"padding: 2rem; text-align: center; color: #666;\">Impossible de localiser l\\'adresse sur la carte.</p>';
                    }
                })
                .catch(error => {
                    console.error('Erreur de géocodage:', error);
                    mapWrapper.innerHTML = '<p style=\"padding: 2rem; text-align: center; color: #666;\">Erreur lors du chargement de la carte.</p>';
                });
        })();
        </script>";
    }
    
    return $output;
}

add_shortcode('display_event_map', 'display_event_map_shortcode');


/**
 * Shortcode pour afficher la date de l'événement selon son type
 * Usage: [display_event_date] ou [display_event_date event_id="123"]
 */
function display_event_date_shortcode($atts) {
    // Attributs du shortcode
    $atts = shortcode_atts(
        array(
            'event_id' => get_the_ID(), // Par défaut, l'événement actuel
            'show_icon' => 'true',
            'show_time' => 'true',
            'format' => 'full', // full, short, minimal
        ),
        $atts,
        'display_event_date'
    );

    $event_id = $atts['event_id'];
    $event_type = get_field('event_type', $event_id);
    $event_time = get_field('event_time', $event_id);
    
    // Début de la structure HTML
    $output = '<div class="event-date-display">';
    
    // Icône optionnelle
    $icon = '';
    if ($atts['show_icon'] === 'true') {
        $icon = '<i class="fa-regular fa-calendar"></i> ';
    }
    
    // Formatage selon le type d'événement
    switch ($event_type) {
        case 'multi_day':
            $start_date = get_field('event_start_date', $event_id);
            $end_date = get_field('event_end_date', $event_id);
            
            if ($atts['format'] === 'minimal') {
                // Format minimal : mardi 15 - vendredi 20 mars 2026
                if (date('Y-m', strtotime($start_date)) === date('Y-m', strtotime($end_date))) {
                    $start_formatted = date_i18n('l j', strtotime($start_date));
                    $end_formatted = date_i18n('l j F Y', strtotime($end_date));
                    $date_text = "$start_formatted - $end_formatted";
                } else {
                    $start_formatted = date_i18n('l j F', strtotime($start_date));
                    $end_formatted = date_i18n('l j F Y', strtotime($end_date));
                    $date_text = "$start_formatted - $end_formatted";
                }
            } else {
                // Format complet : Du mardi 15 au vendredi 20 mars 2026
                if (date('Y-m', strtotime($start_date)) === date('Y-m', strtotime($end_date))) {
                    $start_formatted = date_i18n('l j', strtotime($start_date));
                    $end_formatted = date_i18n('l j F Y', strtotime($end_date));
                    $date_text = "Du $start_formatted au $end_formatted";
                } else {
                    $start_formatted = date_i18n('l j F Y', strtotime($start_date));
                    $end_formatted = date_i18n('l j F Y', strtotime($end_date));
                    $date_text = "Du $start_formatted au $end_formatted";
                }
            }
            
            $output .= '<div class="event-dates multi-day">';
            $output .= '<h3><span class="date-label">' . $icon . $date_text . '</span></h3>';
            
            // Badge multi-jours
            if ($atts['format'] === 'full') {
                $output .= '<span class="event-type-badge multi-day"><i class="fa-solid fa-calendar-days"></i> Événement sur plusieurs jours</span>';
            }
            
            $output .= '</div>';
            break;
            
        case 'recurring':
            $start_date = get_field('event_start_date', $event_id);
            $end_date = get_field('event_end_date', $event_id);
            $recurrence = get_field('event_recurrence', $event_id);
            
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
            $start_formatted = date_i18n('l j F Y', strtotime($start_date));
            $end_formatted = date_i18n('l j F Y', strtotime($end_date));
            
            $output .= '<div class="event-dates recurring">';
            
            if ($atts['format'] === 'minimal') {
                $output .= '<span class="date-label">' . $icon . $recurrence_text . '</span>';
            } else {
                $output .= '<span class="date-label main-recurrence">' . $icon . $recurrence_text . '</span>';
                $output .= '<h3><span class="date-range">Du ' . $start_formatted . ' au ' . $end_formatted . '</span></h3>';
                
                // Badge récurrent
                if ($atts['format'] === 'full') {
                    $output .= '<span class="event-type-badge recurring"><i class="fa-solid fa-repeat"></i> Événement récurrent</span>';
                }
            }
            
            $output .= '</div>';
            break;
            
        case 'single':
        default:
            $event_date = get_field('event_date', $event_id);

            if ($atts['format'] === 'minimal') {
                // Format minimal sans le jour de la semaine : 3 mars 2026
                $formatted_date = date_i18n('j F Y', strtotime($event_date));
            } else {
                // Format complet avec le jour de la semaine : Mardi 3 mars 2026
                $formatted_date = date_i18n('l j F Y', strtotime($event_date));
            }
            
            $output .= '<div class="event-dates single">';
            $output .= '<h3><span class="date-label">' . $icon . $formatted_date . '</span></h3>';
            $output .= '</div>';
            break;
    }
    
    // Affichage de l'heure si demandé
    if ($atts['show_time'] === 'true' && $event_time) {
        $output .= '<div class="event-time-display">';
        $output .= '<h3><i class="fa-regular fa-clock"></i> <time datetime="' . esc_attr($event_time) . '">' . esc_html(format_event_time($event_time)) . '</time></h3>';
        $output .= '</div>';
    }
    
    $output .= '</div>'; // Fin event-date-display
    
    return $output;
}

add_shortcode('display_event_date', 'display_event_date_shortcode');