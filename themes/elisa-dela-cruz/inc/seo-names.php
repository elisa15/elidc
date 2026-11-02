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
	$data['jobTitle']     = 'WordPress & Shopify Developer';
	$data['description']  = 'Web developer and WordPress specialist focused on Shopify development, Shopify migrations, WordPress security, and custom builds.';
	$data['knowsAbout']   = edc_person_knows_about();

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
