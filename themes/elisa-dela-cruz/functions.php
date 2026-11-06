<?php
/**
 * Elisa Dela Cruz theme functions.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EDC_VERSION', '1.6.1' );
define( 'EDC_DIR', get_template_directory() );
define( 'EDC_URI', get_template_directory_uri() );

/* -------------------------------------------------------------------------
 * ACF Local JSON — load/save field groups from this theme
 * ---------------------------------------------------------------------- */
add_filter( 'acf/settings/save_json', function ( $path ) {
	return EDC_DIR . '/acf-json';
} );
add_filter( 'acf/settings/load_json', function ( $paths ) {
	$paths[] = EDC_DIR . '/acf-json';
	return $paths;
} );


/* -------------------------------------------------------------------------
 * Theme setup
 * ---------------------------------------------------------------------- */
function edc_setup() {
	load_theme_textdomain( 'elisa-dela-cruz', EDC_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );

	// Project screenshots (cropped from the top of the page) and the hex portrait.
	add_image_size( 'edc-shot', 1200, 705, array( 'center', 'top' ) );
	add_image_size( 'edc-portrait', 600, 672, true );

	register_nav_menus( array(
		'primary' => __( 'Primary Navigation', 'elisa-dela-cruz' ),
		'footer'  => __( 'Footer Navigation', 'elisa-dela-cruz' ),
	) );
}
add_action( 'after_setup_theme', 'edc_setup' );

/* -------------------------------------------------------------------------
 * ACF Options Page (site-wide singleton content)
 * Guarded because acf_add_options_page() is an ACF PRO feature — if the
 * install only has free ACF, this simply won't register and every
 * edc_opt() call below will fall back to its default string.
 * ---------------------------------------------------------------------- */
function edc_register_options_page() {
	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page( array(
			'page_title' => __( 'Site Content', 'elisa-dela-cruz' ),
			'menu_title' => __( 'Site Content', 'elisa-dela-cruz' ),
			'menu_slug'  => 'edc-site-content',
			'capability' => 'edit_posts',
			'redirect'   => false,
			'icon_url'   => 'dashicons-admin-customizer',
			'position'   => 3, // Directly under Dashboard (2).
		) );
	}
}
add_action( 'acf/init', 'edc_register_options_page' );

/* -------------------------------------------------------------------------
 * Helpers
 * ---------------------------------------------------------------------- */

/**
 * Get an ACF field from the Options page, with a fallback default.
 * Works safely even when ACF is not installed/active yet.
 */
function edc_opt( $selector, $default = '' ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $selector, 'option' );
		if ( null === $value ) {
			$value = edc_acf_json_default( $selector );
		}
		if ( $value !== null && $value !== '' && $value !== false ) {
			return $value;
		}
	}
	return $default;
}

/**
 * Get an ACF field from the current post/queried object, with a fallback default.
 */
function edc_field( $selector, $default = '', $post_id = null ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $selector, $post_id );
		if ( null === $value ) {
			$value = edc_acf_json_default( $selector );
		}
		if ( $value !== null && $value !== '' && $value !== false ) {
			return $value;
		}
	}
	return $default;
}

/**
 * The acf-json default for a field that has never been saved. ACF only applies
 * defaults when a field is read by key, so name lookups need this. Defaults from
 * the legacy Global Theme Options group are skipped: they hold retired copy.
 */
function edc_acf_json_default( $name ) {
	static $cache = array();
	if ( ! is_string( $name ) || ! function_exists( 'acf_get_field' ) ) {
		return null;
	}
	if ( array_key_exists( $name, $cache ) ) {
		return $cache[ $name ];
	}

	$cache[ $name ] = null;
	$field          = acf_get_field( $name );
	if ( ! $field || ! isset( $field['default_value'] ) || '' === $field['default_value'] ) {
		return null;
	}
	$group = acf_get_field_group( $field['parent'] );
	if ( ! $group || 'group_edc_site_content' === $group['key'] ) {
		return null;
	}

	$cache[ $name ] = $field['default_value'];
	return $cache[ $name ];
}

/**
 * Render a repeater field (options page or post) with a fallback array of rows.
 * Returns a plain array of associative sub-field arrays either way, so
 * templates can loop identically whether ACF/Cursor data exists yet or not.
 */
function edc_repeater( $selector, $default_rows = array(), $post_id = 'option' ) {
	if ( function_exists( 'have_rows' ) && have_rows( $selector, $post_id ) ) {
		$rows = array();
		while ( have_rows( $selector, $post_id ) ) {
			the_row();
			$rows[] = get_row( true ); // format values with field names, not field keys
		}
		if ( ! empty( $rows ) ) {
			return $rows;
		}
	}
	return $default_rows;
}

/**
 * Whether a link URL is safe to print: not empty, not a bare "#", and not an
 * anchor to a homepage section that is hidden from public visitors.
 */
function edc_link_is_usable( $url ) {
	$url = is_string( $url ) ? trim( $url ) : '';
	if ( $url === '' || $url === '#' ) {
		return false;
	}
	return edc_nav_target_is_public( $url );
}

/**
 * Output the Contact Form 7 form configured on the options page. Without CF7 or a
 * shortcode there is no working form, so visitors get nothing and admins get a note.
 */
function edc_contact_form() {
	$shortcode = edc_opt( 'cf7_shortcode', '' );

	if ( $shortcode && function_exists( 'wpcf7' ) ) {
		// Allow raw shortcode only — do not strip CF7 attributes.
		echo do_shortcode( $shortcode );
		return;
	}
	if ( current_user_can( 'edit_theme_options' ) ) {
		echo '<p class="admin-note">Admin note: activate Contact Form 7, then paste its shortcode into Site Content &rarr; CF7 Shortcode.</p>';
	}
}

require_once EDC_DIR . '/inc/content-model.php';
require_once EDC_DIR . '/inc/template-helpers.php';
require_once EDC_DIR . '/inc/projects-query.php';
require_once EDC_DIR . '/inc/visibility.php';
require_once EDC_DIR . '/inc/contact-form.php';
require_once EDC_DIR . '/inc/security.php';
require_once EDC_DIR . '/inc/seo-names.php';

/**
 * Drop the core emoji script/styles; the site uses native emoji only.
 */
function edc_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'edc_disable_emojis' );

/* -------------------------------------------------------------------------
 * Assets
 * ---------------------------------------------------------------------- */
function edc_enqueue_assets() {
	wp_enqueue_style(
		'edc-google-fonts',
		'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;1,9..144,500&family=JetBrains+Mono:wght@400;500&family=Manrope:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'edc-tokens', EDC_URI . '/assets/css/tokens.css', array(), EDC_VERSION );
	wp_enqueue_style( 'edc-site', EDC_URI . '/assets/css/site.css', array( 'edc-tokens' ), EDC_VERSION );

	wp_enqueue_script(
		'edc-site',
		EDC_URI . '/assets/js/site.js',
		array(),
		EDC_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'edc_enqueue_assets' );

/**
 * Mark JS support before first paint so hidden-until-revealed content and the
 * mobile menu never flash; site.js loads deferred in the footer.
 */
function edc_print_js_class() {
	echo "<script>document.documentElement.classList.add('js')</script>\n";
}
add_action( 'wp_head', 'edc_print_js_class', 0 );

/* Nav menu fallback moved to inc/visibility.php */

/* -------------------------------------------------------------------------
 * Misc cleanup
 * ---------------------------------------------------------------------- */
add_filter( 'excerpt_more', function () {
	return '&hellip;';
} );

// Keep the block editor's default width sane for standard pages.
add_theme_support( 'wp-block-styles' );

/**
 * Open the Google Fonts connections early (the font files are versioned by
 * Google, so they can't be preloaded by URL).
 */
function edc_font_preconnect( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'edc_font_preconnect', 10, 2 );
