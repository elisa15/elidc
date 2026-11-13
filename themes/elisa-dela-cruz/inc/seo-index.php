<?php
/**
 * What search engines index: keep the sitemap to real, indexable pages, noindex the
 * utility and archive pages, send old filter URLs to /projects/, and give the main
 * pages search-friendly titles (only where no Yoast title has been set).
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Old taxonomy URLs (?taxonomy=project_platform&term=shopify, and their /page/N/ copies)
 * that Google indexed: send them to the matching /projects/ filter, which is noindex.
 */
function edc_seo_redirect_taxonomy_urls() {
	$map = array(
		'project_type'     => 'type',
		'project_platform' => 'platform',
	);
	$tax = isset( $_GET['taxonomy'] ) ? sanitize_key( wp_unslash( $_GET['taxonomy'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $map[ $tax ] ) ) {
		return;
	}
	$term   = isset( $_GET['term'] ) ? sanitize_title( wp_unslash( $_GET['term'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$target = get_post_type_archive_link( 'project' );
	if ( $target && '' !== $term && term_exists( $term, $tax ) ) {
		$target = add_query_arg( $map[ $tax ], $term, $target );
	}
	wp_safe_redirect( $target ? $target : home_url( '/' ), 301 );
	exit;
}
add_action( 'template_redirect', 'edc_seo_redirect_taxonomy_urls', 1 );

/**
 * Pages that should never be in search results.
 */
function edc_seo_is_noindex_request() {
	if ( is_category() || is_tag() || is_author() || is_date() || is_search() ) {
		return true;
	}
	if ( is_tax( array( 'project_type', 'project_platform' ) ) ) {
		return true;
	}
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() || is_shop() ) ) {
		return true;
	}
	return is_singular() && in_array( (int) get_queried_object_id(), edc_seo_hidden_post_ids(), true );
}

function edc_seo_noindex_yoast( $robots ) {
	if ( is_array( $robots ) && edc_seo_is_noindex_request() ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
	}
	return $robots;
}
add_filter( 'wpseo_robots_array', 'edc_seo_noindex_yoast' );

function edc_seo_noindex_core( $robots ) {
	if ( edc_seo_is_noindex_request() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'] );
	}
	return $robots;
}
add_filter( 'wp_robots', 'edc_seo_noindex_core' );

/**
 * Posts and pages kept out of search: WooCommerce utility pages and leftover
 * "Hello world!" sample posts.
 *
 * @return int[]
 */
function edc_seo_hidden_post_ids() {
	static $ids = null;
	if ( null !== $ids ) {
		return $ids;
	}
	$ids = array();
	if ( function_exists( 'wc_get_page_id' ) ) {
		foreach ( array( 'cart', 'checkout', 'myaccount', 'shop' ) as $page ) {
			$id = (int) wc_get_page_id( $page );
			if ( $id > 0 ) {
				$ids[] = $id;
			}
		}
	}
	$samples = get_posts( array(
		'post_type'        => 'post',
		'post_status'      => 'publish',
		'posts_per_page'   => 10,
		'fields'           => 'ids',
		'title'            => 'Hello world!',
		'suppress_filters' => true,
	) );
	$ids = array_map( 'intval', array_merge( $ids, $samples ) );
	return $ids;
}

/* ---------------------------------------------------------------------------
 * Yoast sitemap: only pages that load (no redirects) and are meant to rank.
 * ------------------------------------------------------------------------ */

add_filter( 'wpseo_sitemap_exclude_taxonomy', function ( $excluded, $taxonomy ) {
	return in_array( $taxonomy, array( 'project_type', 'project_platform', 'category', 'post_tag', 'product_cat', 'product_tag' ), true ) ? true : $excluded;
}, 10, 2 );

// Products have no pages of their own (they redirect to /fixes/).
add_filter( 'wpseo_sitemap_exclude_post_type', function ( $excluded, $post_type ) {
	return 'product' === $post_type ? true : $excluded;
}, 10, 2 );

// Author archives redirect to the home page (inc/security.php).
add_filter( 'wpseo_sitemap_exclude_author', '__return_empty_array' );

add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function ( $ids ) {
	return array_values( array_unique( array_merge( (array) $ids, edc_seo_hidden_post_ids() ) ) );
} );

// Projects without a case study redirect to their card on /projects/.
add_filter( 'wpseo_sitemap_entry', function ( $url, $type, $post ) {
	if ( 'post' === $type && $post instanceof WP_Post && 'project' === $post->post_type
		&& function_exists( 'edc_project_has_case_study' ) && ! edc_project_has_case_study( $post->ID ) ) {
		return false;
	}
	return $url;
}, 10, 3 );

/* ---------------------------------------------------------------------------
 * Titles with the words people search for. A title set in Yoast always wins.
 * ------------------------------------------------------------------------ */

/**
 * Fallback title for the current request, or ''.
 */
function edc_seo_title_fallback() {
	if ( is_post_type_archive( 'project' ) && ! edc_project_filter_is_requested() ) {
		return 'WordPress & Shopify Projects and Case Studies | Elisa Dela Cruz';
	}
	if ( ! is_page() ) {
		return '';
	}
	$id = (int) get_queried_object_id();
	if ( '' !== trim( (string) get_post_meta( $id, '_yoast_wpseo_title', true ) ) ) {
		return '';
	}
	$titles = array(
		'about'    => 'About Elisa Dela Cruz | WordPress & Shopify Developer, Manila',
		'services' => 'Shopify & WordPress Development Services | Elisa Dela Cruz',
		'fixes'    => 'Fixed-Price WordPress & Shopify Fixes | Elisa Dela Cruz',
		'contact'  => 'Contact Elisa Dela Cruz | WordPress & Shopify Developer',
	);
	$slug = (string) get_post_field( 'post_name', $id );
	return isset( $titles[ $slug ] ) ? $titles[ $slug ] : '';
}

function edc_seo_title( $title ) {
	$fallback = edc_seo_title_fallback();
	return '' !== $fallback ? $fallback : $title;
}
add_filter( 'wpseo_title', 'edc_seo_title' );
add_filter( 'wpseo_opengraph_title', 'edc_seo_title' );
