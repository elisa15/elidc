<?php
/**
 * Homepage closing contact section.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading     = edc_opt( 'home_contact_heading' );
$intro       = edc_opt( 'home_contact_intro' );
$button      = edc_opt( 'home_contact_button' );
$cv_url      = edc_cv_url();
$contact_url = edc_contact_url();
$has_modal   = edc_should_show_section( 'show_contact_modal' );
$email       = trim( (string) edc_opt( 'contact_email', '' ) );
$email       = is_email( $email ) ? $email : '';
$linkedin    = edc_profile( 'linkedin_url' ) ? edc_profile( 'linkedin_url' ) : edc_footer_social_url( 'linkedin' );
$github      = edc_profile( 'github_url' ) ? edc_profile( 'github_url' ) : edc_footer_social_url( 'github' );
$meta        = implode( ' · ', array_filter( array( trim( (string) edc_profile( 'reply_promise' ) ), trim( (string) edc_profile( 'location_line' ) ) ) ) );
$show_button = $button && ( $contact_url || $has_modal );
?>
<section class="contact" id="contact">
  <div class="wrap" data-reveal>
    <?php get_template_part( 'template-parts/common/path-eyebrow', null, array( 'path' => 'contact' ) ); ?>
    <?php if ( $heading ) : ?>
    <h2><?php echo edc_inline_html( $heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses. ?></h2>
    <?php endif; ?>
    <?php if ( $intro ) : ?>
    <p class="sub"><?php echo esc_html( $intro ); ?></p>
    <?php endif; ?>
    <?php if ( $show_button || $cv_url ) : ?>
    <div class="ctas">
      <?php if ( $show_button ) : ?>
      <a class="btn btn-primary<?php echo $has_modal ? ' js-open-contact' : ''; ?>" href="<?php echo esc_url( $contact_url ? $contact_url : home_url( '/' ) ); ?>" data-ga-event="cta_start_project" data-ga-label="Contact <?php echo esc_attr( $button ); ?>"><?php echo esc_html( $button ); ?> <span class="arr" aria-hidden="true">→</span></a>
      <?php endif; ?>
      <?php if ( $cv_url ) : ?>
      <a class="btn btn-ghost" href="<?php echo esc_url( $cv_url ); ?>" download data-ga-event="cv_download" data-ga-label="Contact CV">Download CV</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if ( $email || edc_link_is_usable( $linkedin ) || edc_link_is_usable( $github ) ) : ?>
    <div class="alt">
      <?php if ( $email ) : ?>
      <button type="button" class="copy-email" data-copy="<?php echo esc_attr( $email ); ?>" data-ga-event="copy_email"><?php echo esc_html( $email ); ?></button>
      <a class="mailto-email" href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a>
      <?php endif; ?>
      <?php if ( edc_link_is_usable( $linkedin ) ) : ?><a href="<?php echo esc_url( $linkedin ); ?>" rel="me noopener">LinkedIn</a><?php endif; ?>
      <?php if ( edc_link_is_usable( $github ) ) : ?><a href="<?php echo esc_url( $github ); ?>" rel="me noopener">GitHub</a><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if ( $meta ) : ?>
    <p class="meta"><?php echo esc_html( $meta ); ?></p>
    <?php endif; ?>
    <p class="sr-only" id="copy-status" aria-live="polite"></p>
  </div>
</section>
