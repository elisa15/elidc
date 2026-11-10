<?php
/**
 * Work page: graphic design gallery (Site content → Graphic design).
 * Each piece links to the full-size image; site.js opens it in a lightbox.
 * Hidden while switched off or empty.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! edc_opt_bool( 'show_graphics', true ) ) {
	return;
}

$kinds = array(
	'print'        => 'Print & magazine ads',
	'banner'       => 'Banners & promos',
	'illustration' => 'Illustration',
	'editing'      => 'Image editing & mockups',
	'email'        => 'E-newsletters',
);

$pieces = array();
foreach ( edc_repeater( 'graphics', array() ) as $row ) {
	$image = isset( $row['image'] ) ? (int) $row['image'] : 0;
	if ( ! $image || ! wp_attachment_is_image( $image ) ) {
		continue;
	}
	$pieces[] = array(
		'image'  => $image,
		'title'  => isset( $row['title'] ) ? trim( (string) $row['title'] ) : '',
		'client' => isset( $row['client'] ) ? trim( (string) $row['client'] ) : '',
		'kind'   => ( isset( $row['kind'] ) && isset( $kinds[ $row['kind'] ] ) ) ? $kinds[ $row['kind'] ] : '',
		'note'   => isset( $row['note'] ) ? trim( (string) $row['note'] ) : '',
	);
}
if ( ! $pieces ) {
	return;
}

$heading = trim( (string) edc_opt( 'graphics_heading', '' ) );
$intro   = trim( (string) edc_opt( 'graphics_intro', '' ) );
?>
<section class="sec graphics" id="graphics" aria-labelledby="graphics-h">
  <div class="wrap">
    <?php get_template_part( 'template-parts/common/path-eyebrow', null, array( 'path' => 'graphics' ) ); ?>
    <h2 id="graphics-h"><?php echo esc_html( '' !== $heading ? $heading : 'Graphic design' ); ?></h2>
    <?php if ( '' !== $intro ) : ?>
    <p class="sub"><?php echo esc_html( $intro ); ?></p>
    <?php endif; ?>

    <ul class="gfx-grid">
      <?php
      foreach ( $pieces as $piece ) :
        $label = trim( $piece['title'] . ( $piece['client'] ? ' · ' . $piece['client'] : '' ) );
        $alt   = trim( (string) get_post_meta( $piece['image'], '_wp_attachment_image_alt', true ) );
        $alt   = '' !== $alt ? $alt : $label;
        $full  = wp_get_attachment_image_url( $piece['image'], 'full' );
        ?>
      <li data-reveal>
        <figure>
          <a class="gfx-link" href="<?php echo esc_url( $full ); ?>" data-caption="<?php echo esc_attr( $label ); ?>" data-ga-event="graphic_open" data-ga-label="<?php echo esc_attr( $label ); ?>">
            <?php echo wp_get_attachment_image( $piece['image'], 'large', false, array( 'alt' => $alt, 'loading' => 'lazy', 'sizes' => '(max-width: 760px) 100vw, (max-width: 1100px) 50vw, 33vw' ) ); ?>
            <span class="sr-only"> (open full size)</span>
          </a>
          <figcaption>
            <?php if ( $piece['kind'] ) : ?><span class="gfx-kind"><?php echo esc_html( $piece['kind'] ); ?></span><?php endif; ?>
            <b><?php echo esc_html( $piece['title'] ); ?></b>
            <?php if ( $piece['client'] || $piece['note'] ) : ?>
            <span class="gfx-meta"><?php echo esc_html( implode( ' · ', array_filter( array( $piece['client'], $piece['note'] ) ) ) ); ?></span>
            <?php endif; ?>
          </figcaption>
        </figure>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <dialog class="lightbox" aria-label="Full-size image">
    <button class="lightbox-close" type="button" aria-label="Close">×</button>
    <img src="" alt="">
    <p class="lightbox-cap"></p>
  </dialog>
</section>
