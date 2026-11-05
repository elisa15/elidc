<?php
/**
 * SEO helpers: name aliases + service topics for discoverability.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common public name variants.
 *
 * @return string[]
 */
function edc_person_alternate_names() {
	return array(
		'Elisa Armas Dela Cruz',
		'Elisa A. Dela Cruz',
		'Elisa Armas',
		'Eli Dela Cruz',
		'Eli DC',
	);
}

/**
 * Service topics people search for.
 *
 * @return string[]
 */
function edc_person_knows_about() {
	return array(
		'Web developer',
		'WordPress developer',
		'Shopify developer',
		'Shopify migration',
		'Programmer',
		'WordPress specialist',
		'Shopify expert',
		'WordPress security',
		'WooCommerce migration',
		'Technical SEO',
		'Performance optimisation',
	);
}

/**
 * Profile URLs for schema sameAs, taken from the footer social links in ACF.
 * Only real http(s) profiles count; email and "#" placeholders are skipped.
 *
 * @return string[]
 */
function edc_person_same_as() {
	if ( ! function_exists( 'edc_repeater' ) ) {
		return array();
	}
	$urls = array();
	foreach ( array( 'linkedin_url', 'github_url' ) as $field ) {
		$urls[] = trim( (string) edc_opt( $field, '' ) );
	}
	foreach ( edc_repeater( 'footer_social', array() ) as $social ) {
		$urls[] = isset( $social['url'] ) ? trim( (string) $social['url'] ) : '';
	}
	$out = array();
	foreach ( $urls as $url ) {
		if ( preg_match( '#^https?://#i', $url ) ) {
			$out[] = esc_url_raw( $url );
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * Enrich Yoast Person schema with aliases, role, and topics.
 *
 * @param array $data Person graph piece.
 * @return array
 */
function edc_enrich_person_schema( $data ) {
	if ( ! is_array( $data ) ) {
		return $data;
	}

	$data['name'] = edc_opt( 'profile_name', ! empty( $data['name'] ) ? $data['name'] : 'Elisa A. Dela Cruz' );

	$names = edc_person_alternate_names();
	if ( ! empty( $data['name'] ) ) {
		$primary = $data['name'];
		$names   = array_values(
			array_filter(
				array_unique( $names ),
				static function ( $n ) use ( $primary ) {
					return $n !== $primary;
				}
			)
		);
	}
	$data['alternateName'] = $names;
	$data['jobTitle']     = edc_opt( 'profile_title', 'Senior WordPress & Shopify Developer' );
	$data['description']  = 'Web developer and WordPress specialist focused on Shopify development, Shopify migrations, WordPress security, and custom builds.';
	$data['knowsAbout']   = edc_person_knows_about();

	$portrait = (int) edc_opt( 'portrait', 0 );
	if ( $portrait && wp_attachment_is_image( $portrait ) ) {
		$data['image'] = array(
			'@type'      => 'ImageObject',
			'url'        => wp_get_attachment_image_url( $portrait, 'full' ),
			'contentUrl' => wp_get_attachment_image_url( $portrait, 'full' ),
			'caption'    => $data['name'],
		);
	}

	$same_as = edc_person_same_as();
	if ( $same_as ) {
		$data['sameAs'] = array_values( array_unique( array_merge( isset( $data['sameAs'] ) ? (array) $data['sameAs'] : array(), $same_as ) ) );
	}

	return $data;
}
add_filter( 'wpseo_schema_person', 'edc_enrich_person_schema' );

/**
 * Also enrich Person pieces inside the full Yoast graph.
 *
 * @param array $data Graph piece.
 * @return array
 */
function edc_enrich_schema_graph_person( $data ) {
	if ( ! is_array( $data ) || empty( $data['@type'] ) ) {
		return $data;
	}
	$types = (array) $data['@type'];
	if ( ! in_array( 'Person', $types, true ) ) {
		return $data;
	}
	return edc_enrich_person_schema( $data );
}
add_filter( 'wpseo_schema_graph_piece', 'edc_enrich_schema_graph_person', 11, 1 );
