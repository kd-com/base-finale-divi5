<?php
// gestion de l'affichage du bloc galerie-slider avec swiper.js
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
}
?>
<!-- affichage du slider galerie avec thumbnail avec le script swipter.js -->
<?
// chargement du script de gestion du slider galerie
wp_enqueue_script(
    'capitaine-blocks-slider',
    get_bloginfo('stylesheet_directory') . '/js/sliderblock.js',
    array('jquery'),
    null,
    true
);
?>
<div class="galerie-slider" <?php echo $animation_type !== 'none' ? 'data-aos="'.esc_attr($animation_type).'" data-aos-delay="'.esc_attr($animation_delay).'"' : ''; ?>>
    <div class="swiper main-slider">
        <div class="swiper-wrapper">
            <?php
            $images = get_field('images');
            if ($images) {
                foreach ($images as $image) {
                    echo '<div class="swiper-slide"><img src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt']) . '"></div>';
                }
            }
            ?>
        </div>
    </div>
    <div class="swiper thumb-slider">
        <div class="swiper-wrapper">
            <?php
            if ($images) {
                foreach ($images as $image) {
                echo '<div class="swiper-slide"><img src="' . esc_url($image['sizes']['thumbnail']) . '" alt="' . esc_attr($image['alt']) . '"></div>';         }
            }
            ?>
        </div>
    </div>
</div>