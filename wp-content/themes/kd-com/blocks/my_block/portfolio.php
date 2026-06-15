<?php
/**
 * Template pour le bloc "Portfolio / Réalisations"
 * Style inspiré d'Olena Blog #6 - 3 colonnes avec image, date, catégorie, titre, extrait, lien
 * Compatible avec les CPTs du thème kd-com :
 *   - project       (Réalisations Divi) → taxonomies project_category / project_tag
 *   - evenements    → taxonomy type_evenements
 *   - post          → taxonomy category
 *   - page          → pas de taxonomy
 *
 * @package kd-com
 */

// ─── Récupération des champs ACF ────────────────────────────────────────────
$titre_section      = get_field('portfolio_titre_section');
$sous_titre         = get_field('portfolio_sous_titre');
$post_type          = get_field('portfolio_post_type') ?: 'project';
$nombre             = get_field('portfolio_nombre') ?: 3;
$ordre_tri          = get_field('portfolio_ordre_tri') ?: 'date';
$sens_tri           = get_field('portfolio_sens_tri') ?: 'DESC';
$afficher_date      = get_field('portfolio_afficher_date');
$afficher_categorie = get_field('portfolio_afficher_categorie');
$afficher_extrait   = get_field('portfolio_afficher_extrait');
$texte_lien         = get_field('portfolio_texte_lien') ?: 'Voir la réalisation';
$lien_voir_tout     = get_field('portfolio_lien_voir_tout');
$texte_voir_tout    = get_field('portfolio_texte_voir_tout') ?: 'Voir toutes nos réalisations';

// Récupérer la catégorie selon le post type sélectionné
$categorie_id = null;
$taxonomy_name = '';
switch ($post_type) {
    case 'project':
        $categorie_id  = get_field('portfolio_cat_project');
        $taxonomy_name = 'project_category';
        // Étiquette optionnelle en complément
        $tag_id        = get_field('portfolio_tag_project');
        break;
    case 'evenements':
        $categorie_id  = get_field('portfolio_cat_evenements');
        $taxonomy_name = 'type_evenements';
        break;
    case 'post':
        $categorie_id  = get_field('portfolio_cat_post');
        $taxonomy_name = 'category';
        break;
    case 'page':
    default:
        $taxonomy_name = '';
        break;
}

// ─── Requête WP_Query ───────────────────────────────────────────────────────
$args = array(
    'post_type'      => $post_type,
    'posts_per_page' => intval($nombre),
    'orderby'        => $ordre_tri,
    'order'          => $sens_tri,
    'post_status'    => 'publish',
);

// Filtrer par catégorie si sélectionnée
if (!empty($categorie_id) && !empty($taxonomy_name)) {
    if ($post_type === 'post') {
        // Pour les posts natifs WordPress, on peut utiliser cat= directement
        $args['cat'] = intval($categorie_id);
    } else {
        $args['tax_query'] = array(
            'relation' => 'AND',
            array(
                'taxonomy' => $taxonomy_name,
                'field'    => 'term_id',
                'terms'    => intval($categorie_id),
            ),
        );
        // Ajouter l'étiquette si définie (project uniquement)
        if ($post_type === 'project' && !empty($tag_id)) {
            $args['tax_query'][] = array(
                'taxonomy' => 'project_tag',
                'field'    => 'term_id',
                'terms'    => intval($tag_id),
            );
        }
    }
} elseif ($post_type === 'project' && !empty($tag_id)) {
    // Tag seul sans catégorie
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'project_tag',
            'field'    => 'term_id',
            'terms'    => intval($tag_id),
        ),
    );
}

// Pour les événements à venir, filtrer les passés par défaut
if ($post_type === 'evenements') {
    $today = date('Y-m-d');
    $args['meta_query'] = array(
        'relation' => 'OR',
        // Événement ponctuel à venir
        array(
            'key'     => 'event_date',
            'value'   => $today,
            'compare' => '>=',
            'type'    => 'DATE',
        ),
        // Événement multi-jours ou récurrent dont la fin est à venir
        array(
            'key'     => 'event_end_date',
            'value'   => $today,
            'compare' => '>=',
            'type'    => 'DATE',
        ),
    );
}

$portfolio_query = new WP_Query($args);

// Preview Gutenberg sans données réelles
if (is_admin() && !$portfolio_query->have_posts()):
?>
<div class="portfolio-block portfolio-block--preview" style="padding:40px 20px;background:#f8f8f8;border:2px dashed #ddd;text-align:center;border-radius:8px;">
    <p style="margin:0;color:#888;font-size:16px;">🖼️ <strong>Portfolio / Réalisations</strong> — Sélectionnez un type de contenu et une catégorie dans les options du bloc.</p>
</div>
<?php
    wp_reset_postdata();
    return;
endif;

if (!$portfolio_query->have_posts()):
    echo '<p style="color:#888;text-align:center;">Aucun élément trouvé pour ces critères.</p>';
    wp_reset_postdata();
    return;
endif;
?>

<div class="portfolio-block acf-block">

    <?php if ($titre_section || $sous_titre): ?>
    <div class="portfolio-block__header">
        <?php if ($titre_section): ?>
            <h2 class="portfolio-block__title">
                <?php echo esc_html($titre_section); ?>
            </h2>
        <?php endif; ?>
        <?php if ($sous_titre): ?>
            <p class="portfolio-block__subtitle">
                <?php echo esc_html($sous_titre); ?>
            </p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="portfolio-block__grid">

        <?php
        $i = 0;
        while ($portfolio_query->have_posts()):
            $portfolio_query->the_post();
            $post_id = get_the_ID();

            // ── Extrait ──────────────────────────────────────────────────
            $extrait = '';
            if ($afficher_extrait) {
                $extrait = get_the_excerpt();
                if (empty($extrait)) {
                    $raw = get_the_content();
                    if (preg_match('/<p>(.*?)<\/p>/s', apply_filters('the_content', $raw), $m)) {
                        $extrait = wp_strip_all_tags($m[1]);
                    } else {
                        $extrait = wp_trim_words(wp_strip_all_tags($raw), 20);
                    }
                }
                $extrait = wp_trim_words($extrait, 20);
            }

            // ── Catégorie / Taxonomie ────────────────────────────────────
            $cat_label = '';
            $cat_url   = '';
            if ($afficher_categorie && !empty($taxonomy_name)) {
                $terms = get_the_terms($post_id, $taxonomy_name);
                if (!empty($terms) && !is_wp_error($terms)) {
                    $term      = $terms[0];
                    $cat_label = $term->name;
                    $cat_url   = get_term_link($term);
                }
            }

            // ── Date pour événements ─────────────────────────────────────
            $date_display = '';
            if ($afficher_date) {
                if ($post_type === 'evenements' && function_exists('format_event_date_display')) {
                    $date_display = format_event_date_display($post_id);
                } else {
                    $date_display = get_the_date('j F Y');
                }
            }

            $i++;
        ?>

        <article class="portfolio-card">

            <!-- IMAGE -->
            <a class="portfolio-card__image-link" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                <div class="portfolio-card__image-wrapper">
                    <?php if (has_post_thumbnail()): ?>
                        <?php the_post_thumbnail('large', array(
                            'class' => 'portfolio-card__image',
                            'alt'   => esc_attr(get_the_title()),
                        )); ?>
                    <?php else: ?>
                        <div class="portfolio-card__image-placeholder">
                            <svg viewBox="0 0 60 40" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <rect width="60" height="40" fill="#e8e8e8"/>
                                <circle cx="22" cy="15" r="5" fill="#c0c0c0"/>
                                <path d="M0 30 L15 18 L28 26 L38 14 L60 30 Z" fill="#d0d0d0"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>
            </a>

            <!-- CONTENU -->
            <div class="portfolio-card__body">

                <!-- META : date + catégorie -->
                <?php if ($date_display || $cat_label): ?>
                <div class="portfolio-card__meta">
                    <?php if ($date_display): ?>
                        <time class="portfolio-card__date" datetime="<?php echo get_the_date('c'); ?>">
                            <?php echo esc_html($date_display); ?>
                        </time>
                    <?php endif; ?>

                    <?php if ($date_display && $cat_label): ?>
                        <span class="portfolio-card__meta-sep" aria-hidden="true"> | </span>
                    <?php endif; ?>

                    <?php if ($cat_label): ?>
                        <a class="portfolio-card__category"
                           href="<?php echo esc_url(is_wp_error($cat_url) ? '#' : $cat_url); ?>">
                            <?php echo esc_html($cat_label); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- TITRE -->
                <h3 class="portfolio-card__title">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_title(); ?>
                    </a>
                </h3>

                <!-- EXTRAIT -->
                <?php if ($extrait): ?>
                <p class="portfolio-card__excerpt"><?php echo esc_html($extrait); ?></p>
                <?php endif; ?>

                <!-- LIEN -->
                <a class="portfolio-card__link u-link-arrow" href="<?php the_permalink(); ?>">
                    <?php echo esc_html($texte_lien); ?>
                </a>

            </div><!-- /.portfolio-card__body -->

        </article>

        <?php
        endwhile;
        wp_reset_postdata();
        ?>

    </div><!-- /.portfolio-block__grid -->

    <?php if ($lien_voir_tout): ?>
    <div class="portfolio-block__footer">
        <a class="portfolio-block__voir-tout" href="<?php echo esc_url($lien_voir_tout); ?>">
            <?php echo esc_html($texte_voir_tout); ?>
            <span aria-hidden="true">→</span>
        </a>
    </div>
    <?php endif; ?>

</div><!-- /.portfolio-block -->