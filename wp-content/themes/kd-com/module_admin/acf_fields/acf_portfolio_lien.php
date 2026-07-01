<?php
/**
 * Champs ACF pour le lien vers le site internet des projets portfolio
 * 
 * Champs :
 *  - ajouter_un_lien (true_false) : affiche ou masque le champ URL + le module Divi
 *  - url_du_lien     (url)        : URL du site internet réalisé
 * 
 * Shortcode : [portfolio_bouton_site]
 * 
 * Utilisation dans Divi :
 *  Ajoutez un Module Code avec [portfolio_bouton_site] dans votre template single project.
 *  Donnez-lui la classe CSS "portfolio-module-site" via Avancé → Classe CSS du module.
 *  Le module sera masqué automatiquement via la classe body .no-lien-portfolio
 *  si le champ "Ajouter un lien" est désactivé.
 */

if ( function_exists( 'acf_add_local_field_group' ) ) :

    acf_add_local_field_group( array(
        'key'   => 'group_portfolio_lien_site',
        'title' => 'Lien vers le site internet',
        'fields' => array(

            // Champ vrai/faux — active l'affichage du lien
            array(
                'key'          => 'field_portfolio_ajouter_lien',
                'label'        => 'Ajouter un lien',
                'name'         => 'ajouter_un_lien',
                'type'         => 'true_false',
                'instructions' => 'Activez cette option pour afficher un bouton vers le site internet réalisé.',
                'default_value' => 0,
                'ui'           => 1,
                'ui_on_text'   => 'Oui',
                'ui_off_text'  => 'Non',
            ),

            // Champ URL — conditionné par le champ ci-dessus
            array(
                'key'               => 'field_portfolio_url_lien',
                'label'             => 'URL du lien',
                'name'              => 'url_du_lien',
                'type'              => 'url',
                'instructions'      => 'Saisissez l\'adresse complète du site (ex : https://www.monsite.fr).',
                'required'          => 0,
                'placeholder'       => 'https://',
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_portfolio_ajouter_lien',
                            'operator' => '==',
                            'value'    => '1',
                        ),
                    ),
                ),
            ),

        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'project', // CPT Divi Portfolio (renommé "Réalisations" dans cpt_portfolio.php)
                ),
            ),
        ),
        'menu_order'         => 10,
        'position'           => 'normal',
        'style'              => 'default',
        'label_placement'    => 'top',
        'instruction_placement' => 'label',
        'active'             => true,
        'description'        => 'Gère l\'affichage du bouton "Voir le site" sur les projets portfolio.',
    ) );

endif;