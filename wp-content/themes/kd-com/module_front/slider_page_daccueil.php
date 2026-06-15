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
                        $my_query->the_post(); ?>
                        <?php if(get_field('video')) { ?>
                            <div class="swiper-slide hero video">
                                <!-- <div class="embed-container"> -->
                                    <?php
                                    // Load value.
                                    $iframe = get_field('video');

                                    // Use preg_match to find iframe src.
                                    preg_match('/src="(.+?)"/', $iframe, $matches);
                                    $src = $matches[1];

                                    // Add extra parameters to src and replace HTML.
                                    $params = array(
                                        'controls'  => 0,
                                        'hd'        => 1,
                                        'autohide'  => 1,
                                        'autoplay'  => 1,
                                        'mute'      => 1,
                                        'loop'      => 1,
                                        'rel'       => 0
                                    );
                                    $new_src = add_query_arg($params, $src);
                                    $iframe = str_replace($src, $new_src, $iframe);

                                    // Add extra attributes to iframe HTML.
                                    $attributes = 'frameborder="0"';
                                    $iframe = str_replace('></iframe>', ' ' . $attributes . '></iframe>', $iframe);

                                    // Display customized HTML.
                                    echo $iframe; ?>
                                <!-- </div> -->
                                <div class="slide-inner slide-bg-image" data-background="<?= the_field('image_slider');?>">
                                    <?php if(get_field('afficher_les_textes')) {?>
                                        <?php if(get_field('texte_slider')) { ?>
                                            <div class="container hero">
                                                <div class="slide-title" data-swiper-parallax="300">
                                                    <h2><?php the_title(); ?></h2>
                                                </div>
                                                <div class="slide-text" data-swiper-parallax="400">
                                                    <p><?php the_field('texte_slider'); ?></p>
                                                </div>
                                                <div class="clearfix"></div>
                                                <?php if(get_field('lien_slider')) { ?>
                                                    <div class="slide-btns et_pb_bg_layout_dark violet-fond" data-swiper-parallax="500">
                                
                                                        <a class="et_pb_button et_pb_bg_layout_light" href="<?= the_field('lien_slider'); ?>">
                                                            <?php the_field('texte_bouton_slider'); ?>
                                                        </a>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } else { ?> 
                            <div class="swiper-slide hero">
                                <div class="slide-inner slide-bg-image" >
                                    <img src="<?= the_field('image_slider'); ?>"/>
                                    <?php if(get_field('afficher_les_textes')) {?>
                                        <div class="container hero">
                                            <div class="slide-title" data-swiper-parallax="300">
                                                <h2><?php the_title(); ?></h2>
                                            </div>
                                            <div class="slide-text" data-swiper-parallax="400">
                                                <p><?php the_field('texte_slider'); ?></p>
                                            </div>
                                            <div class="clearfix"></div>
                                            <?php if(get_field('lien_slider')) { ?>
                                                <div class="slide-btns et_pb_bg_layout_dark violet-fond" data-swiper-parallax="500">
                            
                                                    <a class="et_pb_button" href="<?= the_field('lien_slider'); ?>">
                                                        <?php the_field('texte_bouton_slider'); ?>
                                                    </a>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php }
                    } ?>
                </div>
                <!-- <div class="flamme-image" data-swipper-parallax="600">
                                    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script> 
                                    <dotlottie-player src="https://lottie.host/fa1d20d4-6ab3-466a-b671-b86cdf26e58f/fOw9YT1L6m.json" background="transparent" speed="1" style="width: 300px; height: 300px;" loop autoplay></dotlottie-player>
                                </div> -->
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
        <!-- <div class="et_pb_bottom_inside_divider" style="">
        <svg style="position:relative;bottom:150px;" xmlns="http://www.w3.org/2000/svg" width="100%" height="150px" viewBox="0 0 1280 140" preserveAspectRatio="none"><g fill="#FFFFFF"><path d="M0 140h1280C573.08 140 0 0 0 0z" fill-opacity=".3"/><path d="M0 140h1280C573.08 140 0 30 0 30z" fill-opacity=".5"/><path d="M0 140h1280C573.08 140 0 60 0 60z"/></g></svg>
        </div> -->
        <?php } ?>
    <?php } 

    wp_reset_query();


  
add_shortcode('slider_accueil', 'show_slider_accueil_shortcode');
?>