<?php
/**
 * Shortcode [pages_mises_en_avant] : affiche les pages sélectionnées via le
 * champ ACF "relation" page_mise_en_avant.
 *
 * Carte classique : image mise en avant + titre + extrait.
 * Ordre d'affichage : celui de la sélection dans le champ relation (pas de tri).
 *
 * Par défaut, le champ est lu sur la page courante (get_field sans 2e argument
 * = post courant dans la boucle WP). Un attribut "page_id" permet de forcer
 * la lecture du champ sur une page précise (utile si le shortcode est inséré
 * ailleurs que sur la page d'accueil elle-même, ex: widget, autre page).
 *
 * Usage : [pages_mises_en_avant]
 *         [pages_mises_en_avant page_id="12"]
 *
 * @package kd-com
 */

function kdcom_pages_mises_en_avant_shortcode($atts) {

    $atts = shortcode_atts(
        array(
            'page_id' => 0, // 0 = lit le champ sur le post/page courant
        ),
        $atts,
        'pages_mises_en_avant'
    );

    $source_id = $atts['page_id'] ? (int) $atts['page_id'] : false;

    // get_field(field, false) lit sur le post courant de la boucle WP.
    $pages_mises_en_avant = $source_id
        ? get_field('page_mise_en_avant', $source_id)
        : get_field('page_mise_en_avant');

    if (!$pages_mises_en_avant) {
        return '';
    }

    ob_start();
    ?>

    <div class="pages-mises-en-avant">
        <div class="pages-mises-en-avant-grid">
            <?php foreach ($pages_mises_en_avant as $page) {

                // Le champ relation peut retourner un ID (int) ou un objet WP_Post
                // selon le 'return_format' choisi dans la config ACF du champ.
                $page_id = is_object($page) ? $page->ID : $page;

                if (!$page_id) {
                    continue;
                }
                ?>

                <div class="page-card">
                    <a class="page-card-link" href="<?php echo esc_url(get_permalink($page_id)); ?>">

                        <?php if (has_post_thumbnail($page_id)) { ?>
                            <div class="page-card-image">
                                <?php echo get_the_post_thumbnail($page_id, 'medium_large'); ?>
                            </div>
                        <?php } ?>

                        <div class="page-card-content">
                            <h3 class="page-card-title">
                                <?php echo esc_html(get_the_title($page_id)); ?>
                            </h3>
                            <span class="arrow-icon"><svg viewBox="0 0 22 22" width="22" height="22"><g stroke="currentColor" fill="none"><circle class="arrow-icon--circle" cx="11" cy="11" r="10"/><path d="M7 11h8m0 0l-3-3m3 3l-3 3"/></g></svg></span>

                            
                        </div>

                    </a>
                </div>

            <?php } ?>
        </div>
    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('pages_mises_en_avant', 'kdcom_pages_mises_en_avant_shortcode');