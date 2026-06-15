<?php
/**
 * Réglages de l'ordre d'affichage des Réalisations
 * 
 * Ajoute des champs natifs WordPress (register_setting / add_settings_field)
 * qui s'affichent dans la page "Gestion des modules", juste sous le toggle portfolio.
 * 
 * Emplacement : wp-content/themes/kd-com/module_admin/portfolio_order_settings.php
 * Inclure depuis cpt_portfolio.php :
 *   include get_stylesheet_directory() . '/module_admin/portfolio_order_settings.php';
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─────────────────────────────────────────────
// 1. Enregistrement des options WordPress
// ─────────────────────────────────────────────
add_action( 'admin_init', function () {

    register_setting( 'reglages_site_modules_group', 'portfolio_orderby', array(
        'type'              => 'string',
        'default'           => 'date',
        'sanitize_callback' => function ( $v ) {
            $allowed = array( 'date', 'date_asc', 'cat', 'rand', 'title', 'title_desc', 'menu_order' );
            return in_array( $v, $allowed, true ) ? $v : 'date';
        },
    ) );

    register_setting( 'reglages_site_modules_group', 'portfolio_posts_per_page', array(
        'type'              => 'integer',
        'default'           => 0,
        'sanitize_callback' => function ( $v ) {
            $v = (int) $v;
            return ( $v >= -1 ) ? $v : 0;
        },
    ) );
} );


// ─────────────────────────────────────────────
// 2. Injection HTML dans la page modules
//    On accroche sur admin_footer pour injecter
//    via JS juste après le bloc portfolio
// ─────────────────────────────────────────────
add_action( 'admin_footer', function () {

    // Uniquement sur la page des modules
    if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'reglages-site-modules' ) {
        return;
    }

    $orderby        = get_option( 'portfolio_orderby', 'date' );
    $posts_per_page = (int) get_option( 'portfolio_posts_per_page', 0 );

    $options = array(
        'date'       => '📅 Date de publication (récent → ancien)',
        'date_asc'   => '📅 Date de publication (ancien → récent)',
        'cat'        => '🗂️ Catégorie (ordre alphabétique)',
        'rand'       => '🔀 Aléatoire',
        'title'      => '🔤 Titre (A → Z)',
        'title_desc' => '🔤 Titre (Z → A)',
        'menu_order' => '🔢 Ordre manuel (drag & drop)',
    );

    $select_html = '<select name="portfolio_orderby" id="portfolio_orderby" style="max-width:340px;width:100%;">';
    foreach ( $options as $val => $label ) {
        $selected     = selected( $orderby, $val, false );
        $select_html .= '<option value="' . esc_attr( $val ) . '" ' . $selected . '>' . esc_html( $label ) . '</option>';
    }
    $select_html .= '</select>';

    $ppp_val = $posts_per_page > 0 ? $posts_per_page : '';

    ?>
    <script>
    (function () {
        // Chercher le label du checkbox portfolio (contient "cpt_portfolio")
        var allLabels = document.querySelectorAll('label');
        var portfolioWrap = null;

        allLabels.forEach(function (label) {
            var input = label.querySelector('input[value="cpt_portfolio"]') ||
                        label.querySelector('input[name="module_cpt_portfolio"]');
            if ( ! input ) {
                // chercher aussi via le texte du label
                if ( label.textContent.indexOf('portfolio') !== -1 ) {
                    input = label.querySelector('input[type="checkbox"]');
                }
            }
            if ( input ) {
                portfolioWrap = label.closest('div');
            }
        });

        // Fallback : chercher directement la checkbox
        if ( ! portfolioWrap ) {
            var cb = document.querySelector('input[name="module_cpt_portfolio"]');
            if ( cb ) portfolioWrap = cb.closest('div');
        }

        if ( ! portfolioWrap ) {
            console.warn('KD Portfolio: bloc portfolio non trouvé pour injection des réglages.');
            return;
        }

        var block = document.createElement('div');
        block.id  = 'kd-portfolio-order-block';
        block.style.cssText = 'margin-top:14px;padding:16px 20px;background:#f8fafc;border-left:4px solid #e64449;border-radius:0 6px 6px 0;';
        block.innerHTML = `
            <p style="margin:0 0 12px 0;font-weight:600;font-size:14px;color:#1a2332;">
                ⚙️ Réglages d'affichage des réalisations
            </p>
            <div style="display:flex;gap:20px;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:220px;">
                    <label for="portfolio_orderby" style="display:block;margin-bottom:5px;font-size:13px;color:#444;font-weight:500;">
                        Trier les réalisations par
                    </label>
                    <?php echo addslashes( $select_html ); ?>
                    <p style="margin:5px 0 0 0;font-size:12px;color:#64748b;">
                        Appliqué aux modules Divi Portfolio, Filterable Portfolio et Post Slider.
                    </p>
                </div>
                <div style="min-width:140px;">
                    <label for="portfolio_posts_per_page" style="display:block;margin-bottom:5px;font-size:13px;color:#444;font-weight:500;">
                        Posts par page
                    </label>
                    <input type="number"
                           id="portfolio_posts_per_page"
                           name="portfolio_posts_per_page"
                           value="<?php echo esc_attr( $ppp_val ); ?>"
                           min="-1" max="200"
                           placeholder="Valeur Divi"
                           style="width:120px;padding:6px 8px;border:1px solid #ccd0d4;border-radius:4px;" />
                    <p style="margin:5px 0 0 0;font-size:12px;color:#64748b;">
                        -1 = tout afficher. Vide = valeur du module Divi.
                    </p>
                </div>
            </div>
        `;

        // Insérer après le bloc portfolio
        portfolioWrap.insertAdjacentElement('afterend', block);

        // Afficher/masquer selon l'état du checkbox portfolio
        var portfolioCb = portfolioWrap.querySelector('input[type="checkbox"]');
        if ( portfolioCb ) {
            function toggleBlock() {
                block.style.display = portfolioCb.checked ? 'block' : 'none';
            }
            toggleBlock();
            portfolioCb.addEventListener('change', toggleBlock);
        }
    })();
    </script>
    <?php
} );