<?php
/**
 * Template helpers for the B− redesign. Every helper returns an empty value when
 * content is missing; templates check for empty before printing markup.
 * See docs/redesign/03-acf-schema.md §3.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A Profile & Links option.
 */
function edc_profile( $name, $default = '' ) {
	return edc_opt( $name, $default );
}

/**
 * URL of the uploaded CV, or ''.
 */
function edc_cv_url() {
	$file = edc_opt( 'cv_file', '' );
	if ( is_array( $file ) ) {
		$file = isset( $file['url'] ) ? $file['url'] : '';
	} elseif ( is_numeric( $file ) ) {
		$file = wp_get_attachment_url( (int) $file );
	}
	return is_string( $file ) ? trim( $file ) : '';
}

/**
 * Year range: '2020–22', '2022 →' (end = now), '2019' (same year or no end), or ''.
 */
function edc_years( $start, $end = '' ) {
	$start = trim( (string) $start );
	if ( ! preg_match( '/^\d{4}$/', $start ) ) {
		return '';
	}

	$end = strtolower( trim( (string) $end ) );
	if ( 'now' === $end ) {
		return $start . ' →';
	}
	if ( ! preg_match( '/^\d{4}$/', $end ) || $end === $start ) {
		return $start;
	}

	$short = substr( $start, 0, 2 ) === substr( $end, 0, 2 ) ? substr( $end, 2 ) : $end;
	return $start . '–' . $short;
}

/**
 * Sanitise an "Allowed HTML" field: only em[class], strong and br survive.
 */
function edc_inline_html( $html ) {
	return wp_kses(
		(string) $html,
		array(
			'em'     => array( 'class' => true ),
			'strong' => array(),
			'br'     => array(),
		)
	);
}

/**
 * Repeater rows that have a non-empty value for every key in $required.
 */
function edc_filled_rows( $rows, $required ) {
	$out = array();
	foreach ( (array) $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		foreach ( (array) $required as $key ) {
			if ( ! isset( $row[ $key ] ) || '' === trim( (string) $row[ $key ] ) ) {
				continue 2;
			}
		}
		$out[] = $row;
	}
	return $out;
}

/**
 * Terms of a taxonomy on a post as array( array( 'slug' => …, 'name' => … ) ).
 */
function edc_post_term_list( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return array();
	}
	$out = array();
	foreach ( $terms as $term ) {
		$out[] = array(
			'slug' => $term->slug,
			'name' => $term->name,
		);
	}
	return $out;
}

/**
 * Whether a project has a case study page.
 */
function edc_project_has_case_study( $post_id ) {
	return (bool) edc_field( 'has_case_study', false, $post_id );
}

/**
 * Normalised data for one project, or array() when the post is not a project.
 */
function edc_project( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || 'project' !== $post->post_type ) {
		return array();
	}
	$id = (int) $post->ID;

	$text = function ( $name ) use ( $id ) {
		$value = edc_field( $name, '', $id );
		return is_scalar( $value ) ? trim( (string) $value ) : '';
	};

	$confidential = (bool) edc_field( 'confidential', false, $id );
	$years        = edc_years( $text( 'year_start' ), $text( 'year_end' ) );

	$platforms = edc_post_term_list( $id, 'project_platform' );
	$order     = array_flip( array( 'shopify', 'wordpress', 'woocommerce', 'elementor' ) );
	usort( $platforms, function ( $a, $b ) use ( $order ) {
		$x = isset( $order[ $a['slug'] ] ) ? $order[ $a['slug'] ] : 99;
		$y = isset( $order[ $b['slug'] ] ) ? $order[ $b['slug'] ] : 99;
		return $x - $y;
	} );
	if ( empty( $platforms ) && '' !== $text( 'platform_badge' ) ) {
		$platforms = array(
			array(
				'slug' => sanitize_title( $text( 'platform_badge' ) ),
				'name' => $text( 'platform_badge' ),
			),
		);
	}

	$chips = array();
	foreach ( edc_filled_rows( edc_repeater( 'chips', array(), $id ), array( 'label' ) ) as $row ) {
		$chips[] = trim( $row['label'] );
	}

	$names = array();
	foreach ( edc_filled_rows( edc_repeater( 'names', array(), $id ), array( 'name' ) ) as $row ) {
		$names[] = array(
			'name' => trim( $row['name'] ),
			'note' => isset( $row['note'] ) ? trim( (string) $row['note'] ) : '',
		);
	}

	$behind_rows = array();
	foreach ( edc_filled_rows( edc_repeater( 'behind_rows', array(), $id ), array( 'key', 'value' ) ) as $row ) {
		$behind_rows[] = array(
			'key'   => trim( $row['key'] ),
			'value' => trim( $row['value'] ),
		);
	}

	$summary = $text( 'summary' );
	if ( '' === $summary ) {
		$summary = trim( (string) $post->post_excerpt );
	}

	$span = $text( 'card_span' );
	if ( ! in_array( $span, array( 'wide', 'half', 'auto' ), true ) ) {
		$span = 'auto';
	}

	return array(
		'id'           => $id,
		'slug'         => $post->post_name,
		'title'        => get_the_title( $id ),
		'url'          => edc_project_has_case_study( $id ) ? get_permalink( $id ) : '',
		'kinds'        => edc_post_term_list( $id, 'project_type' ),
		'platforms'    => $platforms,
		'chips'        => $chips,
		'client'       => $text( 'client_display_name' ),
		'confidential' => $confidential,
		'industry'     => $text( 'industry_badge' ),
		'region'       => $text( 'region' ),
		'role'         => $text( 'role' ),
		'role_line'    => implode( ' · ', array_filter( array( $text( 'role' ), $text( 'engaged_via' ), $years ) ) ),
		'years'        => $years,
		'summary'      => $summary,
		'names'        => $names,
		'span'         => $span,
		'visual'       => edc_project_visual( $id, $confidential ),
		'live_url'     => $confidential ? '' : $text( 'live_url' ),
		'behind_meta'  => $text( 'behind_meta' ),
		'behind_rows'  => $behind_rows,
	);
}

/**
 * Visual settings for a project card. Confidential projects never get screenshots
 * and never show a domain-like browser label.
 */
function edc_project_visual( $id, $confidential ) {
	$mode = edc_field( 'visual_mode', 'screenshot', $id );
	if ( ! in_array( $mode, array( 'screenshot', 'split', 'illustration', 'terminal' ), true ) ) {
		$mode = 'screenshot';
	}

	$illustration  = (string) edc_field( 'illustration', '', $id );
	$terminal_line = trim( (string) edc_field( 'terminal_line', '', $id ) );
	$label         = trim( (string) edc_field( 'browser_label', '', $id ) );

	$images = array();
	if ( ! $confidential ) {
		foreach ( array( 'screenshot', 'screenshot_secondary' ) as $name ) {
			$image_id = (int) edc_field( $name, 0, $id );
			if ( $image_id && wp_attachment_is_image( $image_id ) ) {
				$images[] = $image_id;
			}
		}
	}

	if ( $confidential && in_array( $mode, array( 'screenshot', 'split' ), true ) ) {
		$mode = '' !== $illustration ? 'illustration' : 'terminal';
	}
	if ( 'split' === $mode && count( $images ) < 2 ) {
		$mode = 'screenshot';
	}
	if ( 'screenshot' === $mode && empty( $images ) ) {
		$mode = 'none';
	}
	if ( 'split' !== $mode ) {
		$images = array_slice( $images, 0, 1 );
	}
	if ( 'illustration' === $mode && ! preg_match( '/^[a-z0-9-]+$/', $illustration ) ) {
		$mode = 'none';
	}
	if ( 'terminal' === $mode && '' === $terminal_line ) {
		$mode = 'none';
	}

	if ( $confidential && preg_match( '~(https?:|www\.|[a-z0-9-]+\.[a-z]{2,})~i', $label ) ) {
		$label = '';
	}

	return array(
		'mode'          => $mode,
		'images'        => in_array( $mode, array( 'screenshot', 'split' ), true ) ? $images : array(),
		'illustration'  => 'illustration' === $mode ? $illustration : '',
		'terminal_line' => 'terminal' === $mode ? $terminal_line : '',
		'browser_label' => $label,
		'badge'         => trim( (string) edc_field( 'visual_badge', '', $id ) ),
		'gradient'      => array(
			'start' => (string) edc_field( 'gradient_start', '', $id ),
			'end'   => (string) edc_field( 'gradient_end', '', $id ),
		),
	);
}

/**
 * Normalised testimonial, or null when missing, unpublished, without permission or without a quote.
 */
function edc_testimonial( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || 'testimonial' !== $post->post_type || 'publish' !== $post->post_status ) {
		return null;
	}
	$id = (int) $post->ID;

	if ( ! edc_field( 'permission_confirmed', false, $id ) ) {
		return null;
	}

	$text = function ( $name, $default = '' ) use ( $id ) {
		$value = edc_field( $name, $default, $id );
		return is_scalar( $value ) ? trim( (string) $value ) : '';
	};

	$quote = $text( 'quote' );
	$name  = $text( 'person_name' );
	if ( '' === $quote || '' === $name ) {
		return null;
	}

	$relationship = 'employer' === $text( 'relationship' ) ? 'employer' : 'client';

	return array(
		'id'           => $id,
		'quote'        => $quote,
		'quote_short'  => $text( 'quote_short' ),
		'name'         => $name,
		'role'         => $text( 'person_role' ),
		'company'      => $text( 'company' ),
		'relationship' => $relationship,
		'kind_label'   => '$ role: ' . $relationship,
		'source_url'   => $text( 'source_url' ),
		'source_label' => $text( 'source_label', 'Full recommendation on LinkedIn ↗' ),
	);
}

/**
 * Escaped attribution for a testimonial: "<b>Name</b> · Role, Company", skipping empty parts.
 */
function edc_testimonial_attribution( $testimonial ) {
	if ( empty( $testimonial['name'] ) ) {
		return '';
	}
	$detail = implode( ', ', array_filter( array( $testimonial['role'], $testimonial['company'] ) ) );
	return '<b>' . esc_html( $testimonial['name'] ) . '</b>' . ( '' !== $detail ? ' · ' . esc_html( $detail ) : '' );
}

/**
 * Career log rows (newest first) with a 7-character hash on every row.
 */
function edc_career_log() {
	$rows = array();
	foreach ( edc_filled_rows( edc_repeater( 'career_log', array() ), array( 'message' ) ) as $row ) {
		$hash = isset( $row['hash'] ) ? preg_replace( '/[^a-f0-9]/i', '', (string) $row['hash'] ) : '';
		$when = isset( $row['when'] ) ? trim( (string) $row['when'] ) : '';

		$row['hash']    = '' !== $hash ? strtolower( substr( $hash, 0, 7 ) ) : substr( md5( trim( $row['message'] ) . $when ), 0, 7 );
		$row['is_head'] = ! empty( $row['is_head'] );
		$rows[]         = $row;
	}
	return $rows;
}

/**
 * Homepage stats that are marked verified and have both a value and a label.
 */
function edc_verified_stats() {
	$out = array();
	foreach ( edc_filled_rows( edc_repeater( 'home_stats', array() ), array( 'value', 'label' ) ) as $row ) {
		if ( ! empty( $row['verified'] ) ) {
			$out[] = $row;
		}
	}
	return $out;
}

/**
 * A WYSIWYG field as sanitised HTML, or '' when it holds no visible text.
 */
function edc_rich_field( $name, $post_id = null ) {
	$value = edc_field( $name, '', $post_id );
	if ( ! is_string( $value ) || '' === trim( wp_strip_all_tags( $value ) ) ) {
		return '';
	}
	return wp_kses_post( $value );
}

/**
 * Lines of a textarea field, trimmed, empties dropped.
 */
function edc_lines( $text ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\R/', (string) $text ) ), 'strlen' ) );
}
