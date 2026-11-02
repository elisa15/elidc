<?php
/**
 * Enqueue scripts and styles.
 *
 * @package ElisaArmas2026
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front-end assets.
 */
function elisaarmas_2026_enqueue_assets() {
	wp_enqueue_style(
		'elisaarmas-2026-main',
		ELISAARMAS_2026_URI . '/assets/css/main.css',
		array(),
		ELISAARMAS_2026_VERSION
	);

	wp_enqueue_script(
		'elisaarmas-2026-main',
		ELISAARMAS_2026_URI . '/assets/js/main.js',
		array(),
		ELISAARMAS_2026_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'elisaarmas_2026_enqueue_assets' );
