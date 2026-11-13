<?php
/**
 * Homepage "Called in for": up to four capability cards.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cards   = array_slice( edc_filled_rows( edc_repeater( 'home_called', array() ), array( 'title' ) ), 0, 4 );
$heading = edc_opt( 'home_called_heading' );
if ( ! $cards ) {
	return;
}
?>
<section class="sec" id="services">
  <div class="wrap">
    <div class="sec-head" data-reveal>
      <?php get_template_part( 'template-parts/common/path-eyebrow', null, array( 'path' => 'services' ) ); ?>
      <?php if ( $heading ) : ?>
      <h2><?php echo esc_html( $heading ); ?></h2>
      <?php endif; ?>
    </div>
    <div class="called-grid" data-reveal>
      <?php
      foreach ( $cards as $card ) :
        $field = function ( $key ) use ( $card ) {
          return isset( $card[ $key ] ) && is_string( $card[ $key ] ) ? trim( $card[ $key ] ) : '';
        };
        ?>
      <div class="called">
        <?php if ( $field( 'label' ) ) : ?><span class="num"><?php echo esc_html( $field( 'label' ) ); ?></span><?php endif; ?>
        <h3><?php echo esc_html( $field( 'title' ) ); ?></h3>
        <?php if ( $field( 'text' ) ) : ?><p><?php echo esc_html( $field( 'text' ) ); ?></p><?php endif; ?>
        <?php if ( $field( 'stack' ) ) : ?><span class="stack"><?php echo esc_html( $field( 'stack' ) ); ?></span><?php endif; ?>
        <?php if ( $field( 'link_label' ) && edc_link_is_usable( $field( 'link' ) ) ) : ?>
        <a class="link-arrow" href="<?php echo esc_url( $field( 'link' ) ); ?>"><?php echo esc_html( $field( 'link_label' ) ); ?> <span class="arr" aria-hidden="true">→</span></a>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
