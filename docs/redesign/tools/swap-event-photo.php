<?php
/**
 * Replace the main photo of one About-page event, matched by name.
 *
 *   wp eval-file docs/redesign/tools/swap-event-photo.php <image-file> <event-name-contains> "<alt text>"
 *
 * The image file is looked up next to this script's screenshots folder
 * (docs/redesign/reference/prototype/assets/shots). The old image stays in the Media Library.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}
list( $file, $match, $alt ) = array_pad( (array) $args, 3, '' );
if ( ! $file || ! $match ) {
	WP_CLI::error( 'Usage: wp eval-file swap-event-photo.php <image-file> <event-name-contains> "<alt>"' );
}
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$path = realpath( __DIR__ . '/../reference/prototype/assets/shots/' . basename( $file ) );
if ( ! $path ) {
	WP_CLI::error( "Image not found: {$file}" );
}

$about = get_posts( array(
	'post_type'      => 'page',
	'post_status'    => 'any',
	'meta_key'       => '_wp_page_template',
	'meta_value'     => 'page-templates/about.php',
	'posts_per_page' => 1,
	'fields'         => 'ids',
) );
if ( ! $about ) {
	WP_CLI::error( 'No page uses the About template.' );
}
$page_id = (int) $about[0];

$row_index = 0;
foreach ( (array) get_field( 'about_events', $page_id ) as $i => $row ) {
	if ( false !== stripos( (string) ( $row['name'] ?? '' ), $match ) ) {
		$row_index = $i + 1; // ACF rows are 1-based.
		break;
	}
}
if ( ! $row_index ) {
	WP_CLI::error( "No event named like \"{$match}\"." );
}

$found = get_posts( array(
	'post_type'      => 'attachment',
	'post_status'    => 'inherit',
	'meta_key'       => '_edc_seed_source',
	'meta_value'     => basename( $file ),
	'posts_per_page' => 1,
	'fields'         => 'ids',
) );
if ( $found ) {
	$image_id = (int) $found[0];
} else {
	$tmp = wp_tempnam( basename( $file ) );
	copy( $path, $tmp );
	$image_id = media_handle_sideload( array( 'name' => basename( $file ), 'tmp_name' => $tmp ), $page_id );
	if ( is_wp_error( $image_id ) ) {
		WP_CLI::error( $image_id->get_error_message() );
	}
	update_post_meta( $image_id, '_edc_seed_source', basename( $file ) );
	WP_CLI::log( "Imported image #{$image_id}: " . basename( $file ) );
}
if ( $alt ) {
	update_post_meta( $image_id, '_wp_attachment_image_alt', $alt );
}

update_sub_field( array( 'about_events', $row_index, 'photo' ), $image_id, $page_id );
WP_CLI::success( "Event row {$row_index} now uses image #{$image_id}." );
