<?php
/**
 * Affichage du slider de contenu en front
 * Shortcode: [slider_contenu]
 */

function show_slider_contenu_shortcode($atts) {
    // Attributs par défaut
    $atts = shortcode_atts(array(
        'nombre' => -1, // Nombre de slides à afficher (-1 = tous)
    ), $atts);
    
    $args = array(
        'post_type' => 'slider_contenu',
        'posts_per_page' => intval($atts['nombre']),
        'orderby' => 'menu_order',
        'order' => 'ASC',
    );
    
    $slider_query = new WP_Query($args);
    
    if (!$slider_query->have_posts()) {
        wp_reset_postdata();
        return '';
    }
    
    ob_start();
    ?>
    
    <div class="upk-salf-slider-section">
        <div class="bdt-timeline-container">
            <div class="upk-salf-slider-wrapper">
                <div class="swiper slider-contenu-swiper">
                    <div class="swiper-wrapper">
                        <?php while ($slider_query->have_posts()) : $slider_query->the_post(); 
                            // Récupération des champs ACF
                            $page = get_field('page_liee');
                            $article = get_field('article_lie');
                            $titre_perso = get_field('titre_personnalise');
                            $extrait_perso = get_field('extrait_personnalise');
                            $image_perso = get_field('image_personnalisee');
                            $texte_bouton = get_field('texte_bouton') ?: 'En savoir plus';
                            $couleur_fond = get_field('couleur_fond') ?: 'default';
                            
                            // Déterminer le contenu lié (page ou article)
                            $content = $page ? $page : $article;
                            // Si $content est un ID (entier ou chaîne), on récupère l'objet post
                            if ($content && !is_object($content)) {
                                $content = get_post($content);
                            }
                            if (!$content || !isset($content->ID)) { continue; } // Pas de contenu lié ou objet invalide, on skip

                            // Récupérer les données
                            $titre = $titre_perso ? $titre_perso : get_the_title($content->ID);
                            $extrait = $extrait_perso ? $extrait_perso : get_the_excerpt($content->ID);
                            $lien = get_permalink($content->ID);

                            // Image : personnalisée > image à la une > placeholder
                            if ($image_perso) {
                                $image_url = $image_perso;
                            } elseif (has_post_thumbnail($content->ID)) {
                                $image_url = get_the_post_thumbnail_url($content->ID, 'large');
                            } else {
                                $image_url = ''; // Pas d'image
                            }
                        ?>
                            
                            <div class="upk-salf-item swiper-slide bg-<?php echo esc_attr($couleur_fond); ?>">
                                <?php if ($image_url) : ?>
                                    <div class="upk-salf-image-wrap">
                                        <img class="upk-xanc-img" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($titre); ?>" />
                                    </div>
                                <?php endif; ?>
                                
                                <div class="upk-salf-content-wrap">
                                    <h3 class="upk-salf-title" data-swiper-parallax-y="-150" data-swiper-parallax-duration="1200">
                                        <?php echo esc_html($titre); ?>
                                    </h3>
                                    
                                    <?php if ($extrait) : ?>
                                        <div class="upk-salf-desc" data-swiper-parallax-y="-200" data-swiper-parallax-duration="1400">
                                            <?php echo wp_kses_post($extrait); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($lien) : ?>
                                        <div class="upk-salf-button" data-swiper-parallax-y="-300" data-swiper-parallax-duration="1500">
                                            <a class="link link--arrowed" href="<?php echo esc_url($lien); ?>">
                                                <?php echo esc_html($texte_bouton); ?>
                                                <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 32 32">
                                                    <g fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" stroke-miterlimit="10">
                                                        <circle class="arrow-icon--circle" cx="16" cy="16" r="15.12"></circle>
                                                        <path class="arrow-icon--arrow" d="M16.14 9.93L22.21 16l-6.07 6.07M8.23 16h13.98"></path>
                                                    </g>
                                                </svg>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <div class="upk-page-scroll">
                    <a class="arrow-up" href="#section2">
                        <div class="long-arrow-left"></div>
                        <span class="arrow-slide"></span>
                    </a>
                </div>
                
                <div class="upk-salf-nav-pag-wrap">
                    <!-- Navigation -->
                    <div class="upk-salf-navigation">
                        <div class="upk-button-prev-contenu upk-n-p">
                            <a class="link link--arrowed" href="#">
                        <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 32 32">
                            <g fill="none" stroke="#ff215a" stroke-width="1.5" stroke-linejoin="round" stroke-miterlimit="10">
                            <circle class="arrow-icon--circle" cx="16" cy="16" r="15.12"></circle>
                            <path class="arrow-icon--arrow" d="M16.14 9.93L22.21 16l-6.07 6.07M8.23 16h13.98"></path>
                            </g>
                        </svg>
                    </a>
                        </div>
                        <div class="upk-button-next-contenu upk-n-p">
                            <a class="link link--arrowed" href="#">
                        <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 32 32">
                            <g fill="none" stroke="#ff215a" stroke-width="1.5" stroke-linejoin="round" stroke-miterlimit="10">
                            <circle class="arrow-icon--circle" cx="16" cy="16" r="15.12"></circle>
                            <path class="arrow-icon--arrow" d="M16.14 9.93L22.21 16l-6.07 6.07M8.23 16h13.98"></path>
                            </g>
                        </svg>
                    </a>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="upk-salf-pagi-wrap">
                        <div class="swiper-pagination slider-contenu-pagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('slider_contenu', 'show_slider_contenu_shortcode');
