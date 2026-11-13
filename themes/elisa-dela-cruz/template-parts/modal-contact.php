<?php
/**
 * Contact modal — form itself is delegated to Contact Form 7 via edc_contact_form().
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$edc_modal_eyebrow    = edc_opt( 'modal_eyebrow', "Let's talk" );
$edc_modal_subheading = edc_opt( 'modal_subheading', "Send a few details and I'll reply within a day." );
?>
<div class="modal" id="contactModal" role="dialog" aria-modal="true" aria-labelledby="contactModalTitle" data-challenge="<?php echo esc_url( rest_url( 'edc/v1/cf7-challenge' ) ); ?>">
  <div class="modal-box">
    <button class="close" type="button" aria-label="Close">&times;</button>
    <?php if ( $edc_modal_eyebrow ) : ?>
    <div class="eyebrow"><?php echo esc_html( $edc_modal_eyebrow ); ?></div>
    <?php endif; ?>
    <h3 id="contactModalTitle"><?php echo esc_html( edc_opt( 'modal_heading', 'Tell me about your project' ) ); ?></h3>
    <?php if ( $edc_modal_subheading ) : ?>
    <p><?php echo esc_html( $edc_modal_subheading ); ?></p>
    <?php endif; ?>
    <?php edc_contact_form(); ?>
  </div>
</div>
