<?php
/**
 * Champs ACF - CPT "slider" (slider page d'accueil)
 *
 * Ce fichier reprend EXACTEMENT les clés et types du Field Group existant
 * (export group_637ca1dfc63ab.json) afin qu'ACF reconnaisse le même group
 * et ne crée pas de doublon en base.
 *
 * Champs repris du JSON (clés identiques) :
 *  - image_slider        (image, retour URL)              field_637ca1e0605d2
 *  - texte_slider         (wysiwyg)                         field_637ca20b605d4
 *  - lien_slider          (page_link)                       field_637ca228605d5
 *  - texte_bouton_slider  (texte)                            field_637ca239605d6
 *
 * Champs ajoutés (nouveaux, absents du JSON) :
 *  - video_plateforme     (select : YouTube / Vimeo)
 *  - video_id             (texte : uniquement l'ID de la vidéo)
 *  - afficher_les_textes  (true / false)
 *
 * Exclusivité mutuelle image / vidéo :
 *  - image_slider est masqué dès que video_id est rempli
 *  - video_plateforme / video_id sont masqués dès que image_slider est rempli
 *
 * Le CPT "slider" est supposé déjà enregistré ailleurs dans le thème.
 *
 * @package kd-com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Sécurité : pas d'accès direct.
}

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
    return; // ACF Pro non actif, on ne fait rien.
}

add_action( 'acf/init', 'kdcom_register_acf_fields_slider' );

function kdcom_register_acf_fields_slider() {

    acf_add_local_field_group( array(
        'key'                   => 'group_637ca1dfc63ab',
        'title'                 => 'Slider accueil',
        'fields'                => array(

            // --- Champ ajouté : plateforme vidéo --------------------------
            array(
                'key'           => 'field_slider_video_plateforme',
                'label'         => 'Plateforme vidéo',
                'name'          => 'video_plateforme',
                'type'          => 'select',
                'instructions'  => 'Choisir la plateforme d\'hébergement de la vidéo.',
                'required'      => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_637ca1e0605d2',
                            'operator' => '==empty',
                        ),
                    ),
                ),
                'wrapper'       => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
                'choices'       => array(
                    'youtube' => 'YouTube',
                    'vimeo'   => 'Vimeo',
                ),
                'default_value' => 'youtube',
                'allow_null'    => 0,
                'multiple'      => 0,
                'ui'            => 0,
                'ajax'          => 0,
                'return_format' => 'value',
            ),

            // --- Champ ajouté : ID vidéo -----------------------------------
            array(
                'key'           => 'field_slider_video_id',
                'label'         => 'ID de la vidéo',
                'name'          => 'video_id',
                'type'          => 'text',
                'instructions'  => 'Coller uniquement l\'identifiant de la vidéo (ex: YouTube "dQw4w9WgXcQ", Vimeo "76979871"), pas l\'URL complète. Si ce champ est rempli, la vidéo remplace l\'image de fond.',
                'required'      => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_637ca1e0605d2',
                            'operator' => '==empty',
                        ),
                    ),
                ),
                'wrapper'       => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value' => '',
                'placeholder'   => 'ex : dQw4w9WgXcQ',
                'maxlength'     => '',
                'prepend'       => '',
                'append'        => '',
            ),

            // --- Champ repris du JSON : image_slider ----------------------
            array(
                'key'           => 'field_637ca1e0605d2',
                'label'         => 'image slider',
                'name'          => 'image_slider',
                'aria-label'    => '',
                'type'          => 'image',
                'instructions'  => 'Image PNG détourée, affichée à droite du texte. Laisser vide si une vidéo est renseignée.',
                'required'      => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_slider_video_id',
                            'operator' => '==empty',
                        ),
                    ),
                ),
                'wrapper'       => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'return_format' => 'url',
                'library'       => 'all',
                'min_width'     => '',
                'min_height'    => '',
                'min_size'      => '',
                'max_width'     => '',
                'max_height'    => '',
                'max_size'      => '',
                'mime_types'    => '',
                'preview_size'  => 'medium',
            ),

            // --- Champ ajouté : afficher_les_textes (absent du JSON) -----
            array(
                'key'           => 'field_slider_afficher_textes',
                'label'         => 'Afficher les textes',
                'name'          => 'afficher_les_textes',
                'type'          => 'true_false',
                'instructions'  => 'Affiche le titre, le texte et le bouton sur le slide.',
                'required'      => 0,
                'conditional_logic' => 0,
                'wrapper'       => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value' => 1,
                'ui'            => 1,
            ),

            // --- Champ repris du JSON : texte_slider (wysiwyg) ------------
            array(
                'key'           => 'field_637ca20b605d4',
                'label'         => 'texte slider',
                'name'          => 'texte_slider',
                'aria-label'    => '',
                'type'          => 'wysiwyg',
                'instructions'  => '',
                'required'      => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_slider_afficher_textes',
                            'operator' => '==',
                            'value'    => '1',
                        ),
                    ),
                ),
                'wrapper'       => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value' => '',
                'tabs'          => 'all',
                'toolbar'       => 'full',
                'media_upload'  => 1,
                'delay'         => 0,
            ),

            // --- Champ repris du JSON : lien_slider (page_link) -----------
            array(
                'key'           => 'field_637ca228605d5',
                'label'         => 'lien slider',
                'name'          => 'lien_slider',
                'aria-label'    => '',
                'type'          => 'page_link',
                'instructions'  => '',
                'required'      => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_slider_afficher_textes',
                            'operator' => '==',
                            'value'    => '1',
                        ),
                    ),
                ),
                'wrapper'       => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'post_type'     => '',
                'post_status'   => '',
                'taxonomy'      => '',
                'allow_archives' => 1,
                'multiple'      => 0,
                'allow_null'    => 0,
            ),

            // --- Champ repris du JSON : texte_bouton_slider ---------------
            array(
                'key'           => 'field_637ca239605d6',
                'label'         => 'texte bouton slider',
                'name'          => 'texte_bouton_slider',
                'aria-label'    => '',
                'type'          => 'text',
                'instructions'  => '',
                'required'      => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_slider_afficher_textes',
                            'operator' => '==',
                            'value'    => '1',
                        ),
                        array(
                            'field'    => 'field_637ca228605d5',
                            'operator' => '!=empty',
                        ),
                    ),
                ),
                'wrapper'       => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'default_value' => '',
                'maxlength'     => '',
                'placeholder'   => '',
                'prepend'       => '',
                'append'        => '',
            ),

        ),
        'location'              => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'slider',
                ),
            ),
        ),
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen'        => '',
        'active'                => true,
        'description'           => 'Champs ACF utilisés par le shortcode [slider_accueil].',
        'show_in_rest'          => 0,
    ) );

}