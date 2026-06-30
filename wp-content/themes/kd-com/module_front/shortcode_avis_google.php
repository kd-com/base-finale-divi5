<?php
/**
 * Module front — Avis Google
 * Fichier : module_front/avis-google.php
 *
 * Récupère les avis via l'API Google Places (clé/Place ID lus depuis les réglages
 * chiffrés en BDD) et les affiche via [kdcom_google_reviews], en mode cartes ou carrousel Swiper.
 *
 * Le module ne s'active que si l'option ACF "module_shortcode_avis_google" est activée.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! get_option( 'module_shortcode_avis_google' ) ) {
	return;
}

/**
 * Récupère les données de l'établissement (note globale + avis) depuis l'API Google Places.
 * Mise en cache via transient pour limiter les appels à l'API.
 *
 * @return array|WP_Error
 */
function kdcom_get_google_reviews_data() {

	$transient_key = 'kdcom_google_reviews_data';
	$cached        = get_transient( $transient_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$api_key  = kdcom_get_avis_google_api_key();
	$place_id = kdcom_get_avis_google_place_id();

	if ( empty( $api_key ) || empty( $place_id ) ) {
		return new WP_Error(
			'kdcom_missing_config',
			'Clé API ou Place ID manquant. Configurez-les dans Réglages personnalisés > Avis Google.'
		);
	}

	$endpoint = add_query_arg(
		array(
			'place_id' => $place_id,
			'fields'   => 'name,rating,user_ratings_total,reviews,url',
			'language' => 'fr',
			'key'      => $api_key,
		),
		'https://maps.googleapis.com/maps/api/place/details/json'
	);

	$response = wp_remote_get( $endpoint, array( 'timeout' => 15 ) );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 !== $code || empty( $body['result'] ) || 'OK' !== $body['status'] ) {
		$error_message = isset( $body['error_message'] ) ? $body['error_message'] : 'Réponse invalide de l\'API Google Places.';
		return new WP_Error( 'kdcom_api_error', $error_message );
	}

	$data = array(
		'name'               => isset( $body['result']['name'] ) ? sanitize_text_field( $body['result']['name'] ) : '',
		'rating'             => isset( $body['result']['rating'] ) ? (float) $body['result']['rating'] : 0,
		'user_ratings_total' => isset( $body['result']['user_ratings_total'] ) ? (int) $body['result']['user_ratings_total'] : 0,
		'url'                => isset( $body['result']['url'] ) ? esc_url_raw( $body['result']['url'] ) : '',
		'reviews'            => isset( $body['result']['reviews'] ) ? $body['result']['reviews'] : array(),
	);

	set_transient( $transient_key, $data, 12 * HOUR_IN_SECONDS );

	return $data;
}

/**
 * Génère le rendu HTML des étoiles pour une note donnée.
 */
function kdcom_render_stars( $rating ) {
	$rating = max( 0, min( 5, (float) $rating ) );
	$full   = (int) floor( $rating );
	$half   = ( $rating - $full ) >= 0.5 ? 1 : 0;
	$empty  = 5 - $full - $half;

	$html  = str_repeat( '<span class="kdcom-star kdcom-star-full">★</span>', $full );
	$html .= $half ? '<span class="kdcom-star kdcom-star-half">★</span>' : '';
	$html .= str_repeat( '<span class="kdcom-star kdcom-star-empty">☆</span>', $empty );

	return $html;
}

/**
 * Rendu HTML d'une carte d'avis individuelle (réutilisé en mode cards et carrousel).
 */
function kdcom_render_review_card( $review ) {
	ob_start();
	?>
	<div class="kdcom-review-card">
		<div class="kdcom-review-header">
			<img
				class="kdcom-review-avatar"
				src="<?php echo esc_url( $review['profile_photo_url'] ?? '' ); ?>"
				alt="<?php echo esc_attr( $review['author_name'] ?? '' ); ?>"
				loading="lazy"
				width="48"
				height="48"
			/>
			<div class="kdcom-review-author-block">
				<span class="kdcom-review-author"><?php echo esc_html( $review['author_name'] ?? '' ); ?></span>
				<span class="kdcom-review-stars"><?php echo kdcom_render_stars( $review['rating'] ?? 0 ); ?></span>
			</div>
		</div>
		<p class="kdcom-review-text">
			<?php echo esc_html( wp_trim_words( $review['text'] ?? '', 40, '…' ) ); ?>
		</p>
		<span class="kdcom-review-date"><?php echo esc_html( $review['relative_time_description'] ?? '' ); ?></span>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Shortcode [kdcom_google_reviews]
 *
 * Attributs :
 *   mode       (cards/carousel) Force le mode d'affichage. Défaut : réglage admin.
 *   limit      (int)            Nombre d'avis. Défaut : réglage admin.
 *   min_rating (int)            Note minimale (0-5). Défaut : réglage admin.
 *   show_link  (yes/no)         Affiche le lien "voir tous les avis". Défaut : yes.
 */
function kdcom_google_reviews_shortcode( $atts ) {

	$defaults = array(
		'mode'       => get_option( 'kdcom_avis_google_display_mode', 'cards' ),
		'limit'      => get_option( 'kdcom_avis_google_limit', 5 ),
		'min_rating' => get_option( 'kdcom_avis_google_min_rating', 0 ),
		'show_link'  => 'yes',
	);

	$atts = shortcode_atts( $defaults, $atts, 'kdcom_google_reviews' );

	$data = kdcom_get_google_reviews_data();

	if ( is_wp_error( $data ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			return '<p class="kdcom-reviews-error">Erreur avis Google : ' . esc_html( $data->get_error_message() ) . '</p>';
		}
		return '';
	}

	$reviews = $data['reviews'];

	$min_rating = (int) $atts['min_rating'];
	if ( $min_rating > 0 ) {
		$reviews = array_filter(
			$reviews,
			function ( $review ) use ( $min_rating ) {
				return isset( $review['rating'] ) && $review['rating'] >= $min_rating;
			}
		);
	}

	$limit   = max( 1, min( 5, (int) $atts['limit'] ) );
	$reviews = array_slice( $reviews, 0, $limit );

	if ( empty( $reviews ) ) {
		return '';
	}

	$mode        = in_array( $atts['mode'], array( 'cards', 'carousel' ), true ) ? $atts['mode'] : 'cards';
	$instance_id = 'kdcom-reviews-' . wp_unique_id();

	ob_start();
	?>
	<div class="kdcom-google-reviews kdcom-mode-<?php echo esc_attr( $mode ); ?>" id="<?php echo esc_attr( $instance_id ); ?>">

		<div class="kdcom-reviews-summary">
			<span class="kdcom-reviews-rating"><?php echo esc_html( number_format_i18n( $data['rating'], 1 ) ); ?></span>
			<span class="kdcom-reviews-stars"><?php echo kdcom_render_stars( $data['rating'] ); ?></span>
			<span class="kdcom-reviews-total">
				<?php
				printf(
					/* translators: %d: nombre d'avis */
					esc_html( _n( 'basé sur %d avis Google', 'basé sur %d avis Google', $data['user_ratings_total'], 'kdcom' ) ),
					(int) $data['user_ratings_total']
				);
				?>
			</span>
		</div>

		<?php if ( 'carousel' === $mode ) : ?>

			<div class="swiper kdcom-reviews-swiper">
				<div class="swiper-wrapper">
					<?php foreach ( $reviews as $review ) : ?>
						<div class="swiper-slide"><?php echo kdcom_render_review_card( $review ); ?></div>
					<?php endforeach; ?>
				</div>
				<div class="swiper-pagination"></div>
				<?php if ( count( $reviews ) > 1 ) : ?>
					<div class="swiper-button-prev"></div>
					<div class="swiper-button-next"></div>
				<?php endif; ?>
			</div>

		<?php else : ?>

			<div class="kdcom-reviews-grid">
				<?php foreach ( $reviews as $review ) : ?>
					<?php echo kdcom_render_review_card( $review ); ?>
				<?php endforeach; ?>
			</div>

		<?php endif; ?>

		<?php if ( 'yes' === $atts['show_link'] && ! empty( $data['url'] ) ) : ?>
			<div class="kdcom-reviews-footer">
				<a href="<?php echo esc_url( $data['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="kdcom-reviews-link">
					Voir tous les avis sur Google →
				</a>
			</div>
		<?php endif; ?>

	</div>

	<?php if ( 'carousel' === $mode ) : ?>
		<script>
		document.addEventListener('DOMContentLoaded', function () {
			if (typeof Swiper === 'undefined') return;
			var el = document.querySelector('#<?php echo esc_js( $instance_id ); ?> .kdcom-reviews-swiper');
			if (!el) return;
			new Swiper(el, {
				slidesPerView: 1,
				spaceBetween: 24,
				loop: false,
				pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
				navigation: {
					nextEl: el.querySelector('.swiper-button-next'),
					prevEl: el.querySelector('.swiper-button-prev'),
				},
				breakpoints: {
					640:  { slidesPerView: 2 },
					1024: { slidesPerView: 3 },
				},
			});
		});
		</script>
	<?php endif; ?>
	<?php
	return ob_get_clean();
}
add_shortcode( 'kdcom_google_reviews', 'kdcom_google_reviews_shortcode' );

/**
 * Charge les assets (CSS toujours, Swiper uniquement si le shortcode est présent ET en mode carrousel).
 * Suppose que Swiper (CSS/JS) est déjà enregistré globalement dans le thème (cas du slider homepage).
 * Si ce n'est pas le cas, adapter les handles ci-dessous.
 */
function kdcom_google_reviews_assets() {
	if ( ! is_singular() ) {
		return;
	}

	global $post;
	if ( ! $post || ! has_shortcode( $post->post_content, 'kdcom_google_reviews' ) ) {
		return;
	}

	wp_enqueue_style(
		'kdcom-google-reviews',
		get_stylesheet_directory_uri() . '/module_front/css/avis-google.css',
		array(),
		'1.0.0'
	);

	$display_mode = get_option( 'kdcom_avis_google_display_mode', 'cards' );

	if ( 'carousel' === $display_mode || false !== strpos( $post->post_content, 'mode="carousel"' ) ) {
		// Réutilise Swiper s'il est déjà enregistré ailleurs dans le thème (ex: slider homepage).
		if ( wp_style_is( 'swiper', 'registered' ) || wp_style_is( 'swiper', 'enqueued' ) ) {
			wp_enqueue_style( 'swiper' );
		} else {
			wp_enqueue_style( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.0.0' );
		}

		if ( wp_script_is( 'swiper', 'registered' ) ) {
			wp_enqueue_script( 'swiper' );
		} else {
			wp_enqueue_script( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'kdcom_google_reviews_assets' );