<?php
/**
 * Slider Carousel des sous-pages
 * Shortcode: [slider_carousel_pages parent_id="123"]
 * Affiche les pages enfants d'une page parente en ordre aléatoire
 */

function slider_carousel_pages_shortcode($atts) {
    // Attributs par défaut
    $atts = shortcode_atts(array(
        'parent_id' => 0, // ID de la page parente
        'nombre' => -1,   // Nombre de slides à afficher (-1 = tous)
        'ordre' => 'rand', // Ordre: rand (aléatoire), menu_order, title, date
    ), $atts);
    
    $parent_id = intval($atts['parent_id']);
    
    if (!$parent_id) {
        return '<p>Veuillez spécifier un ID de page parente avec l\'attribut parent_id.</p>';
    }
    
    // Arguments de la requête pour récupérer les pages enfants
    $args = array(
        'post_type' => 'page',
        'post_parent' => $parent_id,
        'posts_per_page' => intval($atts['nombre']),
        'orderby' => $atts['ordre'],
        'order' => ($atts['ordre'] === 'rand') ? 'ASC' : 'ASC',
        'post_status' => 'publish',
    );
    
    $pages_query = new WP_Query($args);
    
    if (!$pages_query->have_posts()) {
        wp_reset_postdata();
        return '<p>Aucune sous-page trouvée pour cette page parente.</p>';
    }
    
    ob_start();
    ?>
    
    <div class="slider-carousel-pages-wrapper">
        <div class="swiper slider-carousel-pages">
            <div class="swiper-wrapper">
                <?php while ($pages_query->have_posts()) : $pages_query->the_post(); 
                    $page_id = get_the_ID();
                    $titre = get_the_title();
                    $extrait = get_the_excerpt();
                    $lien = get_permalink();
                    
                    // Image à la une
                    if (has_post_thumbnail()) {
                        $image_url = get_the_post_thumbnail_url($page_id, 'large');
                    } else {
                        $image_url = ''; // Pas d'image
                    }
                ?>
                    
                    <div class="swiper-slide carousel-page-item">
                        <div class="carousel-page-card">
                            <?php if ($image_url) : ?>
                                <div class="carousel-page-image">
                                    <a href="<?php echo esc_url($lien); ?>">
                                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($titre); ?>" />
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="carousel-page-content">
                                <h3 class="carousel-page-title">
                                    <a href="<?php echo esc_url($lien); ?>">
                                        <?php echo esc_html($titre); ?>
                                    </a>
                                </h3>
                                
                                <?php if ($extrait) : ?>
                                    <div class="carousel-page-excerpt">
                                        <?php echo wp_kses_post($extrait); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="carousel-page-link">
                                    <a href="<?php echo esc_url($lien); ?>" class="btn-carousel-page">
                                        Découvrir
                                        <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 32 32">
                                            <g fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" stroke-miterlimit="10">
                                                <circle class="arrow-icon--circle" cx="16" cy="16" r="15.12"></circle>
                                                <path class="arrow-icon--arrow" d="M16.14 9.93L22.21 16l-6.07 6.07M8.23 16h13.98"></path>
                                            </g>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <?php endwhile; ?>
            </div>
            
            <!-- Navigation -->
            <div class="carousel-pages-navigation">
                <div class="swiper-button-prev carousel-pages-prev">
                    <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                        <g fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-miterlimit="10">
                            <circle class="arrow-icon--circle" cx="16" cy="16" r="15.12"></circle>
                            <path class="arrow-icon--arrow" d="M16.14 9.93L22.21 16l-6.07 6.07M8.23 16h13.98"></path>
                        </g>
                    </svg>
                </div>
                <div class="swiper-button-next carousel-pages-next">
                    <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                        <g fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-miterlimit="10">
                            <circle class="arrow-icon--circle" cx="16" cy="16" r="15.12"></circle>
                            <path class="arrow-icon--arrow" d="M16.14 9.93L22.21 16l-6.07 6.07M8.23 16h13.98"></path>
                        </g>
                    </svg>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="swiper-pagination carousel-pages-pagination"></div>
        </div>
    </div>
    
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('slider_carousel_pages', 'slider_carousel_pages_shortcode');
