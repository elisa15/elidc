<?php
/**
 * After the content import: switch on the Work section, point the primary menu at
 * the new pages, assign the page templates and flush permalinks. Idempotent; prints
 * every old value it changes so it can be put back by hand.
 *
 *   wp eval-file docs/redesign/tools/live-settings.php
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Run with: wp eval-file docs/redesign/tools/live-settings.php\n" );
}

$dry = defined( 'EDC_DRY_RUN' ) && EDC_DRY_RUN;
$say = function ( $msg ) use ( $dry ) {
	WP_CLI::log( ( $dry ? '[dry run] ' : '' ) . $msg );
};

/* Work section: only on once there is a published featured project to show. */
if ( function_exists( 'edc_home_featured_project_ids' ) && edc_home_featured_project_ids() ) {
	$old = get_field( 'show_work', 'option' );
	if ( ! $old ) {
		$say( 'show_work: ' . var_export( $old, true ) . ' → true' );
		if ( ! $dry ) {
			update_field( 'show_work', 1, 'option' );
		}
	}
} else {
	WP_CLI::warning( 'No published featured projects: leaving show_work as it is.' );
}

/* Page templates on the existing pages (same URLs, so no redirects). */
foreach ( array( 'about', 'services', 'contact' ) as $slug ) {
	$page = get_page_by_path( $slug );
	if ( ! $page ) {
		WP_CLI::warning( "No /{$slug}/ page." );
		continue;
	}
	$template = "page-templates/{$slug}.php";
	$old      = get_page_template_slug( $page );
	if ( $old !== $template ) {
		$say( "/{$slug}/ template: '" . $old . "' → '{$template}'" );
		if ( ! $dry ) {
			update_post_meta( $page->ID, '_wp_page_template', $template );
		}
	}
}

/* Primary menu: homepage anchors → the new pages. Matched by title; nothing is deleted. */
$targets   = array(
	'projects' => get_post_type_archive_link( 'project' ),
	'work'     => get_post_type_archive_link( 'project' ),
	'services' => home_url( '/services/' ),
	'about'    => home_url( '/about/' ),
	'contact'  => home_url( '/contact/' ),
);
$locations = get_nav_menu_locations();
if ( ! empty( $locations['primary'] ) ) {
	foreach ( (array) wp_get_nav_menu_items( $locations['primary'] ) as $item ) {
		$key = strtolower( trim( $item->title ) );
		if ( 'custom' !== $item->type || ! isset( $targets[ $key ] ) || untrailingslashit( $item->url ) === untrailingslashit( $targets[ $key ] ) ) {
			continue;
		}
		$title = 'projects' === $key ? 'Work' : $item->title;
		$say( "Menu item #{$item->ID} '{$item->title}': {$item->url} → {$targets[ $key ]}" . ( $title !== $item->title ? " (title → {$title})" : '' ) );
		if ( ! $dry ) {
			update_post_meta( $item->ID, '_menu_item_url', esc_url_raw( $targets[ $key ] ) );
			if ( $title !== $item->title ) {
				wp_update_post( array( 'ID' => $item->ID, 'post_title' => $title ) );
			}
		}
	}
	// Order as in the design: Work · Services · About · Contact (anything else keeps its place after Home).
	$rank  = array( 'home' => 0, 'work' => 1, 'services' => 2, 'about' => 3, 'contact' => 4 );
	$items = (array) wp_get_nav_menu_items( $locations['primary'] );
	usort( $items, function ( $a, $b ) use ( $rank ) {
		$x = isset( $rank[ strtolower( trim( $a->title ) ) ] ) ? $rank[ strtolower( trim( $a->title ) ) ] : 5;
		$y = isset( $rank[ strtolower( trim( $b->title ) ) ] ) ? $rank[ strtolower( trim( $b->title ) ) ] : 5;
		return $x === $y ? $a->menu_order - $b->menu_order : $x - $y;
	} );
	foreach ( $items as $i => $item ) {
		if ( (int) $item->menu_order !== $i + 1 ) {
			$say( "Menu item '{$item->title}': position {$item->menu_order} → " . ( $i + 1 ) );
			if ( ! $dry ) {
				wp_update_post( array( 'ID' => $item->ID, 'menu_order' => $i + 1 ) );
			}
		}
	}

	foreach ( (array) wp_get_nav_menu_items( $locations['primary'] ) as $item ) {
		if ( 'home' === strtolower( trim( $item->title ) ) && false !== strpos( $item->url, '#top' ) ) {
			WP_CLI::log( "Note: menu item #{$item->ID} 'Home' (#top) is kept; the logo already links home. Remove it in Appearance → Menus if you like." );
		}
	}
} else {
	WP_CLI::log( 'No menu assigned to primary: the theme fallback menu is used.' );
}

if ( ! $dry ) {
	flush_rewrite_rules( false );
}
WP_CLI::success( $dry ? 'Dry run finished.' : 'Live settings applied.' );
