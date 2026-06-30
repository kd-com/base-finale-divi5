<?php
// affichage du module slider clients pour les logos clients cliquables
function show_slider_clients_shortcode() {
    ob_start();
    $args = array(
        'post_type' => 'slider_client',
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) { ?>
        <div class="logo-slider">
            <div class="swiper-wrapper">
                <?php while ($query->have_posts()) {
                    $query->the_post(); ?>
                    <div class="client-item swiper-slide">
                        <?php if(get_field('client_link')): ?>
                            <a href="<?php the_field('client_link'); ?>" target="_blank">
                                <img src="<?php the_field('client_logo'); ?>" alt="<?php the_title(); ?>" class="img-responive">
                            </a>
                        <?php else: ?>
                            <img src="<?php the_field('client_logo'); ?>" alt="<?php the_title(); ?>" class="img-responive">
                        <?php endif; ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php }
    $html = ob_get_contents();
    ob_end_clean();
    wp_reset_query();
    return $html;
}
add_shortcode('slider_clients', 'show_slider_clients_shortcode');
