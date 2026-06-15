<?php
// Shortcode [affichage_page_spe] : affiche la page choisie dans l'onglet Livre sous forme de bloc sous-page

add_shortcode('affichage_page_spe', function($atts) {
    $atts = shortcode_atts([
        'page_id' => ''
    ], $atts);

    $post = false;

    if (!empty($atts['page_id'])) {
        // Si l'attribut page_id est fourni, l'utiliser
        if (is_numeric($atts['page_id'])) {
            $post = get_post($atts['page_id']);
        } elseif (is_string($atts['page_id'])) {
            $post_id = url_to_postid($atts['page_id']);
            $post = $post_id ? get_post($post_id) : false;
        }
    } else {
        // Sinon, utiliser le champ ACF comme avant
        $front_id = get_option('page_on_front');
        $page = get_field('page_famille', $front_id);
        if (!$page) {
            return '<p class="text-red-600">Aucune page sélectionnée</p>';
        }
        if (is_numeric($page)) {
            $post = get_post($page);
        } elseif (is_string($page)) {
            $post_id = url_to_postid($page);
            $post = $post_id ? get_post($post_id) : false;
        }
    }

    if (!$post) {
        return '<p class="text-red-600">Page introuvable.</p>';
    }

    ob_start();
    ?>
    <div class="sous-pages-block acf-block style-standard grid grid-cols-1 md:grid-cols-2 gap-6">
        <article class="sous-page-card">
            <div class="sous-page-content">
                <h3 class="sous-page-title">
                    <a href="<?php echo get_permalink($post->ID); ?>"><?php echo esc_html($post->post_title); ?></a>
                </h3>
                <div class="sous-page-excerpt"><?php echo wp_trim_words(get_the_excerpt($post->ID), 20); ?></div>
                <div class="sous-page-link-wrapper" style="background-color: #f5f5f5; padding: 12px 16px; width: fit-content; display: inline-block;">
                    <a class="sous-page-link btn-sous-page" href="<?php echo get_permalink($post->ID); ?>">
                        en savoir +
                        <svg class="arrow-icon" width="22" height="22" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                            <g fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" stroke-miterlimit="10">
                                <circle class="arrow-icon--circle" cx="16" cy="16" r="15.12"></circle>
                                <path class="arrow-icon--arrow" d="M16.14 9.93L22.21 16l-6.07 6.07M8.23 16h13.98"></path>
                            </g>
                        </svg>
                    </a>
                </div>
            </div>
            <?php if (has_post_thumbnail($post->ID)) : ?>
                <div class="sous-page-image">
                    <a href="<?php echo get_permalink($post->ID); ?>">
                        <?php echo get_the_post_thumbnail($post->ID, 'medium_large'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </article>
    </div>
    <?php
    return ob_get_clean();
});
// Shortcode [affichage_page_spe2] : mise en page colonne, image à gauche, contenu à droite centré verticalement
add_shortcode('affichage_page_spe2', function($atts) {
    $atts = shortcode_atts([
        'page_id' => ''
    ], $atts);

    $post = false;

    if (!empty($atts['page_id'])) {
        // Si l'attribut page_id est fourni, l'utiliser
        if (is_numeric($atts['page_id'])) {
            $post = get_post($atts['page_id']);
        } elseif (is_string($atts['page_id'])) {
            $post_id = url_to_postid($atts['page_id']);
            $post = $post_id ? get_post($post_id) : false;
        }
    } else {
        // Sinon, utiliser le champ ACF comme avant
        $front_id = get_option('page_on_front');
        $page = get_field('page_livre', $front_id);
        if (!$page) {
            return '<p class="text-red-600">Aucune page sélectionnée</p>';
        }
        if (is_numeric($page)) {
            $post = get_post($page);
        } elseif (is_string($page)) {
            $post_id = url_to_postid($page);
            $post = $post_id ? get_post($post_id) : false;
        }
    }

    if (!$post) {
        return '<p class="text-red-600">Page introuvable.</p>';
    }

    ob_start();
    ?>
    <div class="page-spe-block2 grid grid-cols-1 md:grid-cols-2 gap-0 items-stretch">
        <div class="page-spe-content flex flex-col justify-center h-full p-8 order-1 md:order-2">
            <h3 class="page-spe-title text-2xl font-bold mb-4 text-center md:text-left">
                <a href="<?php echo get_permalink($post->ID); ?>"><?php echo esc_html($post->post_title); ?></a>
            </h3>
            <div class="page-spe-excerpt mb-6 text-center md:text-left"><?php echo wp_trim_words(get_the_excerpt($post->ID), 30); ?></div>
            <div class="page-spe-link-wrapper flex justify-center md:justify-start">
                <a class="px-6 py-3 u-link-arrow" href="<?php echo get_permalink($post->ID); ?>">
                    en savoir +
                    <svg class="arrow-icon ml-2" width="22" height="22" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                        <g fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" stroke-miterlimit="10">
                            <circle class="arrow-icon--circle" cx="16" cy="16" r="15.12"></circle>
                            <path class="arrow-icon--arrow" d="M16.14 9.93L22.21 16l-6.07 6.07M8.23 16h13.98"></path>
                        </g>
                    </svg>
                </a>
            </div>
        </div>
        <?php if (has_post_thumbnail($post->ID)) : ?>
        <div class="page-spe-image h-full w-full order-2 md:order-1">
            <?php echo get_the_post_thumbnail($post->ID, 'large', ['class' => 'w-full h-full object-cover']); ?>
        </div>
        <?php else: ?>
        <div class="page-spe-image h-full w-full bg-gray-100 order-2 md:order-1"></div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
});