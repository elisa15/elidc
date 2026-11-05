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

/**
 * The next featured project with a case study (by menu order, wrapping to the first), or 0.
 */
function edc_next_case_study( $post_id ) {
	$ids = get_posts( array(
		'post_type'        => 'project',
		'post_status'      => 'publish',
		'posts_per_page'   => -1,
		'fields'           => 'ids',
		'orderby'          => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'suppress_filters' => false,
	) );
	$ids = array_values( array_filter( $ids, function ( $id ) {
		return edc_project_has_case_study( $id ) && 'featured' === edc_field( 'tier', 'selected', $id );
	} ) );

	$at = array_search( (int) $post_id, array_map( 'intval', $ids ), true );
	if ( count( $ids ) < 2 && false !== $at ) {
		return 0;
	}
	if ( false === $at ) {
		return $ids ? (int) $ids[0] : 0;
	}
	return (int) $ids[ ( $at + 1 ) % count( $ids ) ];
}

/**
 * CreativeWork schema for a case study, authored by the Person Yoast outputs.
 * Confidential projects use their display label only (never a real client name).
 */
function edc_case_study_schema( $graph ) {
	if ( ! is_singular( 'project' ) || ! is_array( $graph ) ) {
		return $graph;
	}
	$project = edc_project( get_queried_object_id() );
	if ( ! $project ) {
		return $graph;
	}

	$person = '';
	foreach ( $graph as $piece ) {
		if ( isset( $piece['@type'], $piece['@id'] ) && in_array( 'Person', (array) $piece['@type'], true ) ) {
			$person = $piece['@id'];
			break;
		}
	}

	$headline = trim( wp_strip_all_tags( (string) edc_field( 'cs_headline', '', $project['id'] ) ) );
	$work     = array(
		'@type'  => 'CreativeWork',
		'@id'    => get_permalink( $project['id'] ) . '#case-study',
		'name'   => '' !== $headline ? $headline : $project['title'],
		'url'    => get_permalink( $project['id'] ),
		'about'  => implode( ', ', array_filter( array_merge( wp_list_pluck( $project['platforms'], 'name' ), array( $project['industry'] ) ) ) ),
		'author' => $person ? array( '@id' => $person ) : array( '@type' => 'Person', 'name' => edc_profile( 'profile_name', 'Elisa A. Dela Cruz' ) ),
	);
	if ( '' !== $project['summary'] ) {
		$work['description'] = $project['summary'];
	}
	if ( $project['client'] && ! $project['confidential'] ) {
		$work['sourceOrganization'] = array( '@type' => 'Organization', 'name' => $project['client'] );
	}
	if ( '' === $work['about'] ) {
		unset( $work['about'] );
	}

	$graph[] = $work;
	return $graph;
}
add_filter( 'wpseo_schema_graph', 'edc_case_study_schema' );

/**
 * Grouped project cards that were split up (Nov 2026): send their old URLs to the new cards.
 */
function edc_project_retired_slug_redirect() {
	if ( ! is_404() ) {
		return;
	}
	$map  = array(
		'three-wineries'     => 'boat-ocraigo',
		'perkrunner-winehub' => 'winehub',
		'cenit-oasis'        => 'cenit-lighting',
	);
	$path = trim( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ), '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( preg_match( '#^projects/([a-z0-9-]+)$#', $path, $m ) && isset( $map[ $m[1] ] ) ) {
		wp_safe_redirect( get_post_type_archive_link( 'project' ) . '#project-' . $map[ $m[1] ], 301 );
		exit;
	}
}
add_action( 'template_redirect', 'edc_project_retired_slug_redirect', 5 );
