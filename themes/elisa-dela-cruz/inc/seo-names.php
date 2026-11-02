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
	foreach ( edc_repeater( 'footer_social', array() ) as $social ) {
		$url = isset( $social['url'] ) ? trim( (string) $social['url'] ) : '';
		if ( preg_match( '#^https?://#i', $url ) ) {
			$urls[] = esc_url_raw( $url );
		}
	}
	return $urls;
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
	$data['jobTitle']     = 'Senior WordPress & Shopify Developer';
	$data['description']  = 'Web developer and WordPress specialist focused on Shopify development, Shopify migrations, WordPress security, and custom builds.';
	$data['knowsAbout']   = edc_person_knows_about();

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
