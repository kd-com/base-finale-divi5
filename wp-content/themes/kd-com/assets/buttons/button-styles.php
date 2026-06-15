<?php
/**
 * Enregistrement des styles de boutons personnalisés pour Gutenberg
 * Utilisable aussi avec Divi via classes CSS
 */

// Enregistrer les styles de boutons pour Gutenberg
function kd_register_button_styles() {
    // Style 1: Shine (brillance)
    register_block_style(
        'core/button',
        array(
            'name'  => 'btn-shine',
            'label' => __('Brillance (Shine)', 'kd-com'),
        )
    );

    // Style 2: Slide Right (remplissage droite)
    register_block_style(
        'core/button',
        array(
            'name'  => 'btn-slide-right',
            'label' => __('Remplissage Droite', 'kd-com'),
        )
    );

    // Style 3: Circle Expand (déploiement cercle)
    register_block_style(
        'core/button',
        array(
            'name'  => 'btn-circle-expand',
            'label' => __('Cercle Expansif', 'kd-com'),
        )
    );

    // Style 4: Slide Up (remplissage haut)
    register_block_style(
        'core/button',
        array(
            'name'  => 'btn-slide-up',
            'label' => __('Remplissage Haut', 'kd-com'),
        )
    );

    // Style 5: Arrow Tab (onglet flèche)
    register_block_style(
        'core/button',
        array(
            'name'  => 'btn-arrow-tab',
            'label' => __('Onglet Flèche', 'kd-com'),
        )
    );

    // Style 6: Shadow Lift (élévation ombre)
    register_block_style(
        'core/button',
        array(
            'name'  => 'btn-shadow-lift',
            'label' => __('Élévation Ombre', 'kd-com'),
        )
    );

    // Style 7: Border Grow (bordure croissante)
    register_block_style(
        'core/button',
        array(
            'name'  => 'btn-border-grow',
            'label' => __('Bordure Croissante', 'kd-com'),
        )
    );

    // Style 8: Fill Center (remplissage centre)
    register_block_style(
        'core/button',
        array(
            'name'  => 'btn-fill-center',
            'label' => __('Remplissage Centre', 'kd-com'),
        )
    );

    // Style 9: u-link-arrow (flèche animée)
    register_block_style(
        'core/button',
        array(
            'name'  => 'u-link-arrow',
            'label' => __('Lien avec flèche', 'kd-com'),
        )
    );
}
add_action('init', 'kd_register_button_styles');

// Ajout automatique de la classe u-link-arrow sur <a> pour le style Gutenberg
add_filter('render_block', function($block_content, $block) {
    if (
        isset($block['blockName']) && $block['blockName'] === 'core/button' &&
        isset($block['attrs']['className']) && strpos($block['attrs']['className'], 'is-style-u-link-arrow') !== false
    ) {
        // Ajoute la classe u-link-arrow à la balise <a>
        $block_content = preg_replace('/(<a[^>]*class=")([^"]*)/', '$1$2 u-link-arrow', $block_content);
    }
    return $block_content;
}, 10, 2);

// Ajout automatique du SVG flèche pour le style u-link-arrow
add_filter('render_block', function($block_content, $block) {
    if (
        isset($block['blockName']) && $block['blockName'] === 'core/button' &&
        isset($block['attrs']['className']) && strpos($block['attrs']['className'], 'is-style-u-link-arrow') !== false
    ) {
        // Ajoute la classe u-link-arrow à la balise <a>
        $block_content = preg_replace('/(<a[^>]*class=")([^"]*)/', '$1$2 u-link-arrow', $block_content);
        // Ajoute le SVG flèche à la fin du contenu du bouton uniquement s'il n'existe pas déjà
        if (strpos($block_content, 'arrow-icon') === false) {
            $svg = '<span class="arrow-icon"><svg viewBox="0 0 22 22" width="22" height="22"><g stroke="currentColor" fill="none"><circle class="arrow-icon--circle" cx="11" cy="11" r="10"/><path d="M7 11h8m0 0l-3-3m3 3l-3 3"/></g></svg></span>';
            $block_content = preg_replace('/(<a[^>]*>)(.*?)(<\/a>)/', '$1$2 ' . $svg . '$3', $block_content);
        }
    }
    return $block_content;
}, 10, 2);
