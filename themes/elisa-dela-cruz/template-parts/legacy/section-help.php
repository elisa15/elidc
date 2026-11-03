<?php
/**
 * "What people usually come to me for" pain-point list.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$default_items = array(
	'Website running slowly',
	'WooCommerce migration',
	'Website hacked or compromised',
	'Agency overflow work',
	'Performance optimisation',
	'Custom Shopify development',
	'WordPress maintenance',
	'Technical SEO',
);
$rows = edc_repeater( 'help_list_items', array() );
$items = array();
if ( ! empty( $rows ) ) {
	foreach ( $rows as $row ) {
		$items[] = isset( $row['item'] ) ? $row['item'] : '';
	}
} else {
	$items = $default_items;
}
?>
<section class="light">
  <div class="wrap">
    <div class="section-head center" data-reveal="up">
      <div class="eyebrow" style="justify-content:center;"><?php echo esc_html( edc_opt( 'help_eyebrow', 'Sound familiar?' ) ); ?></div>
      <h2><?php echo esc_html( edc_opt( 'help_heading', 'What people usually come to me for' ) ); ?></h2>
    </div>
    <div class="help-list">
      <?php foreach ( $items as $i => $item ) : ?>
        <div class="help-row" data-reveal="up" style="--i:<?php echo intval( $i ); ?>"><span class="help-num"><?php echo esc_html( str_pad( $i + 1, 2, '0', STR_PAD_LEFT ) ); ?></span><p><?php echo esc_html( $item ); ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
