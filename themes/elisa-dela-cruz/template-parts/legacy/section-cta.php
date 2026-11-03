<?php
/**
 * Final "Let's work together" CTA section.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$default_links = array(
	array( 'label' => 'Email', 'url' => 'mailto:hello@elisaarmas.com' ),
	array( 'label' => 'Portfolio', 'url' => '#work' ),
);
$links = edc_repeater( 'cta_links', $default_links );
?>
<section id="contact">
  <div class="wrap footer-cta" data-reveal="up">
    <div class="eyebrow" style="justify-content:center;"><?php echo esc_html( edc_opt( 'cta_eyebrow', "Let's work together" ) ); ?></div>
    <h2><?php echo wp_kses_post( edc_opt( 'cta_heading', 'Need someone who can<br><em class="serif grad-text" style="font-style:italic;">actually solve it?</em>' ) ); ?></h2>
    <p><?php echo wp_kses_post( edc_opt( 'cta_paragraph', "If you're launching a Shopify store, fixing a broken WordPress site, or planning a migration, tell me what's going on. Happy to take a look." ) ); ?></p>
    <div class="actions">
      <a href="#contact" class="btn btn-fill js-open-contact" data-ga-event="footer_schedule_call" data-ga-label="Footer Schedule a call"><?php echo esc_html( edc_opt( 'cta_btn_primary', 'Schedule a call' ) ); ?></a>
      <a href="mailto:<?php echo esc_attr( edc_opt( 'contact_email', 'hello@elisaarmas.com' ) ); ?>" class="btn btn-outline" data-ga-event="footer_email_me" data-ga-label="Footer Email me"><?php echo esc_html( edc_opt( 'cta_btn_tertiary', 'Email me' ) ); ?></a>
    </div>
    <div class="footer-links">
      <?php foreach ( $links as $link ) :
        $label = isset( $link['label'] ) ? $link['label'] : '';
        $url   = isset( $link['url'] ) ? $link['url'] : '';
        if ( ! edc_link_is_usable( $url ) ) {
          continue; // skips "#" placeholders and anchors to hidden sections
        }
      ?>
        <a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
