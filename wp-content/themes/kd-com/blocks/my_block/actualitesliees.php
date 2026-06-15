<?php
/**
 * Template pour le bloc "Actualités liées"
 * Utilise le champ ACF 'couleur_bloc' pour appliquer les couleurs
 */

// Récupération des champs ACF
$parent_page_id        = get_field('actualites_liees');
$nombre_colonnes       = get_field('nombre_colonnes') ?: '3';
$ordre_tri             = get_field('ordre_tri') ?: 'menu_order';
$sens_tri              = get_field('sens_tri') ?: 'ASC';
$style_grille          = get_field('style_grille') ?: 'standard';
$afficher_titre_parent = get_field('afficher_titre_parent');
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

// Options avancées
$limite_pages   = get_field('limite_pages');
$couleur_fond   = get_field('couleur_fond');
$couleur_texte  = get_field('couleur_texte');
$texte_lien     = get_field('texte_lien') ?: 'en savoir +';
$couleur_bouton = get_field('couleur_bouton');
// Nouvelles options de couleur
$couleur_titre_block = get_field('couleur_titre_block');
$couleur_titre = get_field('couleur_titre');
$couleur_liens = get_field('couleur_liens');
$couleur_fond_lien = get_field('couleur_fond_lien');

// Vérification basique
if (empty($parent_page_id)) {
    echo '<p class="text-red-600">Veuillez sélectionner une page parente dans les paramètres du bloc.</p>';
    return;
}

// Classes pour la grille
$grid_class = match($nombre_colonnes) {
    '2' => 'grid-cols-1 md:grid-cols-2',
    '3' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
    '4' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
    default => 'grid-cols-1 md:grid-cols-3'
};

// Récupérer le champ couleur_bloc (injecté automatiquement dans tous les blocs ACF)
$block_color_slug = get_field('couleur_bloc');

// Construire les classes du bloc
$block_classes = array('sous-pages-block');

// Ajouter le style de grille
$style_class = 'style-' . sanitize_title($style_grille);
$block_classes[] = $style_class;

// Ajouter la classe acf-block
$block_classes[] = 'acf-block';

// Ajouter la classe de couleur si définie
if (!empty($block_color_slug)) {
    $block_classes[] = 'has-' . $block_color_slug . '-color';
}

// Préparer la requête pour les sous-pages
$args = array(
    'post_type'      => 'post',
    
    'posts_per_page' => ($limite_pages ? intval($limite_pages) : -1),
    'orderby'        => $ordre_tri,
    'order'          => $sens_tri,
    'post_status'    => 'publish'
);

$subpages_query = new WP_Query($args);

if ($subpages_query->have_posts()) :
    // Appliquer toutes les classes
    $all_classes = implode(' ', $block_classes);
    echo '<div class="' . esc_attr($all_classes) . '">';

    // Titre parent
    if ($afficher_titre_parent) {
        $parent_page = get_post($parent_page_id);
        if ($parent_page) {
            echo '<h2 class="sous-pages-parent-title has-' . esc_attr($couleur_titre_block) . '-color">' . esc_html("Actualités liées") . '</h2>';
        }
    }

    echo '<div class="grid ' . esc_attr($grid_class) . ' gap-6">';

    $i = 0;
    while ($subpages_query->have_posts()) : $subpages_query->the_post();
        $delay = $animation_delay * $i;
        ?>
        <article class="sous-page-card has-<?php echo esc_attr($couleur_fond); ?>-background-color" <?php echo $animation_type !== 'none' ? 'data-aos="'.esc_attr($animation_type).'" data-aos-delay="'.esc_attr($delay).'"' : ''; ?>>
            <?php if (has_post_thumbnail()) : ?>
                <div class="sous-page-image">
                    <?php the_post_thumbnail('medium', array('class' => 'w-full h-auto')); ?>
                </div>
            <?php endif; ?>

            <div class="sous-page-content ">
                <h3 class="sous-page-title has-<?php echo esc_attr($couleur_titre); ?>-color">
                    <?php the_title(); ?>
                </h3>

                <div class="sous-page-excerpt has-<?php echo esc_attr($couleur_texte); ?>-color"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></div>

                <div class='sous-page-link-wrapper'>
                    <a class="sous-page-link btn-sous-page has-<?php echo esc_attr($couleur_fond_lien); ?>-background-color has-<?php echo esc_attr($couleur_liens);?>-color" href="<?php the_permalink(); ?>">
                        <?php echo esc_html($texte_lien); ?>
                        <svg class="arrow-icon" width="22" height="22" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                            <g fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" stroke-miterlimit="10">
                                <circle class="arrow-icon--circle" cx="16" cy="16" r="15.12"></circle>
                                <path class="arrow-icon--arrow" d="M16.14 9.93L22.21 16l-6.07 6.07M8.23 16h13.98"></path>
                            </g>
                        </svg>
                    </a>
                </div>
            </div>
        </article>
        <?php
        $i++;
    endwhile;

    echo '</div>'; // .grid
    echo '</div>'; // .sous-pages-block

    wp_reset_postdata();
else :
    echo '<p class="text-gray-600">Aucune actualité liée trouvée.</p>';
endif;

?>