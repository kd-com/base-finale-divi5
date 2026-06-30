<?php
/**
 * Utilitaires de chiffrement — KD-COM
 * Chiffre/déchiffre des valeurs sensibles stockées en BDD (clé API, Place ID...)
 * en dérivant une clé AES-256 à partir des salts WordPress (AUTH_KEY / SECURE_AUTH_KEY),
 * déjà présents dans wp-config.php et jamais stockés en base.
 *
 * Fichier : module_admin/inc/kdcom-crypto.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dérive une clé de chiffrement 256 bits stable à partir des salts WordPress.
 *
 * @return string Clé binaire 32 octets.
 */
function kdcom_get_encryption_key() {
	$base = ( defined( 'AUTH_KEY' ) ? AUTH_KEY : '' ) . ( defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : '' );

	if ( empty( $base ) ) {
		// Filet de sécurité si wp-config.php n'a pas (encore) de salts uniques générés.
		$base = 'kdcom-fallback-' . DB_NAME . DB_HOST;
	}

	return hash_hmac( 'sha256', $base, 'kdcom-encryption-salt', true );
}

/**
 * Chiffre une chaîne en AES-256-CBC. Retourne une chaîne base64 contenant l'IV + le texte chiffré.
 *
 * @param string $value Valeur en clair.
 * @return string Valeur chiffrée encodée en base64, ou chaîne vide si entrée vide.
 */
function kdcom_encrypt( $value ) {
	if ( empty( $value ) ) {
		return '';
	}

	$key    = kdcom_get_encryption_key();
	$iv_len = openssl_cipher_iv_length( 'aes-256-cbc' );
	$iv     = openssl_random_pseudo_bytes( $iv_len );

	$encrypted = openssl_encrypt( $value, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );

	if ( false === $encrypted ) {
		return '';
	}

	return base64_encode( $iv . $encrypted );
}

/**
 * Déchiffre une chaîne précédemment chiffrée par kdcom_encrypt().
 *
 * @param string $encoded Valeur chiffrée encodée en base64.
 * @return string Valeur en clair, ou chaîne vide en cas d'échec.
 */
function kdcom_decrypt( $encoded ) {
	if ( empty( $encoded ) ) {
		return '';
	}

	$key    = kdcom_get_encryption_key();
	$data   = base64_decode( $encoded, true );

	if ( false === $data ) {
		return '';
	}

	$iv_len = openssl_cipher_iv_length( 'aes-256-cbc' );

	if ( strlen( $data ) <= $iv_len ) {
		return '';
	}

	$iv         = substr( $data, 0, $iv_len );
	$ciphertext = substr( $data, $iv_len );

	$decrypted = openssl_decrypt( $ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );

	return false === $decrypted ? '' : $decrypted;
}