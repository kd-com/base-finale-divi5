<?php
/**
 * Shortcode [projets_galerie] — Grille de projets avec galerie en lightbox
 * 
 * Affiche les réalisations (CPT project) sous forme de grille cliquable :
 * chaque carte ouvre sa galerie photo (champ ACF "projet_galerie") en lightbox,
 * sans navigation vers la page single du projet.
 * 
 * Le shortcode est piloté par les réglages du bloc "Réglages d'affichage des
 * réalisations" (portfolio_order_settings.php) : il ne s'exécute que si la
 * case "Activer la galerie lightbox" y est cochée. La catégorie choisie dans
 * ces réglages sert de filtre par défaut, surchargeable via l'attribut
 * "categorie" du shortcode.
 * 
 * ─────────────────────────────────────────────────────────────
 * UTILISATION (module Texte ou module Code dans Divi) :
 * 
 *   [projets_galerie]
 *      → tous les projets, dans la catégorie définie dans les réglages
 *        (ou toutes catégories si aucune n'est définie)
 * 
 *   [projets_galerie categorie="site-vitrine"]
 *      → tous les projets d'une catégorie précise (slug de project_category)
 *        → surcharge la catégorie définie dans les réglages
 * 
 *   [projets_galerie aleatoire="oui"]
 *      → 3 projets choisis au hasard (catégorie des réglages appliquée)
 * 
 *   [projets_galerie categorie="site-vitrine" aleatoire="oui" nombre="5"]
 *      → 5 projets aléatoires dans la catégorie "site-vitrine"
 * 
 * Attributs disponibles :
 *   - categorie : slug de la taxonomy project_category (vide = valeur des réglages)
 *   - aleatoire : "oui" ou "non" (défaut : "non")
 *   - nombre    : nombre de projets à afficher (vide = auto : 3 si aléatoire, tous sinon)
 * 
 * Prérequis :
 *   - Case "Activer la galerie lightbox" cochée dans les réglages du module portfolio
 *   - Champ ACF Galerie "projet_galerie" renseigné sur chaque fiche projet
 *   - Script gallery-lightbox.js enregistré (functions.php), il n'est chargé
 *     que lorsque ce shortcode s'exécute réellement
 * 
 * Emplacement : wp-content/themes/kd-com/module_front/projets_galerie.php
 * 
 * Inclure depuis cpt_portfolio.php :
 *   include get_stylesheet_directory() . '/module_front/projets_galerie.php';
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function kd_shortcode_projets_galerie( $atts ) {

    // Le shortcode ne s'exécute que si la case est cochée dans les réglages
    if ( (int) get_option( 'portfolio_lightbox_active', 0 ) !== 1 ) {
        return '';
    }

    $atts = shortcode_atts([
        'categorie' => get_option( 'portfolio_lightbox_categorie', '' ), // valeur par défaut = réglages
        'aleatoire' => 'non',
        'nombre'    => '',
    ], $atts, 'projets_galerie');

    $aleatoire = ( $atts['aleatoire'] === 'oui' );
    $nombre    = $atts['nombre'] !== '' ? intval( $atts['nombre'] ) : ( $aleatoire ? 3 : -1 );

    $args = [
        'post_type'      => 'project',
        'posts_per_page' => $nombre,
        'orderby'        => $aleatoire ? 'rand' : 'date',
        'order'          => 'DESC',
    ];

    if ( ! empty( $atts['categorie'] ) ) {
        $args['tax_query'] = [[
            'taxonomy' => 'project_category',
            'field'    => 'slug',
            'terms'    => sanitize_title( $atts['categorie'] ),
        ]];
    }

    $projets = new WP_Query( $args );

    if ( ! $projets->have_posts() ) {
        return '<p>Aucun projet trouvé.</p>';
    }

    // Script chargé uniquement quand le shortcode s'exécute réellement
    // Enregistre le script uniquement s'il ne l'est pas déjà
    // (indépendant du module galerie_lightbox.php, qui peut être actif ou non)
    if ( ! wp_script_is( 'gallery-lightbox', 'registered' ) ) {
        wp_register_script(
            'gallery-lightbox',
            get_stylesheet_directory_uri() . '/js/gallery-lightbox.js',
            array(),
            '1.1',
            true
        );
    }
    wp_enqueue_script( 'gallery-lightbox' );

    ob_start();
    ?>
    <div class="projets-grid">
        <?php while ( $projets->have_posts() ) : $projets->the_post();

            $gallery = get_field('projet_galerie');
            $images  = [];

            if ( $gallery ) {
                foreach ( $gallery as $img ) {
                    $images[] = [
                        'src' => $img['url'],
                        'alt' => $img['alt'],
                    ];
                }
            }
        ?>
            <div class="projet-item" data-gallery='<?php echo esc_attr( wp_json_encode( $images ) ); ?>'>
                <?php the_post_thumbnail('medium'); ?>
                <h3><?php the_title(); ?></h3>
            </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'projets_galerie', 'kd_shortcode_projets_galerie' );