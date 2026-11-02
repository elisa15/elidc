<?php
/**
 * Project archive — basic grid fallback (the primary browsing experience
 * is the front-page carousel; this exists for /projects/ and search/SEO).
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section style="padding-top:170px;">
	<div class="wrap">
		<div class="section-head" data-reveal="up">
			<div class="eyebrow">Selected work</div>
			<h2>All projects</h2>
		</div>

		<?php if ( have_posts() ) : ?>
			<div class="archive-project-grid">
				<?php while ( have_posts() ) : the_post();
					$industry_badge = edc_field( 'industry_badge', '' );
					$thumb_url      = get_the_post_thumbnail_url( get_the_ID(), 'edc-case-card' );
					$gradient_start = edc_field( 'gradient_start', '' );
					$gradient_end   = edc_field( 'gradient_end', '' );
					if ( $thumb_url ) {
						$thumb_style = 'background-image:url(' . esc_url( $thumb_url ) . ');';
					} elseif ( $gradient_start && $gradient_end ) {
						$thumb_style = 'background:linear-gradient(135deg,' . esc_attr( $gradient_start ) . ',' . esc_attr( $gradient_end ) . ');';
					} else {
						$thumb_style = 'background:linear-gradient(135deg,var(--magenta),var(--violet));';
					}
				?>
					<a href="<?php the_permalink(); ?>" class="archive-project-card" data-reveal="up">
						<div class="thumb" style="<?php echo esc_attr( $thumb_style ); ?>"></div>
						<div class="body">
							<?php if ( $industry_badge ) : ?><span class="p-badge"><?php echo esc_html( $industry_badge ); ?></span><?php endif; ?>
							<h3><?php the_title(); ?></h3>
							<p><?php echo esc_html( edc_field( 'summary', get_the_excerpt() ) ); ?></p>
						</div>
					</a>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p style="color:var(--dim);">No projects published yet.</p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
