<?php
/**
 * Template Name: About
 *
 * Night hero with portrait, story, the full career log, leadership, how I work,
 * lessons, a little more and the names note. Every block hides while empty.
 * See docs/redesign/04-build-spec.md §3, page templates.
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

	$id         = get_the_ID();
	$h1         = trim( (string) edc_field( 'about_h1', '', $id ) );
	$lede       = trim( (string) edc_field( 'about_lede', '', $id ) );
	$story      = edc_rich_field( 'about_story', $id );
	$leadership = edc_rich_field( 'about_leadership', $id );
	$principles = edc_filled_rows( edc_repeater( 'about_principles', array(), $id ), array( 'title' ) );
	$lessons    = edc_filled_rows( edc_repeater( 'about_lessons', array(), $id ), array( 'text' ) );
	$more       = edc_lines( edc_field( 'about_more', '', $id ) );
	$names      = trim( (string) edc_field( 'about_names', '', $id ) );
	$log        = edc_career_log();
	$portrait   = (int) edc_field( 'about_photo', 0, $id );
	$portrait   = $portrait ? $portrait : (int) edc_profile( 'portrait', 0 );
	$portrait   = ( $portrait && wp_attachment_is_image( $portrait ) ) ? $portrait : 0;
	?>
<section class="page-hero<?php echo $portrait ? ' with-portrait' : ''; ?>">
  <div class="wrap">
    <div>
      <?php get_template_part( 'template-parts/common/path-eyebrow', null, array( 'path' => 'about' ) ); ?>
      <h1><?php echo esc_html( '' !== $h1 ? $h1 : get_the_title() ); ?></h1>
      <?php if ( '' !== $lede ) : ?>
      <p class="lede"><?php echo edc_inline_html( $lede ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses. ?></p>
      <?php endif; ?>
    </div>
    <?php if ( $portrait ) : ?>
    <div class="hex"><?php echo wp_get_attachment_image( $portrait, 'edc-portrait', false, array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></div>
    <?php endif; ?>
  </div>
</section>

	<?php if ( $story ) : ?>
<section class="sec light page-sec">
  <div class="wrap">
    <h2>Story</h2>
    <div class="prose" data-reveal><?php echo $story; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses_post. ?></div>
  </div>
</section>
	<?php endif; ?>

	<?php if ( $log ) : ?>
<section class="sec page-sec" id="track-record">
  <div class="wrap">
    <?php get_template_part( 'template-parts/common/path-eyebrow', null, array( 'path' => 'career' ) ); ?>
    <h2>Career</h2>
    <?php get_template_part( 'template-parts/common/commit-log', null, array( 'entries' => $log, 'visible' => 0 ) ); ?>
  </div>
</section>
	<?php endif; ?>

	<?php if ( $leadership || $principles || $lessons || $more || '' !== $names ) : ?>
<section class="sec light page-sec">
  <div class="wrap">
		<?php if ( $leadership ) : ?>
    <h2>Leadership</h2>
    <div class="prose" data-reveal><?php echo $leadership; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses_post. ?></div>
		<?php endif; ?>

		<?php if ( $principles ) : ?>
    <h2<?php echo $leadership ? ' class="gap-top"' : ''; ?>>How I work</h2>
    <ol class="numbered" data-reveal>
			<?php foreach ( $principles as $row ) : ?>
      <li><h3><?php echo esc_html( trim( $row['title'] ) ); ?></h3><?php if ( ! empty( $row['text'] ) ) : ?><p><?php echo esc_html( trim( $row['text'] ) ); ?></p><?php endif; ?></li>
			<?php endforeach; ?>
    </ol>
		<?php endif; ?>

		<?php if ( $lessons ) : ?>
    <h2<?php echo ( $leadership || $principles ) ? ' class="gap-top"' : ''; ?>>Things I've learned</h2>
    <ul class="lessons" data-reveal>
			<?php foreach ( $lessons as $row ) : ?>
      <li><?php echo esc_html( trim( $row['text'] ) ); ?></li>
			<?php endforeach; ?>
    </ul>
		<?php endif; ?>

		<?php if ( $more ) : ?>
    <h2<?php echo ( $leadership || $principles || $lessons ) ? ' class="gap-top"' : ''; ?>>A little more</h2>
    <div class="prose" data-reveal>
			<?php foreach ( $more as $paragraph ) : ?>
      <p><?php echo esc_html( $paragraph ); ?></p>
			<?php endforeach; ?>
    </div>
		<?php endif; ?>

		<?php if ( '' !== $names ) : ?>
    <p class="small-mono gap-top"><?php echo esc_html( $names ); ?></p>
		<?php endif; ?>
  </div>
</section>
	<?php endif; ?>

	<?php
	get_template_part( 'template-parts/common/cta-strip', null, array(
		'label'   => 'About',
		'cv'      => true,
	) );
endwhile;
?>
</main>
<?php
get_footer();
