<?php
/**
 * Homepage "Behind the code": portrait, story, principles, pull quote and leadership.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$portrait   = (int) edc_profile( 'portrait', 0 );
$heading    = edc_opt( 'home_about_heading' );
$story      = array_filter( array_map( 'trim', preg_split( '/\R/', (string) edc_opt( 'home_about_story', '' ) ) ) );
$principles = edc_filled_rows( edc_repeater( 'home_principles', array() ), array( 'title' ) );
$pull       = edc_testimonial( (int) edc_opt( 'home_pull_testimonial', 0 ) );
$lead_title = edc_opt( 'home_lead_title' );
$lead_text  = trim( (string) edc_opt( 'home_lead_text', '' ) );
$about_page = get_page_by_path( 'about' );
$about_url  = ( $about_page && 'publish' === $about_page->post_status ) ? get_permalink( $about_page ) : '';

if ( ! $heading && ! $story && ! $principles && ! $pull && ! $lead_text ) {
	return;
}
$has_portrait = $portrait && wp_attachment_is_image( $portrait );
?>
<section class="sec light" id="about">
  <div class="wrap btc<?php echo $has_portrait ? '' : ' no-portrait'; ?>">
    <?php if ( $has_portrait ) : ?>
    <div class="hex" data-reveal><?php echo wp_get_attachment_image( $portrait, 'large', false, array( 'loading' => 'lazy' ) ); ?></div>
    <?php endif; ?>
    <div data-reveal>
      <?php get_template_part( 'template-parts/common/path-eyebrow', null, array( 'path' => 'about' ) ); ?>
      <?php if ( $heading ) : ?>
      <h2><?php echo edc_inline_html( $heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses. ?></h2>
      <?php endif; ?>
      <?php if ( $story ) : ?>
      <div class="story">
        <?php foreach ( $story as $paragraph ) : ?>
        <p><?php echo esc_html( $paragraph ); ?></p>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php if ( $principles ) : ?>
      <ol class="principles">
        <?php foreach ( $principles as $principle ) : ?>
        <li><h3><?php echo esc_html( trim( $principle['title'] ) ); ?></h3><?php if ( ! empty( $principle['text'] ) ) : ?><p><?php echo esc_html( trim( $principle['text'] ) ); ?></p><?php endif; ?></li>
        <?php endforeach; ?>
      </ol>
      <?php endif; ?>
      <?php if ( $pull ) : ?>
      <figure class="pull">
        <blockquote>&ldquo;<?php echo esc_html( $pull['quote_short'] ? $pull['quote_short'] : $pull['quote'] ); ?>&rdquo;</blockquote>
        <figcaption><?php echo edc_testimonial_attribution( $pull ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></figcaption>
      </figure>
      <?php endif; ?>
      <?php if ( $lead_text ) : ?>
      <div class="lead-strip">
        <?php if ( $lead_title ) : ?><strong><?php echo esc_html( $lead_title ); ?></strong><?php endif; ?>
        <p><?php echo esc_html( $lead_text ); ?></p>
      </div>
      <?php endif; ?>
      <?php if ( $about_url ) : ?>
      <p class="more"><a class="link-arrow" href="<?php echo esc_url( $about_url ); ?>">More about me <span class="arr" aria-hidden="true">→</span></a></p>
      <?php endif; ?>
    </div>
  </div>
</section>
