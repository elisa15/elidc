<?php
/**
 * Sync the theme's ACF local JSON into the database, the same as
 * ACF → Field Groups → "Sync changes", for every group whose JSON is newer.
 *
 *   wp eval-file docs/redesign/tools/acf-sync.php
 *
 * Prints what it did. Run it with `--exec="define('EDC_DRY_RUN', true);"` to only report.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Run with: wp eval-file docs/redesign/tools/acf-sync.php\n" );
}
if ( ! function_exists( 'acf_import_field_group' ) ) {
	WP_CLI::error( 'ACF PRO must be active.' );
}

$dry = defined( 'EDC_DRY_RUN' ) && EDC_DRY_RUN;
$dir = get_stylesheet_directory() . '/acf-json';

// Import into the database only: don't let ACF rewrite the theme's JSON files.
add_filter( 'acf/settings/save_json', '__return_empty_string', 99 );
add_filter( 'acf/settings/json', '__return_false', 99 );

foreach ( glob( $dir . '/group_*.json' ) as $file ) {
	$group = json_decode( (string) file_get_contents( $file ), true );
	if ( empty( $group['key'] ) ) {
		WP_CLI::warning( 'Skipped (not a field group): ' . basename( $file ) );
		continue;
	}

	$db       = acf_get_field_group_post( $group['key'] );
	$db_mod   = $db ? (int) get_post_meta( $db->ID, '_edc_json_modified', true ) : 0;
	$json_mod = isset( $group['modified'] ) ? (int) $group['modified'] : 0;
	$db_time  = $db ? strtotime( $db->post_modified_gmt . ' UTC' ) : 0;

	if ( $db && $json_mod && $json_mod <= max( $db_mod, $db_time ) ) {
		WP_CLI::log( "Up to date: {$group['key']} ({$group['title']})" );
		continue;
	}

	$action = $db ? 'Update' : 'Create';
	if ( $dry ) {
		WP_CLI::log( "Would {$action}: {$group['key']} ({$group['title']})" );
		continue;
	}

	if ( $db ) {
		$group['ID'] = $db->ID;
	}
	$imported = acf_import_field_group( $group );
	update_post_meta( $imported['ID'], '_edc_json_modified', $json_mod );
	WP_CLI::log( "{$action}d: {$group['key']} ({$group['title']}) → post #{$imported['ID']}, " . count( acf_get_fields( $imported['key'] ) ) . ' fields' );
}

WP_CLI::success( $dry ? 'Dry run finished.' : 'ACF field groups synced.' );
