<?php
/**
 * Section visibility helpers for elisa-dela-cruz.
 * Included from functions.php — do not load directly.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a homepage section should render.
 * Public visitors only see enabled sections.
 * Administrators with manage_options still see disabled sections for preview.
 *
 * @param string $field_name ACF true/false field name, e.g. show_services.
 */
/**
 * Read an ACF true/false options field without treating false as missing.
 * edc_opt() falls back on false; visibility must not.
 *
 * @param string $field_name Field name.
 * @param bool   $default    Default when unset.
 */
function edc_opt_bool( $field_name, $default = true ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $field_name, 'option' );
		if ( $value === null || $value === '' ) {
			return (bool) $default;
		}
		return (bool) $value;
	}
	return (bool) $default;
}

/**
 * Whether at least one project is published (cached per request).
 */
function edc_has_published_projects() {
	static $has = null;
	if ( null === $has ) {
		$has = (bool) get_posts( array(
			'post_type'      => 'project',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		) );
	}
	return $has;
}

/**
 * Whether a section is enabled for the public. The work section also needs
 * real projects, so every "#work" link disappears while it would be empty.
 *
 * @param string $field_name ACF field name.
 */
function edc_section_is_public( $field_name ) {
	$is_enabled = edc_opt_bool( $field_name, true );
	if ( $is_enabled && 'show_work' === $field_name ) {
		return edc_has_published_projects();
	}
	return $is_enabled;
}

function edc_should_show_section( $field_name ) {
	$is_enabled = edc_section_is_public( $field_name );
	if ( $is_enabled ) {
		return true;
	}
	return is_user_logged_in() && current_user_can( 'manage_options' );
}

/**
 * Whether this section is disabled for the public but visible to an admin.
 *
 * @param string $field_name ACF field name.
 */
function edc_is_admin_preview_section( $field_name ) {
	$is_enabled = edc_section_is_public( $field_name );
	return ! $is_enabled && is_user_logged_in() && current_user_can( 'manage_options' );
}

/**
 * Open a section wrapper; prints admin preview badge when needed.
 *
 * @param string $field_name ACF field name.
 * @param string $label      Human section label for the badge.
 */
function edc_section_open( $field_name, $label = '' ) {
	$classes = array( 'edc-section' );
	if ( edc_is_admin_preview_section( $field_name ) ) {
		$classes[] = 'edc-section--admin-hidden';
		echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" data-edc-section="' . esc_attr( $field_name ) . '">';
		echo '<div class="edc-admin-hidden-badge" role="status">Hidden from public visitors' . ( $label ? ' — ' . esc_html( $label ) : '' ) . '</div>';
		return;
	}
	echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" data-edc-section="' . esc_attr( $field_name ) . '">';
}

/**
 * Close section wrapper.
 */
function edc_section_close() {
	echo '</div>';
}

/**
 * Map homepage section anchors to visibility fields.
 *
 * @return array<string,string> anchor => field
 */
function edc_section_anchor_map() {
	return array(
		'hire'         => 'show_tabs',
		'services'     => 'show_services',
		'work'         => 'show_work',
		'contact'      => 'show_cta',
		'toolbox'      => 'show_toolbox',
		'map'          => 'show_map',
		'testimonials' => 'show_testimonials',
	);
}

/**
 * Whether a hash/URL target should appear in public navigation.
 *
 * @param string $url Menu item URL.
 */
function edc_nav_target_is_public( $url ) {
	if ( ! is_string( $url ) || $url === '' ) {
		return true;
	}
	$fragment = '';
	if ( false !== strpos( $url, '#' ) ) {
		$parts    = explode( '#', $url, 2 );
		$fragment = isset( $parts[1] ) ? $parts[1] : '';
	}
	if ( $fragment === '' ) {
		return true;
	}
	$map = edc_section_anchor_map();
	if ( ! isset( $map[ $fragment ] ) ) {
		return true;
	}
	return edc_should_show_section( $map[ $fragment ] );
}

/**
 * Filter custom menu items that point at hidden homepage sections.
 *
 * @param array $items Menu items.
 * @return array
 */
function edc_filter_nav_menu_items( $items ) {
	if ( empty( $items ) || ! is_array( $items ) ) {
		return $items;
	}
	// Admins previewing keep all links.
	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		return $items;
	}
	$filtered = array();
	foreach ( $items as $item ) {
		$url = isset( $item->url ) ? $item->url : '';
		if ( edc_nav_target_is_public( $url ) ) {
			$filtered[] = $item;
		}
	}
	return $filtered;
}
add_filter( 'wp_nav_menu_objects', 'edc_filter_nav_menu_items', 20 );

/**
 * Primary menu fallback with visibility-aware anchors.
 */
function edc_primary_menu_fallback() {
	$items = array(
		array( 'href' => '#hire', 'label' => 'Why me', 'field' => 'show_tabs' ),
		array( 'href' => '#services', 'label' => 'Services', 'field' => 'show_services' ),
		array( 'href' => '#work', 'label' => 'Work', 'field' => 'show_work' ),
		array( 'href' => '#contact', 'label' => 'Contact', 'field' => 'show_cta' ),
	);
	echo '<ul>';
	foreach ( $items as $item ) {
		if ( ! edc_should_show_section( $item['field'] ) ) {
			continue;
		}
		echo '<li><a href="' . esc_url( $item['href'] ) . '">' . esc_html( $item['label'] ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Google Analytics 4 (gtag) — printed early in <head> per Google's snippet.
 */
function edc_print_google_analytics() {
	if ( is_admin() ) {
		return;
	}
	if ( defined( 'EDC_GA_PRINTED' ) && EDC_GA_PRINTED ) {
		return;
	}

	$measurement_id = 'G-4TNEDPHB08';
	define( 'EDC_GA_PRINTED', true );
	?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo rawurlencode( $measurement_id ); ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '<?php echo esc_js( $measurement_id ); ?>');
</script>
	<?php
}
add_action( 'wp_head', 'edc_print_google_analytics', 1 );

/**
 * Admin-only CSS for hidden-section preview indicator.
 */
function edc_admin_preview_styles() {
	if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$css = '.edc-section--admin-hidden{outline:1px dashed rgba(255,47,177,.45);outline-offset:6px;position:relative;}'
		. '.edc-admin-hidden-badge{position:absolute;top:10px;left:10px;z-index:50;font:600 11px/1.2 Manrope,system-ui,sans-serif;letter-spacing:.04em;text-transform:uppercase;color:#fff;background:rgba(20,20,26,.82);border:1px solid rgba(255,47,177,.55);border-radius:999px;padding:6px 10px;pointer-events:none;}';
	wp_add_inline_style( 'edc-style', $css );
}
add_action( 'wp_enqueue_scripts', 'edc_admin_preview_styles', 30 );
