<?php
/**
 * Closing "Start a project" strip for inner pages. Args: heading, label (GA label prefix).
 * Hidden when the button would have nowhere to go.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading     = isset( $args['heading'] ) ? (string) $args['heading'] : '';
$label       = isset( $args['label'] ) ? (string) $args['label'] : 'Page';
$cv_url      = ! empty( $args['cv'] ) ? edc_cv_url() : '';
$contact_url = edc_contact_url();
$has_modal   = edc_should_show_section( 'show_contact_modal' );
$button      = edc_opt( 'nav_cta_label', 'Start a project' );
if ( ! $contact_url && ! $has_modal && ! $cv_url ) {
	return;
}
?>
<section class="contact cta-strip">
  <div class="wrap" data-reveal>
    <?php if ( $heading ) : ?>
    <h2><?php echo esc_html( $heading ); ?></h2>
    <?php endif; ?>
    <div class="ctas">
      <?php if ( $contact_url || $has_modal ) : ?>
      <a class="btn btn-primary<?php echo $has_modal ? ' js-open-contact' : ''; ?>" href="<?php echo esc_url( $contact_url ? $contact_url : home_url( '/' ) ); ?>" data-ga-event="cta_start_project" data-ga-label="<?php echo esc_attr( $label . ' ' . $button ); ?>"><?php echo esc_html( $button ); ?> <span class="arr" aria-hidden="true">→</span></a>
      <?php endif; ?>
      <?php if ( $cv_url ) : ?>
      <a class="btn btn-ghost" href="<?php echo esc_url( $cv_url ); ?>" download data-ga-event="cv_download" data-ga-label="<?php echo esc_attr( $label . ' CV' ); ?>">Download CV</a>
      <?php endif; ?>
    </div>
  </div>
</section>
