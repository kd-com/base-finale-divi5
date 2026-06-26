<?php
// affichage du module slider accueil en front
function show_slider_accueil_shortcode() {
    $args = array(
        'post_type' => 'slider',
    );

    $my_query = new WP_Query($args);
    if ($my_query->have_posts()) { ?>

        <div class="hero-slider hero-style">
            <div class="swiper-container">
                <div class="swiper-wrapper hero">
                    <?php while ($my_query->have_posts()) {
                        $my_query->the_post();

                        $image_slider = get_field('image_slider'); // image PNG détourée
                        ?>
                        <?php
                        $video_id = get_field('video_id');
                        $video_plateforme = get_field('video_plateforme');
                        ?>
                        <?php if ($video_id) { ?>
                            <div class="swiper-slide hero video">
                                <?php
                                // Construction de l'URL d'embed à partir de la plateforme + l'ID.
                                // autoplay/mute/loop pour un usage "vidéo de fond" sans contrôles.
                                if ($video_plateforme === 'vimeo') {
                                    $embed_src = add_query_arg(
                                        array(
                                            'autoplay'   => 1,
                                            'muted'      => 1,
                                            'loop'       => 1,
                                            'background' => 1, // mode "fond" natif Vimeo : cache contrôles/titre
                                        ),
                                        'https://player.vimeo.com/video/' . rawurlencode($video_id)
                                    );
                                } else {
                                    // YouTube par défaut.
                                    $embed_src = add_query_arg(
                                        array(
                                            'autoplay'       => 1,
                                            'mute'           => 1,
                                            'loop'           => 1,
                                            'playlist'       => $video_id, // requis par YouTube pour boucler une seule vidéo
                                            'controls'       => 0,
                                            'showinfo'       => 0,
                                            'rel'            => 0,
                                            'modestbranding' => 1,
                                            'playsinline'    => 1,
                                        ),
                                        'https://www.youtube.com/embed/' . rawurlencode($video_id)
                                    );
                                }
                                ?>
                                <iframe
                                    class="slide-video-bg"
                                    src="<?php echo esc_url($embed_src); ?>"
                                    frameborder="0"
                                    allow="autoplay; encrypted-media; picture-in-picture"
                                    allowfullscreen
                                ></iframe>
                                <div class="slide-inner slide-inner-video">
                                    <?php
                                    // texte_slider est un champ wysiwyg : la valeur contient déjà
                                    // ses propres balises (paragraphes, gras, liens...), pas besoin
                                    // d'<p> englobant ni d'esc_html/the_field brut.
                                    $texte_slider = get_field('texte_slider');
                                    // lien_slider est un champ page_link (multiple => 0) : ACF
                                    // résout déjà la permalink complète, c'est une simple URL en string.
                                    $lien_slider = get_field('lien_slider');
                                    ?>
                                    <?php if (get_field('afficher_les_textes') && $texte_slider) { ?>
                                        <div class="container hero">
                                            <div class="slide-title" data-swiper-parallax="300">
                                                <h2><?php the_title(); ?></h2>
                                            </div>
                                            <div class="slide-text" data-swiper-parallax="400">
                                                <?php echo wp_kses_post($texte_slider); ?>
                                            </div>
                                            <div class="clearfix"></div>
                                            <?php if ($lien_slider) { ?>
                                                <div class="slide-btns" data-swiper-parallax="500">
                                                    <a class="et_pb_button et_pb_bg_layout_light" href="<?php echo esc_url($lien_slider); ?>">
                                                        <?php the_field('texte_bouton_slider'); ?>
                                                    </a>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="swiper-slide hero">
                                <div class="slide-inner">
                                    <?php
                                    $texte_slider = get_field('texte_slider');
                                    $lien_slider = get_field('lien_slider');
                                    ?>
                                    <?php if (get_field('afficher_les_textes') && $texte_slider) { ?>
                                        <div class="container hero">
                                            <div class="slide-title" data-swiper-parallax="300">
                                                <h2><?php the_title(); ?></h2>
                                            </div>
                                            <div class="slide-text" data-swiper-parallax="400">
                                                <?php echo wp_kses_post($texte_slider); ?>
                                            </div>
                                            <div class="clearfix"></div>
                                            <?php if ($lien_slider) { ?>
                                                <div class="slide-btns" data-swiper-parallax="500">
                                                    <a class="et_pb_button" href="<?php echo esc_url($lien_slider); ?>">
                                                        <?php the_field('texte_bouton_slider'); ?>
                                                    </a>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>

                                    <?php if ($image_slider) { ?>
                                        <img class="slide-visual" src="<?php echo esc_url($image_slider); ?>" alt="<?php the_title_attribute(); ?>" />
                                    <?php } ?>
                                </div>
                            </div>
                        <?php }
                    } ?>
                </div>
                <!-- swipper controls -->
                <div class="upk-salf-nav-pag-wrap hero">
                    <div class="upk-salf-navigation hero">
                        <div class="upk-button-prev upk-n-p">
                            <a class="link link--arrowed" href="#">
                                <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 32 32">
                                    <g fill="none" stroke="#ff215a" stroke-width="1.5" stroke-linejoin="round" stroke-miterlimit="10">
                                        <circle class="arrow-icon--circle" cx="16" cy="16" r="15.12"></circle>
                                        <path class="arrow-icon--arrow" d="M16.14 9.93L22.21 16l-6.07 6.07M8.23 16h13.98"></path>
                                    </g>
                                </svg>
                            </a>
                        </div>
                        <div class="upk-button-next upk-n-p">
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
                    <div class="upk-salf-pagi-wrap hero">
                        <div class="swiper-pagination hero"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php }

    wp_reset_query();
} // fin de show_slider_accueil_shortcode()

add_shortcode('slider_accueil', 'show_slider_accueil_shortcode');