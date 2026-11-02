<?php
/**
 * Contact modal — form itself is delegated to Contact Form 7 via edc_contact_form().
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="modal-overlay" id="contactModal" role="dialog" aria-modal="true" aria-labelledby="contactModalTitle" aria-hidden="true" style="position:fixed;inset:0;background:rgba(9,9,13,.75);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;z-index:300;opacity:0;visibility:hidden;transition:opacity .35s ease;padding:24px;">
  <div class="modal-box" style="position:relative;width:100%;max-width:440px;background:var(--bg-2);border:1px solid var(--border);border-radius:20px;padding:38px 34px;transform:translateY(24px) scale(.96);transition:transform .35s cubic-bezier(.16,1,.3,1);">
    <button id="modalClose" type="button" aria-label="Close" style="position:absolute;top:16px;right:16px;width:32px;height:32px;border-radius:50%;border:1px solid var(--border-2);background:transparent;color:var(--dim);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px;">&times;</button>
    <div class="eyebrow"><?php echo esc_html( edc_opt( 'modal_eyebrow', "Let's talk" ) ); ?></div>
    <h3 id="contactModalTitle" style="font-family:'Fraunces',serif;font-size:24px;font-weight:600;color:#fff;margin-bottom:8px;"><?php echo esc_html( edc_opt( 'modal_heading', 'Tell me about your project' ) ); ?></h3>
    <p style="font-size:14px;color:var(--dim);margin-bottom:22px;line-height:1.6;"><?php echo esc_html( edc_opt( 'modal_subheading', "Send a few details and I'll reply within a day." ) ); ?></p>
    <?php edc_contact_form(); ?>
  </div>
</div>
