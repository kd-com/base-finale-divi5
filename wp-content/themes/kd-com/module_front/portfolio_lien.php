<?php
/**
 * MODULE PORTFOLIO — BOUTON LIEN SITE INTERNET
 * =============================================
 *
 * Shortcode : [portfolio_bouton_site]
 *
 * Affiche un bouton "Voir le site" uniquement si :
 *   - le champ ACF "ajouter_un_lien" est activé (true)
 *   - le champ ACF "url_du_lien" est renseigné
 *
 * Intégration Divi :
 *   1. Dans votre template Single Project Divi, ajoutez un Module Code.
 *   2. Saisissez [portfolio_bouton_site] dans le module.
 *   3. Dans Avancé → Classe CSS du module, saisissez : portfolio-module-site
 *   4. Le module disparaît automatiquement (display:none) quand le lien est désactivé,
 *      grâce à la classe CSS ajoutée sur le <body> (.no-lien-portfolio).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================================
// 1. SHORTCODE [portfolio_bouton_site]
// ============================================================

add_shortcode( 'portfolio_bouton_site', 'kd_portfolio_bouton_site_shortcode' );

function kd_portfolio_bouton_site_shortcode( $atts ) {

    $post_id = get_the_ID();

    // Champ vrai/faux
    $ajouter_lien = get_field( 'ajouter_un_lien', $post_id );

    // Si désactivé → chaîne vide (le module Divi reste mais n'affiche rien)
    if ( ! $ajouter_lien ) {
        return '';
    }

    // URL du lien
    $url = get_field( 'url_du_lien', $post_id );

    // Si URL vide → rien non plus
    if ( empty( $url ) ) {
        return '';
    }

    // Texte du bouton personnalisable via attribut shortcode
    $atts = shortcode_atts( array(
        'texte' => 'Voir le site',
    ), $atts, 'portfolio_bouton_site' );

    // Rendu HTML — classe et structure compatibles Divi (et_pb_button)
    return sprintf(
        '<a href="%s" target="_blank" rel="noopener noreferrer" class="et_pb_button portfolio-site-btn u-link-arrow">%s</a>',
        esc_url( $url ),
        esc_html( $atts['texte'] )
    );
}


// ============================================================
// 2. CLASSE CSS SUR LE BODY (masque le module Divi entier)
// ============================================================

add_filter( 'body_class', 'kd_portfolio_body_class_lien' );

function kd_portfolio_body_class_lien( $classes ) {

    // Uniquement sur le single du CPT project
    if ( ! is_singular( 'project' ) ) {
        return $classes;
    }

    $ajouter_lien = get_field( 'ajouter_un_lien' );

    if ( $ajouter_lien ) {
        $classes[] = 'has-lien-portfolio';
    } else {
        $classes[] = 'no-lien-portfolio';
    }

    return $classes;
}


// ============================================================
// 3. CSS INLINE — masquage du wrapper Divi si pas de lien
// ============================================================

add_action( 'wp_head', 'kd_portfolio_lien_css' );

function kd_portfolio_lien_css() {

    // On n'injecte le style que sur les singles project
    if ( ! is_singular( 'project' ) ) {
        return;
    }
    ?>
    <style id="kd-portfolio-lien-css">
        /* Cache le Module Code Divi entier si le lien est désactivé.
           Donnez la classe CSS "portfolio-module-site" au module dans Divi
           (Avancé → Classe CSS du module). */
        .no-lien-portfolio .portfolio-module-site {
            display: none !important;
        }
    </style>
    <?php
}