<?php
/**
 * Template Name: Contact
 *
 * The CF7 form inline (the same form as the modal), email and LinkedIn
 * alternatives, what happens next, reply promise and location.
 * See docs/redesign/04-build-spec.md §3.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main">
<?php
while ( have_posts() ) :
	the_post();

	$id       = get_the_ID();
	$h1       = trim( (string) edc_field( 'contact_h1', '', $id ) );
	$lede     = trim( (string) edc_field( 'contact_lede', '', $id ) );
	$steps    = edc_filled_rows( edc_repeater( 'contact_next_steps', array(), $id ), array( 'text' ) );
	$has_form = edc_opt( 'cf7_shortcode', '' ) && function_exists( 'wpcf7' );
	$email    = trim( (string) edc_opt( 'contact_email', '' ) );
	$email    = is_email( $email ) ? $email : '';
	$linkedin = edc_profile( 'linkedin_url' ) ? edc_profile( 'linkedin_url' ) : edc_footer_social_url( 'linkedin' );
	$linkedin = edc_link_is_usable( $linkedin ) ? $linkedin : '';
	$meta     = implode( ' · ', array_filter( array( trim( (string) edc_profile( 'reply_promise' ) ), trim( (string) edc_profile( 'location_line' ) ) ) ) );
	?>
<section class="page-hero">
  <div class="wrap">
    <?php get_template_part( 'template-parts/common/path-eyebrow', null, array( 'path' => 'contact' ) ); ?>
    <h1><?php echo esc_html( '' !== $h1 ? $h1 : get_the_title() ); ?></h1>
    <?php if ( '' !== $lede ) : ?>
    <p class="lede"><?php echo edc_inline_html( $lede ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses. ?></p>
    <?php endif; ?>
  </div>
</section>

	<?php if ( $has_form || $email || $linkedin || $steps || $meta ) : ?>
<section class="sec light page-sec">
  <div class="wrap contact-layout<?php echo $has_form ? '' : ' no-form'; ?>">
		<?php if ( $has_form ) : ?>
    <div class="contact-form">
      <h2 class="sr-only">Send a message</h2>
      <?php edc_contact_form(); ?>
    </div>
		<?php endif; ?>
    <div class="contact-side">
		<?php if ( $email || $linkedin ) : ?>
      <h2><?php echo $has_form ? 'Or reach me directly' : 'Reach me directly'; ?></h2>
      <div class="alt">
			<?php if ( $email ) : ?>
        <button type="button" class="copy-email" data-copy="<?php echo esc_attr( $email ); ?>" data-ga-event="copy_email"><?php echo esc_html( $email ); ?></button>
        <a class="mailto-email" href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a>
			<?php endif; ?>
			<?php if ( $linkedin ) : ?>
        <a href="<?php echo esc_url( $linkedin ); ?>" rel="me noopener">LinkedIn</a>
			<?php endif; ?>
      </div>
      <p class="sr-only" id="copy-status" aria-live="polite"></p>
		<?php endif; ?>
		<?php if ( $steps ) : ?>
      <h2>What happens next</h2>
      <ol class="numbered">
			<?php foreach ( $steps as $row ) : ?>
        <li><p><?php echo esc_html( trim( $row['text'] ) ); ?></p></li>
			<?php endforeach; ?>
      </ol>
		<?php endif; ?>
		<?php if ( $meta ) : ?>
      <p class="meta"><?php echo esc_html( $meta ); ?></p>
		<?php endif; ?>
    </div>
  </div>
</section>
	<?php endif; ?>
	<?php
endwhile;
?>
</main>
<?php
get_footer();
