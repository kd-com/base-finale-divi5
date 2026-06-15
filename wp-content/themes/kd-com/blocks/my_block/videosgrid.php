<?php
/**
 * Template pour le bloc YouTube Grid
 * Affiche une grille de vidéos YouTube avec système intelligent RGPD/Natif
 * 
 * @package kd-com
 */
// Récupération du type/délai d'animation au niveau du bloc — fallback vers les options ACF globales
$animation_type_field  = get_field('animation_type');
$animation_delay_field = get_field('animation_delay');
// Si un contrôle global force la désactivation, forcer 'none'
if ( function_exists('get_field') && get_field('aos_force_disable', 'option') ) {
    $animation_type = 'none';
    $animation_delay = 0;
} else {
    // fallback: champ de bloc -> option globale -> valeur par défaut
    $animation_type = $animation_type_field ?: ( function_exists('get_field') ? get_field('aos_default_type', 'option') : null );
    if ( empty( $animation_type ) ) {
        $animation_type = 'fade-up';
    }

    $animation_delay = (int) ( $animation_delay_field ?: ( function_exists('get_field') ? get_field('aos_default_delay', 'option') : 100 ) );
    if ($animation_delay <= 0) $animation_delay = 100;
}

// Fonction pour extraire l'ID YouTube depuis une URL ou retourner l'ID direct
if (!function_exists('extract_youtube_id_grid')) {
    function extract_youtube_id_grid($input) {
        if (empty($input)) return '';
        
        // Si c'est déjà un ID simple (11 caractères alphanumériques)
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $input)) {
            return $input;
        }
        
        // Extraction depuis différents formats d'URL YouTube
        $patterns = [
            '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/.*[?&]v=([a-zA-Z0-9_-]{11})/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input, $matches)) {
                return $matches[1];
            }
        }
        
        return $input; // Retourner tel quel si aucun pattern ne match
    }
}

// Fonction pour extraire les URLs YouTube du contenu Gutenberg
if (!function_exists('extract_youtube_from_content')) {
    function extract_youtube_from_content($content) {
        $youtube_urls = array();
        
        if (empty($content)) {
            return $youtube_urls;
        }
        
        // Parser les blocs Gutenberg
        $blocks = parse_blocks($content);
        
        foreach ($blocks as $block) {
            // Vérifier si c'est un bloc embed avec une URL YouTube
            if ($block['blockName'] === 'core/embed' && isset($block['attrs']['url'])) {
                $url = $block['attrs']['url'];
                if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
                    $youtube_id = extract_youtube_id_grid($url);
                    if ($youtube_id) {
                        $youtube_urls[] = $youtube_id;
                    }
                }
            }
            
            // Vérifier si c'est un bloc HTML personnalisé avec du code YouTube
            if ($block['blockName'] === 'core/html' && isset($block['innerHTML'])) {
                $html = $block['innerHTML'];
                // Chercher les URLs YouTube dans le HTML
                preg_match_all('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $html, $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $youtube_id) {
                        $youtube_urls[] = $youtube_id;
                    }
                }
            }
            
            // Vérifier si c'est un bloc vidéo avec une URL YouTube (cas spécial)
            if ($block['blockName'] === 'core/video' && isset($block['innerHTML'])) {
                $html = $block['innerHTML'];
                preg_match_all('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $html, $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $youtube_id) {
                        $youtube_urls[] = $youtube_id;
                    }
                }
            }
            
            // Traitement récursif pour les blocs imbriqués
            if (!empty($block['innerBlocks'])) {
                foreach ($block['innerBlocks'] as $inner_block) {
                    $inner_content = serialize_block($inner_block);
                    $inner_urls = extract_youtube_from_content($inner_content);
                    $youtube_urls = array_merge($youtube_urls, $inner_urls);
                }
            }
        }
        
        // Également chercher dans le contenu brut au cas où
        preg_match_all('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $youtube_id) {
                if (!in_array($youtube_id, $youtube_urls)) {
                    $youtube_urls[] = $youtube_id;
                }
            }
        }
        
        return array_unique($youtube_urls);
    }
}

// Récupérer les données du bloc
$title = get_field('youtube_grid_title');
$subtitle = get_field('youtube_grid_subtitle');
$columns = get_field('youtube_grid_columns') ?: '3';
$gap = get_field('youtube_grid_gap') ?: 'medium';
$max_videos = get_field('youtube_grid_max_videos');
$aspect_ratio = get_field('youtube_grid_aspect_ratio') ?: '16:9';
$show_titles = get_field('youtube_grid_show_titles');
$show_descriptions = get_field('youtube_grid_show_descriptions');
$lightbox = get_field('youtube_grid_lightbox');
$autoplay = get_field('youtube_grid_autoplay');

// Nouvelle logique : source des vidéos
$source = get_field('youtube_grid_source') ?: 'category';
$videos = array();

switch ($source) {
    case 'category':
        $category_id = get_field('youtube_grid_post_category');
        $posts_limit = get_field('youtube_grid_posts_limit') ?: 12;
        $use_post_title = get_field('youtube_grid_use_post_title');
        
        if ($category_id) {
            $posts = get_posts(array(
                'post_type' => 'post',
                'posts_per_page' => $posts_limit,
                'category' => $category_id
            ));
            
            if (is_admin()) {
                error_log('YouTube Grid Debug - Catégorie ID: ' . $category_id . ', Posts trouvés: ' . count($posts));
            }
            
            foreach ($posts as $post) {
                // Analyser le contenu pour trouver les blocs YouTube
                $youtube_urls = extract_youtube_from_content($post->post_content);
                
                if (is_admin()) {
                    error_log('Post ID ' . $post->ID . ' (' . $post->post_title . ') - URLs YouTube trouvées: ' . count($youtube_urls));
                }
                
                foreach ($youtube_urls as $youtube_url) {
                    // Extraire le premier paragraphe du contenu sans afficher l'URL YouTube
                    $first_paragraph = '';
                    if ($show_descriptions) {
                        $content = apply_filters('the_content', $post->post_content);
                        if (preg_match('/<p>(.*?)<\/p>/is', $content, $matches)) {
                            // Vérifier que le paragraphe n'est pas une URL YouTube
                            if (!preg_match('/https?:\/\/(www\.)?(youtube\.com|youtu\.be)\//i', $matches[1])) {
                                $first_paragraph = $matches[1];
                            }
                        }
                    }
                    $videos[] = array(
                        'video_id' => $youtube_url,
                        'video_title' => $use_post_title ? $post->post_title : '',
                        'video_description' => $use_post_title ? $first_paragraph : ''
                    );
                }
            }
        }
        break;
        
    case 'cpt':
        $post_type = get_field('youtube_grid_post_type');
        $posts_limit = get_field('youtube_grid_posts_limit') ?: 12;
        $use_post_title = get_field('youtube_grid_use_post_title');
        
        if ($post_type) {
            $posts = get_posts(array(
                'post_type' => $post_type,
                'posts_per_page' => $posts_limit
            ));
            
            if (is_admin()) {
                error_log('YouTube Grid Debug - CPT: ' . $post_type . ', Posts trouvés: ' . count($posts));
            }
            
            foreach ($posts as $post) {
                // Analyser le contenu pour trouver les blocs YouTube
                $youtube_urls = extract_youtube_from_content($post->post_content);
                
                if (is_admin()) {
                    error_log('Post ID ' . $post->ID . ' (' . $post->post_title . ') - URLs YouTube trouvées: ' . count($youtube_urls));
                }
                
                foreach ($youtube_urls as $youtube_url) {
                    // Extraire le premier paragraphe du contenu sans afficher l'URL YouTube
                    $first_paragraph = '';
                    if ($show_descriptions) {
                        $content = apply_filters('the_content', $post->post_content);
                        if (preg_match('/<p>(.*?)<\/p>/is', $content, $matches)) {
                            if (!preg_match('/https?:\/\/(www\.)?(youtube\.com|youtu\.be)\//i', $matches[1])) {
                                $first_paragraph = $matches[1];
                            }
                        }
                    }
                    $videos[] = array(
                        'video_id' => $youtube_url,
                        'video_title' => $use_post_title ? $post->post_title : '',
                        'video_description' => $use_post_title ? $first_paragraph : ''
                    );
                }
            }
        }
        break;
        
    case 'subpages':
        $parent_page = get_field('youtube_grid_parent_page');
        $posts_limit = get_field('youtube_grid_posts_limit') ?: 12;
        $use_post_title = get_field('youtube_grid_use_post_title');
        
        if ($parent_page) {
            $posts = get_posts(array(
                'post_type' => 'page',
                'posts_per_page' => $posts_limit,
                'post_parent' => $parent_page
            ));
            
            if (is_admin()) {
                error_log('YouTube Grid Debug - Page parent: ' . $parent_page . ', Sous-pages trouvées: ' . count($posts));
            }
            
            foreach ($posts as $post) {
                // Analyser le contenu pour trouver les blocs YouTube
                $youtube_urls = extract_youtube_from_content($post->post_content);
                
                if (is_admin()) {
                    error_log('Page ID ' . $post->ID . ' (' . $post->post_title . ') - URLs YouTube trouvées: ' . count($youtube_urls));
                }
                
                foreach ($youtube_urls as $youtube_url) {
                    // Extraire le premier paragraphe du contenu sans afficher l'URL YouTube
                    $first_paragraph = '';
                    if ($show_descriptions) {
                        $content = apply_filters('the_content', $post->post_content);
                        if (preg_match('/<p>(.*?)<\/p>/is', $content, $matches)) {
                            if (!preg_match('/https?:\/\/(www\.)?(youtube\.com|youtu\.be)\//i', $matches[1])) {
                                $first_paragraph = $matches[1];
                            }
                        }
                    }
                    $videos[] = array(
                        'video_id' => $youtube_url,
                        'video_title' => $use_post_title ? $post->post_title : '',
                        'video_description' => $use_post_title ? $first_paragraph : ''
                    );
                }
            }
        }
        break;
        
    case 'manual':
    default:
        $videos = get_field('youtube_grid_videos_manual') ?: array();
        break;
}

// Configuration du bloc
$block_classes = ['youtube-grid'];
if (!empty($block['className'])) {
    $block_classes[] = $block['className'];
}
if (!empty($block['align'])) {
    $block_classes[] = 'align' . $block['align'];
}

// Calculer le ratio CSS
$ratio_map = [
    '16:9' => '56.25%',
    '4:3' => '75%',
    '1:1' => '100%',
    '21:9' => '42.86%'
];
$ratio_css = $ratio_map[$aspect_ratio] ?? '56.25%';

// Vérifier s'il y a des vidéos
if (!$videos || empty($videos)) {
    if (is_admin()) {
        $message = '🎬 <strong>YouTube Grid :</strong> ';
        switch ($source) {
            case 'category':
                $message .= 'Aucune vidéo trouvée dans la catégorie sélectionnée.';
                break;
            case 'cpt':
                $message .= 'Aucune vidéo trouvée dans le CPT sélectionné.';
                break;
            case 'subpages':
                $message .= 'Aucune vidéo trouvée dans les sous-pages.';
                break;
            case 'manual':
            default:
                $message .= 'Ajoutez des vidéos dans les paramètres du bloc.';
                break;
        }
        
        echo '<div class="youtube-grid youtube-grid--empty">
                <p>' . $message . '</p>
                <p style="font-size: 12px; color: #666;">Source: ' . esc_html($source) . ' | Trouvé: ' . (is_array($videos) ? count($videos) . ' vidéos' : gettype($videos)) . '</p>';
        
        // Debug supplémentaire pour les catégories
        if ($source === 'category') {
            $category_id = get_field('youtube_grid_post_category');
            $video_field = get_field('youtube_grid_video_field') ?: 'youtube_url';
            if ($category_id) {
                echo '<p style="font-size: 11px; color: #999;">Catégorie ID: ' . esc_html($category_id);
                $category = get_category($category_id);
                if ($category) {
                    echo ' (' . esc_html($category->name) . ')';
                }
                echo ' | Champ recherché: ' . esc_html($video_field);
                
                // Compter les posts avec et sans le champ
                $all_posts_count = get_posts(array(
                    'post_type' => 'post',
                    'posts_per_page' => -1,
                    'category' => $category_id,
                    'fields' => 'ids'
                ));
                
                $posts_with_field = get_posts(array(
                    'post_type' => 'post',
                    'posts_per_page' => -1,
                    'category' => $category_id,
                    'meta_key' => $video_field,
                    'meta_value' => '',
                    'meta_compare' => '!=',
                    'fields' => 'ids'
                ));
                
                echo ' | Posts total: ' . count($all_posts_count) . ' | Avec champ: ' . count($posts_with_field);
                echo '</p>';
            }
        }
        
        // Debug supplémentaire pour les CPT
        if ($source === 'cpt') {
            $cpt_type = get_field('youtube_grid_cpt_type');
            $cpt_category_id = get_field('youtube_grid_cpt_category');
            if ($cpt_type) {
                echo '<p style="font-size: 11px; color: #999;">CPT: ' . esc_html($cpt_type);
                if ($cpt_category_id) {
                    echo ' | Catégorie ID: ' . esc_html($cpt_category_id);
                }
                $taxonomies = get_object_taxonomies($cpt_type, 'objects');
                if (!empty($taxonomies)) {
                    $tax_names = array_map(function($tax) { return $tax->name; }, $taxonomies);
                    echo ' | Taxonomies disponibles: ' . esc_html(implode(', ', $tax_names));
                }
                echo '</p>';
            }
        }
        
        if (is_array($videos) && !empty($videos)) {
            echo '<p style="font-size: 11px; color: #999;">Contenu: ' . esc_html(print_r($videos, true)) . '</p>';
        }
        echo '</div>';
    }
    return;
}

// Limiter le nombre de vidéos si spécifié
if ($max_videos && is_numeric($max_videos)) {
    $videos = array_slice($videos, 0, intval($max_videos));
}

// Vérifier si Tarteaucitron est activé
$is_tarteaucitron_enabled = function_exists('kd_is_tarteaucitron_enabled') ? kd_is_tarteaucitron_enabled() : false;

// Désactiver le filtre RGPD sur 'the_content' si RGPD désactivé
if (!$is_tarteaucitron_enabled) {
    remove_filter('the_content', 'kd_tac_transform_iframes_in_content', 12);
}

// ID unique pour le bloc
$block_id = 'youtube-grid-' . uniqid();
?>

<div class="<?php echo esc_attr(implode(' ', $block_classes)); ?>" 
     id="<?php echo esc_attr($block_id); ?>" 
     data-columns="<?php echo esc_attr($columns); ?>"
     data-gap="<?php echo esc_attr($gap); ?>"
     data-aspect-ratio="<?php echo esc_attr($aspect_ratio); ?>"
     data-tarteaucitron="<?php echo $is_tarteaucitron_enabled ? 'enabled' : 'disabled'; ?>">

    <?php if ($title || $subtitle): ?>
    <div class="youtube-grid__header">
        <?php if ($title): ?>
            <h2 class="youtube-grid__title"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>
        
        <?php if ($subtitle): ?>
            <div class="youtube-grid__subtitle"><?php echo wp_kses_post(wpautop($subtitle)); ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="youtube-grid__container 
                youtube-grid__container--columns-<?php echo esc_attr($columns); ?>
                youtube-grid__container--gap-<?php echo esc_attr($gap); ?>
                youtube-grid__container--ratio-<?php echo str_replace(':', '-', $aspect_ratio); ?>">
        <?php $i = 0; ?>
        <?php foreach ($videos as $index => $video): 
            $video_id_raw = $video['video_id'] ?? '';
            $video_title = $video['video_title'] ?? '';
            $video_description = $video['video_description'] ?? '';
            
            $delay = $animation_delay * $i;
            
            // Extraire l'ID YouTube proprement
            $video_id = extract_youtube_id_grid($video_id_raw);
            
            if (empty($video_id)) continue;
            
            // ...
            
            // Paramètres autoplay pour la première vidéo si activé
            $is_first = ($index === 0);
            $autoplay_param = ($autoplay && $is_first && !$is_tarteaucitron_enabled) ? '&autoplay=1' : '';
        ?>
        
        <div class="youtube-grid__item" <?php echo $animation_type !== 'none' ? 'data-aos="'.esc_attr($animation_type).'" data-aos-delay="'.esc_attr($delay).'"' : ''; ?>  data-video-index="<?php echo esc_attr($index); ?>">
            <div class="youtube-grid__video-wrapper" style="padding-bottom: <?php echo esc_attr($ratio_css); ?>;">
                
                <?php if ($is_tarteaucitron_enabled): ?>
                    <!-- Mode RGPD avec Tarteaucitron -->
                    <div class="youtube_player" 
                        videoID="<?php echo esc_attr($video_id); ?>">
                            Vidéo YouTube - <?php echo esc_html($video_title ?: 'Vidéo'); ?>
                    </div>
                <?php else: ?>
                    <!-- Mode natif avec iframe directe -->
                    <iframe 
                        class="youtube-grid__iframe"
                        src="https://www.youtube.com/embed/<?php echo esc_attr($video_id); ?>?rel=0&showinfo=0&modestbranding=1<?php echo $autoplay_param; ?>"
                        title="<?php echo esc_attr($video_title ?: 'Vidéo YouTube'); ?>"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                    </iframe>
                <?php endif; ?>
                
            </div>
            
            <?php if (($show_titles && $video_title) || ($show_descriptions && $video_description)): ?>
            <div class="youtube-grid__content">
                <?php if ($show_titles && $video_title): ?>
                    <h3 class="youtube-grid__video-title"><?php echo esc_html($video_title); ?></h3>
                <?php endif; ?>
                
                <?php if ($show_descriptions && $video_description): ?>
                    <div class="youtube-grid__video-description">
                        <?php echo wp_kses_post(wpautop($video_description)); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php $i++; ?>
        
        <?php endforeach; ?>
    </div>

    <!-- ... -->
</div>

