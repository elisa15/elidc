<?php
/**
 * "My toolbox" floating chip cloud.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$default_chips = array(
	array( 'label' => 'Shopify', 'size' => 'lg' ),
	array( 'label' => 'WordPress', 'size' => 'lg' ),
	array( 'label' => 'Liquid', 'size' => '' ),
	array( 'label' => 'WooCommerce', 'size' => '' ),
	array( 'label' => 'PHP', 'size' => '' ),
	array( 'label' => 'JavaScript', 'size' => '' ),
	array( 'label' => 'Git', 'size' => '' ),
	array( 'label' => 'Performance', 'size' => 'lg' ),
	array( 'label' => 'Technical SEO', 'size' => '' ),
	array( 'label' => 'Security', 'size' => 'sm' ),
	array( 'label' => 'GitHub', 'size' => 'sm' ),
	array( 'label' => 'GA4', 'size' => 'sm' ),
	array( 'label' => 'Klaviyo', 'size' => 'sm' ),
	array( 'label' => 'Cursor', 'size' => '' ),
	array( 'label' => 'Claude', 'size' => '' ),
	array( 'label' => 'ChatGPT', 'size' => '' ),
	array( 'label' => 'Figma', 'size' => 'sm' ),
);
$chips = edc_repeater( 'toolbox_chips', $default_chips );
?>
<section class="light" id="toolbox">
  <div class="wrap">
    <div class="glass-strip">
      <div class="section-head center" data-reveal="up"><div class="eyebrow" style="justify-content:center;"><?php echo esc_html( edc_opt( 'toolbox_eyebrow', 'My toolbox' ) ); ?></div><h2 class="serif" style="font-size:26px;"><?php echo esc_html( edc_opt( 'toolbox_heading', 'Tools I use every day' ) ); ?></h2></div>
      <div class="toolbox-cloud">
        <?php foreach ( $chips as $i => $chip ) :
          $label = isset( $chip['label'] ) ? $chip['label'] : '';
          $size  = isset( $chip['size'] ) ? $chip['size'] : '';
          $class = trim( 'chip ' . $size );
          $delay = -0.4 - ( $i * 0.7 );
        ?>
          <span class="<?php echo esc_attr( $class ); ?>" data-reveal="up" style="--i:<?php echo intval( $i ); ?>;animation-delay:<?php echo esc_attr( $delay ); ?>s;"><?php echo esc_html( $label ); ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
