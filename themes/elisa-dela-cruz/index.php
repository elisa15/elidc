<?php
/**
 * Fallback for blog, search and 404 views.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_search() ) {
	/* translators: %s: search query. */
	$edc_title = sprintf( __( 'Results for "%s"', 'elisa-dela-cruz' ), get_search_query() );
} elseif ( is_404() ) {
	$edc_title = __( 'Page not found', 'elisa-dela-cruz' );
} elseif ( is_archive() ) {
	$edc_title = wp_strip_all_tags( get_the_archive_title() );
} else {
	$edc_title = __( 'Posts', 'elisa-dela-cruz' );
}

get_header();
?>
<main id="main">
<section class="page-hero">
  <div class="wrap">
    <h1><?php echo esc_html( $edc_title ); ?></h1>
  </div>
</section>
<section class="sec light page-sec">
  <div class="wrap">
    <?php if ( have_posts() && ! is_404() ) : ?>
    <div class="post-list">
      <?php while ( have_posts() ) : the_post(); ?>
      <article>
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <div class="prose"><?php the_excerpt(); ?></div>
      </article>
      <?php endwhile; ?>
    </div>
    <?php the_posts_pagination(); ?>
    <?php else : ?>
    <p class="empty-note"><?php esc_html_e( 'Nothing here.', 'elisa-dela-cruz' ); ?> <a class="link-arrow" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the homepage', 'elisa-dela-cruz' ); ?> <span class="arr" aria-hidden="true">→</span></a></p>
    <?php endif; ?>
  </div>
</section>
</main>
<?php
get_footer();
