<?php
/**
 * Site hardening and basic bot / abuse protection for elisaarmas.com.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable XML-RPC (common brute-force / pingback abuse vector).
 */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'wp_xmlrpc_server_class', function () {
	return 'EDC_Disabled_XMLRPC_Server';
} );

if ( ! class_exists( 'EDC_Disabled_XMLRPC_Server' ) ) {
	class EDC_Disabled_XMLRPC_Server {
		public function serve_request() {
			status_header( 403 );
			header( 'Content-Type: text/plain; charset=UTF-8' );
			echo 'XML-RPC is disabled.';
			exit;
		}
	}
}

/**
 * Remove noisy / fingerprinting head tags.
 */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );
add_filter( 'the_generator', '__return_empty_string' );

/**
 * Block author enumeration (/?author=1 and /author/name).
 */
function edc_block_author_enumeration() {
	if ( is_admin() ) {
		return;
	}
	if ( isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
	if ( is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'edc_block_author_enumeration', 1 );

/**
 * Hide users from public REST API.
 *
 * @param array $endpoints Endpoints.
 * @return array
 */
function edc_disable_public_user_rest( $endpoints ) {
	if ( isset( $endpoints['/wp/v2/users'] ) ) {
		unset( $endpoints['/wp/v2/users'] );
	}
	if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
		unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
}
add_filter( 'rest_endpoints', 'edc_disable_public_user_rest' );

/**
 * Prevent theme/plugin file editing in wp-admin (without touching wp-config.php).
 *
 * @param array  $caps    Required caps.
 * @param string $cap     Capability.
 * @return array
 */
function edc_disable_file_editors( $caps, $cap ) {
	if ( in_array( $cap, array( 'edit_themes', 'edit_plugins', 'edit_files' ), true ) ) {
		return array( 'do_not_allow' );
	}
	return $caps;
}
add_filter( 'map_meta_cap', 'edc_disable_file_editors', 10, 2 );

/**
 * Simple login rate limit: 5 failures / 15 minutes per IP.
 *
 * @param WP_User|WP_Error|null $user User.
 * @return WP_User|WP_Error|null
 */
function edc_login_rate_limit( $user ) {
	$ip  = edc_client_ip();
	$key = 'edc_login_fail_' . md5( $ip );
	$hits = (int) get_transient( $key );
	if ( $hits >= 5 ) {
		return new WP_Error(
			'edc_too_many_logins',
			__( 'Too many failed login attempts. Please try again in 15 minutes.', 'elisa-dela-cruz' )
		);
	}
	return $user;
}
add_filter( 'authenticate_authenticate', 'edc_login_rate_limit', 30 );

/**
 * @param string|null $username Username.
 */
function edc_login_failed( $username ) {
	$ip  = edc_client_ip();
	$key = 'edc_login_fail_' . md5( $ip );
	$hits = (int) get_transient( $key );
	set_transient( $key, $hits + 1, 15 * MINUTE_IN_SECONDS );
}
add_action( 'wp_login_failed', 'edc_login_failed' );

/**
 * Clear failures on successful login.
 */
function edc_login_success( $login, $user ) {
	delete_transient( 'edc_login_fail_' . md5( edc_client_ip() ) );
}
add_action( 'wp_login', 'edc_login_success', 10, 2 );

/**
 * Block obvious bad bots on front-end requests (not search engines).
 */
function edc_block_bad_bots() {
	if ( is_admin() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}

	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( (string) $_SERVER['HTTP_USER_AGENT'] ) : '';
	// Do not blanket-block empty UA on public pages (can affect some fetchers).

	$bad = array(
		'nikto',
		'sqlmap',
		'havij',
		'masscan',
		'nmap',
		'acunetix',
		'dirbuster',
		'wfuzz',
		'mutillidae',
		'libwww-perl',
		'python-requests',
		'curl/',
		'wget/',
		'httpclient',
		'zgrab',
		'scanner',
		'exploit',
		'attack',
	);

	// Allow WP cron / CF7 / logged-in admins / known good bots.
	if ( is_user_logged_in() ) {
		return;
	}
	// Search engines and major crawlers must always pass.
	$allow = array(
		'googlebot',
		'google-inspectiontool',
		'storebot-google',
		'adsbot-google',
		'mediapartners-google',
		'apis-google',
		'feedfetcher-google',
		'bingbot',
		'slurp',
		'duckduckbot',
		'baiduspider',
		'yandexbot',
		'applebot',
		'facebookexternalhit',
		'twitterbot',
		'linkedinbot',
		'semrushbot',
		'ahrefsbot',
		'dotbot',
		'petalbot',
	);
	foreach ( $allow as $ok ) {
		if ( false !== strpos( $ua, $ok ) ) {
			return;
		}
	}

	foreach ( $bad as $needle ) {
		if ( false !== strpos( $ua, $needle ) ) {
			status_header( 403 );
			exit( 'Forbidden' );
		}
	}
}
add_action( 'init', 'edc_block_bad_bots', 0 );

/**
 * Extra CF7 spam signal: reject empty user agents and obvious script clients.
 *
 * @param bool $spam Spam flag.
 * @return bool
 */
function edc_cf7_block_scripted_clients( $spam ) {
	if ( $spam ) {
		return $spam;
	}
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( (string) $_SERVER['HTTP_USER_AGENT'] ) : '';
	if ( $ua === '' || false !== strpos( $ua, 'curl/' ) || false !== strpos( $ua, 'wget/' ) || false !== strpos( $ua, 'python-requests' ) ) {
		return true;
	}
	return $spam;
}
add_filter( 'wpcf7_spam', 'edc_cf7_block_scripted_clients', 9 );

/**
 * Client IP helper (Cloudflare / proxy aware, sanitized).
 *
 * @return string
 */
function edc_client_ip() {
	$keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
	foreach ( $keys as $key ) {
		if ( empty( $_SERVER[ $key ] ) ) {
			continue;
		}
		$raw = (string) $_SERVER[ $key ];
		if ( $key === 'HTTP_X_FORWARDED_FOR' ) {
			$parts = explode( ',', $raw );
			$raw   = trim( $parts[0] );
		}
		if ( filter_var( $raw, FILTER_VALIDATE_IP ) ) {
			return $raw;
		}
	}
	return '0.0.0.0';
}

/**
 * Basic security headers on front-end responses.
 */
function edc_security_headers() {
	if ( headers_sent() || is_admin() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
}
add_action( 'send_headers', 'edc_security_headers' );
