<?php
/**
 * Seed a LOCAL test site with the verified content from docs/redesign/05-content-entry.md.
 *
 *   wp eval-file docs/redesign/tools/seed-content.php
 *
 * Idempotent: re-running updates existing posts and options, never duplicates them.
 * Every ⏳ pending field is written as empty. Never run this against the live site.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Run with: wp eval-file docs/redesign/tools/seed-content.php\n" );
}
if ( false !== stripos( home_url(), 'elisaarmas.com' ) || false !== stripos( site_url(), 'elisaarmas.com' ) ) {
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
 * Write fields by name for one group. Empty strings / arrays clear the field.
 */
function edc_seed_fields( $group, $post_id, $values ) {
	foreach ( $values as $name => $value ) {
		update_field( edc_seed_key( $group, $name ), $value, $post_id );
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
	'portrait'           => '', // The illustrated portrait lives only in the live Media Library.
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
	'quote'                => "Give her an unfamiliar technology, and she'll learn it quickly, then teach you a thing or two along the way. … Elisa is someone I was fortunate to hire and would gladly work with again.",
	'quote_short'          => "She brings the confidence to challenge assumptions and the judgment to make that pushback constructive. If a timeline needs rethinking or there's a better way to approach a problem, she'll speak up and explain why.",
	'person_name'          => 'Christopher Sorini',
	'person_role'          => '', // ⏳
	'company'              => '', // ⏳
	'relationship'         => 'employer',
	'source_url'           => '', // ⏳
	'permission_confirmed' => 0,  // ⏳ off until Elisa confirms
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

$p_wineries = edc_seed_project( 'three-wineries', 'Three wineries, moved to Shopify', 2, array( 'solved', 'built' ), array( 'shopify' ), array(
	'tier'                => 'featured',
	'client_display_name' => "Boat O'Craigo, Frankland Estate and God's Hill",
	'industry_badge'      => 'Wine',
	'region'              => 'Australia',
	'role'                => 'Design & development',
	'summary'             => "Australian wineries moved off older platforms onto custom Shopify stores. For Boat O'Craigo I did the new design too.",
	'names'               => edc_seed_rows( array( 'name', 'note' ), array(
		array( "Boat O'Craigo", 'Yarra Valley' ),
		array( 'Frankland Estate', '' ),
		array( "God's Hill", 'Barossa Valley' ),
	) ),
	'chips'               => $chips( array( 'Migration' ) ),
	'visual_mode'         => 'screenshot',
	'screenshot'          => edc_seed_image( 'boatocraigo.jpg', "Boat O'Craigo homepage" ),
	'browser_label'       => 'boatocraigo.com.au',
	'visual_badge'        => '+ 2 more wineries',
	'live_url'            => 'https://boatocraigo.com.au/',
	'behind_meta'         => 'migrations',
	'behind_rows'         => $behind( array(
		array( 'from', 'Vin Creative (×2), WordPress (×1)' ),
		array( 'to', 'custom Shopify stores' ),
		array( 'did', "Boat O'Craigo: new design + build. Frankland Estate, God's Hill: custom build + migration" ),
	) ),
) );

$p_perk = edc_seed_project( 'perkrunner-winehub', 'Selling subscriptions to Shopify merchants', 3, array( 'built' ), array( 'wordpress' ), array(
	'tier'                 => 'featured',
	'client_display_name'  => 'PerkRunner and Winehub',
	'industry_badge'       => 'Shopify subscription and loyalty software',
	'role'                 => 'Design & development',
	'summary'              => 'Designed and built from scratch: the websites for two Shopify subscription and loyalty platforms, PerkRunner and Winehub.',
	'chips'                => $chips( array( 'Design' ) ),
	'visual_mode'          => 'split',
	'screenshot'           => edc_seed_image( 'perkrunner.jpg', 'PerkRunner homepage' ),
	'screenshot_secondary' => edc_seed_image( 'winehub.jpg', 'Winehub homepage' ),
	'browser_label'        => 'perkrunner.com · winehub.io',
	'behind_meta'          => 'built · from scratch',
	'behind_rows'          => $behind( array(
		array( 'needed', 'marketing sites for two SaaS products' ),
		array( 'did', 'design, build and launch, from blank page to live' ),
		array( 'stack', 'WordPress' ),
	) ),
) );

$p_sound = edc_seed_project( 'sound-and-sight', 'From design file to working website', 4, array( 'built' ), array( 'wordpress', 'elementor' ), array(
	'tier'                => 'featured',
	'client_display_name' => 'Sound & Sight Solutions',
	'industry_badge'      => 'Smart home & AV',
	'region'              => 'Chicago, US',
	'role'                => 'Development',
	'summary'             => 'A custom WordPress site for Sound & Sight Solutions, a Chicago smart-home and AV company, built from their design all the way to deployment.',
	'chips'               => $chips( array( 'Custom' ) ),
	'visual_mode'         => 'screenshot',
	'screenshot'          => edc_seed_image( 'soundsight-design.jpg', 'Sound and Sight Solutions homepage design' ),
	'browser_label'       => 'Sound & Sight Solutions',
	'visual_badge'        => 'design file',
	'behind_meta'         => 'built · design → deploy',
	'behind_rows'         => $behind( array(
		array( 'needed', 'a working site matching the approved design' ),
		array( 'did', 'custom build, responsive behaviour, deployment' ),
		array( 'stack', 'WordPress · Elementor' ),
	) ),
) );

$p_cenit = edc_seed_project( 'cenit-oasis', 'A Shopify store, then the sister brand', 5, array( 'built' ), array( 'shopify', 'wordpress', 'elementor' ), array(
	'tier'                 => 'featured',
	'client_display_name'  => 'Cenit Lighting and Oasis Water Philippines',
	'industry_badge'       => 'Lighting and water',
	'region'               => 'Philippines',
	'role'                 => 'Shopify & WordPress developer',
	'summary'              => 'Cenit Lighting hired me to build their Shopify store. Then they brought me back to turn their design for Oasis Water into a working WordPress site in Elementor.',
	'card_span'            => 'half',
	'visual_mode'          => 'split',
	'screenshot'           => edc_seed_image( 'cenit.jpg', 'Cenit Lighting Shopify store' ),
	'screenshot_secondary' => edc_seed_image( 'oasis.jpg', 'Oasis Water Philippines homepage' ),
	'browser_label'        => 'shop.cenitlighting.com · oasiswaterph.com',
	'behind_meta'          => 'built · repeat client',
	'behind_rows'          => $behind( array(
		array( 'first', 'Shopify store for Cenit Lighting (their WordPress site stayed as is)' ),
		array( 'then', 'Oasis Water Philippines: their design, built in WordPress + Elementor' ),
		array( 'why it matters', 'they came back' ),
	) ),
) );

// Selected work and highlights: name, platform and region/role only (summaries and links ⏳).
$minor = array(
	array( 'tvi-resource-development', 'TVI Resource Development', array( 'wordpress', 'elementor' ), 'selected', 'PH', 'From scratch to deployment', array() ),
	array( 'st-augustine-gold-copper', 'St. Augustine Gold & Copper', array( 'wordpress', 'elementor' ), 'selected', 'PH', 'From scratch to deployment', array() ),
	array( 'dd-music', 'D&D Music', array( 'wordpress', 'elementor' ), 'selected', 'PH', 'From scratch to deployment', array() ),
	array( 'futbol-funatics', 'Futbol Funatics', array( 'wordpress', 'elementor' ), 'selected', 'PH', 'From scratch to deployment', array() ),
	array( 'sorini-strategic-advisors', 'Sorini Strategic Advisors', array( 'wordpress', 'elementor' ), 'selected', '', 'Design → deploy', array() ),
	array( 'vertical-green', 'Vertical Green', array( 'wordpress', 'elementor' ), 'selected', '', 'Implemented their design + updates', array() ),
	array( 'home-of-lights', 'Home of Lights', array( 'shopify' ), 'highlight', '', 'Custom PDP template', array(
		array( 'did', 'custom product field + custom product page template' ),
		array( 'stack', 'Shopify · Liquid' ),
	) ),
);
$minor_ids = array();
foreach ( $minor as $i => $m ) {
	list( $slug, $title, $platforms, $tier, $region, $role, $rows ) = $m;
	$minor_ids[ $slug ] = edc_seed_project( $slug, $title, 10 + $i, array( 'built' ), $platforms, array(
		'tier'        => $tier,
		'region'      => $region,
		'role'        => $role,
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
		array( 'o', '✔ Three wineries, moved to Shopify', $archive ),
	) ),
	'home_featured_projects' => array( $p_beauty, $p_wineries, $p_perk, $p_sound, $p_cenit ),
	'home_more_projects'     => array(
		$minor_ids['tvi-resource-development'],
		$minor_ids['st-augustine-gold-copper'],
		$minor_ids['dd-music'],
		$minor_ids['futbol-funatics'],
		$minor_ids['sorini-strategic-advisors'],
		$minor_ids['home-of-lights'],
	),
	'home_called'            => edc_seed_rows( array( 'label', 'title', 'text', 'stack', 'link_label', 'link' ), array(
		array( '01 / move', 'Moving house without losing the furniture', 'Migrations: WooCommerce to Shopify, host to host, theme to theme. Rankings, redirects, customers and orders come along.', '301 maps · data import · DNS · Search Console', 'See a migration', $archive ),
		array( '02 / rescue', 'Getting hacked sites back on their feet', "Malware cleanup, recovery, and hardening, so it doesn't happen twice.", 'file forensics · clean restore · hardening · monitoring', 'See a rescue', '' ),
		array( '03 / speed', 'Making slow sites stop being slow', 'Core Web Vitals, bloated page builders, image pipelines, caching, and the plugin nobody remembers installing.', 'Lighthouse · caching · images · script audits', 'See a speed fix', '' ),
		array( '04 / build', "Building the bit the theme can't do", 'Custom Liquid sections, ACF architecture, PHP and JavaScript functionality, API integrations and automation.', 'Liquid · PHP · ACF · REST / GraphQL · JS', 'See a build', $archive ),
	) ),
	'home_about_story'       => 'I started in 2011 writing PHP portal systems. Somewhere along the way I became the person teams call when a migration stalls, a site gets hacked, or a checkout breaks at 2am.',
	'home_principles'        => edc_seed_rows( array( 'title', 'text' ), array(
		array( 'Find the real problem first.', "I'd rather spend an hour diagnosing than a week fixing the wrong thing." ),
		array( 'Leave it better documented than I found it.', 'Handovers, redirect maps, and notes for whoever comes next.' ),
		array( 'Say the uncomfortable thing early.', "If the timeline, plugin or plan won't work, you'll hear it in week one, not week six." ),
	) ),
	'home_pull_testimonial'  => $chris,
	'home_lead_text'         => 'I\'ve led development teams, creative teams and junior developers, and I mentor young designers and aspiring developers. Today I coordinate SEO specialists, ads, copywriters and junior developers on client projects.',
	'home_record_heading'    => '',      // ⏳
	'home_stats'             => array(), // ⏳
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

edc_seed_fields( 'group_edc_page_services', $page_ids['services'], array(
	'services_lede'          => '', // ⏳
	'services_engagements'   => edc_seed_rows( array( 'key', 'title', 'who', 'included', 'process', 'related' ), array(
		array( 'build', 'Build', '', '', '', array() ),
		array( 'move', 'Move', '', '', '', array() ),
		array( 'rescue', 'Rescue', '', '', '', array() ),
		array( 'care', 'Look after', '', '', '', array() ),
	) ),
	'services_process'       => edc_seed_rows( array( 'title', 'text' ), array(
		array( 'Diagnose', '' ),
		array( 'Plan', '' ),
		array( 'Build or fix', '' ),
		array( 'Hand over', '' ),
	) ),
	'services_agencies_text' => '',      // ⏳
	'services_faq'           => array(), // ⏳
) );

edc_seed_fields( 'group_edc_page_contact', $page_ids['contact'], array(
	'contact_lede'       => '',      // ⏳
	'contact_next_steps' => array(), // ⏳
) );

WP_CLI::success( 'Seeded. Pending (⏳) fields are empty.' );
