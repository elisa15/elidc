<?php
/**
 * Set up the fixed-price fixes shop: store settings, the "fixes" category, the products
 * and the /fixes/ page. Idempotent; products are matched by SKU and only CREATED here:
 * once a product exists, its price and text belong to Elisa in wp-admin.
 *
 *   wp eval-file docs/redesign/tools/shop-setup.php
 *   (add --exec="define('EDC_SHOP_PUBLISH', true);" to publish /fixes/; default is draft)
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Run with: wp eval-file docs/redesign/tools/shop-setup.php\n" );
}
if ( ! function_exists( 'WC' ) ) {
	WP_CLI::error( 'WooCommerce must be active.' );
}

/* Store settings for selling services: guest checkout, no shipping, no accounts. */
$settings = array(
	'woocommerce_currency'                         => 'USD',
	'woocommerce_price_num_decimals'               => '0',
	'woocommerce_enable_guest_checkout'            => 'yes',
	'woocommerce_enable_checkout_login_reminder'   => 'no',
	'woocommerce_enable_signup_and_login_from_checkout' => 'no',
	'woocommerce_enable_myaccount_registration'    => 'no',
	'woocommerce_ship_to_countries'                => 'disabled',
	'woocommerce_calc_taxes'                       => 'no', // Elisa to confirm with her accountant.
	'woocommerce_enable_coupons'                   => 'yes',
	'woocommerce_enable_reviews'                   => 'no',
	'woocommerce_cart_redirect_after_add'          => 'no',
	'woocommerce_enable_ajax_add_to_cart'          => 'yes',
	'woocommerce_checkout_company_field'           => 'hidden',
	'woocommerce_checkout_address_2_field'         => 'hidden',
	'woocommerce_checkout_phone_field'             => 'hidden',
	'woocommerce_coming_soon'                      => 'no',
	'woocommerce_analytics_enabled'                => 'no',
	'woocommerce_allow_tracking'                   => 'no',
	'woocommerce_show_marketplace_suggestions'     => 'no',
);
foreach ( $settings as $key => $value ) {
	if ( get_option( $key ) !== $value ) {
		update_option( $key, $value );
		WP_CLI::log( "Setting {$key} = {$value}" );
	}
}

/* Category. */
$term = get_term_by( 'slug', 'fixes', 'product_cat' );
if ( ! $term ) {
	$created = wp_insert_term( 'Fixed-price fixes', 'product_cat', array( 'slug' => 'fixes' ) );
	if ( is_wp_error( $created ) ) {
		WP_CLI::error( $created->get_error_message() );
	}
	$term_id = (int) $created['term_id'];
	WP_CLI::log( "Created product category #{$term_id}: fixes" );
} else {
	$term_id = (int) $term->term_id;
}

/*
 * Products. Prices for Site audit, Malware removal, Speed fix and Care plan were confirmed
 * by Elisa on 6 Nov 2026. "1 hour of fixes" and "Custom section or template" are
 * SUGGESTED prices and must be confirmed before /fixes/ is published.
 */
$products = array(
	array(
		'sku' => 'edc-site-audit', 'name' => 'Site audit', 'price' => '350', 'type' => 'simple', 'individual' => true, 'max' => 1,
		'unit' => 'per site', 'turnaround' => 'Written report in 3 working days',
		'note' => 'Credited toward your project if you hire me within 30 days.',
		'summary' => 'Find out what is slowing your site down, what is at risk and what to fix first.',
		'included' => "Speed and Core Web Vitals check\nSecurity and malware scan\nTechnical SEO check\nPlugin, app and theme review\nWritten report with prioritised fixes\n30-minute call to walk through it",
		'excluded' => 'Fixing the issues (quoted separately)',
	),
	array(
		'sku' => 'edc-malware-removal', 'name' => 'Malware removal', 'price' => '450', 'type' => 'simple', 'individual' => false, 'max' => 5,
		'unit' => 'per WordPress site', 'turnaround' => 'Starts within 1 working day',
		'note' => 'Free re-clean if the same site is reinfected within 30 days.',
		'summary' => 'Hacked WordPress site cleaned, hardened and back in search results.',
		'included' => "Malware and backdoor cleanup\nCore, plugin and theme integrity check\nHardening and security settings\nBlacklist and Search Console review requests\nReport of what was found and fixed",
		'excluded' => "Rebuilding lost content\nHosting changes",
	),
	array(
		'sku' => 'edc-speed-fix', 'name' => 'Speed fix', 'price' => '500', 'type' => 'simple', 'individual' => false, 'max' => 5,
		'unit' => 'per site', 'turnaround' => 'Done in 5 working days',
		'note' => 'Before and after Lighthouse scores included.',
		'summary' => 'A faster homepage and key pages, measured before and after.',
		'included' => "Audit of the homepage and key templates\nImage, script and caching fixes\nSafe plugin and app clean-up\nBefore and after report",
		'excluded' => "Theme rebuilds\nHosting migration",
	),
	array(
		'sku' => 'edc-hour-of-fixes', 'name' => '1 hour of fixes', 'price' => '75', 'type' => 'simple', 'individual' => false, 'max' => 5,
		'unit' => 'per hour', 'turnaround' => 'Starts within 2 working days',
		'note' => 'Buy 1 to 5 hours. Unused time is refunded.',
		'summary' => 'Bug fixes and small changes on Shopify or WordPress: a broken layout, a form that stopped sending, a plugin conflict.',
		'included' => "Bug fixes and small changes\nShopify or WordPress\nNote of what was changed",
		'excluded' => "New features or pages (see Custom section)\nWork beyond the hours bought, without your OK",
	),
	array(
		'sku' => 'edc-custom-section', 'name' => 'Custom section or template', 'price' => '350', 'type' => 'simple', 'individual' => false, 'max' => 5,
		'unit' => 'per section', 'turnaround' => 'Done in 5 working days',
		'note' => 'One section or one page template, built to match your theme.',
		'summary' => 'A new Shopify section or WordPress page template, editable from your admin.',
		'included' => "One custom section or page template\nEditable settings in your admin\nResponsive on every device\nOne round of changes",
		'excluded' => "Full theme builds (start a project instead)\nCopywriting and photography",
	),
	array(
		'sku' => 'edc-care-plan', 'name' => 'Care plan', 'price' => '150', 'type' => 'external', 'individual' => true, 'max' => 1,
		'unit' => '/ month', 'turnaround' => 'Starts the next working day',
		'note' => 'Cancel any time.',
		'summary' => 'Updates, backups, monitoring and small fixes, every month.',
		'included' => "Core, plugin and theme updates\nBackups checked\nUptime monitoring\nUp to 1 hour of small fixes\nMonthly report",
		'excluded' => "New features\nUnused hours don't roll over",
		'button' => 'Start a care plan',
	),
);

foreach ( $products as $i => $p ) {
	$existing = wc_get_product_id_by_sku( $p['sku'] );
	if ( $existing ) {
		WP_CLI::log( "Exists, left as is: {$p['name']} (#{$existing})" );
		continue;
	}
	$product = 'external' === $p['type'] ? new WC_Product_External() : new WC_Product_Simple();
	$product->set_name( $p['name'] );
	$product->set_sku( $p['sku'] );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'hidden' ); // Shown on /fixes/ only.
	$product->set_regular_price( $p['price'] );
	$product->set_short_description( $p['summary'] );
	$product->set_menu_order( $i + 1 );
	$product->set_category_ids( array( $term_id ) );
	$product->set_reviews_allowed( false );
	if ( 'external' === $p['type'] ) {
		$product->set_product_url( '' ); // Elisa's Stripe subscription link goes here.
		$product->set_button_text( $p['button'] );
	} else {
		$product->set_virtual( true );
		$product->set_sold_individually( $p['individual'] );
		$product->set_tax_status( 'none' );
	}
	$id = $product->save();
	foreach ( array( 'unit', 'turnaround', 'note', 'included', 'excluded', 'max' ) as $key ) {
		update_post_meta( $id, '_edc_' . ( 'max' === $key ? 'max_qty' : $key ), (string) $p[ $key ] );
	}
	WP_CLI::log( "Created product #{$id}: {$p['name']} (\${$p['price']})" );
}

/* The /fixes/ page. Draft unless EDC_SHOP_PUBLISH is set. */
$page = get_page_by_path( 'fixes' );
if ( ! $page ) {
	$page_id = wp_insert_post( array(
		'post_type'   => 'page',
		'post_status' => ( defined( 'EDC_SHOP_PUBLISH' ) && EDC_SHOP_PUBLISH ) ? 'publish' : 'draft',
		'post_name'   => 'fixes',
		'post_title'  => 'Fixed-price fixes',
	), true );
	if ( is_wp_error( $page_id ) ) {
		WP_CLI::error( $page_id->get_error_message() );
	}
	update_post_meta( $page_id, '_wp_page_template', 'page-templates/fixes.php' );
	WP_CLI::log( "Created page #{$page_id}: /fixes/ (" . get_post_status( $page_id ) . ')' );
} else {
	update_post_meta( $page->ID, '_wp_page_template', 'page-templates/fixes.php' );
	if ( defined( 'EDC_SHOP_PUBLISH' ) && EDC_SHOP_PUBLISH && 'publish' !== $page->post_status ) {
		wp_update_post( array( 'ID' => $page->ID, 'post_status' => 'publish' ) );
		WP_CLI::log( 'Published /fixes/' );
	}
}

WP_CLI::success( 'Shop set up. /fixes/ is ' . get_post_status( get_page_by_path( 'fixes' ) ) . '.' );
