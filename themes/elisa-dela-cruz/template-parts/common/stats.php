<?php
/**
 * Verified stats row. Args: stats (from edc_verified_stats()). Nothing renders without stats.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stats = isset( $args['stats'] ) ? (array) $args['stats'] : array();
if ( ! $stats ) {
	return;
}
?>
<div class="stats" data-reveal>
  <?php foreach ( $stats as $stat ) : ?>
  <div class="stat"><b><?php echo esc_html( trim( $stat['value'] ) ); ?></b><span><?php echo esc_html( trim( $stat['label'] ) ); ?></span></div>
  <?php endforeach; ?>
</div>
