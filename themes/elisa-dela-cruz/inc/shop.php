<?php
/**
 * WooCommerce for the fixed-price fixes shop (/fixes/). Loaded only when WooCommerce is active.
 * Products live in the "fixes" category and are created by docs/redesign/tools/shop-setup.php.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'woocommerce' );
} );

/**
 * The /fixes/ page, or null when it doesn't exist.
 */
function edc_fixes_page() {
	$page = get_page_by_path( 'fixes' );
	return ( $page && 'page' === $page->post_type ) ? $page : null;
}

/**
 * Whether the shop is open to visitors (the /fixes/ page is published).
 */
function edc_shop_is_open() {
	$page = edc_fixes_page();
	return $page && 'publish' === $page->post_status;
}

/**
 * Products in the "fixes" category, in menu order.
 *
 * @return WC_Product[]
 */
function edc_fix_products() {
	return wc_get_products( array(
		'status'   => 'publish',
		'category' => array( 'fixes' ),
		'orderby'  => 'menu_order',
		'order'    => 'ASC',
		'limit'    => 20,
	) );
}

/**
 * Normalised card data for one product.
 */
function edc_fix_product( WC_Product $product ) {
	$id   = $product->get_id();
	$meta = function ( $key ) use ( $id ) {
		return trim( (string) get_post_meta( $id, '_edc_' . $key, true ) );
	};
	$external = $product->is_type( 'external' );
	$url      = $external ? trim( (string) $product->get_product_url() ) : '';

	return array(
		'id'         => $id,
		'slug'       => $product->get_slug(),
		'name'       => $product->get_name(),
		'price'      => (float) $product->get_price(),
		'price_html' => wc_price( (float) $product->get_price(), array( 'decimals' => 0 ) ),
		'unit'       => $meta( 'unit' ),
		'note'       => $meta( 'note' ),
		'turnaround' => $meta( 'turnaround' ),
		'included'   => edc_lines( $meta( 'included' ) ),
		'excluded'   => edc_lines( $meta( 'excluded' ) ),
		'summary'    => wp_strip_all_tags( $product->get_short_description() ),
		'max_qty'    => $product->is_sold_individually() ? 1 : (int) ( $meta( 'max_qty' ) ? $meta( 'max_qty' ) : 1 ),
		'external'   => $external,
		'ext_url'    => $url,
		'button'     => $external ? $product->get_button_text() : '',
		'buyable'    => ! $external && $product->is_purchasable() && $product->is_in_stock(),
	);
}

/**
 * "Buy now" link: adds the product (and quantity) and goes straight to checkout.
 */
function edc_fix_buy_url( $product_id, $qty = 1 ) {
	return add_query_arg( array(
		'add-to-cart' => (int) $product_id,
		'quantity'    => max( 1, (int) $qty ),
		'edc_buy'     => 1,
	), wc_get_checkout_url() );
}

add_filter( 'woocommerce_add_to_cart_redirect', function ( $url ) {
	return ! empty( $_REQUEST['edc_buy'] ) ? wc_get_checkout_url() : $url; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
} );

/**
 * No shop archive or product pages of their own: everything lives on /fixes/.
 */
add_action( 'template_redirect', function () {
	$page = edc_fixes_page();
	if ( ! $page || ! ( is_shop() || is_product() || is_product_category() || is_product_tag() ) ) {
		return;
	}
	// While /fixes/ is still a draft, send visitors to the packages on Services instead of a 404.
	if ( ! edc_shop_is_open() ) {
		$services = get_page_by_path( 'services' );
		$target   = $services ? get_permalink( $services ) . '#packages' : home_url( '/' );
		wp_safe_redirect( $target, 302 );
		exit;
	}
	$target = get_permalink( $page );
	if ( is_product() ) {
		$target .= '#fix-' . get_post_field( 'post_name', get_queried_object_id() );
	}
	wp_safe_redirect( $target, 301 );
	exit;
} );

/**
 * Short checkout: services need no postal address, company or phone.
 * The block checkout reads these from the country locale.
 */
add_filter( 'woocommerce_get_country_locale_default', function ( $locale ) {
	foreach ( array( 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'phone' ) as $field ) {
		$locale[ $field ]['required'] = false;
		$locale[ $field ]['hidden']   = true;
	}
	return $locale;
} );
add_filter( 'woocommerce_get_country_locale', function ( $locales ) {
	foreach ( $locales as $country => $fields ) {
		foreach ( array( 'address_1', 'address_2', 'city', 'state', 'postcode' ) as $field ) {
			$locales[ $country ][ $field ]['required'] = false;
			$locales[ $country ][ $field ]['hidden']   = true;
		}
	}
	return $locales;
} );
add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );

/**
 * Two questions at checkout, so nobody has to send a separate message.
 */
add_action( 'woocommerce_init', function () {
	if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
		return;
	}
	woocommerce_register_additional_checkout_field( array(
		'id'         => 'edc/website',
		'label'      => 'Website URL',
		'location'   => 'order',
		'type'       => 'text',
		'required'   => true,
		'attributes' => array(
			'autocomplete' => 'url',
		),
		'sanitize_callback' => function ( $value ) {
			$value = trim( (string) $value );
			return ( '' !== $value && ! preg_match( '#^https?://#i', $value ) ) ? 'https://' . $value : $value;
		},
		'validate_callback' => function ( $value ) {
			if ( ! wp_http_validate_url( $value ) ) {
				return new WP_Error( 'edc_website', 'Please enter the website address, e.g. https://example.com' );
			}
		},
	) );
	woocommerce_register_additional_checkout_field( array(
		'id'       => 'edc/details',
		'label'    => "What's going on?",
		'location' => 'order',
		'type'     => 'text',
		'required' => false,
	) );
} );

/**
 * After payment: what happens next.
 */
add_action( 'woocommerce_thankyou', function ( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	$email = trim( (string) edc_opt( 'contact_email', '' ) );
	?>
	<section class="next-steps" aria-labelledby="next-steps-h">
		<h2 id="next-steps-h">What happens next</h2>
		<ol class="numbered">
			<li><h3>You'll get an email from me</h3><p>Within one working day, asking for access to the site. Please don't send passwords by email: I'll send a secure way to share them.</p></li>
			<li><h3>I start the work</h3><p>Turnaround times count from when I have access.</p></li>
			<li><h3>You get a short report</h3><p>What I found, what I changed and anything to watch.</p></li>
		</ol>
		<?php if ( is_email( $email ) ) : ?>
		<p class="next-mail">Questions in the meantime: <a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
		<?php endif; ?>
	</section>
	<?php
}, 5 );

/**
 * The classic WooCommerce stylesheets aren't used (cart and checkout are blocks).
 */
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/**
 * Store API nonce for the "Add to order" buttons on /fixes/.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_page( 'fixes' ) ) {
		return;
	}
	wp_add_inline_script( 'edc-site', 'window.edcShop=' . wp_json_encode( array(
		'nonce'    => wp_create_nonce( 'wc_store_api' ),
		'api'      => esc_url_raw( rest_url( 'wc/store/v1/' ) ),
		'checkout' => wc_get_checkout_url(),
		'currency' => get_woocommerce_currency_symbol(),
	) ) . ';', 'before' );
}, 20 );

/**
 * Cart count and total for the order bar.
 */
function edc_cart_summary() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return array( 'count' => 0, 'total' => '' );
	}
	return array(
		'count' => (int) WC()->cart->get_cart_contents_count(),
		'total' => wp_strip_all_tags( wc_price( (float) WC()->cart->get_cart_contents_total(), array( 'decimals' => 0 ) ) ),
	);
}

// "What's going on?" covers it; no separate order-notes box.
add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
