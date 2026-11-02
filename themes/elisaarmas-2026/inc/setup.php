<?php
/**
 * Theme setup.
 *
 * @package ElisaArmas2026
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme supports and menus.
 */
function elisaarmas_2026_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'elisaarmas-2026' ),
			'footer'  => __( 'Footer Menu', 'elisaarmas-2026' ),
		)
	);
}
add_action( 'after_setup_theme', 'elisaarmas_2026_setup' );
