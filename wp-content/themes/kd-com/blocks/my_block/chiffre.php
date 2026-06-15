<?php
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
<div class="chiffre_cle_gut has-<?php the_field('choix_couleurs_copier');?>-background-color <?php if(get_field('ombre_portee')) echo' has-shadow' ?>" <?php echo $animation_type !== 'none' ? 'data-aos="'.esc_attr($animation_type).'" data-aos-delay="'.esc_attr($animation_delay).'"' : ''; ?>>
    <?php if(get_field('icon_ou_image')):
        if(get_field('image')):?>        
            <img class="ico-img" src="<?= get_field('image'); ?>">
        <?php endif; ?>
    <?php else :
        if(get_field('icon_fontawesome')):?>
            <i class="<?= esc_attr(get_field('icon_fontawesome')); ?> fa-2x has-<?= esc_attr(get_field('couleur_de_licon')); ?>-color"></i>
        <?php endif; ?>        
    <?php endif;?>
    <?php if(get_field("description_du_chiffre_cle_haut")):?>
        <span class="text has-<?= get_field('choix_couleurs'); ?>-color"><?= get_field('description_du_chiffre_cle_haut'); ?></span>
    <?php endif;?>
    <?php if(get_field('poucentage_check')) { ?>
        <div class="flex-pourcent">
            <span class="num has-<?= get_field('couleur_de_licon'); ?>-color" data-count-from="0" data-count-to="<?= get_field('nombre'); ?>" data-count-speed="100"><?= get_field('nombre'); ?></span>
            <span class="pourcent has-<?= get_field('couleur_de_licon'); ?>-color">%</span>
        </div>
    <?php } else if (get_field('symbole_monetaire')) { ?>
        <div class="flex-pourcent">
            <span class="num has-<?= get_field('couleur_de_licon'); ?>-color" data-count-from="0" data-count-to="<?= get_field('nombre'); ?>" data-count-speed="100"><?= get_field('nombre'); ?></span>
            <span class="pourcent has-<?= get_field('couleur_de_licon'); ?>-color"><?= get_field('symbole_monetaire'); ?></span>
        </div>
    <?php } else { ?>
        <span class="num has-<?= get_field('couleur_de_licon'); ?>-color" data-count-from="0" data-count-to="<?= get_field('nombre'); ?>" data-count-speed="<?= get_field('vitesse_du_compteur'); ?>"><?= get_field('nombre'); ?></span>
    <?php } ?>
    <?php if(get_field("description_du_chiffre_cle_bas")):?>
        <span class="text has-<?= get_field('choix_couleurs'); ?>-color"><?= get_field('description_du_chiffre_cle_bas'); ?></span>
    <?php endif;?>
</div>

