<?php
/**
 * Add The Win Win Services project, the graphic design gallery and the conferences
 * on the About page. CREATE-ONLY: anything that already has content is left alone,
 * so it is safe to run on the live site after Elisa's edits in wp-admin.
 *
 *   wp eval-file docs/redesign/tools/seed-showcase.php
 *
 * Images come from docs/redesign/reference/prototype/assets/shots (imported once).
 * Copy is Elisa's to review: see docs/redesign/05-content-entry.md, "Showcase (Nov 2026)".
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Run with: wp eval-file docs/redesign/tools/seed-showcase.php\n" );
}
if ( ! function_exists( 'update_field' ) ) {
	WP_CLI::error( 'ACF must be active.' );
}
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$edc_shots = (string) realpath( __DIR__ . '/../reference/prototype/assets/shots' );
if ( '' === $edc_shots ) {
	WP_CLI::error( 'Screenshots folder not found next to this script.' );
}

/** Import an image once (matched by file name), then reuse it. */
$img = function ( $file, $alt ) use ( $edc_shots ) {
	$found = get_posts( array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'meta_key'       => '_edc_seed_source',
		'meta_value'     => $file,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( $found ) {
		return (int) $found[0];
	}
	$tmp = wp_tempnam( $file );
	copy( $edc_shots . '/' . $file, $tmp );
	$id = media_handle_sideload( array( 'name' => $file, 'tmp_name' => $tmp ), 0 );
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( "Image {$file}: " . $id->get_error_message() );
	}
	update_post_meta( $id, '_edc_seed_source', $file );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	WP_CLI::log( "Imported image #{$id}: {$file}" );
	return (int) $id;
};

/** Field name → key for a group, so values land on the right ACF fields. */
$keys = function ( $group ) {
	$map = array();
	foreach ( (array) acf_get_fields( $group ) as $field ) {
		$map[ $field['name'] ] = $field['key'];
	}
	return $map;
};

/**
 * Write only the fields that are still empty. $force writes every value: used for a
 * post created in this run, where ACF reports field defaults as if they were filled.
 */
$fill = function ( $group, $target, $values, $force = false ) use ( $keys ) {
	$map = $keys( $group );
	foreach ( $values as $name => $value ) {
		if ( ! isset( $map[ $name ] ) ) {
			WP_CLI::warning( "No field {$name} in {$group}" );
			continue;
		}
		$current = $force ? null : get_field( $map[ $name ], $target, false );
		if ( ! empty( $current ) || ( is_string( $current ) && '' !== trim( $current ) ) ) {
			WP_CLI::log( "Kept {$name} (already filled)" );
			continue;
		}
		update_field( $map[ $name ], $value, $target );
		WP_CLI::log( "Filled {$name}" );
	}
};

/* ---------------------------------------------------------------------------
 * 1. The Win Win Services (Selected work)
 * ------------------------------------------------------------------------ */
$slug = 'the-win-win-services';
if ( get_page_by_path( $slug, OBJECT, 'project' ) ) {
	WP_CLI::log( 'Project exists, left as is: The Win Win Services' );
} else {
	$term_ids = function ( $taxonomy, $slugs ) {
		$ids = array();
		foreach ( $slugs as $s ) {
			$t = get_term_by( 'slug', $s, $taxonomy );
			if ( $t ) {
				$ids[] = (int) $t->term_id;
			}
		}
		return $ids;
	};
	$kinds     = $term_ids( 'project_type', array( 'built' ) );
	$platforms = $term_ids( 'project_platform', array( 'wordpress', 'elementor' ) );
	$id        = wp_insert_post( array(
		'post_type'   => 'project',
		'post_status' => 'publish',
		'post_title'  => 'The Win Win Services',
		'post_name'   => $slug,
		'menu_order'  => 9, // Ahead of the other Selected work (10+).
	), true );
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	wp_set_object_terms( $id, $kinds, 'project_type' );
	wp_set_object_terms( $id, $platforms, 'project_platform' );
	$fill( 'group_edc_project_fields', $id, array(
		'project_kind'        => $kinds,
		'project_platforms'   => $platforms,
		'tier'                => 'selected',
		'client_display_name' => 'The Win Win Services',
		'industry_badge'      => 'Home services',
		'region'              => 'Sarasota, Florida, US',
		'role'                => 'Website & brochure design',
		'summary'             => 'Website for a Sarasota home services company: home watch, cleaning, repairs and more, with an estimate request on every service. I also designed its tri-fold print brochure to match.',
		'chips'               => array( array( 'label' => 'Website' ), array( 'label' => 'Print brochure' ) ),
		'card_span'           => 'auto',
		'visual_mode'         => 'screenshot',
		'screenshot'          => $img( 'winwin-live.jpg', 'The Win Win Services homepage' ),
		'browser_label'       => 'thewinwinservices.com',
		'live_url'            => 'https://thewinwinservices.com/',
	), true );
	WP_CLI::log( "Created project #{$id}: The Win Win Services" );
}

/* ---------------------------------------------------------------------------
 * 2. Graphic design gallery (Site content options, shown on the Work page)
 * ------------------------------------------------------------------------ */
// array( file, alt, title, client, kind, note )
$pieces = array(
	array( 'gfx-winwin-brochure.jpg', 'Tri-fold brochure for The Win Win Services, inside and outside spreads', 'Tri-fold brochure', 'The Win Win Services', 'print', 'Inside and outside spreads' ),
	array( 'gfx-winehub-wca-sponsor.jpg', 'Winehub sponsor ad for Wine Communicators of Australia', 'Sponsor ad', 'Winehub', 'print', 'Wine Communicators of Australia' ),
	array( 'gfx-winehub-a5-portrait.jpg', 'Winehub A5 portrait magazine ad', 'Magazine ad', 'Winehub', 'print', 'A5 · portrait' ),
	array( 'gfx-winehub-napa-event.jpg', 'In-person event ad for Winehub and Pacific IQ in Napa', 'In-person event ad', 'Winehub × Pacific IQ', 'print', 'Napa, CA' ),
	array( 'gfx-winehub-a5-landscape.jpg', 'Winehub A5 landscape magazine ad', 'Magazine ad', 'Winehub', 'print', 'A5 · landscape' ),
	array( 'gfx-spine-day-banner.jpg', 'World Spine Day promo banner', 'Event banner', 'World Spine Day promo', 'banner', '' ),
	array( 'gfx-burnout-challenge.jpg', 'Online challenge banner, From Burned Out to Balanced', 'Online challenge banner', 'Centre for Life Therapies', 'banner', '' ),
	array( 'gfx-wine-case-illustrations.jpg', 'Illustrations of wine cases of 3, 6 and 24 bottles', 'Illustration set', 'Wine case sizes', 'illustration', '' ),
	array( 'gfx-maria-nicola-newsletter.jpg', 'Maria Nicola e-newsletter for Breast Cancer Awareness Month', 'E-newsletter', 'Maria Nicola', 'email', '' ),
	array( 'gfx-wine-cutout-mockup.jpg', 'Wine bottle cut out with a label mockup', 'Cutout & label mockup', 'Image manipulation', 'editing', '' ),
	array( 'gfx-aura-product-mockup.jpg', 'Skincare product mockup in a gift box', 'Product mockup', 'Image editing', 'editing', '' ),
);
$rows = array();
foreach ( $pieces as $p ) {
	$rows[] = array(
		'image'  => $img( $p[0], $p[1] ),
		'title'  => $p[2],
		'client' => $p[3],
		'kind'   => $p[4],
		'note'   => $p[5],
	);
}
$fill( 'group_edc_graphics', 'option', array(
	'graphics_heading' => 'Graphic design',
	'graphics_intro'   => 'Alongside web work, I design marketing graphics: print and magazine ads, event and promo banners, illustration, image editing, product mockups and e-newsletters.',
	'graphics'         => $rows,
) );
// The toggle defaults to on; write it once so it shows as on in wp-admin.
if ( null === get_field( 'show_graphics', 'option', false ) ) {
	update_field( 'field_edc_gfx_show', 1, 'option' );
}

/* ---------------------------------------------------------------------------
 * 3. Conferences & seminars (About page)
 * ------------------------------------------------------------------------ */
$about = get_posts( array(
	'post_type'      => 'page',
	'post_status'    => 'any',
	'meta_key'       => '_wp_page_template',
	'meta_value'     => 'page-templates/about.php',
	'posts_per_page' => 1,
	'fields'         => 'ids',
) );
if ( ! $about ) {
	WP_CLI::warning( 'No page uses the About template; events not added.' );
} else {
	$fill( 'group_edc_page_about', (int) $about[0], array(
		'about_events_heading' => 'Still learning, in the room',
		'about_events_intro'   => "Thirteen years in, I still make time for conferences. It's where I hear how product and design teams think, swap notes with other builders, and bring fresh ideas back to client work.",
		'about_events'         => array(
			array(
				'name'      => 'UX+ Conference 2025',
				'when'      => 'September 2025',
				'where'     => 'Philippines',
				'theme'     => '',
				'note'      => "A day of talks from design and product leads at teams like Dropbox, Google and GCash. It feeds straight into the app UI/UX work I do, like the Winehub and PerkRunner redesigns.\nNot my first UX+, either.",
				'photo'     => $img( 'event-uxplus-2025.jpg', 'Elisa with friends at the UX+ 2025 conference' ),
				'photo_2'   => $img( 'event-uxplus-group.jpg', 'Elisa with a group at an earlier UX+ conference' ),
				'caption_2' => 'An earlier UX+',
			),
			array(
				'name'      => "SaaSCon PH '25",
				'when'      => 'April 2025',
				'where'     => 'Philippines',
				'theme'     => 'SaaS 2.0: Surviving and Thriving in the Age of AI',
				'note'      => 'Founders, enterprises and investors talking about building SaaS products as AI changes the game. Useful context for the SaaS teams I build for, from marketing sites to the apps themselves.',
				'photo'     => $img( 'event-saascon-2025.jpg', "Elisa with colleagues at SaaSCon PH '25" ),
				'photo_2'   => '',
				'caption_2' => '',
			),
		),
	) );
}

WP_CLI::success( 'Showcase content checked.' );
