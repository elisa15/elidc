<?php
/**
 * /projects/ archive query, filters and the case-study redirect.
 * See docs/redesign/04-build-spec.md §2.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Valid filter slugs from the request: array( 'type' => slug|'', 'platform' => slug|'' ).
 * Anything that isn't a real term (including "all") is ignored.
 */
function edc_project_filters() {
	$map     = array(
		'type'     => 'project_type',
		'platform' => 'project_platform',
	);
	$filters = array();
	foreach ( $map as $param => $taxonomy ) {
		$slug = isset( $_GET[ $param ] ) ? sanitize_key( wp_unslash( $_GET[ $param ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$filters[ $param ] = ( '' !== $slug && term_exists( $slug, $taxonomy ) ) ? $slug : '';
	}
	return $filters;
}

function edc_projects_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'project' ) ) {
		return;
	}

	$query->set( 'posts_per_page', -1 );
	$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );

	$filters   = edc_project_filters();
	$tax_query = array();
	if ( '' !== $filters['type'] ) {
		$tax_query[] = array( 'taxonomy' => 'project_type', 'field' => 'slug', 'terms' => $filters['type'] );
	}
	if ( '' !== $filters['platform'] ) {
		$tax_query[] = array( 'taxonomy' => 'project_platform', 'field' => 'slug', 'terms' => $filters['platform'] );
	}
	if ( $tax_query ) {
		$tax_query['relation'] = 'AND';
		$query->set( 'tax_query', $tax_query );
	}
}
add_action( 'pre_get_posts', 'edc_projects_archive_query' );

/**
 * Projects without a case study have no page of their own: send visitors to their card.
 */
function edc_project_case_study_redirect() {
	if ( ! is_singular( 'project' ) ) {
		return;
	}
	$post = get_queried_object();
	if ( ! $post || edc_project_has_case_study( $post->ID ) ) {
		return;
	}
	wp_safe_redirect( get_post_type_archive_link( 'project' ) . '#project-' . $post->post_name, 302 );
	exit;
}
add_action( 'template_redirect', 'edc_project_case_study_redirect' );

/**
 * Only the unfiltered /projects/ archive is indexable.
 */
function edc_project_filter_is_requested() {
	return is_post_type_archive( 'project' ) && ( isset( $_GET['type'] ) || isset( $_GET['platform'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

function edc_projects_filtered_noindex( $robots ) {
	if ( edc_project_filter_is_requested() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'] );
	}
	return $robots;
}
add_filter( 'wp_robots', 'edc_projects_filtered_noindex' );

function edc_projects_filtered_noindex_yoast( $robots ) {
	if ( edc_project_filter_is_requested() && is_array( $robots ) ) {
		$robots['index'] = 'noindex';
	}
	return $robots;
}
add_filter( 'wpseo_robots_array', 'edc_projects_filtered_noindex_yoast' );
