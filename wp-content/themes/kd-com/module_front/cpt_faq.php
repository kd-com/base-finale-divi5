<?php
/**
 * Shortcodes FAQ
 * 
 * Utilisation :
 *  [faq_liste]                          → toutes les questions
 *  [faq_liste categorie="ma-categorie"] → filtrée par catégorie (slug)
 *  [faq_categories]                     → liste des catégories cliquables
 *  [faq_complete]                       → navigation catégories + questions groupées
 */


/**
 * [faq_liste] — Affiche les questions/réponses en accordéon
 * 
 * Attributs :
 *   categorie  : slug de la faq_categorie (optionnel)
 *   nombre     : nombre de posts à afficher (défaut : -1 = tous)
 */
add_shortcode( 'faq_liste', 'kd_shortcode_faq_liste' );
function kd_shortcode_faq_liste( $atts ) {

    $atts = shortcode_atts( array(
        'categorie' => '',
        'nombre'    => -1,
    ), $atts, 'faq_liste' );

    $args = array(
        'post_type'      => 'kd_faq',
        'posts_per_page' => intval( $atts['nombre'] ),
        'post_status'    => 'publish',
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
    );

    if ( ! empty( $atts['categorie'] ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'faq_categorie',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $atts['categorie'] ),
            ),
        );
    }

    $query = new WP_Query( $args );

    if ( ! $query->have_posts() ) {
        return '<p class="faq-empty">Aucune question trouvée.</p>';
    }

    ob_start();
    ?>
    <div class="faq-article-list blog faq">
        <?php while ( $query->have_posts() ) : $query->the_post(); ?>
            <?php
            $answer = get_field( 'faq_answer' );
            $post_id = get_the_ID();
            ?>
            <article id="faq-<?php echo esc_attr( $post_id ); ?>" class="et_pb_post faq-item">
                <div class="blog_info">
                    <h2 class="entry-title">
                        <button
                            class="faq-question"
                            aria-expanded="false"
                            aria-controls="faq-answer-<?php echo esc_attr( $post_id ); ?>"
                        >
                            <?php the_title(); ?>
                        </button>
                    </h2>
                    <div
                        id="faq-answer-<?php echo esc_attr( $post_id ); ?>"
                        class="faq-answer"
                        hidden
                    >
                        <?php echo wp_kses_post( $answer ); ?>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
    <?php
    wp_reset_postdata();

    return ob_get_clean();
}


/**
 * [faq_categories] — Affiche la liste des catégories FAQ avec liens filtrants
 * 
 * Attributs :
 *   selected : slug de la catégorie active (optionnel)
 */
add_shortcode( 'faq_categories', 'kd_shortcode_faq_categories' );
function kd_shortcode_faq_categories( $atts ) {

    $atts = shortcode_atts( array(
        'selected' => '',
    ), $atts, 'faq_categories' );

    $categories = get_terms( array(
        'taxonomy'   => 'faq_categorie',
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ) );

    if ( is_wp_error( $categories ) || empty( $categories ) ) {
        return '';
    }

    ob_start();
    ?>
    <ul class="faq_cat_list">
        <?php foreach ( $categories as $cat ) : ?>
            <li class="faq_cat_list_item <?php echo ( $atts['selected'] === $cat->slug ) ? 'selected' : ''; ?>">
                <a
                    href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
                    class="faq_cat_list_link"
                    data-cat="<?php echo esc_attr( $cat->slug ); ?>"
                >
                    <h5><?php echo esc_html( $cat->name ); ?></h5>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean();
}


add_shortcode( 'faq_categories_scroll', 'kd_shortcode_faq_categories_scroll' );
function kd_shortcode_faq_categories_scroll( $atts ) {

    $atts = shortcode_atts( array(
        'selected' => '',
    ), $atts, 'faq_categories' );

    $categories = get_terms( array(
        'taxonomy'   => 'faq_categorie',
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ) );

    if ( is_wp_error( $categories ) || empty( $categories ) ) {
        return '';
    }

    ob_start();
    ?>
    <ul class="faq_cat_list sticky">
        <?php foreach ( $categories as $cat ) : ?>
            <li class="faq_cat_list_item <?php echo ( $atts['selected'] === $cat->slug ) ? 'selected' : ''; ?>">
                <a
                    href="<?php echo '#faq-cat-' . esc_attr( $cat->slug ); ?>"
                    class="faq_cat_list_link"
                    data-cat="<?php echo esc_attr( $cat->slug ); ?>"
                >
                    <h5><?php echo esc_html( $cat->name ); ?></h5>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean();
}


/**
 * [faq_complete] — Navigation par catégories + questions groupées par catégorie
 * 
 * Attributs :
 *   nombre : nombre de posts par catégorie (défaut : -1 = tous)
 */
add_shortcode( 'faq_complete', 'kd_shortcode_faq_complete' );
function kd_shortcode_faq_complete( $atts ) {

    $atts = shortcode_atts( array(
        'nombre' => -1,
    ), $atts, 'faq_complete' );

    $categories = get_terms( array(
        'taxonomy'   => 'faq_categorie',
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ) );

    if ( is_wp_error( $categories ) || empty( $categories ) ) {
        // Pas de catégories → affichage simple
        return kd_shortcode_faq_liste( array( 'nombre' => $atts['nombre'] ) );
    }

    ob_start();
    ?>
    <div class="faq_list">

        <!-- Navigation catégories -->
        <nav class="faq-categories-nav" aria-label="Catégories FAQ">
            <?php echo kd_shortcode_faq_categories_scroll( array() ); ?>
        </nav>

        <!-- Questions groupées par catégorie -->
         <div class="faq-section-grid">
        <?php foreach ( $categories as $cat ) : ?>
            <section class="faq-section" id="faq-cat-<?php echo esc_attr( $cat->slug ); ?>">
                <h2><?php echo esc_html( $cat->name ); ?></h2>

                <?php
                $args = array(
                    'post_type'      => 'kd_faq',
                    'posts_per_page' => intval( $atts['nombre'] ),
                    'post_status'    => 'publish',
                    'orderby'        => 'menu_order title',
                    'order'          => 'ASC',
                    'tax_query'      => array(
                        array(
                            'taxonomy' => 'faq_categorie',
                            'field'    => 'slug',
                            'terms'    => $cat->slug,
                        ),
                    ),
                );

                $query = new WP_Query( $args );

                if ( $query->have_posts() ) :
                ?>
                    <div class="faq-article-list blog faq">
                        <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                            <?php
                            $answer  = get_field( 'faq_answer' );
                            $post_id = get_the_ID();
                            ?>
                            <article id="faq-<?php echo esc_attr( $post_id ); ?>" class="et_pb_post faq-item">
                                <div class="blog_info">
                                    <h2 class="entry-title">
                                        <button
                                            class="faq-question"
                                            aria-expanded="false"
                                            aria-controls="faq-answer-<?php echo esc_attr( $post_id ); ?>"
                                        >
                                            <?php the_title(); ?>
                                        </button>
                                    </h2>
                                    <div
                                        id="faq-answer-<?php echo esc_attr( $post_id ); ?>"
                                        class="faq-answer"
                                        hidden
                                    >
                                        <?php echo wp_kses_post( $answer ); ?>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                    <?php wp_reset_postdata(); ?>
                <?php endif; ?>

            </section>
        <?php endforeach; ?>
                </div>

    </div>
    <?php
    return ob_get_clean();
}


/**
 * JS : accordéon accessible (aucune dépendance jQuery)
 * Injecté une seule fois en pied de page si un shortcode FAQ est présent.
 */
add_action( 'wp_footer', 'kd_faq_accordion_script' );
function kd_faq_accordion_script() {
    // On n'injecte le script que si la page contient des items FAQ
    if ( ! has_shortcode( get_post()->post_content ?? '', 'faq_liste' )
        && ! has_shortcode( get_post()->post_content ?? '', 'faq_complete' ) ) {
        return;
    }
    ?>
    <script>
    (function () {
        'use strict';

        document.addEventListener('DOMContentLoaded', function () {
            var buttons = document.querySelectorAll('.faq-question');

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var expanded = this.getAttribute('aria-expanded') === 'true';
                    var answerId = this.getAttribute('aria-controls');
                    var answer   = document.getElementById(answerId);

                    // Fermer les autres items du même groupe
                    var group = this.closest('.faq-article-list, .faq_list');
                    if (group) {
                        group.querySelectorAll('.faq-question[aria-expanded="true"]').forEach(function (openBtn) {
                            if (openBtn !== btn) {
                                openBtn.setAttribute('aria-expanded', 'false');
                                var openId = openBtn.getAttribute('aria-controls');
                                var openAnswer = document.getElementById(openId);
                                if (openAnswer) { openAnswer.hidden = true; }
                                openBtn.closest('.faq-item')?.classList.remove('is-open');
                            }
                        });
                    }

                    // Basculer l'item courant
                    this.setAttribute('aria-expanded', String(!expanded));
                    if (answer) { answer.hidden = expanded; }
                    this.closest('.faq-item')?.classList.toggle('is-open', !expanded);
                });
            });
        });
    })();
    </script>
    <?php
}