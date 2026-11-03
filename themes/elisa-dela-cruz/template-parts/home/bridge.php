<?php
/**
 * Homepage bridge statement.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bridge = edc_opt( 'home_bridge' );
if ( ! $bridge ) {
	return;
}
?>
<section class="bridge" aria-label="Philosophy">
  <div class="wrap" data-reveal>
    <p><?php echo edc_inline_html( $bridge ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses. ?></p>
  </div>
</section>
