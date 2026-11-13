<?php
/**
 * Speed: self-hosted fonts, inlined CSS, lighter scripts, WebP images,
 * delayed analytics, meta description fallbacks and a small page cache.
 *
 * Everything here lives in the theme, so nothing outside wp-content is touched.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Fonts (self-hosted, latin subset; files from Google Fonts, SIL OFL)
 * ---------------------------------------------------------------------- */

/**
 * @font-face rules for the theme's three families.
 *
 * @return string
 */
function edc_font_face_css() {
	$base  = EDC_URI . '/assets/fonts/';
	$range = 'U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD';
	$faces = array(
		array( 'Manrope', 'normal', '200 800', 'manrope-latin.woff2' ),
		array( 'Fraunces', 'normal', '500', 'fraunces-500-latin.woff2' ),
		array( 'Fraunces', 'italic', '500', 'fraunces-500-italic-latin.woff2' ),
		array( 'JetBrains Mono', 'normal', '100 800', 'jetbrains-mono-latin.woff2' ),
	);
	$css = '';
	foreach ( $faces as $f ) {
		$css .= sprintf(
			'@font-face{font-family:"%1$s";font-style:%2$s;font-weight:%3$s;font-display:swap;src:url(%4$s) format("woff2");unicode-range:%5$s}',
			$f[0],
			$f[1],
			$f[2],
			esc_url( $base . $f[3] ),
			$range
		);
	}
	return $css;
}

/**
 * Preload the body font so text paints in Manrope straight away.
 */
function edc_preload_fonts() {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( EDC_URI . '/assets/fonts/manrope-latin.woff2' )
	);
}
add_action( 'wp_head', 'edc_preload_fonts', 2 );

/* -------------------------------------------------------------------------
 * Inline CSS: the theme stylesheets are small, so they ship inside the page
 * instead of costing extra round trips before anything can paint.
 * ---------------------------------------------------------------------- */

/**
 * Handles whose stylesheet is printed inline. Their wp_add_inline_style()
 * extras still print right after, as usual.
 *
 * @return string[]
 */
function edc_inline_style_handles() {
	return array( 'edc-tokens', 'edc-site', 'contact-form-7' );
}

/**
 * Minified contents of a stylesheet, cached by file time.
 *
 * @param string $handle Style handle.
 * @param string $src    Registered URL.
 * @return string|false
 */
function edc_inline_css_for( $handle, $src ) {
	$path = edc_local_path_for_url( $src );
	if ( ! $path ) {
		return false;
	}
	$key = 'edc_css_' . md5( $path . '|' . filemtime( $path ) . '|' . EDC_VERSION );
	$css = get_transient( $key );
	if ( false === $css ) {
		$css = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local file.
		// Relative url() values must resolve from the stylesheet's own folder.
		$dir = trailingslashit( dirname( strtok( $src, '?' ) ) );
		$css = preg_replace_callback(
			'#url\(\s*([\'"]?)(?!data:|https?:|/|\#)([^\'")]+)\1\s*\)#i',
			function ( $m ) use ( $dir ) {
				return 'url(' . $m[1] . $dir . $m[2] . $m[1] . ')';
			},
			$css
		);
		$css = preg_replace( '#/\*.*?\*/#s', '', $css );
		$css = preg_replace( '/\s+/', ' ', $css );
		$css = preg_replace( '/\s*([{};,>])\s*/', '$1', $css );
		$css = str_replace( ';}', '}', $css );
		$css = trim( $css );
		if ( 'edc-tokens' === $handle ) {
			$css = edc_font_face_css() . $css;
		}
		set_transient( $key, $css, WEEK_IN_SECONDS );
	}
	return $css;
}

/**
 * Map a same-site asset URL to its file on disk (wp-content or wp-includes only).
 *
 * @param string $src URL.
 * @return string|false
 */
function edc_local_path_for_url( $src ) {
	$src = strtok( (string) $src, '?' );
	if ( 0 === strpos( $src, '//' ) ) {
		$src = ( is_ssl() ? 'https:' : 'http:' ) . $src;
	}
	$maps = array(
		content_url() => WP_CONTENT_DIR,
		includes_url() => ABSPATH . WPINC . '/',
	);
	foreach ( $maps as $url => $dir ) {
		$url = set_url_scheme( $url );
		$src = set_url_scheme( $src );
		if ( 0 === strpos( $src, $url ) ) {
			$path = realpath( rtrim( $dir, '/' ) . '/' . ltrim( substr( $src, strlen( $url ) ), '/' ) );
			if ( $path && '.css' === substr( $path, -4 ) && is_readable( $path ) ) {
				return $path;
			}
		}
	}
	return false;
}

/**
 * Swap the <link> for an inline <style> for the handles above.
 *
 * @param string $tag    Link tag.
 * @param string $handle Handle.
 * @param string $href   URL.
 * @param string $media  Media.
 * @return string
 */
function edc_inline_style_tag( $tag, $handle, $href, $media ) {
	if ( is_admin() || ! in_array( $handle, edc_inline_style_handles(), true ) ) {
		return $tag;
	}
	$css = edc_inline_css_for( $handle, $href );
	if ( false === $css || '' === $css ) {
		return $tag;
	}
	$media = ( $media && 'all' !== $media ) ? ' media="' . esc_attr( $media ) . '"' : '';
	return '<style id="' . esc_attr( $handle ) . '-css"' . $media . '>' . $css . "</style>\n";
}
add_filter( 'style_loader_tag', 'edc_inline_style_tag', 10, 4 );

/* -------------------------------------------------------------------------
 * Scripts: WooCommerce's jQuery helpers only where the shop needs them.
 * ---------------------------------------------------------------------- */

/**
 * Is this a page where WooCommerce's own front-end scripts do something?
 *
 * @return bool
 */
function edc_is_shop_context() {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return false;
	}
	return is_woocommerce() || is_cart() || is_checkout() || is_account_page() || is_page( 'fixes' );
}

/**
 * Drop WooCommerce's add-to-cart, cookie and blockUI scripts (and so jQuery)
 * from portfolio pages. Order attribution stays everywhere so sales still
 * record where the buyer came from.
 */
function edc_trim_scripts() {
	if ( is_admin() || edc_is_shop_context() ) {
		return;
	}
	foreach ( array( 'wc-add-to-cart', 'woocommerce', 'wc-cart-fragments', 'jquery-blockui', 'js-cookie', 'wc-jquery-blockui', 'wc-js-cookie' ) as $handle ) {
		wp_dequeue_script( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'edc_trim_scripts', 99 );

/**
 * The block-editor stylesheets only matter on pages written with blocks
 * (and the block cart and checkout); the theme templates don't use them.
 */
function edc_trim_block_styles() {
	if ( is_admin() || edc_is_shop_context() ) {
		return;
	}
	if ( is_singular() && has_blocks( get_queried_object_id() ) ) {
		return;
	}
	foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles' ) as $handle ) {
		wp_dequeue_style( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'edc_trim_block_styles', 99 );

/**
 * The contact form and order-attribution scripts only matter after the page
 * is up, so they download in the background (WordPress keeps the order and
 * drops the defer itself if a dependency can't take it).
 */
function edc_defer_footer_scripts() {
	if ( is_admin() ) {
		return;
	}
	foreach ( array( 'wp-hooks', 'wp-i18n', 'swv', 'contact-form-7', 'sourcebuster-js', 'wc-order-attribution', 'ea-vl-tracker' ) as $handle ) {
		if ( wp_script_is( $handle, 'registered' ) ) {
			wp_script_add_data( $handle, 'strategy', 'defer' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'edc_defer_footer_scripts', 100 );

/**
 * Nothing on the front end needs jQuery Migrate.
 *
 * @param WP_Scripts $scripts Scripts registry.
 */
function edc_drop_jquery_migrate( $scripts ) {
	if ( is_admin() || empty( $scripts->registered['jquery'] ) ) {
		return;
	}
	$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
}
add_action( 'wp_default_scripts', 'edc_drop_jquery_migrate' );

/* -------------------------------------------------------------------------
 * Images: new uploads (and regenerated sizes) are saved as WebP.
 * ---------------------------------------------------------------------- */

/**
 * @param array $formats Mime map.
 * @return array
 */
function edc_webp_output( $formats ) {
	$formats['image/jpeg'] = 'image/webp';
	$formats['image/png']  = 'image/webp';
	return $formats;
}
add_filter( 'image_editor_output_format', 'edc_webp_output' );

/**
 * WebP at 78 is visually the same as the default 82 for these screenshots and photos, and lighter.
 *
 * @param int    $quality Quality.
 * @param string $mime    Output type.
 * @return int
 */
function edc_webp_quality( $quality, $mime = '' ) {
	return 'image/webp' === $mime ? 78 : $quality;
}
add_filter( 'wp_editor_set_quality', 'edc_webp_quality', 10, 2 );

/* -------------------------------------------------------------------------
 * Meta descriptions: when Yoast has none for a page, reuse the page's intro.
 * ---------------------------------------------------------------------- */

/**
 * @param string $desc Yoast description.
 * @return string
 */
function edc_meta_description_fallback( $desc ) {
	if ( '' !== trim( (string) $desc ) ) {
		return $desc;
	}
	$text = '';
	if ( is_front_page() ) {
		$text = (string) edc_opt( 'home_lede', '' );
	} elseif ( is_post_type_archive( 'project' ) ) {
		$text = 'Selected work by Elisa Dela Cruz: WordPress, WooCommerce and Shopify builds, redesigns and app UI, plus the fixes behind them. Builds you can see, and fixes you mostly can\'t.';
	} elseif ( is_singular() ) {
		$id     = get_queried_object_id();
		$fields = array( 'services_lede', 'about_lede', 'contact_lede', 'cs_lede', 'summary' );
		foreach ( $fields as $field ) {
			$value = edc_field( $field, '', $id );
			if ( is_string( $value ) && '' !== trim( wp_strip_all_tags( $value ) ) ) {
				$text = $value;
				break;
			}
		}
		// Last resort for the theme's own pages, from copy already on the site.
		$by_slug = array(
			'fixes'   => 'Clearly scoped WordPress and Shopify jobs you can buy straight away. Pay, tell me the site, and I\'ll take it from there. No calls needed.',
			'about'   => 'About Elisa Dela Cruz, web developer for WordPress, WooCommerce and Shopify: career, how I work, and the conferences I still make time for.',
			'contact' => 'Get in touch with Elisa Dela Cruz about a WordPress, WooCommerce or Shopify website. Send a message or reach me directly.',
		);
		$slug = (string) get_post_field( 'post_name', $id );
		if ( '' === $text && isset( $by_slug[ $slug ] ) ) {
			$text = $by_slug[ $slug ];
		}
		if ( '' === $text ) {
			$text = has_excerpt( $id ) ? get_the_excerpt( $id ) : '';
		}
	}
	$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $text ) ) );
	if ( strlen( $text ) > 158 ) {
		$text = rtrim( wp_html_excerpt( $text, 155 ), " ,.;:" ) . '…';
	}
	return $text;
}
add_filter( 'wpseo_metadesc', 'edc_meta_description_fallback' );

/* -------------------------------------------------------------------------
 * Page cache: finished HTML for visitors who aren't logged in and have no
 * cart, served at `init` (after the visitor-log IP blocker) so the page
 * isn't rebuilt on every visit. Cleared whenever content changes.
 * ---------------------------------------------------------------------- */

define( 'EDC_PAGE_CACHE_TTL', 10 * HOUR_IN_SECONDS ); // Under 12h, so nonces in cached pages stay valid.

/**
 * @return string Cache folder (no trailing slash).
 */
function edc_page_cache_dir() {
	return WP_CONTENT_DIR . '/cache/edc-pages';
}

/**
 * Can this request be served from (or saved to) the cache?
 *
 * @return bool
 */
function edc_page_cache_request_ok() {
	if ( ( defined( 'EDC_PAGE_CACHE' ) && ! EDC_PAGE_CACHE ) || ( 'local' === wp_get_environment_type() && ! defined( 'EDC_PAGE_CACHE' ) ) || get_option( 'edc_page_cache_off' ) ) {
		return false;
	}
	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) : 'GET';
	if ( 'GET' !== $method && 'HEAD' !== $method ) {
		return false;
	}
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return false;
	}
	if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
		return false;
	}
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '/';
	if ( preg_match( '#^/(wp-|cart|checkout|my-account|fixes|feed|comments/feed)|/feed/?$|\.(xml|txt|php)$|^/wp-json#i', $uri ) ) {
		return false;
	}
	foreach ( array_keys( $_COOKIE ) as $name ) {
		if ( preg_match( '/^(wordpress_logged_in_|wordpress_sec_|wp-postpass_|comment_author_|woocommerce_items_in_cart|woocommerce_cart_hash|wp_woocommerce_session_|store_api_session)/', (string) $name ) ) {
			return false;
		}
	}
	return ! is_user_logged_in();
}

/**
 * @return string Cache file for this URL.
 */
function edc_page_cache_file() {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( (string) $_SERVER['HTTP_HOST'] ) : '';
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '/';
	$gen  = (int) get_option( 'edc_page_cache_gen', 1 );
	return edc_page_cache_dir() . '/' . md5( $host . '|' . $uri . '|' . EDC_VERSION . '|' . $gen ) . '.html';
}

/**
 * Serve a fresh cached copy and stop.
 */
function edc_page_cache_serve() {
	if ( ! edc_page_cache_request_ok() ) {
		return;
	}
	$file = edc_page_cache_file();
	if ( ! is_readable( $file ) || ( time() - filemtime( $file ) ) > EDC_PAGE_CACHE_TTL ) {
		header( 'X-EDC-Cache: MISS' );
		return;
	}
	if ( function_exists( 'edc_security_headers' ) ) {
		edc_security_headers();
	}
	header( 'Content-Type: text/html; charset=UTF-8' );
	header( 'X-EDC-Cache: HIT' );
	header( 'Vary: Accept-Encoding' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $file ) ) . ' GMT' );
	// A pre-compressed copy saves gzipping the same page on every visit.
	$accept = isset( $_SERVER['HTTP_ACCEPT_ENCODING'] ) ? (string) $_SERVER['HTTP_ACCEPT_ENCODING'] : '';
	if ( false !== stripos( $accept, 'gzip' ) && is_readable( $file . '.gz' ) && ! ini_get( 'zlib.output_compression' ) ) {
		while ( ob_get_level() ) {
			ob_end_clean();
		}
		$file = $file . '.gz';
		header( 'Content-Encoding: gzip' );
		header( 'Content-Length: ' . filesize( $file ) );
	}
	if ( 'HEAD' !== strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) ) {
		readfile( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- static HTML we wrote.
	}
	exit;
}
add_action( 'init', 'edc_page_cache_serve', 1 );

/**
 * Start capturing the page so it can be saved once finished.
 */
function edc_page_cache_start() {
	if ( ! edc_page_cache_request_ok() ) {
		return;
	}
	if ( is_404() || is_search() || is_feed() || is_preview() || is_trackback() || is_robots() || post_password_required() || edc_is_shop_context() ) {
		return;
	}
	ob_start( 'edc_page_cache_store' );
}
add_action( 'template_redirect', 'edc_page_cache_start', 9999 );

/**
 * Output buffer callback: save complete 200 HTML pages.
 *
 * @param string $html Page.
 * @return string Unchanged page.
 */
function edc_page_cache_store( $html ) {
	if ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) {
		return $html;
	}
	if ( 200 !== http_response_code() || strlen( $html ) < 2000 || false === stripos( $html, '</html>' ) ) {
		return $html;
	}
	foreach ( headers_list() as $header ) {
		if ( 0 === stripos( $header, 'Set-Cookie:' ) || ( 0 === stripos( $header, 'Content-Type:' ) && false === stripos( $header, 'text/html' ) ) ) {
			return $html;
		}
	}
	$dir = edc_page_cache_dir();
	if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
		return $html;
	}
	if ( ! file_exists( $dir . '/.htaccess' ) ) {
		@file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" ); // phpcs:ignore
		@file_put_contents( $dir . '/index.php', "<?php // Silence.\n" ); // phpcs:ignore
	}
	$file = edc_page_cache_file();
	$tmp  = $file . '.' . getmypid() . '.tmp';
	$out  = $html . "\n<!-- edc cache " . gmdate( 'c' ) . " -->\n";
	if ( false !== @file_put_contents( $tmp, $out ) ) { // phpcs:ignore
		if ( function_exists( 'gzencode' ) && false !== @file_put_contents( $tmp . '.gz', gzencode( $out, 9 ) ) ) { // phpcs:ignore
			@rename( $tmp . '.gz', $file . '.gz' ); // phpcs:ignore
		}
		@rename( $tmp, $file ); // phpcs:ignore
	}
	return $html;
}

/**
 * Empty the cache (bumps the generation, then deletes the old files).
 */
function edc_page_cache_purge() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	update_option( 'edc_page_cache_gen', (int) get_option( 'edc_page_cache_gen', 1 ) + 1, true );
	foreach ( (array) glob( edc_page_cache_dir() . '/*.{html,gz,tmp}', GLOB_BRACE ) as $file ) {
		@unlink( $file ); // phpcs:ignore
	}
}
foreach ( array(
	'save_post',
	'deleted_post',
	'trashed_post',
	'untrashed_post',
	'edit_term',
	'created_term',
	'delete_term',
	'acf/save_post',
	'wp_update_nav_menu',
	'customize_save_after',
	'switch_theme',
	'activated_plugin',
	'deactivated_plugin',
	'upgrader_process_complete',
	'woocommerce_product_set_stock',
	'woocommerce_variation_set_stock',
	'wpcf7_after_save',
) as $edc_hook ) {
	add_action( $edc_hook, 'edc_page_cache_purge' );
}
unset( $edc_hook );

/**
 * Settings that change what every page shows.
 *
 * @param string $option Option name.
 */
function edc_page_cache_purge_on_option( $option ) {
	if ( 0 === strpos( $option, 'options_' ) || 0 === strpos( $option, 'wpseo' ) || in_array( $option, array( 'blogname', 'blogdescription', 'sidebars_widgets', 'show_on_front', 'page_on_front', 'permalink_structure', 'woocommerce_currency' ), true ) ) {
		edc_page_cache_purge();
	}
}
add_action( 'updated_option', 'edc_page_cache_purge_on_option' );

/**
 * "Clear page cache" in the admin bar for administrators.
 *
 * @param WP_Admin_Bar $bar Admin bar.
 */
function edc_page_cache_admin_bar( $bar ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$bar->add_node( array(
		'id'    => 'edc-clear-cache',
		'title' => 'Clear page cache',
		'href'  => wp_nonce_url( admin_url( 'admin-post.php?action=edc_clear_cache' ), 'edc_clear_cache' ),
	) );
}
add_action( 'admin_bar_menu', 'edc_page_cache_admin_bar', 90 );

add_action( 'admin_post_edc_clear_cache', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.' );
	}
	check_admin_referer( 'edc_clear_cache' );
	edc_page_cache_purge();
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
	exit;
} );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'edc-cache clear', function () {
		edc_page_cache_purge();
		WP_CLI::success( 'Page cache cleared.' );
	} );
}
