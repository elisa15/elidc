<?php
/**
 * Template Name: Services
 *
 * Night hero, engagements, process strip, agencies block and FAQ.
 * Every block hides while empty. See docs/redesign/04-build-spec.md §3.
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

	$id          = get_the_ID();
	$h1          = trim( (string) edc_field( 'services_h1', '', $id ) );
	$lede        = trim( (string) edc_field( 'services_lede', '', $id ) );
	$engagements = edc_filled_rows( edc_repeater( 'services_engagements', array(), $id ), array( 'title' ) );
	$process     = edc_filled_rows( edc_repeater( 'services_process', array(), $id ), array( 'title' ) );
	$agency_h    = trim( (string) edc_field( 'services_agencies_heading', '', $id ) );
	$agency_text = edc_lines( edc_field( 'services_agencies_text', '', $id ) );
	$faq         = edc_filled_rows( edc_repeater( 'services_faq', array(), $id ), array( 'q', 'a' ) );
	$packages    = edc_service_packages();
	$pkg_heading = trim( (string) edc_field( 'services_packages_heading', '', $id ) );
	$pkg_intro   = trim( (string) edc_field( 'services_packages_intro', '', $id ) );
	$terms       = (string) edc_field( 'services_terms_page', '', $id );
	$terms_id    = $terms ? url_to_postid( $terms ) : 0;
	$terms       = ( $terms_id && 'publish' === get_post_status( $terms_id ) ) ? $terms : '';
	$contact_url = edc_contact_url();
	?>
<section class="page-hero">
  <div class="wrap">
    <?php get_template_part( 'template-parts/common/path-eyebrow', null, array( 'path' => 'services' ) ); ?>
    <h1><?php echo esc_html( '' !== $h1 ? $h1 : get_the_title() ); ?></h1>
    <?php if ( '' !== $lede ) : ?>
    <p class="lede"><?php echo edc_inline_html( $lede ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses. ?></p>
    <?php endif; ?>
  </div>
</section>

	<?php if ( $packages ) : ?>
<section class="sec page-sec packages-sec" id="packages">
  <div class="wrap">
		<?php if ( '' !== $pkg_heading ) : ?>
    <h2 data-reveal><?php echo esc_html( $pkg_heading ); ?></h2>
		<?php endif; ?>
		<?php if ( '' !== $pkg_intro ) : ?>
    <p class="sub" data-reveal><?php echo esc_html( $pkg_intro ); ?></p>
		<?php endif; ?>
    <div class="packages">
		<?php
		foreach ( $packages as $pkg ) :
			$included = edc_lines( isset( $pkg['included'] ) ? $pkg['included'] : '' );
			$excluded = edc_lines( isset( $pkg['excluded'] ) ? $pkg['excluded'] : '' );
			$pay      = isset( $pkg['pay_url'] ) && edc_link_is_usable( $pkg['pay_url'] ) ? $pkg['pay_url'] : '';
			$href     = $pay ? $pay : ( $contact_url ? add_query_arg( 'package', $pkg['slug'], $contact_url ) : '' );
			$button   = ! empty( $pkg['button_label'] ) ? $pkg['button_label'] : 'Book now';
			?>
      <article class="package" id="package-<?php echo esc_attr( $pkg['slug'] ); ?>" data-reveal>
        <h3><?php echo esc_html( $pkg['name'] ); ?></h3>
        <p class="price"><b><?php echo esc_html( trim( $pkg['price'] ) ); ?></b><?php if ( ! empty( $pkg['unit'] ) ) : ?> <span><?php echo esc_html( trim( $pkg['unit'] ) ); ?></span><?php endif; ?></p>
			<?php if ( ! empty( $pkg['note'] ) ) : ?>
        <p class="note"><?php echo esc_html( trim( $pkg['note'] ) ); ?></p>
			<?php endif; ?>
			<?php if ( $included ) : ?>
        <ul class="inc"><?php foreach ( $included as $item ) : ?><li><?php echo esc_html( $item ); ?></li><?php endforeach; ?></ul>
			<?php endif; ?>
			<?php if ( $excluded ) : ?>
        <p class="exc"><b>Not included:</b> <?php echo esc_html( implode( ' · ', $excluded ) ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $pkg['turnaround'] ) ) : ?>
        <p class="turn"><?php echo esc_html( trim( $pkg['turnaround'] ) ); ?></p>
			<?php endif; ?>
			<?php if ( $href ) : ?>
        <a class="btn btn-primary" href="<?php echo esc_url( $href ); ?>"<?php echo $pay ? ' rel="noopener"' : ''; ?> data-ga-event="package_book" data-ga-label="<?php echo esc_attr( $pkg['name'] . ( $pay ? ' (checkout)' : ' (contact)' ) ); ?>"><?php echo esc_html( $button ); ?> <span class="arr" aria-hidden="true">→</span><span class="sr-only"> (<?php echo esc_html( $pkg['name'] ); ?>)</span></a>
			<?php endif; ?>
      </article>
		<?php endforeach; ?>
    </div>
		<?php if ( $terms ) : ?>
    <p class="pkg-terms">Fixed prices are for the scope listed. <a href="<?php echo esc_url( $terms ); ?>">Read the service terms</a>.</p>
		<?php endif; ?>
  </div>
</section>
	<?php endif; ?>

	<?php if ( $engagements ) : ?>
<section class="sec light page-sec">
  <div class="wrap">
    <div class="engagements">
		<?php
		foreach ( $engagements as $row ) :
			$key      = isset( $row['key'] ) ? sanitize_key( $row['key'] ) : '';
			$who      = isset( $row['who'] ) ? trim( (string) $row['who'] ) : '';
			$included = edc_lines( isset( $row['included'] ) ? $row['included'] : '' );
			$runs     = isset( $row['process'] ) ? trim( (string) $row['process'] ) : '';
			$related  = array();
			foreach ( (array) ( isset( $row['related'] ) ? $row['related'] : array() ) as $rel ) {
				$rel_id = (int) ( is_object( $rel ) ? $rel->ID : $rel );
				if ( $rel_id && 'publish' === get_post_status( $rel_id ) ) {
					$project = edc_project( $rel_id );
					if ( $project ) {
						$related[] = $project;
					}
				}
			}
			?>
      <article class="engagement"<?php echo $key ? ' id="' . esc_attr( $key ) . '"' : ''; ?> data-reveal>
        <header>
          <?php if ( $key ) : ?><span class="eyebrow"><?php echo esc_html( '$ ' . $key ); ?></span><?php endif; ?>
          <h2><?php echo esc_html( trim( $row['title'] ) ); ?></h2>
        </header>
			<?php if ( '' !== $who ) : ?>
        <div><h3>Who it's for</h3><p><?php echo esc_html( $who ); ?></p></div>
			<?php endif; ?>
			<?php if ( $included ) : ?>
        <div><h3>What's included</h3><ul><?php foreach ( $included as $item ) : ?><li><?php echo esc_html( $item ); ?></li><?php endforeach; ?></ul></div>
			<?php endif; ?>
			<?php if ( '' !== $runs ) : ?>
        <div><h3>How it usually runs</h3><p><?php echo esc_html( $runs ); ?></p></div>
			<?php endif; ?>
			<?php if ( $related ) : ?>
        <p class="related">
				<?php foreach ( $related as $project ) : ?>
					<?php $target = $project['url'] ? $project['url'] : get_post_type_archive_link( 'project' ) . '#project-' . $project['slug']; ?>
          <a class="link-arrow" href="<?php echo esc_url( $target ); ?>"><?php echo esc_html( $project['client'] ? $project['client'] : $project['title'] ); ?> <span class="arr" aria-hidden="true">→</span></a>
				<?php endforeach; ?>
        </p>
			<?php endif; ?>
      </article>
		<?php endforeach; ?>
    </div>
  </div>
</section>
	<?php endif; ?>

	<?php if ( $process ) : ?>
<section class="sec page-sec">
  <div class="wrap">
    <h2>Process</h2>
    <ol class="numbered" data-reveal>
		<?php foreach ( $process as $row ) : ?>
      <li><h3><?php echo esc_html( trim( $row['title'] ) ); ?></h3><?php if ( ! empty( $row['text'] ) ) : ?><p><?php echo esc_html( trim( $row['text'] ) ); ?></p><?php endif; ?></li>
		<?php endforeach; ?>
    </ol>
  </div>
</section>
	<?php endif; ?>

	<?php if ( $agency_text || $faq ) : ?>
<section class="sec light page-sec">
  <div class="wrap">
		<?php if ( $agency_text ) : ?>
			<?php if ( '' !== $agency_h ) : ?>
    <h2><?php echo esc_html( $agency_h ); ?></h2>
			<?php endif; ?>
    <div class="prose" data-reveal>
			<?php foreach ( $agency_text as $paragraph ) : ?>
      <p><?php echo esc_html( $paragraph ); ?></p>
			<?php endforeach; ?>
    </div>
		<?php endif; ?>

		<?php if ( $faq ) : ?>
    <h2<?php echo $agency_text ? ' class="gap-top"' : ''; ?>>FAQ</h2>
    <div class="faq">
			<?php foreach ( $faq as $row ) : ?>
      <details><summary><?php echo esc_html( trim( $row['q'] ) ); ?></summary><div class="a"><?php foreach ( edc_lines( $row['a'] ) as $paragraph ) : ?><p><?php echo esc_html( $paragraph ); ?></p><?php endforeach; ?></div></details>
			<?php endforeach; ?>
    </div>
		<?php endif; ?>
  </div>
</section>
	<?php endif; ?>

	<?php
	get_template_part( 'template-parts/common/cta-strip', null, array( 'label' => 'Services' ) );
endwhile;
?>
</main>
<?php
get_footer();
