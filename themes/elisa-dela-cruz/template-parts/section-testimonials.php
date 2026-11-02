<?php
/**
 * "What clients say" rotating testimonial carousel.
 * The actual quote data is injected into JS via window.edcData.testimonials
 * (see functions.php edc_enqueue_assets()) so this markup only needs to
 * render the shell + correct number of nav dots.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$default_testimonials = array( 1, 2, 3 );
$rows = edc_repeater( 'testimonials', array() );
$count = ! empty( $rows ) ? count( $rows ) : count( $default_testimonials );
?>
<section>
  <div class="wrap">
    <div class="section-head center" data-reveal="up">
      <div class="eyebrow" style="justify-content:center;"><?php echo esc_html( edc_opt( 'testimonials_eyebrow', 'Testimonials' ) ); ?></div>
      <h2><?php echo esc_html( edc_opt( 'testimonials_heading', 'What clients say' ) ); ?></h2>
    </div>
    <div class="testi-wrap" data-reveal="scale">
      <div class="testi-stars" id="testiStars">★★★★★</div>
      <div class="testi-quote" id="testiQuote"></div>
      <div class="testi-person">
        <span class="testi-name" id="testiName"></span>
        <span class="testi-sep"></span>
        <span class="testi-company" id="testiCompany"></span>
      </div>
      <div class="testi-nav" id="testiNav">
        <?php for ( $i = 0; $i < $count; $i++ ) : ?>
          <div class="testi-dot<?php echo ( 0 === $i ) ? ' active' : ''; ?>" data-i="<?php echo intval( $i ); ?>"></div>
        <?php endfor; ?>
      </div>
    </div>
  </div>
</section>
