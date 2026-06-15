<?php
if( function_exists('acf_add_local_field_group') ) {
    // Vérifie si AOS est activé dans les réglages généraux
    $aos_enabled = get_option('kd_com_aos_enabled', '1');
    if ($aos_enabled === '1') {
        // Ajout du groupe Animation à tous les blocks ACF et Gutenberg
        $block_locations = array();
        if (function_exists('acf_register_block_type')) {
            // Récupère tous les blocks ACF déclarés
            $acf_blocks = [
                'cocon', 'partenaires', 'download', 'sous-pages', 'contacts', 'chiffre', 'actualite-liees', 'youtube-grid'
            ];
            foreach ($acf_blocks as $block_name) {
                $block_locations[] = array(
                    array(
                        'param' => 'block',
                        'operator' => '==',
                        'value' => "acf/$block_name"
                    )
                );
            }
        }
        // Ajout pour tous les blocks core Gutenberg
        $core_blocks = [
            'core/paragraph', 'core/image', 'core/heading', 'core/list', 'core/quote', 'core/gallery', 'core/audio', 'core/cover', 'core/file', 'core/video', 'core/table', 'core/column', 'core/group', 'core/button', 'core/buttons', 'core/separator', 'core/spacer', 'core/html', 'core/code', 'core/preformatted', 'core/pullquote', 'core/media-text', 'core/more', 'core/nextpage', 'core/page-break', 'core/embed', 'core/categories', 'core/latest-posts', 'core/shortcode', 'core/tag-cloud', 'core/rss', 'core/search', 'core/social-links', 'core/social-link', 'core/block', 'core/widget', 'core/navigation', 'core/site-title', 'core/site-logo', 'core/site-tagline', 'core/query', 'core/post-title', 'core/post-content', 'core/post-featured-image', 'core/post-date', 'core/post-excerpt', 'core/post-terms', 'core/post-navigation-link', 'core/comments', 'core/comment-author', 'core/comment-content', 'core/comment-date', 'core/comment-edit-link', 'core/comment-reply-link', 'core/comment-avatars', 'core/loginout', 'core/archives', 'core/calendar', 'core/latest-comments', 'core/tag-cloud', 'core/custom-html', 'core/legacy-widget', 'core/block', 'core/navigation-menu', 'core/site-logo', 'core/site-title', 'core-site-tagline', 'core/query-loop', 'core/post-template', 'core/post-title', 'core/post-content', 'core/post-featured-image', 'core/post-date', 'core/post-excerpt', 'core/post-terms', 'core/post-navigation-link', 'core/comments', 'core/comment-author', 'core/comment-content', 'core/comment-date', 'core/comment-edit-link', 'core/comment-reply-link', 'core/comment-avatars', 'core/loginout', 'core/archives', 'core/calendar', 'core/latest-comments', 'core/tag-cloud', 'core/custom-html', 'core/legacy-widget', 'core/block', 'core/navigation-menu', 'core/site-logo', 'core/site-title', 'core/site-tagline', 'core/query-loop', 'core/post-template'
        ];
        foreach ($core_blocks as $core_block) {
            $block_locations[] = array(
                array(
                    'param' => 'block',
                    'operator' => '==',
                    'value' => $core_block
                )
            );
        }
        acf_add_local_field_group(array(
            'key' => 'group_animation_settings',
            'title' => 'Animation',
            'fields' => array(
                array(
                    'key' => 'field_animation_type',
                    'label' => "Type d'animation",
                    'name' => 'animation_type',
                    'type' => 'select',
                    'instructions' => "Choisissez l'effet d'apparition",
                    'choices' => array(
                        'fade-up' => 'Fondu montant',
                        'fade-down' => 'Fondu descendant',
                        'fade-left' => 'Fondu depuis la gauche',
                        'fade-right' => 'Fondu depuis la droite',
                        'zoom-in' => 'Zoom avant',
                        'zoom-out' => 'Zoom arrière',
                        'none' => 'Aucune animation',
                    ),
                    'default_value' => 'fade-up',
                ),
                array(
                    'key' => 'field_animation_delay',
                    'label' => 'Délai entre les animations',
                    'name' => 'animation_delay',
                    'type' => 'select',
                    'instructions' => 'Temps entre chaque animation',
                    'choices' => array(
                        '0' => 'Pas de délai',
                        '100' => 'Court (0.1s)',
                        '200' => 'Moyen (0.2s)',
                        '300' => 'Long (0.3s)',
                    ),
                    'default_value' => '100',
                ),
            ),
            'location' => $block_locations,
        ));
    }
}
