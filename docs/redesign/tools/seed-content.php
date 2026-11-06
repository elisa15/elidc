<?php
/**
 * Seed a LOCAL test site with the verified content from docs/redesign/05-content-entry.md.
 *
 *   wp eval-file docs/redesign/tools/seed-content.php
 *
 * Idempotent: re-running updates existing posts and options, never duplicates them.
 * Every ⏳ pending field is written as empty. On the live site it runs only through the
 * approved deploy runbook (docs/redesign/07-deploy-plan.md).
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Run with: wp eval-file docs/redesign/tools/seed-content.php\n" );
}
// The live site is refused unless the deploy runbook passes Elisa's approval explicitly
// (docs/redesign/07-deploy-plan.md): --exec="define( 'EDC_SEED_LIVE', 'approved-by-elisa' );"
$edc_is_live = false !== stripos( home_url(), 'elisaarmas.com' ) || false !== stripos( site_url(), 'elisaarmas.com' );
if ( $edc_is_live && ! ( defined( 'EDC_SEED_LIVE' ) && 'approved-by-elisa' === EDC_SEED_LIVE ) ) {
	WP_CLI::error( 'Refusing to seed: this looks like the live site (' . home_url() . ').' );
}
if ( ! function_exists( 'update_field' ) || ! function_exists( 'acf_get_field' ) ) {
	WP_CLI::error( 'ACF (or Secure Custom Fields) must be active.' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

if ( ! defined( 'EDC_SEED_SHOTS' ) ) {
	define( 'EDC_SEED_SHOTS', (string) realpath( __DIR__ . '/../reference/prototype/assets/shots' ) );
}
if ( '' === EDC_SEED_SHOTS ) {
	WP_CLI::error( 'Screenshot folder not found next to this script.' );
}

/* ---------------------------------------------------------------------------
 * Helpers
 * ------------------------------------------------------------------------ */

/**
 * Field key for a top-level field name in a field group, read from acf-json.
 */
function edc_seed_key( $group, $name ) {
	static $map = array();
	if ( ! isset( $map[ $group ] ) ) {
		$file = get_template_directory() . '/acf-json/' . $group . '.json';
		$json = json_decode( (string) file_get_contents( $file ), true );
		if ( ! $json ) {
			WP_CLI::error( "Cannot read {$file}" );
		}
		$map[ $group ] = array();
		foreach ( $json['fields'] as $field ) {
			if ( 'tab' !== $field['type'] ) {
				$map[ $group ][ $field['name'] ] = $field['key'];
			}
		}
	}
	if ( ! isset( $map[ $group ][ $name ] ) ) {
		WP_CLI::error( "Unknown field {$group}.{$name}" );
	}
	return $map[ $group ][ $name ];
}

/**
 * Whether a seed value counts as "nothing to write" (⏳ fields are seeded empty).
 */
function edc_seed_is_empty( $value ) {
	return null === $value || '' === $value || array() === $value || false === $value;
}

/**
 * Write fields by name for one group. Empty seed values never clear what is already
 * there, so anything Elisa fills in wp-admin (CV, links, payment links…) survives
 * a re-run. Repeater rows get the same rule per sub-field, matched by row position.
 */
function edc_seed_fields( $group, $post_id, $values ) {
	foreach ( $values as $name => $value ) {
		$key     = edc_seed_key( $group, $name );
		$current = get_field( $key, $post_id, false );

		if ( edc_seed_is_empty( $value ) && ! edc_seed_is_empty( $current ) ) {
			continue;
		}

		if ( is_array( $value ) && isset( $value[0] ) && is_array( $value[0] ) && is_array( $current ) ) {
			$field = acf_get_field( $key );
			$subs  = array();
			foreach ( (array) ( isset( $field['sub_fields'] ) ? $field['sub_fields'] : array() ) as $sub ) {
				$subs[ $sub['name'] ] = $sub['key'];
			}
			$rows = array_values( $current );
			foreach ( $value as $i => $row ) {
				foreach ( $row as $sub_name => $sub_value ) {
					$old = isset( $rows[ $i ], $subs[ $sub_name ], $rows[ $i ][ $subs[ $sub_name ] ] ) ? $rows[ $i ][ $subs[ $sub_name ] ] : null;
					if ( edc_seed_is_empty( $sub_value ) && ! edc_seed_is_empty( $old ) && ! is_array( $old ) ) {
						$value[ $i ][ $sub_name ] = $old;
					}
				}
			}
		}

		update_field( $key, $value, $post_id );
	}
}

function edc_seed_term_ids( $taxonomy, $slugs ) {
	$ids = array();
	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( ! $term ) {
			WP_CLI::error( "Missing term {$taxonomy}:{$slug}" );
		}
		$ids[] = (int) $term->term_id;
	}
	return $ids;
}

/**
 * Create or update a post found by slug (or by a seed key for non-public types).
 */
function edc_seed_post( $type, $slug, $title, $extra = array(), $seed_key = '' ) {
	$existing = null;
	if ( $seed_key ) {
		$found = get_posts( array(
			'post_type'      => $type,
			'post_status'    => 'any',
			'meta_key'       => '_edc_seed_key',
			'meta_value'     => $seed_key,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		) );
		$existing = $found ? get_post( $found[0] ) : null;
	} else {
		$existing = get_page_by_path( $slug, OBJECT, $type );
	}

	$args = array_merge(
		array(
			'post_type'   => $type,
			'post_status' => 'publish',
			'post_title'  => $title,
		),
		$slug ? array( 'post_name' => $slug ) : array(),
		$extra
	);
	if ( $existing ) {
		$args['ID'] = $existing->ID;
		$id         = wp_update_post( wp_slash( $args ), true );
	} else {
		$id = wp_insert_post( wp_slash( $args ), true );
	}
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	if ( $seed_key ) {
		update_post_meta( $id, '_edc_seed_key', $seed_key );
	}
	WP_CLI::log( ( $existing ? 'Updated ' : 'Created ' ) . "{$type} #{$id}: {$title}" );
	return (int) $id;
}

/**
 * Import a screenshot from the prototype folder once; reuse it afterwards.
 */
function edc_seed_image( $file, $alt ) {
	$found = get_posts( array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'meta_key'       => '_edc_seed_source',
		'meta_value'     => $file,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( $found ) {
		$id = (int) $found[0];
	} else {
		$tmp = wp_tempnam( $file );
		copy( EDC_SEED_SHOTS . '/' . $file, $tmp );
		$id = media_handle_sideload( array( 'name' => $file, 'tmp_name' => $tmp ), 0 );
		if ( is_wp_error( $id ) ) {
			WP_CLI::error( "Image {$file}: " . $id->get_error_message() );
		}
		update_post_meta( $id, '_edc_seed_source', $file );
		WP_CLI::log( "Imported image #{$id}: {$file}" );
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	return (int) $id;
}

function edc_seed_rows( $keys, $rows ) {
	$out = array();
	foreach ( $rows as $row ) {
		$out[] = array_combine( $keys, $row );
	}
	return $out;
}

/* ---------------------------------------------------------------------------
 * 1. Profile & Links
 * ------------------------------------------------------------------------ */

$profile = 'group_edc_profile';
edc_seed_fields( $profile, 'option', array(
	'profile_name'       => 'Elisa A. Dela Cruz',
	'profile_short_name' => 'Elisa Dela Cruz',
	'profile_title'      => 'Senior WordPress & Shopify Developer',
	'portrait'           => edc_seed_image( 'portrait-elisa.jpg', 'Elisa A. Dela Cruz' ), // Elisa's photo shoot, Nov 2026.
	'availability'       => '', // ⏳
	'location_line'      => 'Manila, working with AU / US / UK time zones',
	'reply_promise'      => 'Replies within one working day',
	'cv_file'            => '', // ⏳
	'linkedin_url'       => '', // ⏳
	'github_url'         => '', // ⏳
	'career_log'         => edc_seed_rows(
		array( 'type', 'message', 'context', 'when', 'meta', 'is_head', 'body', 'hash' ),
		array(
			array( 'feat', 'going independent', 'HEAD → now', '2022 →', 'Client Website Manager · independent · local & overseas clients', 1, 'Customising Shopify themes, building WordPress funnels and landing pages, and looking after sites long-term. I also coordinate a small team of SEO specialists, Google Ads people, copywriters and junior developers.', '' ),
			array( 'feat', 'Shopify at scale, four storefronts', 'US beauty brand, via Microsourcing', '2020–22', 'Shopify Developer · US account', 0, 'Maintaining the US store and replicating it for Australia, Europe and Canada. In the same years: WordPress development and support for Capability Partners and The AM College in Australia, and consulting for Cenit Lighting in Manila.', '' ),
			array( 'chore', 'dedicated developer for US & AU accounts', 'Microsourcing, EMAPTA', '2018–20', 'WordPress Developer · Fourandhalf (US real estate) · RedEx Digital & Filta (AU)', 0, 'Designing, building and maintaining WordPress sites for overseas agencies and their clients.', '' ),
			array( 'feat', 'leading the dev team', '', '2016–18', 'Mid-level Web Developer / Project Coordinator', 0, "E-commerce sites for US clients on Shopify and WordPress. I ran the dev team, the scrum meetings and the client reporting. This is where I learned that most project problems aren't code problems.", '' ),
			array( 'refactor', 'agency years, AU clients', 'ZigZag / The Colour Club, Follow Digital', '2015–18', 'Web Developer · Sage + SASS builds', 0, 'Custom WordPress themes for Australian agencies, including photography portfolios built from scratch on Sage.', '' ),
			array( 'feat', 'HTML, jQuery, PHP, WordPress & Magento', 'Glued Creative', '2012–15', 'Web Developer', 0, 'Where WordPress became the main tool, with Magento along the way.', '' ),
			array( 'init', 'PHP portal systems', 'J.A.O. Builders', '2011', 'PHP Web Developer', 0, 'A web-based portal system and its database, from scratch. The first commit.', '' ),
		)
	),
) );

/* ---------------------------------------------------------------------------
 * 2. Testimonials
 * ------------------------------------------------------------------------ */

$tg = 'group_edc_testimonial_fields';

$rachel = edc_seed_post( 'testimonial', '', 'Rachel S. – Capability Partners', array(), 'rachel-s' );
edc_seed_fields( $tg, $rachel, array(
	'quote'                => 'Working with Elisa has been an outstanding experience. She consistently delivers high-quality solutions, responds quickly to client needs, and combines strong technical expertise with genuine professionalism.',
	'quote_short'          => '',
	'person_name'          => 'Rachel S.',
	'person_role'          => 'Principal Consultant',
	'company'              => 'Capability Partners',
	'relationship'         => 'client',
	'source_url'           => '',
	'permission_confirmed' => 1,
) );

$chris = edc_seed_post( 'testimonial', '', 'Christopher Sorini – LinkedIn', array(), 'christopher-sorini' );
edc_seed_fields( $tg, $chris, array(
	'quote'                => "Out of the dozens of candidates I interviewed, she stood out for her independent thinking, curiosity, and versatility. She brings the confidence to challenge assumptions and the judgment to make that pushback constructive. Give her an unfamiliar technology, and she'll learn it quickly, then teach you a thing or two along the way. As her role grew, she led our UX and UI teams.",
	'quote_short'          => 'Elisa is someone I was fortunate to hire and would gladly work with again.',
	'person_name'          => 'Christopher Sorini',
	'person_role'          => 'Director of Automation and Technical Solutions',
	'company'              => '', // Not named in the portfolio.
	'relationship'         => 'employer',
	'source_url'           => '', // ⏳ LinkedIn recommendation URL
	'permission_confirmed' => 1,  // Elisa asked for it on the site, 6 Nov 2026.
) );

/* ---------------------------------------------------------------------------
 * 3. Projects
 * ------------------------------------------------------------------------ */

/**
 * Create/update one project. $f holds field values; kinds/platforms are term slugs.
 */
function edc_seed_project( $slug, $title, $order, $kinds, $platforms, $f ) {
	$pg = 'group_edc_project_fields';
	$id = edc_seed_post( 'project', $slug, $title, array( 'menu_order' => $order, 'post_content' => '', 'post_excerpt' => '' ) );

	$kind_ids     = edc_seed_term_ids( 'project_type', $kinds );
	$platform_ids = edc_seed_term_ids( 'project_platform', $platforms );
	wp_set_object_terms( $id, $kind_ids, 'project_type' );
	wp_set_object_terms( $id, $platform_ids, 'project_platform' );

	$defaults = array(
		'project_kind'         => $kind_ids,
		'project_platforms'    => $platform_ids,
		'tier'                 => 'selected',
		'client_display_name'  => $title,
		'confidential'         => 0,
		'industry_badge'       => '',
		'region'               => '',
		'role'                 => '',
		'engaged_via'          => '',
		'year_start'           => '',
		'year_end'             => '',
		'summary'              => '',
		'names'                => array(),
		'chips'                => array(),
		'card_span'            => 'auto',
		'visual_mode'          => 'screenshot',
		'screenshot'           => '',
		'screenshot_secondary' => '',
		'illustration'         => 'swatches',
		'terminal_line'        => '',
		'browser_label'        => '',
		'visual_badge'         => '',
		'live_url'             => '',
		'behind_meta'          => '',
		'behind_rows'          => array(),
		'has_case_study'       => 0,
		'cs_headline'          => '',
		'cs_lede'              => '',
		'at_a_glance'          => array(),
		'situation'            => '',
		'tricky'               => '',
		'approach'             => array(),
		'technical_breakdown'  => '',
		'result'               => '',
		'learned'              => '',
		'cs_testimonial'       => '',
	);
	edc_seed_fields( $pg, $id, array_merge( $defaults, $f ) );
	return $id;
}

$chips  = function ( $labels ) {
	return array_map( function ( $label ) {
		return array( 'label' => $label );
	}, $labels );
};
$behind = function ( $pairs ) {
	return edc_seed_rows( array( 'key', 'value' ), $pairs );
};

$p_beauty = edc_seed_project( 'us-beauty-brand', 'One beauty brand, four Shopify storefronts', 1, array( 'built' ), array( 'shopify' ), array(
	'tier'                => 'featured',
	'client_display_name' => 'US beauty brand',
	'confidential'        => 1,
	'industry_badge'      => 'Beauty',
	'region'              => 'US',
	'role'                => 'Shopify developer',
	'engaged_via'         => 'via Microsourcing',
	'year_start'          => 2020,
	'year_end'            => '2022',
	'summary'             => 'Two years on a US beauty brand. I replicated the US store into Australia, Europe and Canada, and built product filtering and colour swatches in custom code instead of apps, across more than 1,000 SKUs. I worked alongside the marketing team on launches, sales and pricing.',
	'chips'               => $chips( array( 'Liquid', '1,000+ SKUs' ) ),
	'card_span'           => 'wide',
	'visual_mode'         => 'illustration',
	'illustration'        => 'swatches',
	'browser_label'       => 'us-beauty-brand · 4 storefronts',
	'visual_badge'        => 'illustration · client not named',
	'behind_meta'         => 'built · 2020–22',
	'behind_rows'         => $behind( array(
		array( 'needed', 'the US store, replicated for AU, EU and CA' ),
		array( 'tricky', '1,000+ SKUs, four regions, and no apps for filtering or swatches' ),
		array( 'did', 'store replication, custom filtering, colour swatches, launch + sale + pricing support' ),
		array( 'stack', 'Shopify · Liquid · JavaScript' ),
		array( 'with', 'the marketing team' ),
	) ),
	'has_case_study'      => 1,
	'cs_headline'         => 'One beauty brand, four storefronts',
	'cs_lede'             => "For two years I looked after a US beauty brand's Shopify store, then replicated it for Australia, Europe and Canada, with filtering and colour swatches written in custom code instead of bolted on with apps.",
	'at_a_glance'         => edc_seed_rows( array( 'label', 'value' ), array(
		array( 'client', 'US beauty brand (not named)' ),
		array( 'industry', 'Beauty / cosmetics' ),
		array( 'role', 'Shopify developer, via Microsourcing' ),
		array( 'when', '2020–22 · 2 years' ),
		array( 'regions', 'US · AU · EU · CA' ),
		array( 'catalogue', '1,000+ SKUs' ),
		array( 'stack', 'Shopify · Liquid · JavaScript · no apps for filtering or swatches' ),
		array( 'worked with', 'Marketing team' ),
	) ),
	'situation'           => '<p>The brand sells beauty products online, with a catalogue of more than 1,000 SKUs. The US store was the original. The brand wanted the same shopping experience in Australia, Europe and Canada.</p>',
	'tricky'              => '<p>Colour is the product, so shoppers need to filter and compare shades fast. The obvious route was stacking apps for filtering and swatches. On a catalogue this size, every app adds load time, cost and another thing that can break, four times over.</p>',
	'approach'            => edc_seed_rows( array( 'step' ), array(
		array( 'Maintained the US store for two years: fixes, updates and changes as the business needed them.' ),
		array( 'Replicated it for AU, EU and CA, so each region got the same experience.' ),
		array( 'Built product filtering in custom code, without an app.' ),
		array( 'Built colour swatches in custom code, so shades read correctly across product pages and collections.' ),
		array( 'Worked with the marketing team on new product launches, sales and discount pricing.' ),
	) ),
	'technical_breakdown' => '', // ⏳
	'result'              => "<p>The US store runs filtering and swatches that don't depend on third-party apps.</p>",
	'learned'             => '', // ⏳
) );

// Featured (homepage + top of /projects/). Wording from Elisa's 2026 portfolio PDF.
$p_boat = edc_seed_project( 'boat-ocraigo', "Boat O'Craigo, moved to Shopify", 2, array( 'built', 'solved' ), array( 'shopify' ), array(
	'tier'                => 'featured',
	'client_display_name' => "Boat O'Craigo",
	'industry_badge'      => 'Wine',
	'region'              => 'Yarra Valley, AU',
	'role'                => 'Platform migration',
	'summary'             => 'Moved a boutique Yarra Valley winery onto Shopify with wine club integration, with zero disruption to ongoing club sales. Wines, cellar door tastings, dining and private events now live in one store.',
	'chips'               => $chips( array( 'Wine club', 'Migration' ) ),
	'screenshot'          => edc_seed_image( 'boatocraigo-live.jpg', "Boat O'Craigo homepage" ),
	'browser_label'       => 'boatocraigo.com.au',
	'live_url'            => 'https://boatocraigo.com.au/',
	'behind_meta'         => 'migration · Shopify',
	'behind_rows'         => $behind( array(
		array( 'needed', 'a new store without interrupting wine club sales' ),
		array( 'did', 'new design, Shopify build, wine club integration, migration' ),
		array( 'now', 'wines, tastings, dining and events in one store' ),
	) ),
) );

$p_godshill = edc_seed_project( 'gods-hill-wines', "God's Hill Wines, rebuilt on Shopify", 3, array( 'built', 'solved' ), array( 'shopify' ), array(
	'tier'                => 'featured',
	'client_display_name' => "God's Hill Wines",
	'industry_badge'      => 'Wine',
	'region'              => 'Barossa Valley, AU',
	'role'                => 'Shopify migration',
	'summary'             => 'Rebuilt a family-owned Barossa winery on Shopify with wine club integration. Migrated the full product range and enhanced the product photography so every bottle looks its best.',
	'chips'               => $chips( array( 'Wine club', 'Product migration' ) ),
	'screenshot'          => edc_seed_image( 'godshill.jpg', "God's Hill Wines homepage" ),
	'browser_label'       => 'godshillwines.com',
	'live_url'            => 'https://godshillwines.com/',
	'behind_meta'         => 'migration · Shopify',
	'behind_rows'         => $behind( array(
		array( 'did', 'Shopify rebuild, wine club integration, full product migration' ),
		array( 'plus', 'enhanced product photography across the range' ),
	) ),
) );

$p_winehub = edc_seed_project( 'winehub', "Winehub's marketing site", 4, array( 'built' ), array( 'wordpress' ), array(
	'tier'                => 'featured',
	'client_display_name' => 'Winehub',
	'industry_badge'      => 'SaaS',
	'region'              => 'Worldwide',
	'role'                => 'Design & development',
	'summary'             => 'Designed and built the marketing site for a Shopify wine club, subscription and loyalty platform used by wineries worldwide, with custom feature pages and product mockups drawn from the real app.',
	'chips'               => $chips( array( 'UI design', 'Product mockups' ) ),
	'screenshot'          => edc_seed_image( 'winehub-live.jpg', 'Winehub homepage' ),
	'browser_label'       => 'winehub.io',
	'live_url'            => 'https://winehub.io/',
	'behind_meta'         => 'built · from scratch',
	'behind_rows'         => $behind( array(
		array( 'did', 'design and build, custom feature pages' ),
		array( 'mockups', 'dashboard, members, email automation, integrations' ),
		array( 'stack', 'custom WordPress' ),
	) ),
) );

$p_perk = edc_seed_project( 'perkrunner', "PerkRunner's marketing site", 5, array( 'built' ), array( 'wordpress' ), array(
	'tier'                => 'featured',
	'client_display_name' => 'PerkRunner',
	'industry_badge'      => 'SaaS',
	'region'              => 'Worldwide',
	'role'                => 'Design & development',
	'summary'             => "Designed and built the marketing site for a Shopify subscriptions and loyalty app: five core pages, eight feature pages, a grouped mega menu, and story-led product visuals that walk through a customer's journey.",
	'chips'               => $chips( array( 'UX writing', 'Design system' ) ),
	'screenshot'          => edc_seed_image( 'perkrunner-live.jpg', 'PerkRunner homepage' ),
	'browser_label'       => 'perkrunner.com',
	'live_url'            => 'https://perkrunner.com/',
	'behind_meta'         => 'built · from scratch',
	'behind_rows'         => $behind( array(
		array( 'pages', '5 core pages, 8 feature pages, grouped mega menu' ),
		array( 'did', 'design system, UX writing, build' ),
		array( 'stack', 'custom WordPress' ),
	) ),
) );

$p_frankland = edc_seed_project( 'frankland-estate', "Frankland Estate's new Shopify store", 6, array( 'built', 'solved' ), array( 'shopify' ), array(
	'tier'                => 'featured',
	'client_display_name' => 'Frankland Estate',
	'industry_badge'      => 'Wine',
	'region'              => 'Western Australia',
	'role'                => 'Shopify store setup',
	'summary'             => 'Set up a new Shopify store for a leading Western Australian winery, migrated from VinCreative with wine club integration. Included pickup locations and email domain setup with the client team.',
	'chips'               => $chips( array( 'Wine club', 'Migration' ) ),
	'screenshot'          => edc_seed_image( 'frankland-live.jpg', 'Frankland Estate homepage' ),
	'browser_label'       => 'franklandestate.com.au',
	'live_url'            => 'https://franklandestate.com.au/',
	'behind_meta'         => 'migration · Shopify',
	'behind_rows'         => $behind( array(
		array( 'from', 'VinCreative' ),
		array( 'to', 'Shopify, with wine club integration' ),
		array( 'plus', 'pickup locations, email domain setup' ),
	) ),
) );

// Retired grouped cards: kept as drafts (nothing deleted), old URLs redirect to the new cards.
foreach ( array( 'three-wineries', 'perkrunner-winehub', 'cenit-oasis' ) as $old_slug ) {
	$old = get_page_by_path( $old_slug, OBJECT, 'project' );
	if ( $old && 'draft' !== $old->post_status ) {
		wp_update_post( array( 'ID' => $old->ID, 'post_status' => 'draft' ) );
		WP_CLI::log( "Drafted grouped project #{$old->ID}: {$old_slug}" );
	}
}

// Selected work: name, platform, region, role, summary, screenshot where the live site is Elisa's work.
// array( slug, title, kinds, platforms, region, role, industry, summary, chips, shot, label, url )
$selected = array(
	array( 'enerco', 'Enerco', array( 'built', 'solved' ), array( 'shopify', 'woocommerce' ), 'United States', 'Ecommerce migration', 'Heating & propane', 'Migrated a US heating and propane manufacturer to Shopify: 100+ SKUs across hoses, regulators, heaters and accessories. Zero data loss and full SEO redirect coverage.', array( '100+ SKUs', 'SEO redirects' ), 'enerco.jpg', 'enerco.com', 'https://enerco.com/' ),
	array( 'grand-teton-collection', 'Grand Teton Collection', array( 'built', 'solved' ), array( 'shopify', 'woocommerce' ), 'United States', 'WooCommerce → Shopify', 'Outdoor heating', 'Moved a US outdoor heating brand from WooCommerce to Shopify while keeping SEO intact. Built a full video hero and restructured navigation across wood stoves, pellet stoves, ash vacuums and accessories.', array( 'Migration', 'Video hero' ), 'grandteton.jpg', 'grandtetoncollection.com', 'https://grandtetoncollection.com/' ),
	array( 'fuelkeg', 'FuelKeg', array( 'built', 'solved' ), array( 'shopify', 'woocommerce' ), 'United States', 'WooCommerce → Shopify rebuild', 'Propane', 'Migrated a single-product propane brand to Shopify with a full brand-story rebuild, including a custom retailer finder, how-to-use section and product registration.', array( 'Brand rebuild', 'Retailer finder' ), 'fuelkeg.jpg', 'fuelkegrefillablepropanetank.com', 'https://fuelkegrefillablepropanetank.com/' ),
	array( 'sound-and-sight', 'Sound & Sight Solutions', array( 'built' ), array( 'wordpress', 'elementor' ), 'Chicago, US', 'Development', 'Smart home & AV', 'A custom WordPress site for a Chicago smart-home and AV company, built from their design all the way to deployment.', array( 'Design → build' ), 'soundsight.jpg', 'Sound & Sight Solutions', '' ),
	array( 'cenit-lighting', 'Cenit Lighting', array( 'built' ), array( 'shopify' ), 'Philippines', 'Shopify store build', 'Lighting', 'Hired to create the Shopify store for a Philippine lighting brand, running alongside its existing WordPress website, with category and brand navigation, new arrivals and a clean product-led look.', array( 'Store build', 'Collections' ), 'cenit-live.jpg', 'shop.cenitlighting.com', 'https://shop.cenitlighting.com/' ),
	array( 'oasis-water', 'Oasis Water Philippines', array( 'built' ), array( 'wordpress', 'elementor' ), 'Philippines', 'WordPress developer', 'Water', "Cenit Lighting's sister brand brought me back to turn their design into a working WordPress site in Elementor.", array( 'Repeat client' ), 'oasis-live.jpg', 'oasiswaterph.com', 'https://oasiswaterph.com/' ),
	array( 'flat-stacks', 'Flat Stacks', array( 'built' ), array( 'shopify' ), 'Australia', 'Custom development', 'Kitchenware', 'Built custom B2B ordering functionality that no off-the-shelf app could handle, for an Australian collapsible kitchenware brand.', array( 'B2B', 'Custom build' ), 'flatstacks.jpg', 'flatstacks.com.au', 'https://flatstacks.com.au/' ),
	array( 'suubee-premium', 'Suubee Premium', array( 'built' ), array( 'wordpress' ), 'Australia', 'Custom WordPress build', 'Trading education', "Built in WordPress for an Australian trading education and mentorship program, with a custom API integration that brings live Australian stock data into the members' trading desk.", array( 'API integration', 'Members' ), 'suubee.jpg', 'suubeepremium.com', 'https://suubeepremium.com/' ),
	array( 'maria-nicola', 'Maria Nicola', array( 'built' ), array( 'shopify' ), 'Australia', 'Ongoing design & optimisation partner', 'Jewellery', 'Long-term Shopify partner for an Australian jewellery brand. Custom design and conversion work around the signature 10 Way Necklace, multi-currency selling in six currencies, collection management and performance monitoring.', array( 'CRO', 'Multi-currency' ), 'marianicola.jpg', 'marianicola.com', 'https://marianicola.com/' ),
	array( 'proserveus', 'ProServeUS', array( 'built' ), array( 'shopify' ), 'United States', 'Shopify design & build', 'Preparedness & government supply', 'Designed from the ground up and built on Shopify for a US preparedness and government contract supplier, with a catalogue of 80,000+ SKUs hidden from non-members. The store is currently on hiatus.', array( '80K SKUs', 'Members-only' ), '', '', '' ),
	array( 'stanno', 'Stanno', array( 'built' ), array( 'shopify' ), '', 'B2B storefront build', 'Sportswear', "Delivered a Shopify Plus B2B storefront for a sportswear brand's wholesale buyers, with tiered pricing.", array( 'Shopify Plus', 'B2B wholesale' ), '', '', '' ),
	array( 'capability-partners', 'Capability Partners', array( 'built' ), array( 'wordpress' ), 'Australia', 'Sole web developer since 2020', 'Asset management consulting', 'Sole web developer since 2020 for an Australian asset management consultancy and its training college: three company websites and a public learning management system.', array( 'LMS', 'Support' ), '', '', '' ),
	array( 'chateau-tanunda', 'Château Tanunda', array( 'built' ), array( 'shopify' ), 'Barossa Valley, AU', 'Shopify migration & UI/UX', 'Wine', "Finishing a WordPress to Shopify migration for one of the Barossa's historic estates: a 30-page UI/UX review and designs for the homepage, mega menu, shop filters, product page, cart drawer, Wine Club and Loyalty pages, and the wine club sign-up flow.", array( 'In progress', 'Wine club' ), 'chateau-tanunda-design.jpg', 'chateautanunda.com · new design', '' ),
	array( 'seppeltsfield', 'Seppeltsfield', array( 'built' ), array( 'shopify' ), 'Barossa Valley, AU', 'Shopify store work', 'Wine', "Restructuring a landmark winery's Shopify store: six main collections with Centennial as a featured range, collection landing pages, rebuilt gifting and voucher pages, and a new mega menu.", array( 'In progress', 'Collections' ), 'seppeltsfield-design.jpg', 'seppeltsfield.com.au · new design', '' ),
	array( 'all-saints-estate', 'All Saints Estate, St Leonards Vineyard & Mount Ophir Estate', array( 'built' ), array( 'shopify' ), 'Victoria, AU', 'Three-site Shopify rebuild', 'Wine & hospitality', 'Full Shopify rebuilds of three sister winery and hospitality sites, moving off VinCreative onto Shopify with wine club integration. Discovery review and redesign for all three, with All Saints as the lead site.', array( 'In progress', 'Redesign' ), 'allsaints-design.jpg', 'allsaintswine.com.au · new design', '' ),
	array( 'winehub-app', 'Winehub app UI/UX (prototype)', array( 'built' ), array(), '', 'Product & app design', 'SaaS', 'Several weeks of UX and UI work on a clickable prototype of the Winehub admin app: dashboard, wine clubs, club editor, products, club runs, allocations, loyalty tiers and subscriptions, on one shared design system.', array( 'UI/UX', 'Prototype' ), 'winehub-app.jpg', 'Prototype · Dashboard', '' ),
	array( 'perkrunner-app', 'PerkRunner app UI/UX (prototype)', array( 'built' ), array(), '', 'Product & app design', 'SaaS', 'UX and UI redesign of a Shopify embedded app for subscriptions, loyalty and bundles. I set up its design system and UI library, then redesigned the screens one by one as connected prototypes handed off to the development team.', array( 'UI/UX', 'Design system' ), 'perkrunner-app.jpg', 'Prototype · Billing schedule', '' ),
	array( 'vertical-green', 'Vertical Green', array( 'built' ), array( 'wordpress' ), 'Philippines', 'Company website', 'Vertical gardens', 'Website for a vertical garden and green wall company, showcasing its HyGro wall, moss and mat systems, project gallery and industry solutions for hotels, offices and developers.', array( 'Brand site', 'Lead gen' ), 'verticalgreen.jpg', 'verticalgreen.com.ph', 'https://verticalgreen.com.ph/' ),
	array( 'dd-music', 'D&D Music', array( 'built' ), array( 'woocommerce', 'wordpress' ), 'Philippines', 'Online store', 'Music gear', 'WooCommerce store for a Filipino guitar and music gear brand, with nationwide shipping, GCash, Maya and card payments, a custom shop, news and a store directory.', array( 'Local payments' ), 'ddmusic.jpg', 'ddmusic.ph', 'https://ddmusic.ph/' ),
	array( 'futbol-funatics', 'Futbol Funatics', array( 'built' ), array( 'wordpress' ), 'Philippines', 'Academy website', 'Sports', 'Website for a youth football academy, covering training programs, services, updates and sponsors, with a clear path for families to join.', array( 'Sports programs' ), 'futbol.jpg', 'futbolfunatics.com', 'https://futbolfunatics.com/' ),
	array( 'tvi-resource-development', 'TVI Resource Development', array( 'built' ), array( 'wordpress' ), 'Philippines', 'Corporate website', 'Mining & resources', 'Corporate website for a Philippine resource development company, covering its business, sustainability and community programs, and news.', array( 'Corporate', 'Sustainability' ), 'tvird.jpg', 'tvird.com.ph', 'https://tvird.com.ph/' ),
	array( 'tvi-pacific', 'TVI Pacific', array( 'built' ), array( 'wordpress' ), 'Canada / Philippines', 'Investor website', 'Mining & resources', 'Investor-facing website for a TSX-V listed company with Philippine projects: holdings, investor resources, news and live share and metals price tickers.', array( 'Investor relations', 'Live market data' ), 'tvipacific.jpg', 'tvipacific.com', 'https://tvipacific.com/' ),
	array( 'st-augustine-gold-copper', 'St. Augustine Gold & Copper', array( 'built' ), array( 'wordpress' ), 'Mindanao, PH', 'Corporate & investor website', 'Mining & resources', 'Corporate and investor website for the company behind a major copper-gold project in Mindanao, with company, project, responsibility and investor portal sections.', array( 'Investor portal' ), 'sagc.jpg', 'sagcmining.com', 'https://sagcmining.com/' ),
	array( 'sorini-strategic-advisors', 'Sorini Strategic Advisors', array( 'built' ), array( 'wordpress' ), 'Washington, DC, US', 'Firm website', 'Trade advisory', 'Website for a Washington, DC international trade advisory firm, covering trade and tariff policy, market access and customs compliance services, plus regular trade updates.', array( 'Professional services' ), 'sorini.jpg', 'ssa-dc.com', 'https://ssa-dc.com/' ),
	array( 'dcx-commerce', 'DCX Commerce', array( 'built' ), array( 'wordpress' ), '', 'Agency website · co-founder', 'Own brand', 'The site for my Shopify migration agency: a custom theme, full technical SEO setup, and a Migration Academy content hub that brings in organic leads.', array( 'Custom theme', 'SEO' ), 'dcx.jpg', 'dcxcommerce.com', 'https://dcxcommerce.com/' ),
);
$sel_ids = array();
foreach ( $selected as $i => $s ) {
	list( $slug, $title, $kinds, $platforms, $region, $role, $industry, $summary, $chip_labels, $shot, $label, $url ) = $s;
	$alt = false !== strpos( $label, 'new design' ) ? $title . ' new homepage design' : ( 0 === strpos( $label, 'Prototype' ) ? str_replace( ' UI/UX (prototype)', ' prototype', $title ) . ': ' . trim( substr( $label, strpos( $label, '·' ) + 2 ) ) . ' screen' : $title . ' homepage' );
	$sel_ids[ $slug ] = edc_seed_project( $slug, $title, 10 + $i, $kinds, $platforms, array(
		'tier'                => 'selected',
		'client_display_name' => $title,
		'industry_badge'      => $industry,
		'region'              => $region,
		'role'                => $role,
		'summary'             => $summary,
		'chips'               => $chips( $chip_labels ),
		'visual_mode'         => 'screenshot',
		'screenshot'          => $shot ? edc_seed_image( $shot, $alt ) : '',
		'browser_label'       => $label,
		'live_url'            => $url,
	) );
}

// Technical highlights: short problem → fix notes.
$highlights = array(
	array( '66-site-performance', '66-site performance programme', array( 'solved' ), array( 'wordpress' ), 'agency portfolio optimisation', array(
		array( 'scope', '66 sites on one hosting portfolio' ),
		array( 'did', 'audited with Lighthouse data and plugin fingerprinting' ),
		array( 'fix', 'rolled out fixes site by site' ),
	) ),
	array( 'home-of-lights', 'Home of Lights', array( 'built' ), array( 'shopify' ), 'custom PDP template', array(
		array( 'did', 'custom product field + custom product page template' ),
		array( 'stack', 'Shopify · Liquid' ),
	) ),
);
foreach ( $highlights as $i => $h ) {
	list( $slug, $title, $kinds, $platforms, $meta, $rows ) = $h;
	$sel_ids[ $slug ] = edc_seed_project( $slug, $title, 60 + $i, $kinds, $platforms, array(
		'tier'        => 'highlight',
		'behind_meta' => $meta,
		'behind_rows' => $behind( $rows ),
	) );
}

/* ---------------------------------------------------------------------------
 * 4. Homepage (B−)
 * ------------------------------------------------------------------------ */

$archive = get_post_type_archive_link( 'project' );
$hg      = 'group_edc_home_v2';

edc_seed_fields( $hg, 'option', array(
	'home_terminal'          => edc_seed_rows( array( 'kind', 'text', 'link' ), array(
		array( 'p', '$ whoami', '' ),
		array( 'o', 'Elisa A. Dela Cruz · Senior WordPress & Shopify Developer', '' ),
		array( 'p', '$ stack', '' ),
		array( 'o', 'WordPress · Shopify · WooCommerce · PHP · Liquid · JS', '' ),
		array( 'p', '$ last_fixed', '' ),
		array( 'o', '✔ One beauty brand, four storefronts', $p_beauty ),
		array( 'o', "✔ Boat O'Craigo, God's Hill, Frankland: wine club stores on Shopify", $archive ),
	) ),
	'home_more_heading'      => 'More work across Shopify and WordPress',
	'home_featured_projects' => array( $p_beauty, $p_boat, $p_godshill, $p_winehub, $p_perk, $p_frankland ),
	'home_more_projects'     => array(
		$sel_ids['enerco'],
		$sel_ids['grand-teton-collection'],
		$sel_ids['fuelkeg'],
		$sel_ids['maria-nicola'],
		$sel_ids['sound-and-sight'],
		$sel_ids['cenit-lighting'],
		$sel_ids['suubee-premium'],
		$sel_ids['flat-stacks'],
	),
	'home_called'            => edc_seed_rows( array( 'label', 'title', 'text', 'stack', 'link_label', 'link' ), array(
		array( '01 / move', 'Moving house without losing the furniture', 'Migrations: WooCommerce to Shopify, host to host, theme to theme. Rankings, redirects, customers and orders come along.', '301 maps · data import · DNS · Search Console', 'See a migration', $archive ),
		array( '02 / rescue', 'Getting hacked sites back on their feet', "Malware cleanup, recovery, and hardening, so it doesn't happen twice.", 'file forensics · clean restore · hardening · monitoring', 'See a rescue', '' ),
		array( '03 / speed', 'Making slow sites stop being slow', 'Core Web Vitals, bloated page builders, image pipelines, caching, and the plugin nobody remembers installing.', 'Lighthouse · caching · images · script audits', 'See a speed fix', '' ),
		array( '04 / build', "Building the bit the theme can't do", 'Custom Liquid sections, ACF architecture, PHP and JavaScript functionality, API integrations and automation.', 'Liquid · PHP · ACF · REST / GraphQL · JS', 'See a build', $archive ),
	) ),
	// Toolbox, roles and skills: from the 2026 portfolio PDF (pages 1, 3 and 18).
	'home_tools'             => edc_seed_rows( array( 'group', 'name' ), array(
		array( 'platform', 'WordPress' ), array( 'platform', 'Shopify' ), array( 'platform', 'Shopify Plus' ), array( 'platform', 'Liquid' ),
		array( 'interface', 'HTML5' ), array( 'interface', 'CSS3' ), array( 'interface', 'jQuery' ), array( 'interface', 'Tailwind' ), array( 'interface', 'Adobe CC' ),
		array( 'build', 'VS Code' ), array( 'build', 'GitHub' ), array( 'build', 'Cursor' ), array( 'build', 'Claude' ), array( 'build', 'Vercel' ), array( 'build', 'Supabase' ),
	) ),
	'home_roles'             => edc_seed_rows( array( 'title', 'role' ), array(
		array( 'Plan & audit', 'Strategist' ),
		array( 'Document', 'Analyst' ),
		array( 'Pitch & present', 'Consultant' ),
		array( 'Design UI/UX', 'Designer' ),
		array( 'Develop', 'Engineer' ),
		array( 'Test & QA', 'Tester, every device' ),
		array( 'Deploy', 'Release manager' ),
		array( 'Train & support', 'Trainer' ),
	) ),
	'home_skills'            => edc_seed_rows( array( 'name' ), array(
		array( 'Shopify & Shopify Plus' ), array( 'WordPress & WooCommerce' ), array( 'Platform migrations' ), array( 'Wine club stores' ),
		array( 'Custom Shopify builds' ), array( 'B2B ordering' ), array( 'Custom WordPress builds' ), array( 'SaaS websites' ),
		array( 'API integrations' ), array( 'App UI/UX' ), array( 'Design systems' ), array( 'Performance & SEO' ), array( 'Security & maintenance' ),
	) ),
	'home_about_story'       => 'I started in 2011 writing PHP portal systems. Somewhere along the way I became the person teams call when a migration stalls, a site gets hacked, or a checkout breaks at 2am.',
	'home_principles'        => edc_seed_rows( array( 'title', 'text' ), array(
		array( 'Find the real problem first.', "I'd rather spend an hour diagnosing than a week fixing the wrong thing." ),
		array( 'Leave it better documented than I found it.', 'Handovers, redirect maps, and notes for whoever comes next.' ),
		array( 'Say the uncomfortable thing early.', "If the timeline, plugin or plan won't work, you'll hear it in week one, not week six." ),
	) ),
	'home_pull_testimonial'  => $chris,
	'home_lead_text'         => 'I\'ve led development teams, creative teams and junior developers, and I mentor young designers and aspiring developers. Today I coordinate SEO specialists, ads, copywriters and junior developers on client projects.',
	'home_record_heading'    => '',
	// From the 2026 portfolio PDF, page 1.
	'home_stats'             => edc_seed_rows( array( 'value', 'label', 'verified' ), array(
		array( '13+', 'Years', 1 ),
		array( '150+', 'Projects', 1 ),
		array( '5', 'Countries', 1 ),
	) ),
	'home_testimonials'      => array( $rachel, $chris ),
) );

/* ---------------------------------------------------------------------------
 * 5. Inner pages
 * ------------------------------------------------------------------------ */

$pages = array(
	'about'    => 'About',
	'services' => 'Services',
	'contact'  => 'Contact',
);
$page_ids = array();
foreach ( $pages as $slug => $title ) {
	$page_ids[ $slug ] = edc_seed_post( 'page', $slug, $title, array( 'page_template' => "page-templates/{$slug}.php" ) );
}

edc_seed_fields( 'group_edc_page_about', $page_ids['about'], array(
	'about_photo'      => edc_seed_image( 'portrait-elisa-about.jpg', 'Elisa A. Dela Cruz at her desk' ),
	'about_lede'       => '', // ⏳
	'about_story'      => '', // ⏳
	'about_leadership' => '', // ⏳
	'about_principles' => edc_seed_rows( array( 'title', 'text' ), array(
		array( 'Find the real problem first.', "I'd rather spend an hour diagnosing than a week fixing the wrong thing." ),
		array( 'Leave it better documented than I found it.', 'Handovers, redirect maps, and notes for whoever comes next.' ),
		array( 'Say the uncomfortable thing early.', "If the timeline, plugin or plan won't work, you'll hear it in week one, not week six." ),
	) ),
	'about_lessons'    => array(), // ⏳
	'about_more'       => '',      // ⏳
) );

// Service terms: created once as a DRAFT for Elisa to review and publish. Never overwritten.
$terms_page = get_page_by_path( 'service-terms' );
if ( ! $terms_page ) {
	$terms_id = wp_insert_post( wp_slash( array(
		'post_type'    => 'page',
		'post_status'  => 'draft',
		'post_name'    => 'service-terms',
		'post_title'   => 'Service terms',
		'post_content' => implode( "\n\n", array(
			'<!-- wp:paragraph --><p><strong>Draft for Elisa to review. Lines marked [confirm] are suggestions, not commitments yet.</strong></p><!-- /wp:paragraph -->',
			'<!-- wp:heading --><h2>What a fixed price covers</h2><!-- /wp:heading -->',
			'<!-- wp:paragraph --><p>Each package covers what is listed under "Included", for one website on the platform named. Anything outside that list is quoted separately, and nothing extra is charged without your written OK.</p><!-- /wp:paragraph -->',
			'<!-- wp:heading --><h2>When work starts</h2><!-- /wp:heading -->',
			'<!-- wp:paragraph --><p>Work starts once payment is received and I have the access I need. Turnaround times are counted in working days (Monday to Friday, Philippine time) from that point. [confirm]</p><!-- /wp:paragraph -->',
			'<!-- wp:heading --><h2>Access and backups</h2><!-- /wp:heading -->',
			'<!-- wp:paragraph --><p>I will ask for a separate admin or collaborator account rather than your own login, and send you a secure way to share it. Please do not email passwords. I take a backup before changing anything. You can remove my access as soon as the work is done.</p><!-- /wp:paragraph -->',
			'<!-- wp:heading --><h2>Malware removal cover</h2><!-- /wp:heading -->',
			'<!-- wp:paragraph --><p>If the same site is reinfected within 30 days of a cleanup, I clean it again at no cost, provided the hardening steps have not been undone and the site has been kept updated. [confirm]</p><!-- /wp:paragraph -->',
			'<!-- wp:heading --><h2>Care plan</h2><!-- /wp:heading -->',
			'<!-- wp:paragraph --><p>Billed monthly in advance. Unused hours do not roll over. Cancel any time before the next billing date. [confirm]</p><!-- /wp:paragraph -->',
			'<!-- wp:heading --><h2>Refunds</h2><!-- /wp:heading -->',
			'<!-- wp:paragraph --><p>If I cannot take on the work after you pay, you get a full refund. Once work has started, refunds are considered case by case. [confirm]</p><!-- /wp:paragraph -->',
		) ),
	) ), true );
	if ( is_wp_error( $terms_id ) ) {
		WP_CLI::error( $terms_id->get_error_message() );
	}
	WP_CLI::log( "Created draft page #{$terms_id}: Service terms" );
} else {
	$terms_id = (int) $terms_page->ID;
}

// Packages are seeded once (while the list is empty); after that they belong to Elisa in wp-admin.
if ( edc_seed_is_empty( get_field( edc_seed_key( 'group_edc_page_services', 'services_packages' ), $page_ids['services'], false ) ) ) {
	edc_seed_fields( 'group_edc_page_services', $page_ids['services'], array(
	// Fixed-price packages: suggested by Claude, prices and scope confirmed by Elisa (6 Nov 2026).
		// Hidden until she switches show_services_packages on in wp-admin (seeded empty, so never overwritten).
		'show_services_packages'    => '',
		'services_packages_heading' => 'Fixed-price fixes',
		'services_packages_intro'   => 'Small, clearly scoped jobs you can book straight away. Bigger work, like migrations and custom builds, starts with a conversation.',
		'services_packages'         => edc_seed_rows( array( 'name', 'price', 'unit', 'turnaround', 'note', 'included', 'excluded', 'pay_url', 'button_label' ), array(
			array( 'Site audit', '$350', 'per site', 'Written report in 3 working days', 'Credited toward your project if you hire me within 30 days.', "Speed and Core Web Vitals check\nSecurity and malware scan\nTechnical SEO check\nPlugin, app and theme review\nWritten report with prioritised fixes\n30-minute call to walk through it", 'Fixing the issues (quoted separately)', '', 'Book an audit' ),
			array( 'Malware removal', '$450', 'per WordPress site', 'Starts within 1 working day', 'Free re-clean if the same site is reinfected within 30 days.', "Malware and backdoor cleanup\nCore, plugin and theme integrity check\nHardening and security settings\nBlacklist and Search Console review requests\nReport of what was found and fixed", "Rebuilding lost content\nHosting changes", '', 'Book a cleanup' ),
			array( 'Speed fix', '$500', 'per site', 'Done in 5 working days', 'Before and after Lighthouse scores included.', "Audit of the homepage and key templates\nImage, script and caching fixes\nSafe plugin and app clean-up\nBefore and after report", "Theme rebuilds\nHosting migration", '', 'Book a speed fix' ),
			array( 'Care plan', '$150', '/ month', 'Starts the next working day', 'Cancel any time.', "Core, plugin and theme updates\nBackups checked\nUptime monitoring\nUp to 1 hour of small fixes\nMonthly report", "New features\nUnused hours don't roll over", '', 'Start a care plan' ),
		) ),
		'services_terms_page'       => $terms_id,
	) );
}

// From the 2026 portfolio PDF (pages 1 and 18) and the projects above. Pricing and hours stay out until Elisa confirms them.
edc_seed_fields( 'group_edc_page_services', $page_ids['services'], array(
	'services_lede'          => 'Shopify and WordPress, from the first audit to the final handover. One person, one point of contact, nothing lost between departments.',
	'services_engagements'   => edc_seed_rows( array( 'key', 'title', 'who', 'included', 'process', 'related' ), array(
		array(
			'move',
			'Move to Shopify',
			'Stores on WooCommerce, Magento, Wix, Squarespace, VinCreative or WordPress that need to move to Shopify without losing rankings or sales.',
			"Audit of the current store, content and integrations\nProduct, customer and order migration\n301 redirect map and SEO checks\nWine club and subscription integration where needed\nDNS, launch and Search Console",
			'Audit and plan first, then a documented migration, a staged build, testing on every device, and a launch with redirects already in place.',
			array( $p_boat, $sel_ids['enerco'], $sel_ids['grand-teton-collection'], $p_frankland ),
		),
		array(
			'build',
			'Custom builds',
			"Brands and SaaS teams that need a Shopify or WordPress site built properly, or a feature no app can handle.",
			"UI/UX design and design systems\nCustom Shopify themes and sections in Liquid\nWine clubs and B2B ordering\nCustom WordPress themes and API integrations\nMarketing sites for SaaS products",
			"From the first audit to the final handover: plan it, document it, design it, build it, test it, ship it, and train your team on it.",
			array( $p_winehub, $p_perk, $sel_ids['flat-stacks'], $sel_ids['suubee-premium'] ),
		),
		array(
			'rescue',
			'Fix, secure and speed up',
			"Sites that are slow, hacked, or breaking in ways nobody can explain.",
			"Malware cleanup, recovery and hardening\nCore Web Vitals and Lighthouse audits\nPlugin and script audits\nTechnical SEO",
			'Diagnose first: find the real problem, fix it, then document what changed and what to watch.',
			array( $sel_ids['66-site-performance'] ),
		),
		array(
			'care',
			'Look after',
			'Businesses that want one developer who knows their site, for the long term.',
			"Updates, fixes and changes as the business needs them\nConversion and performance monitoring\nNew features and launches with your team\nTraining and handover docs",
			'Ongoing, with one point of contact who already knows how your site is put together.',
			array( $sel_ids['maria-nicola'], $sel_ids['capability-partners'] ),
		),
	) ),
	'services_process'       => edc_seed_rows( array( 'title', 'text' ), array(
		array( 'Diagnose', "Audit the site, the data and what's actually wrong." ),
		array( 'Plan', 'Document the scope, the risks and the steps before anything changes.' ),
		array( 'Build or fix', 'Design, build and test on every device.' ),
		array( 'Hand over', 'Deploy safely, then train your team and leave docs they can run with.' ),
	) ),
	'services_agencies_text' => 'I have worked inside and alongside agencies in Australia and the US since 2015, from custom WordPress themes to Shopify builds and a 66-site performance programme. I can work under your brand, with your process and your clients.',
	'services_faq'           => edc_seed_rows( array( 'q', 'a' ), array(
		array( 'Which platforms do you work on?', 'Shopify and Shopify Plus, WordPress and WooCommerce, with Liquid, PHP, HTML, CSS and JavaScript.' ),
		array( 'Can you move my store without losing SEO?', 'Every migration includes a 301 redirect map and SEO checks before and after launch, so rankings and links carry over.' ),
		array( 'Do you work with agencies?', 'Yes, including white-label and overflow work for agencies in Australia and the US.' ),
		array( 'Where are you based?', 'Manila, working with AU / US / UK time zones.' ),
	) ),
) );

edc_seed_fields( 'group_edc_page_contact', $page_ids['contact'], array(
	'contact_photo'      => edc_seed_image( 'photo-elisa-call.jpg', 'Elisa A. Dela Cruz on a client call' ),
	'contact_lede'       => '',      // ⏳
	'contact_next_steps' => array(), // ⏳
) );

WP_CLI::success( 'Seeded. Pending (⏳) fields are empty.' );
