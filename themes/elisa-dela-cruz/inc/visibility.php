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
 * Published project IDs picked for the homepage Work section, in their chosen order.
 *
 * @return int[]
 */
function edc_home_featured_project_ids() {
	$ids = array();
	foreach ( (array) edc_opt( 'home_featured_projects', array() ) as $id ) {
		$id = (int) ( is_object( $id ) ? $id->ID : $id );
		if ( $id && 'project' === get_post_type( $id ) && 'publish' === get_post_status( $id ) ) {
			$ids[] = $id;
		}
	}
	return $ids;
}

/**
 * Whether the homepage Work section has at least one published featured project.
 */
function edc_has_published_projects() {
	static $has = null;
	if ( null === $has ) {
		$has = (bool) edc_home_featured_project_ids();
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
		'work'         => 'show_work',
		'services'     => 'show_home_called',
		'about'        => 'show_home_about',
		'track-record' => 'show_home_record',
		'contact'      => 'show_cta',
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
	// Admins previewing keep links to hidden sections, but nobody gets an empty or "#" link.
	$is_admin_preview = is_user_logged_in() && current_user_can( 'manage_options' );
	$filtered         = array();
	foreach ( $items as $item ) {
		$url = isset( $item->url ) ? trim( (string) $item->url ) : '';
		if ( '' === $url || '#' === $url ) {
			continue;
		}
		if ( $is_admin_preview || edc_nav_target_is_public( $url ) ) {
			$filtered[] = $item;
		}
	}
	return $filtered;
}
add_filter( 'wp_nav_menu_objects', 'edc_filter_nav_menu_items', 20 );

/**
 * Default site navigation: Work · Services · About · Contact. Each item is listed
 * only when its target exists, so no link ever leads nowhere.
 *
 * @return array<int,array{label:string,url:string,current:bool}>
 */
function edc_primary_nav_items() {
	$items  = array();
	$counts = wp_count_posts( 'project' );
	if ( $counts && ! empty( $counts->publish ) ) {
		$items[] = array(
			'label'   => 'Work',
			'url'     => get_post_type_archive_link( 'project' ),
			'current' => is_post_type_archive( 'project' ) || is_singular( 'project' ),
		);
	}
	foreach ( array( 'services' => 'Services', 'about' => 'About', 'contact' => 'Contact' ) as $slug => $label ) {
		$page = get_page_by_path( $slug );
		if ( ! $page || 'publish' !== $page->post_status ) {
			continue;
		}
		$items[] = array(
			'label'   => $label,
			'url'     => get_permalink( $page ),
			'current' => is_page( $page->ID ),
		);
	}
	return $items;
}

/**
 * Where "Start a project" goes without JS: the Contact page, else the homepage contact section.
 */
function edc_contact_url() {
	$page = get_page_by_path( 'contact' );
	if ( $page && 'publish' === $page->post_status ) {
		return get_permalink( $page );
	}
	return edc_section_is_public( 'show_cta' ) ? home_url( '/#contact' ) : '';
}

/**
 * "Start a project" nav button as a list item, or '' when it has nowhere to go.
 */
function edc_nav_cta_item() {
	$url   = edc_contact_url();
	$modal = edc_should_show_section( 'show_contact_modal' );
	if ( '' === $url && ! $modal ) {
		return '';
	}
	return sprintf(
		'<li><a class="btn btn-primary%s" href="%s" data-ga-event="cta_start_project" data-ga-label="Nav Start a project">%s</a></li>',
		$modal ? ' js-open-contact' : '',
		esc_url( '' !== $url ? $url : home_url( '/' ) ),
		esc_html( edc_opt( 'nav_cta_label', 'Start a project' ) )
	);
}

/**
 * Primary menu fallback (no menu assigned to the "primary" location).
 */
function edc_primary_menu_fallback() {
	echo '<ul id="menu">';
	foreach ( edc_primary_nav_items() as $item ) {
		printf(
			'<li><a href="%s"%s>%s</a></li>',
			esc_url( $item['url'] ),
			$item['current'] ? ' aria-current="page"' : '',
			esc_html( $item['label'] )
		);
	}
	echo edc_nav_cta_item(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in edc_nav_cta_item().
	echo '</ul>';
}

/**
 * Footer site links: the "footer" menu when assigned, else the primary items.
 *
 * @return array<int,array{label:string,url:string,current:bool}>
 */
function edc_footer_nav_items() {
	$locations = get_nav_menu_locations();
	if ( empty( $locations['footer'] ) ) {
		return edc_primary_nav_items();
	}

	$items = array();
	$menu  = wp_get_nav_menu_items( $locations['footer'] );
	foreach ( edc_filter_nav_menu_items( $menu ? $menu : array() ) as $item ) {
		if ( ! empty( $item->menu_item_parent ) ) {
			continue;
		}
		$items[] = array(
			'label'   => $item->title,
			'url'     => $item->url,
			'current' => untrailingslashit( $item->url ) === untrailingslashit( home_url( add_query_arg( array() ) ) ),
		);
	}
	return $items;
}

/**
 * URL of a row in the legacy footer social repeater, or ''.
 */
function edc_footer_social_url( $type ) {
	foreach ( edc_repeater( 'footer_social', array() ) as $row ) {
		if ( isset( $row['type'], $row['url'] ) && $type === $row['type'] && edc_link_is_usable( $row['url'] ) ) {
			return trim( $row['url'] );
		}
	}
	return '';
}

/**
 * CV, LinkedIn, GitHub and Email links for the footer; each only when set.
 *
 * @return array<int,array{label:string,url:string,attrs:string}>
 */
function edc_footer_elsewhere_links() {
	$email = trim( (string) edc_opt( 'contact_email', '' ) );
	$email = is_email( $email ) ? 'mailto:' . $email : edc_footer_social_url( 'email' );

	$links = array(
		array( 'CV ↓', edc_cv_url(), ' download data-ga-event="cv_download" data-ga-label="Footer CV"' ),
		array( 'LinkedIn', edc_profile( 'linkedin_url' ) ? edc_profile( 'linkedin_url' ) : edc_footer_social_url( 'linkedin' ), ' rel="me noopener"' ),
		array( 'GitHub', edc_profile( 'github_url' ) ? edc_profile( 'github_url' ) : edc_footer_social_url( 'github' ), ' rel="me noopener"' ),
		array( 'Email', $email, '' ),
	);

	$out = array();
	foreach ( $links as $link ) {
		if ( edc_link_is_usable( $link[1] ) ) {
			$out[] = array(
				'label' => $link[0],
				'url'   => trim( $link[1] ),
				'attrs' => $link[2],
			);
		}
	}
	return $out;
}

/**
 * Width and height attributes for a theme or Media Library image URL, or ''.
 */
function edc_image_size_attrs( $url ) {
	$id = attachment_url_to_postid( $url );
	if ( $id ) {
		$src = wp_get_attachment_image_src( $id, 'full' );
		if ( $src ) {
			return sprintf( ' width="%d" height="%d"', $src[1], $src[2] );
		}
	}
	if ( 0 === strpos( $url, EDC_URI ) ) {
		$path = EDC_DIR . substr( $url, strlen( EDC_URI ) );
		$size = is_readable( $path ) ? getimagesize( $path ) : false;
		if ( $size ) {
			return sprintf( ' width="%d" height="%d"', $size[0], $size[1] );
		}
	}
	return '';
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
	// Local copies keep the dataLayer for debugging but never send hits to the live property.
	$send_hits = 'local' !== wp_get_environment_type();
	?>
<!-- Google tag (gtag.js) -->
<?php if ( $send_hits ) : ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo rawurlencode( $measurement_id ); ?>"></script>
<?php endif; ?>
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
