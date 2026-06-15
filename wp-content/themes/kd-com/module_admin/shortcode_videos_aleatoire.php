<?php 
/**
 * Affichage d'une vidéo aléatoire selon différents critères
 * Shortcode: [video_aleatoire]
 * 
 * ⚙️ Activation : Réglages kd-com > Réglages du site > Modules du site > "Vidéo aléatoire"
 * 
 * IMPORTANT: Ce module récupère les vidéos depuis les blocs Gutenberg (core/video) 
 * dans le contenu des posts, pas depuis des champs ACF.
 * 
 * Paramètres :
 * - type : 'cpt' (Custom Post Type), 'posts' (Articles), 'subpages' (Sous-pages)
 * - post_type : nom du CPT (requis si type='cpt')
 * - category : slug de la catégorie (requis si type='cpt' ou 'posts')
 * - parent_page : ID de la page parente (requis si type='subpages')
 * 
 * Exemples d'utilisation :
 * [video_aleatoire type="cpt" post_type="films" category="action"]
 * [video_aleatoire type="posts" category="actualites"]
 * [video_aleatoire type="subpages" parent_page="123"]
 */

/**
 * Fonction utilitaire pour extraire les blocs vidéo du contenu Gutenberg
 */
function extract_video_blocks_from_content($content) {
    $videos = array();
    
    // Parse les blocs Gutenberg
    if (function_exists('parse_blocks')) {
        $blocks = parse_blocks($content);
        $videos = find_video_blocks_recursive($blocks);
    }
    
    // Fallback : recherche des balises video HTML si pas de blocs trouvés
    if (empty($videos)) {
        preg_match_all('/<video[^>]*>.*?<\/video>/is', $content, $matches);
        if (!empty($matches[0])) {
            $videos = $matches[0];
        }
    }
    
    // Nouveau : recherche des URLs YouTube en texte brut si toujours rien
    if (empty($videos)) {
        // Chercher les URLs YouTube dans le contenu brut
        preg_match_all('/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})(?:\S+)?/i', $content, $matches);
        if (!empty($matches[0])) {
            // Créer des "blocs" fictifs pour les URLs trouvées
            foreach ($matches[0] as $url) {
                $videos[] = array(
                    'blockName' => 'text/youtube',
                    'innerHTML' => '<p>URL YouTube trouvée en texte : ' . esc_html($url) . '</p>',
                    'url' => $url
                );
            }
        }
    }
    
    return $videos;
}

/**
 * Fonction récursive pour trouver les blocs vidéo dans la structure Gutenberg
 */
function find_video_blocks_recursive($blocks) {
    $videos = array();
    
    foreach ($blocks as $block) {
        // Bloc vidéo direct
        if ($block['blockName'] === 'core/video') {
            $videos[] = $block;
        }
        
        // Recherche dans les blocs imbriqués (colonnes, groupes, etc.)
        if (!empty($block['innerBlocks'])) {
            $inner_videos = find_video_blocks_recursive($block['innerBlocks']);
            $videos = array_merge($videos, $inner_videos);
        }
    }
    
    return $videos;
}
/**
 * Fonction utilitaire pour extraire l'ID d'une vidéo YouTube à partir d'une URL
 */
function extract_youtube_id($url) {
    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
        return $matches[1];
    }
    return '';
}
/**
 * Fonction pour générer le HTML d'un bloc vidéo
 */
function render_video_block($video_block, $for_shortcode = false) {
    if (is_array($video_block)) {
        // Cas spécial : URL YouTube trouvée en texte brut
        if ($for_shortcode && isset($video_block['blockName']) && $video_block['blockName'] === 'text/youtube' && isset($video_block['url'])) {
            $url = $video_block['url'];
            // RGPD : wrapper Tarteaucitron si activé
            if (function_exists('kd_is_tarteaucitron_enabled') && kd_is_tarteaucitron_enabled()) {
                return '<div class="youtube_player" videoID="' . esc_attr(extract_youtube_id($url)) . '">Vidéo YouTube</div>';
            } else {
                // Embed natif
                return '<div class="wp-block-embed__wrapper">
                    <iframe src="https://www.youtube.com/embed/' . extract_youtube_id($url) . '" 
                    frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen></iframe>
                </div>';
            }
        }
        // Bloc Gutenberg structuré normal
        else if (isset($video_block['innerHTML'])) {
            $html = $video_block['innerHTML'];
            // RGPD : transformer iframe YouTube en wrapper si Tarteaucitron activé
            if (function_exists('kd_is_tarteaucitron_enabled') && kd_is_tarteaucitron_enabled()) {
                // Remplacer toute iframe YouTube par un div .youtube_player
                $html = preg_replace_callback(
                    '/<iframe[^>]*src=["\\\']https?:\\/\\/www\\.youtube\\.com\\/embed\\/([a-zA-Z0-9_-]{11})[^"\\\']*["\\\'][^>]*>(.*?)<\\/iframe>/is',
                    function($matches) {
                        $video_id = $matches[1];
                        return '<div class="youtube_player" videoID="' . esc_attr($video_id) . '">Vidéo YouTube</div>';
                    },
                    $html
                );
            }
            return $html;
        }
    } else if (is_string($video_block)) {
        // HTML brut de fallback
        return $video_block;
    }
    return '';
}
function video_aleatoire_shortcode($atts) {
    // Définition des attributs par défaut
    $attributes = shortcode_atts(array(
        'type' => 'posts',
        'post_type' => '',
        'category' => '',
        'parent_page' => '',
        'debug' => false, // Paramètre pour le débogage
    ), $atts);

    // Mode debug pour diagnostiquer les problèmes
    $debug_mode = $attributes['debug'] === 'true' || $attributes['debug'] === '1';
    $debug_info = array();

    if ($debug_mode) {
        $debug_info[] = "🐛 Mode debug activé - Recherche de blocs vidéo Gutenberg (core/video)";
        $debug_info[] = "Paramètres reçus : " . print_r($attributes, true);
    }

    // Validation des paramètres
    if (empty($attributes['type'])) {
        return '<p>Erreur : Le paramètre "type" est requis.</p>';
    }

    // Arguments de base pour WP_Query (sans meta_query ACF)
    $args = array(
        'posts_per_page' => 50, // Récupérer plus de posts pour filtrer ceux avec vidéos
        'post_status' => 'publish',
        'orderby' => 'rand' // Ordre aléatoire
    );

    // Configuration selon le type
    switch ($attributes['type']) {
        case 'cpt':
            // Custom Post Type avec catégorie
            if (empty($attributes['post_type'])) {
                return '<p>Erreur : Le paramètre "post_type" est requis pour le type "cpt".</p>';
            }
            if (empty($attributes['category'])) {
                return '<p>Erreur : Le paramètre "category" est requis pour le type "cpt".</p>';
            }
            
            $args['post_type'] = $attributes['post_type'];
            $args['tax_query'] = array(
                array(
                    'taxonomy' => get_taxonomy_for_post_type($attributes['post_type'], $attributes['category']),
                    'field' => 'slug',
                    'terms' => $attributes['category']
                )
            );
            break;

        case 'posts':
            // Articles avec catégorie
            if (empty($attributes['category'])) {
                return '<p>Erreur : Le paramètre "category" est requis pour le type "posts".</p>';
            }
            
            $args['post_type'] = 'post';
            $args['category_name'] = $attributes['category'];
            break;

        case 'subpages':
            // Sous-pages d'une page
            if (empty($attributes['parent_page'])) {
                return '<p>Erreur : Le paramètre "parent_page" est requis pour le type "subpages".</p>';
            }
            
            $args['post_type'] = 'page';
            $args['post_parent'] = intval($attributes['parent_page']);
            break;

        default:
            return '<p>Erreur : Type non reconnu. Utilisez "cpt", "posts" ou "subpages".</p>';
    }

    // Exécution de la requête
    $query = new WP_Query($args);

    if ($debug_mode) {
        $debug_info[] = "Nombre de posts trouvés : " . $query->found_posts;
    }

    if (!$query->have_posts()) {
        wp_reset_postdata();
        if ($debug_mode) {
            $debug_output = '<div style="background: #f0f0f0; border: 1px solid #ddd; padding: 15px; margin: 10px 0;">';
            $debug_output .= '<h4>🐛 Informations de débogage :</h4>';
            $debug_output .= '<pre>' . implode("\n", $debug_info) . '</pre>';
            $debug_output .= '</div>';
            return $debug_output . '<p>Aucun post trouvé correspondant aux critères.</p>';
        }
        return '<p>Aucun post trouvé correspondant aux critères.</p>';
    }

    // Filtrer les posts qui contiennent des vidéos et en sélectionner un au hasard
    $posts_with_videos = array();
    
    while ($query->have_posts()) : $query->the_post();
        $post_id = get_the_ID();
        $content = get_the_content();
        $videos = extract_video_blocks_from_content($content);
        
        if (!empty($videos)) {
            $posts_with_videos[] = array(
                'post_id' => $post_id,
                'title' => get_the_title(),
                'permalink' => get_permalink(),
                'videos' => $videos,
                'content' => $content
            );
        }
        
        if ($debug_mode) {
            $debug_info[] = "Post ID {$post_id}: " . (empty($videos) ? 'Aucune vidéo' : count($videos) . ' vidéo(s) trouvée(s)');
        }
    endwhile;
    
    wp_reset_postdata();

    if (empty($posts_with_videos)) {
        if ($debug_mode) {
            $debug_output = '<div style="background: #f0f0f0; border: 1px solid #ddd; padding: 15px; margin: 10px 0;">';
            $debug_output .= '<h4>🐛 Informations de débogage :</h4>';
            $debug_output .= '<pre>' . implode("\n", $debug_info) . '</pre>';
            $debug_output .= '</div>';
            return $debug_output . '<p>Aucune vidéo trouvée dans les posts correspondant aux critères.</p>';
        }
        return '<p>Aucune vidéo trouvée dans les posts correspondant aux critères.</p>';
    }

    // Sélectionner un post au hasard parmi ceux qui ont des vidéos
    $selected_post = $posts_with_videos[array_rand($posts_with_videos)];
    
    // Sélectionner une vidéo au hasard dans ce post
    $selected_video = $selected_post['videos'][array_rand($selected_post['videos'])];

    if ($debug_mode) {
        $debug_info[] = "Post sélectionné : ID {$selected_post['post_id']} - {$selected_post['title']}";
        $debug_info[] = "Nombre de vidéos dans ce post : " . count($selected_post['videos']);
    }

    // Récupération du contexte (catégorie ou page parente)
    $context = '';
    switch ($attributes['type']) {
        case 'cpt':
        case 'posts':
            $categories = wp_get_post_categories($selected_post['post_id'], array('fields' => 'names'));
            if (!empty($categories)) {
                $context = $categories[0];
            }
            break;
        
        case 'subpages':
            $parent_id = wp_get_post_parent_id($selected_post['post_id']);
            if ($parent_id) {
                $context = get_the_title($parent_id);
            }
            break;
    }

    ob_start();
    ?>
    
    <?php if ($debug_mode) : ?>
        <div style="background: #e7f3ff; border: 1px solid #0073aa; padding: 15px; margin: 10px 0;">
            <h4>🐛 Informations de débogage :</h4>
            <pre><?php echo implode("\n", $debug_info); ?></pre>
        </div>
    <?php endif; ?>
    
    <div class="video-aleatoire-container">
        <div class="video-wrapper">
            <?php echo render_video_block($selected_video, true); ?>
        </div>
        
        <div class="video-info">
            <h3 class="video-title">
                <a href="<?php echo esc_url($selected_post['permalink']); ?>"><?php echo esc_html($selected_post['title']); ?></a>
            </h3>
            
            <?php if ($context) : ?>
                <p class="video-context">
                    <?php 
                    switch ($attributes['type']) {
                        case 'cpt':
                        case 'posts':
                            
                            break;
                        case 'subpages':
                            
                            break;
                    }
                    ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
    return ob_get_clean();
}

/**
 * Fonction utilitaire pour déterminer la taxonomie appropriée pour un post type
 */
function get_taxonomy_for_post_type($post_type, $category_slug) {
    // Récupérer toutes les taxonomies associées au post type
    $taxonomies = get_object_taxonomies($post_type, 'objects');
    
    // Chercher la taxonomie qui contient le terme spécifié
    foreach ($taxonomies as $taxonomy) {
        if ($taxonomy->hierarchical) { // Privilégier les taxonomies hiérarchiques (comme category)
            $term = get_term_by('slug', $category_slug, $taxonomy->name);
            if ($term) {
                return $taxonomy->name;
            }
        }
    }
    
    // Si aucune taxonomie hiérarchique trouvée, essayer toutes les taxonomies
    foreach ($taxonomies as $taxonomy) {
        $term = get_term_by('slug', $category_slug, $taxonomy->name);
        if ($term) {
            return $taxonomy->name;
        }
    }
    
    // Par défaut, retourner 'category' pour les posts standard
    return 'category';
}

// Enregistrement du shortcode
add_shortcode('video_aleatoire', 'video_aleatoire_shortcode');

// Shortcode de test pour diagnostiquer les blocs vidéo Gutenberg
function video_gutenberg_test_shortcode($atts) {
    $attributes = shortcode_atts(array(
        'category' => '',
        'post_id' => '',
    ), $atts);
    
    ob_start();
    ?>
    <div style="background: #f9f9f9; border: 1px solid #ddd; padding: 20px; margin: 20px 0;">
        <h3>🔧 Test de diagnostic blocs vidéo Gutenberg</h3>
        
        <?php
        if (!empty($attributes['post_id'])) {
            // Test d'un post spécifique
            $post_id = intval($attributes['post_id']);
            $content = get_post_field('post_content', $post_id);
            $videos = extract_video_blocks_from_content($content);
            
            echo "<p><strong>Test du post ID {$post_id} :</strong></p>";
            echo "<p>Nombre de blocs vidéo trouvés : " . count($videos) . "</p>";
            
            if (!empty($videos)) {
                echo "<h4>Vidéos trouvées :</h4>";
                foreach ($videos as $i => $video) {
                    echo "<div style='border: 1px solid #ccc; margin: 10px 0; padding: 10px;'>";
                    echo "<strong>Vidéo " . ($i + 1) . " :</strong><br>";
                    echo render_video_block($video, false);
                    echo "</div>";
                }
            }
        } else {
            // Test général avec catégorie
            $args = array(
                'post_type' => 'post',
                'posts_per_page' => 5,
                'post_status' => 'publish'
            );
            
            if (!empty($attributes['category'])) {
                $args['category_name'] = $attributes['category'];
            }
            
            $query = new WP_Query($args);
            echo "<p><strong>Posts analysés :</strong> " . $query->found_posts . "</p>";
            
            if ($query->have_posts()) {
                echo "<ul>";
                while ($query->have_posts()) : $query->the_post();
                    $post_id = get_the_ID();
                    $title = get_the_title();
                    $content = get_the_content();
                    $videos = extract_video_blocks_from_content($content);
                    echo "<li>ID: {$post_id} - {$title} - Blocs vidéo: " . count($videos) . "</li>";
                endwhile;
                echo "</ul>";
            }
            wp_reset_postdata();
        }
        ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('video_gutenberg_test', 'video_gutenberg_test_shortcode');

