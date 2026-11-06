<?php
/**
 * Homepage track record: verified stats, career log and testimonials.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading = edc_opt( 'home_record_heading' );
$stats   = edc_verified_stats();
$log     = edc_career_log();
$visible = (int) edc_opt( 'home_log_visible', 5 );

$testimonials = array();
foreach ( (array) edc_opt( 'home_testimonials', array() ) as $id ) {
	$t = edc_testimonial( (int) ( is_object( $id ) ? $id->ID : $id ) );
	if ( $t ) {
		$testimonials[] = $t;
	}
}
$testimonials = array_slice( $testimonials, 0, 3 );

if ( ! $stats && ! $log && ! $testimonials ) {
	return;
}
?>
<section class="sec" id="track-record">
  <div class="wrap">
    <div class="sec-head" data-reveal>
      <?php get_template_part( 'template-parts/common/path-eyebrow', null, array( 'path' => 'track-record' ) ); ?>
      <h2><?php echo $heading ? edc_inline_html( $heading ) : esc_html( 'Track record.' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses / esc_html. ?></h2>
    </div>
    <?php
    get_template_part( 'template-parts/common/stats', null, array( 'stats' => $stats ) );
    get_template_part( 'template-parts/common/commit-log', null, array( 'entries' => $log, 'visible' => $visible ) );
    ?>
    <?php if ( $testimonials ) : ?>
    <div class="testis<?php echo 3 === count( $testimonials ) ? ' three' : ''; ?>">
      <?php
      foreach ( $testimonials as $t ) {
        get_template_part( 'template-parts/common/testimonial', null, array( 'testimonial' => $t ) );
      }
      ?>
    </div>
    <?php endif; ?>
  </div>
</section>
