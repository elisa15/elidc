<?php
/**
 * Plugin Name: Elisa Armas Visitor Log
 * Description: Private contact-form + activity log and IP blocker for elisaarmas.com only. Admin-only.
 * Version: 1.1.0
 * Author: Elisa Armas
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EA_VL_VERSION', '1.1.0' );
define( 'EA_VL_FILE', __FILE__ );
define( 'EA_VL_DIR', plugin_dir_path( __FILE__ ) );
define( 'EA_VL_URL', plugin_dir_url( __FILE__ ) );

/**
 * Only run on elisaarmas.com (protects other sites sharing this WP install path).
 */
function ea_vl_is_target_site() {
	$host = '';
	if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
		$host = strtolower( (string) wp_unslash( $_SERVER['HTTP_HOST'] ) );
	}
	$host = preg_replace( '/:\d+$/', '', $host );

	$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
	$home_host = $home_host ? strtolower( $home_host ) : '';

	$allowed = array( 'elisaarmas.com', 'www.elisaarmas.com' );

	if ( $host && in_array( $host, $allowed, true ) ) {
		return true;
	}
	if ( $home_host && in_array( $home_host, $allowed, true ) ) {
		return true;
	}

	if ( ( defined( 'WP_CLI' ) && WP_CLI ) || wp_doing_cron() ) {
		return in_array( $home_host, $allowed, true );
	}

	return false;
}

if ( ! ea_vl_is_target_site() ) {
	return;
}

require_once EA_VL_DIR . 'includes/class-db.php';
require_once EA_VL_DIR . 'includes/class-geo.php';
require_once EA_VL_DIR . 'includes/class-blocker.php';
require_once EA_VL_DIR . 'includes/class-logger.php';
require_once EA_VL_DIR . 'includes/class-activity.php';
require_once EA_VL_DIR . 'includes/class-admin.php';

register_activation_hook( __FILE__, array( 'EA_VL_DB', 'activate' ) );

add_action(
	'plugins_loaded',
	static function () {
		EA_VL_Blocker::init();
		EA_VL_Logger::init();
		EA_VL_Activity::init();
		if ( is_admin() ) {
			EA_VL_Admin::init();
		}
	},
	1
);
