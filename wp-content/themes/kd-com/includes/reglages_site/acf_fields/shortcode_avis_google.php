<?php
/**
 * Déclaration ACF du module "Avis Google"
 * Fichier : module_admin/acf_fields/shortcode_avis_google.php
 *
 * append séquentiel dans $acf_module_fields + enregistrement via acf_add_local_field_group().
 */

if ( function_exists( 'acf_add_local_field_group' ) ) {
	global $acf_module_fields;
	$acf_module_fields[] = array(
		'key'    => 'group_avis_google',
		'title'  => 'Module avis Google',
		'fields' => array(
			array(
				'key'           => 'field_avis_google',
				'label'         => 'Avis Google',
				'name'          => 'shortcode_avis_google',
				'type'          => 'true_false',
				'instructions'  => "Active le module d'avis Google (récupération via l'API Google Places). "
					. "Une fois activé, configurez la clé API et le Place ID dans le sous-menu "
					. "« Avis Google » des réglages personnalisés, puis utilisez le shortcode "
					. "[kdcom_google_reviews] sur n'importe quelle page.",
				'message'       => "module d'affichage des avis Google (cartes ou carrousel)",
				'default_value' => 0,
				'ui'            => 1,
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'reglages-site-modules',
				),
			),
		),
	);
	acf_add_local_field_group( $acf_module_fields[ count( $acf_module_fields ) - 1 ] );
}