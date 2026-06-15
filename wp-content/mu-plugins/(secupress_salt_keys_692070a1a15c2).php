<?php
/**
 * Plugin Name: SecuPress Salt Keys
 * Description: Great Security Keys for your site
 * Version: 2.3.17
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Copyright 2012-2025 SecuPress
 */

defined( 'ABSPATH' ) or die( 'Something went wrong.' );

if ( defined( 'SECUPRESS_SALT_KEYS_MODULE_ACTIVE' ) ) {
	@unlink( __FILE__ ); // We are in a duplicated file, should not happen, delete us!
	return;
}

if ( ! get_site_option( 'secupress_active_submodule_wp-config-constant-saltkeys' ) ) {
	return;
}

define( 'SECUPRESS_SALT_KEYS_MODULE_ACTIVE', true );

$hash_1     = 'j4[tUGwM;$pkAgqJxz0#p2jl,N>ka3nXVU32$Ah WL?Ga@><$Q 4?>,lP8*e1n{C';
$hash_2     = 'Rt.<89=5^=1P)$j#o.o:&fp4BUn)(;1s#L|.=ve.]a#oX7P%7MT,qTv60):&$H_g';
$file_str   = __FILE__;
$sp_setup   = get_option( 'secupress_settings' );
$hash_key   = isset( $sp_setup['hash_key'] ) ? $sp_setup['hash_key'] : md5( __FILE__ );
$hash_1    .= $hash_2;
$file_str  .= $hash_2;
$main_keys  = [ 'SECRET_KEY', 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'SECRET_SALT', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT' ];

foreach ( $main_keys as $main_key ) {
	if( ! defined( $main_key ) ) {
		define( $main_key, sha1( 'secupress' . $hash_key . $main_key . md5( $main_key . $file_str ) ) . md5( $hash_key . $main_key . $file_str ) );
	}
}

unset( $file_str, $main_key, $main_keys, $hash_1, $hash_2, $hash_key, $sp_setup );

if ( ! function_exists( 'wp_salt' ) ) {
	function wp_salt( $scheme = 'auth' ) {
		$scheme = strtoupper( $scheme );
		if ( ! defined( "{$scheme}_KEY" ) || ! defined( "{$scheme}_SALT" ) ) {
			$scheme = 'secret';
		}
		/** This filter is documented in wp-includes/pluggable.php */
		return apply_filters( 'salt', constant( strtoupper( "{$scheme}_KEY" ) ) . constant( strtoupper( "{$scheme}_SALT" ) ), $scheme );
	}
}
