<?php
/**
 * Elisa Dela Cruz theme functions.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EDC_VERSION', '1.2.0' );
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

	// Image sizes used across case-study cards / single-project hero.
	add_image_size( 'edc-case-card', 900, 700, true );
	add_image_size( 'edc-project-hero', 1600, 1000, true );

	register_nav_menus( array(
		'primary' => __( 'Primary Navigation', 'elisa-dela-cruz' ),
		'footer'  => __( 'Footer Navigation', 'elisa-dela-cruz' ),
	) );
}
add_action( 'after_setup_theme', 'edc_setup' );

/* -------------------------------------------------------------------------
 * Custom Post Type: Project
 * (Registered here by the theme. Cursor only needs to attach an ACF field
 * group to this existing post type — see SCHEMA.md.)
 * ---------------------------------------------------------------------- */
function edc_register_project_cpt() {
	$labels = array(
		'name'                  => __( 'Projects', 'elisa-dela-cruz' ),
		'singular_name'         => __( 'Project', 'elisa-dela-cruz' ),
		'menu_name'             => __( 'Projects', 'elisa-dela-cruz' ),
		'add_new'               => __( 'Add New', 'elisa-dela-cruz' ),
		'add_new_item'          => __( 'Add New Project', 'elisa-dela-cruz' ),
		'edit_item'             => __( 'Edit Project', 'elisa-dela-cruz' ),
		'new_item'              => __( 'New Project', 'elisa-dela-cruz' ),
		'view_item'             => __( 'View Project', 'elisa-dela-cruz' ),
		'view_items'            => __( 'View Projects', 'elisa-dela-cruz' ),
		'search_items'          => __( 'Search Projects', 'elisa-dela-cruz' ),
		'not_found'             => __( 'No projects found', 'elisa-dela-cruz' ),
		'not_found_in_trash'    => __( 'No projects found in Trash', 'elisa-dela-cruz' ),
		'all_items'             => __( 'All Projects', 'elisa-dela-cruz' ),
		'archives'              => __( 'Project Archives', 'elisa-dela-cruz' ),
		'featured_image'        => __( 'Project Screenshot', 'elisa-dela-cruz' ),
		'set_featured_image'    => __( 'Set project screenshot', 'elisa-dela-cruz' ),
		'remove_featured_image' => __( 'Remove project screenshot', 'elisa-dela-cruz' ),
	);

	register_post_type( 'project', array(
		'labels'        => $labels,
		'public'        => true,
		'has_archive'   => true,
		'rewrite'       => array( 'slug' => 'projects', 'with_front' => false ),
		'menu_icon'     => 'dashicons-portfolio',
		'menu_position' => 5,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'show_in_rest'  => true,
		'show_ui'       => true,
		'show_in_menu'  => true,
	) );
}
add_action( 'init', 'edc_register_project_cpt' );

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
		if ( $value !== null && $value !== '' && $value !== false ) {
			return $value;
		}
	}
	return $default;
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
 * Output the Contact Form 7 shortcode configured on the options page.
 * Falls back to a static (non-functional) form matching the mockup design
 * if CF7 isn't installed/active yet or no shortcode has been set.
 */
function edc_contact_form() {
	$shortcode = edc_opt( 'cf7_shortcode', '' );

	if ( $shortcode && function_exists( 'wpcf7' ) ) {
		// Allow raw shortcode only — do not strip CF7 attributes.
		echo do_shortcode( $shortcode );
		return;
	}
	?>
	<form onsubmit="return false;" style="display:flex;flex-direction:column;gap:14px;">
		<input type="text" placeholder="Your name" style="width:100%;background:rgba(255,255,255,.04);border:1px solid var(--border-2);border-radius:8px;padding:12px 14px;color:#fff;font-family:'Manrope',sans-serif;font-size:14px;">
		<input type="email" placeholder="name@company.com" style="width:100%;background:rgba(255,255,255,.04);border:1px solid var(--border-2);border-radius:8px;padding:12px 14px;color:#fff;font-family:'Manrope',sans-serif;font-size:14px;">
		<textarea placeholder="Tell me about your project" style="width:100%;height:90px;resize:none;background:rgba(255,255,255,.04);border:1px solid var(--border-2);border-radius:8px;padding:12px 14px;color:#fff;font-family:'Manrope',sans-serif;font-size:14px;"></textarea>
		<button class="btn btn-fill" type="submit" style="width:100%;justify-content:center;">Send message</button>
		<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
			<p style="font-size:11px;color:var(--dim-2);margin-top:4px;">Admin note: install &amp; activate Contact Form 7, then paste its shortcode into Site Content &rarr; CF7 Shortcode to make this form functional.</p>
		<?php endif; ?>
	</form>
	<?php
}

require_once EDC_DIR . '/inc/visibility.php';
require_once EDC_DIR . '/inc/contact-form.php';
require_once EDC_DIR . '/inc/security.php';
require_once EDC_DIR . '/inc/seo-names.php';

/* -------------------------------------------------------------------------
 * Assets
 * ---------------------------------------------------------------------- */
function edc_enqueue_assets() {
	wp_enqueue_style(
		'edc-google-fonts',
		'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;1,9..144,500;1,9..144,600&family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'edc-style', get_stylesheet_uri(), array(), EDC_VERSION );

	wp_enqueue_script( 'edc-gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', array(), '3.12.5', true );

	wp_enqueue_script( 'edc-main', EDC_URI . '/assets/js/main.js', array( 'edc-gsap' ), EDC_VERSION, true );

	// Dynamic data handed to main.js — terminal lines & testimonials — with
	// fallbacks that exactly match the original static mockup content.
	$default_terminal_lines = array(
		array( 'p' => '$ whoami', 'o' => array( 'Elisa Dela Cruz', 'Senior Shopify Consultant' ) ),
		array( 'p' => '$ expertise', 'o' => array( 'Shopify', 'WordPress', 'WooCommerce', 'Performance', 'SEO' ) ),
		array( 'p' => '$ current_status', 'o' => array( 'Helping businesses launch better websites' ) ),
		array( 'p' => '$ recent_work', 'o' => array( '✔ Shopify migration', '✔ Website rescue', '✔ Core Web Vitals', '✔ Security hardening' ), 'cls' => 'accent' ),
	);
	$terminal_rows = edc_repeater( 'terminal_lines', array() );
	$terminal_lines = $default_terminal_lines;
	if ( ! empty( $terminal_rows ) ) {
		$terminal_lines = array();
		foreach ( $terminal_rows as $row ) {
			$output_raw = isset( $row['output'] ) ? $row['output'] : '';
			$terminal_lines[] = array(
				'p'   => isset( $row['prompt'] ) ? $row['prompt'] : '$',
				'o'   => array_filter( array_map( 'trim', explode( "\n", $output_raw ) ) ),
				'cls' => ! empty( $row['accent'] ) ? 'accent' : '',
			);
		}
	}

	// No fallback quotes: testimonials only ever come from real ACF rows.
	$testimonial_rows = edc_repeater( 'testimonials', array() );
	$testimonials = array();
	if ( ! empty( $testimonial_rows ) ) {
		$testimonials = array();
		foreach ( $testimonial_rows as $row ) {
			$quote = isset( $row['quote'] ) ? trim( (string) $row['quote'] ) : '';
			if ( $quote === '' ) {
				continue;
			}
			$name = isset( $row['name'] ) ? trim( (string) $row['name'] ) : '';
			$company = isset( $row['company'] ) ? trim( (string) $row['company'] ) : '';
			if ( $name === '' && $company !== '' ) {
				$name = $company;
				$company = '';
			}
			$testimonials[] = array(
				'q' => $quote,
				'n' => $name,
				'c' => $company,
				's' => isset( $row['rating'] ) ? intval( $row['rating'] ) : 5,
			);
		}
	}

	wp_add_inline_script(
		'edc-main',
		'window.edcData = ' . wp_json_encode( array(
			'terminalLines' => $terminal_lines,
			'testimonials'  => $testimonials,
		) ) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'edc_enqueue_assets' );

/* Nav menu fallback moved to inc/visibility.php */

/* -------------------------------------------------------------------------
 * Misc cleanup
 * ---------------------------------------------------------------------- */
add_filter( 'excerpt_more', function () {
	return '&hellip;';
} );

// Keep the block editor's default width sane for standard pages.
add_theme_support( 'wp-block-styles' );
