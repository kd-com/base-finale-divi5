<?php
/**
 * Page de réglages — Module Avis Google
 * Fichier : module_admin/pages/reglages_avis_google.php
 *
 * Stocke la clé API Google Places et le Place ID chiffrés en BDD (AES-256-CBC),
 * ainsi que le mode d'affichage choisi (cards / carrousel) et les options associées.
 *
 * Convention suivie : même structure que reglages_generaux.php / reglages_couleurs.php
 * (register_setting + add_settings_field + fonction d'affichage dédiée).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistrement des réglages du module Avis Google.
 */
add_action( 'admin_init', function () {

	register_setting(
		'reglages_avis_google_group',
		'kdcom_avis_google_api_key',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'kdcom_sanitize_and_encrypt_api_key',
		)
	);

	register_setting(
		'reglages_avis_google_group',
		'kdcom_avis_google_place_id',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'kdcom_sanitize_and_encrypt_place_id',
		)
	);

	register_setting(
		'reglages_avis_google_group',
		'kdcom_avis_google_display_mode',
		array(
			'type'              => 'string',
			'sanitize_callback' => function ( $value ) {
				return in_array( $value, array( 'cards', 'carousel' ), true ) ? $value : 'cards';
			},
		)
	);

	register_setting(
		'reglages_avis_google_group',
		'kdcom_avis_google_limit',
		array(
			'type'              => 'integer',
			'sanitize_callback' => function ( $value ) {
				$value = (int) $value;
				return $value > 0 ? min( $value, 5 ) : 5;
			},
		)
	);

	register_setting(
		'reglages_avis_google_group',
		'kdcom_avis_google_min_rating',
		array(
			'type'              => 'integer',
			'sanitize_callback' => function ( $value ) {
				$value = (int) $value;
				return ( $value >= 0 && $value <= 5 ) ? $value : 0;
			},
		)
	);

	add_settings_section( 'section_avis_google', '', null, 'reglages-avis-google' );
} );

/**
 * Chiffre la clé API avant stockage. Si le champ est laissé vide lors d'une
 * modification ultérieure, on conserve la valeur déjà enregistrée (évite
 * d'effacer la clé par erreur si l'admin ne retape pas le champ).
 */
function kdcom_sanitize_and_encrypt_api_key( $value ) {
	$value = trim( sanitize_text_field( $value ) );

	if ( '' === $value ) {
		return get_option( 'kdcom_avis_google_api_key', '' );
	}

	// Évite de re-chiffrer une valeur déjà chiffrée (cas où le champ masqué renverrait la même valeur).
	if ( '__unchanged__' === $value ) {
		return get_option( 'kdcom_avis_google_api_key', '' );
	}

	return kdcom_encrypt( $value );
}

/**
 * Chiffre le Place ID avant stockage, même logique que la clé API.
 */
function kdcom_sanitize_and_encrypt_place_id( $value ) {
	$value = trim( sanitize_text_field( $value ) );

	if ( '' === $value || '__unchanged__' === $value ) {
		return get_option( 'kdcom_avis_google_place_id', '' );
	}

	return kdcom_encrypt( $value );
}

/**
 * Récupère la clé API déchiffrée — à utiliser côté front/API, jamais en admin.
 */
function kdcom_get_avis_google_api_key() {
	return kdcom_decrypt( get_option( 'kdcom_avis_google_api_key', '' ) );
}

/**
 * Récupère le Place ID déchiffré — à utiliser côté front/API.
 */
function kdcom_get_avis_google_place_id() {
	return kdcom_decrypt( get_option( 'kdcom_avis_google_place_id', '' ) );
}

/**
 * Affichage de la page de réglages.
 */
function afficher_reglages_avis_google() {

	$api_key_set  = '' !== get_option( 'kdcom_avis_google_api_key', '' );
	$place_id_set = '' !== get_option( 'kdcom_avis_google_place_id', '' );
	$display_mode = get_option( 'kdcom_avis_google_display_mode', 'cards' );
	$limit        = get_option( 'kdcom_avis_google_limit', 5 );
	$min_rating   = get_option( 'kdcom_avis_google_min_rating', 0 );

	?>
	<div class="wrap">
		<h1 style="margin-bottom:24px;">Réglages — Avis Google</h1>

		<?php if ( ! get_option( 'module_shortcode_avis_google' ) ) : ?>
			<div class="notice notice-warning">
				<p>
					Le module n'est pas activé. Rendez-vous dans
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=reglages-site-modules' ) ); ?>">Gestion des modules</a>
					pour l'activer avant de configurer les réglages ci-dessous.
				</p>
			</div>
		<?php endif; ?>

		<form method="post" action="options.php" style="max-width:600px;">
			<?php
			settings_fields( 'reglages_avis_google_group' );
			do_settings_sections( 'reglages-avis-google' );
			?>

			<table class="form-table">

				<tr>
					<th scope="row"><label for="kdcom_avis_google_api_key">Clé API Google Places</label></th>
					<td>
						<input
							type="password"
							id="kdcom_avis_google_api_key"
							name="kdcom_avis_google_api_key"
							value=""
							placeholder="<?php echo $api_key_set ? esc_attr__( '•••••••••••••••• (laisser vide pour conserver)' ) : ''; ?>"
							class="regular-text"
							autocomplete="new-password"
						/>
						<p class="description">
							<?php echo $api_key_set ? '✅ Une clé est déjà enregistrée (chiffrée en BDD). Laissez vide pour ne pas la modifier.' : 'Aucune clé enregistrée pour le moment.'; ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="kdcom_avis_google_place_id">Place ID Google</label></th>
					<td>
						<input
							type="text"
							id="kdcom_avis_google_place_id"
							name="kdcom_avis_google_place_id"
							value=""
							placeholder="<?php echo $place_id_set ? esc_attr__( '•••••••••••••••• (laisser vide pour conserver)' ) : 'ChIJ...'; ?>"
							class="regular-text"
							autocomplete="off"
						/>
						<p class="description">
							<?php echo $place_id_set ? '✅ Un Place ID est déjà enregistré (chiffré en BDD). Laissez vide pour ne pas le modifier.' : "À récupérer via l'outil officiel Google Place ID Finder."; ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row">Mode d'affichage</th>
					<td>
						<label style="margin-right:20px;">
							<input type="radio" name="kdcom_avis_google_display_mode" value="cards" <?php checked( $display_mode, 'cards' ); ?> />
							Cartes (grid)
						</label>
						<label>
							<input type="radio" name="kdcom_avis_google_display_mode" value="carousel" <?php checked( $display_mode, 'carousel' ); ?> />
							Carrousel (Swiper)
						</label>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="kdcom_avis_google_limit">Nombre d'avis affichés</label></th>
					<td>
						<input
							type="number"
							id="kdcom_avis_google_limit"
							name="kdcom_avis_google_limit"
							value="<?php echo esc_attr( $limit ); ?>"
							min="1"
							max="5"
							class="small-text"
						/>
						<p class="description">Maximum 5 (limite de l'API Google Places).</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="kdcom_avis_google_min_rating">Note minimale</label></th>
					<td>
						<select id="kdcom_avis_google_min_rating" name="kdcom_avis_google_min_rating">
							<?php for ( $i = 0; $i <= 5; $i++ ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $min_rating, $i ); ?>>
									<?php echo 0 === $i ? 'Tous les avis' : $i . ' étoiles et plus'; ?>
								</option>
							<?php endfor; ?>
						</select>
					</td>
				</tr>

			</table>

			<?php submit_button(); ?>
		</form>

		<hr style="margin:30px 0;" />

		<p>
			<strong>Utilisation :</strong> placez le shortcode <code>[kdcom_google_reviews]</code>
			sur n'importe quelle page, ou dans un module Texte/Code du Builder Divi.
			Le mode d'affichage défini ci-dessus s'applique automatiquement ;
			vous pouvez le forcer ponctuellement avec
			<code>[kdcom_google_reviews mode="carousel"]</code>.
		</p>
	</div>
	<?php
}