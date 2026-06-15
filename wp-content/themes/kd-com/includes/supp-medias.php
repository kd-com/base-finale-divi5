<?php

add_action( 'before_delete_post', 'wpturbo_delete_attached_media', 10, 1 );

function wpturbo_delete_attached_media( $post_id ) {

    // Obtenir tous les post types publics (native + CPT)
    $public_post_types = get_post_types( [ 'public' => true ], 'names' );

    $post_type = get_post_type( $post_id );

    // On agit uniquement sur les post types publics
    if ( ! in_array( $post_type, $public_post_types ) ) {
        return;
    }

    // Récupérer les médias attachés au post en cours de suppression
    $attachments = get_attached_media( '', $post_id );

    foreach ( $attachments as $attachment ) {

        $attachment_id = $attachment->ID;

        /*
        ---------------------------------------------------------
        Vérification 1 :
        L'image est-elle utilisée comme "featured image" ailleurs ?
        ---------------------------------------------------------
        */
        $used_as_thumbnail = get_posts([
            'post_type'      => $public_post_types, 
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'post__not_in'   => [ $post_id ],
            'meta_query'     => [
                [
                    'key'   => '_thumbnail_id',
                    'value' => $attachment_id,
                ]
            ]
        ]);

        /*
        ---------------------------------------------------------
        Vérification 2 :
        L'image est-elle mentionnée dans le contenu d'un autre post ?
        ---------------------------------------------------------
        */
        $used_in_content = get_posts([
            'post_type'      => $public_post_types,
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'post__not_in'   => [ $post_id ],
            's'              => (string) $attachment_id,
        ]);

        /*
        ---------------------------------------------------------
        Vérification 3 :
        Vérification via l'URL du média (plus fiable)
        ---------------------------------------------------------
        */
        $url = wp_get_attachment_url( $attachment_id );

        $used_via_url = get_posts([
            'post_type'      => $public_post_types,
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'post__not_in'   => [ $post_id ],
            's'              => $url,
        ]);

        /*
        ---------------------------------------------------------
        Suppression finale si le média n'est utilisé nulle part
        ---------------------------------------------------------
        */
        if ( empty( $used_as_thumbnail ) && empty( $used_in_content ) && empty( $used_via_url ) ) {
            wp_delete_attachment( $attachment_id, true );
        }

    }
}