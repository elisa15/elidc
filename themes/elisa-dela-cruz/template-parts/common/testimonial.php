<?php
/**
 * One testimonial card. Args: testimonial (from edc_testimonial()).
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$t = isset( $args['testimonial'] ) ? $args['testimonial'] : null;
if ( ! $t ) {
	return;
}
?>
<figure class="testi" data-reveal>
  <span class="kind<?php echo 'employer' === $t['relationship'] ? ' emp' : ''; ?>"><?php echo esc_html( $t['kind_label'] ); ?></span>
  <blockquote>&ldquo;<?php echo esc_html( $t['quote'] ); ?>&rdquo;</blockquote>
  <figcaption class="who"><?php echo edc_testimonial_attribution( $t ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></figcaption>
  <?php if ( edc_link_is_usable( $t['source_url'] ) && $t['source_label'] ) : ?>
  <a class="more" href="<?php echo esc_url( $t['source_url'] ); ?>" rel="noopener"><?php echo esc_html( $t['source_label'] ); ?></a>
  <?php endif; ?>
</figure>
