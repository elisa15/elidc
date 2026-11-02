<?php
/**
 * Single proof-testimonial strip directly under the hero.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stars = max( 0, min( 5, intval( edc_opt( 'proof_rating', 5 ) ) ) );
?>
<section class="proof-testi" data-reveal="up">
  <div class="wrap">
    <div class="proof-stars"><?php echo esc_html( str_repeat( '★', $stars ) . str_repeat( '☆', 5 - $stars ) ); ?></div>
    <blockquote><?php echo wp_kses_post( edc_opt( 'proof_quote', '"Elisa doesn\'t just build websites. She fixes the problems other developers walk away from."' ) ); ?></blockquote>
    <div class="proof-attribution"><span class="line"></span><?php echo esc_html( edc_opt( 'proof_attribution', 'From a client review' ) ); ?><span class="line"></span></div>
  </div>
</section>
