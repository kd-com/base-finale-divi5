<?php
/**
 * Champs ACF pour le bloc Portfolio
 * Affichage de réalisations par catégorie
 * Adapté aux CPTs du thème kd-com :
 *   - project       (Réalisations Divi) → taxonomy project_category / project_tag
 *   - evenements    → taxonomy type_evenements
 *   - post          → taxonomy category
 *   - page          → pas de taxonomy
 */

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
    'key' => 'group_portfolio_block',
    'title' => 'Configuration Portfolio',
    'fields' => array(

        // === TITRE DE SECTION ===
        array(
            'key'           => 'field_portfolio_titre_section',
            'label'         => 'Titre de la section (optionnel)',
            'name'          => 'portfolio_titre_section',
            'type'          => 'text',
            'instructions'  => 'Titre affiché au-dessus de la grille',
            'required'      => 0,
            'default_value' => '',
        ),
        array(
            'key'           => 'field_portfolio_sous_titre',
            'label'         => 'Sous-titre (optionnel)',
            'name'          => 'portfolio_sous_titre',
            'type'          => 'textarea',
            'instructions'  => 'Texte affiché sous le titre',
            'required'      => 0,
            'rows'          => 2,
        ),

        // === TYPE DE CONTENU ===
        array(
            'key'           => 'field_portfolio_post_type',
            'label'         => 'Type de contenu',
            'name'          => 'portfolio_post_type',
            'type'          => 'select',
            'instructions'  => 'Choisissez le type de contenu à afficher',
            'choices'       => array(
                'project'     => 'Réalisations (CPT Divi)',
                'evenements'  => 'Événements',
                'post'        => 'Articles',
                'page'        => 'Pages',
            ),
            'default_value' => 'project',
            'return_format' => 'value',
            'allow_null'    => 0,
        ),

        // === FILTRER PAR CATEGORIE : Réalisations (project_category) ===
        array(
            'key'               => 'field_portfolio_cat_project',
            'label'             => 'Catégorie de réalisation',
            'name'              => 'portfolio_cat_project',
            'type'              => 'taxonomy',
            'instructions'      => 'Filtrer par type de réalisation (laisser vide = toutes)',
            'taxonomy'          => 'project_category',
            'field_type'        => 'select',
            'allow_null'        => 1,
            'add_term'          => 0,
            'save_terms'        => 0,
            'load_terms'        => 0,
            'return_format'     => 'id',
            'multiple'          => 0,
            'required'          => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field'    => 'field_portfolio_post_type',
                        'operator' => '==',
                        'value'    => 'project',
                    ),
                ),
            ),
        ),

        // === FILTRER PAR ETIQUETTE : Réalisations (project_tag) ===
        array(
            'key'               => 'field_portfolio_tag_project',
            'label'             => 'Étiquette de réalisation (optionnel)',
            'name'              => 'portfolio_tag_project',
            'type'              => 'taxonomy',
            'instructions'      => 'Affiner par étiquette (en plus de la catégorie)',
            'taxonomy'          => 'project_tag',
            'field_type'        => 'select',
            'allow_null'        => 1,
            'add_term'          => 0,
            'save_terms'        => 0,
            'load_terms'        => 0,
            'return_format'     => 'id',
            'multiple'          => 0,
            'required'          => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field'    => 'field_portfolio_post_type',
                        'operator' => '==',
                        'value'    => 'project',
                    ),
                ),
            ),
        ),

        // === FILTRER PAR CATEGORIE : Événements (type_evenements) ===
        array(
            'key'               => 'field_portfolio_cat_evenements',
            'label'             => 'Catégorie d\'événement',
            'name'              => 'portfolio_cat_evenements',
            'type'              => 'taxonomy',
            'instructions'      => 'Filtrer par type d\'événement (laisser vide = tous)',
            'taxonomy'          => 'type_evenements',
            'field_type'        => 'select',
            'allow_null'        => 1,
            'add_term'          => 0,
            'save_terms'        => 0,
            'load_terms'        => 0,
            'return_format'     => 'id',
            'multiple'          => 0,
            'required'          => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field'    => 'field_portfolio_post_type',
                        'operator' => '==',
                        'value'    => 'evenements',
                    ),
                ),
            ),
        ),

        // === FILTRER PAR CATEGORIE : Articles (category) ===
        array(
            'key'               => 'field_portfolio_cat_post',
            'label'             => 'Catégorie d\'article',
            'name'              => 'portfolio_cat_post',
            'type'              => 'taxonomy',
            'instructions'      => 'Filtrer par catégorie (laisser vide = toutes)',
            'taxonomy'          => 'category',
            'field_type'        => 'select',
            'allow_null'        => 1,
            'add_term'          => 0,
            'save_terms'        => 0,
            'load_terms'        => 0,
            'return_format'     => 'id',
            'multiple'          => 0,
            'required'          => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field'    => 'field_portfolio_post_type',
                        'operator' => '==',
                        'value'    => 'post',
                    ),
                ),
            ),
        ),

        // === NOMBRE & TRI ===
        array(
            'key'           => 'field_portfolio_nombre',
            'label'         => 'Nombre d\'éléments à afficher',
            'name'          => 'portfolio_nombre',
            'type'          => 'number',
            'instructions'  => 'Par défaut : 3',
            'default_value' => 3,
            'min'           => 1,
            'max'           => 12,
        ),
        array(
            'key'           => 'field_portfolio_ordre_tri',
            'label'         => 'Ordre de tri',
            'name'          => 'portfolio_ordre_tri',
            'type'          => 'select',
            'choices'       => array(
                'date'       => 'Date de publication',
                'title'      => 'Titre',
                'menu_order' => 'Ordre personnalisé',
                'modified'   => 'Date de modification',
            ),
            'default_value' => 'date',
            'return_format' => 'value',
        ),
        array(
            'key'           => 'field_portfolio_sens_tri',
            'label'         => 'Sens du tri',
            'name'          => 'portfolio_sens_tri',
            'type'          => 'select',
            'choices'       => array(
                'DESC' => 'Décroissant (plus récent en premier)',
                'ASC'  => 'Croissant (plus ancien en premier)',
            ),
            'default_value' => 'DESC',
            'return_format' => 'value',
        ),

        // === AFFICHAGE ===
        array(
            'key'           => 'field_portfolio_afficher_date',
            'label'         => 'Afficher la date',
            'name'          => 'portfolio_afficher_date',
            'type'          => 'true_false',
            'default_value' => 1,
            'ui'            => 1,
        ),
        array(
            'key'           => 'field_portfolio_afficher_categorie',
            'label'         => 'Afficher la catégorie',
            'name'          => 'portfolio_afficher_categorie',
            'type'          => 'true_false',
            'default_value' => 1,
            'ui'            => 1,
        ),
        array(
            'key'           => 'field_portfolio_afficher_extrait',
            'label'         => 'Afficher l\'extrait',
            'name'          => 'portfolio_afficher_extrait',
            'type'          => 'true_false',
            'default_value' => 1,
            'ui'            => 1,
        ),
        array(
            'key'           => 'field_portfolio_texte_lien',
            'label'         => 'Texte du lien',
            'name'          => 'portfolio_texte_lien',
            'type'          => 'text',
            'default_value' => 'Voir la réalisation',
        ),
        array(
            'key'           => 'field_portfolio_lien_voir_tout',
            'label'         => 'Lien "Voir tout" (optionnel)',
            'name'          => 'portfolio_lien_voir_tout',
            'type'          => 'url',
            'instructions'  => 'URL vers la page listant tous les éléments',
            'required'      => 0,
        ),
        array(
            'key'           => 'field_portfolio_texte_voir_tout',
            'label'         => 'Texte du lien "Voir tout"',
            'name'          => 'portfolio_texte_voir_tout',
            'type'          => 'text',
            'default_value' => 'Voir toutes nos réalisations',
        ),
    ),
    'location' => array(
        array(
            array(
                'param'    => 'block',
                'operator' => '==',
                'value'    => 'acf/portfolio',
            ),
        ),
    ),
    'menu_order'          => 0,
    'position'            => 'normal',
    'style'               => 'default',
    'label_placement'     => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen'      => array('style'),
    'active'              => true,
    'description'         => 'Bloc de réalisations portfolio style Olena Blog #6',
    'show_in_rest'        => 0,
));

endif;