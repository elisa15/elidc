<?php
/**
 * Fallback template — used when no more specific template matches
 * (e.g. blog listing, search results, if front-page.php doesn't apply).
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main">
<section style="padding-top:170px;">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<div class="section-head" data-reveal="up">
				<div class="eyebrow"><?php echo is_search() ? 'Search results' : 'Latest'; ?></div>
				<h2><?php echo is_search() ? sprintf( 'Results for "%s"', esc_html( get_search_query() ) ) : 'Recent posts'; ?></h2>
			</div>
			<div style="max-width:760px;">
				<?php while ( have_posts() ) : the_post(); ?>
					<article style="padding:24px 0;border-bottom:1px solid var(--border);">
						<h3 style="font-family:'Fraunces',serif;font-size:22px;margin-bottom:8px;"><a href="<?php the_permalink(); ?>" style="color:#fff;"><?php the_title(); ?></a></h3>
						<div style="color:var(--dim);font-size:14px;line-height:1.6;"><?php the_excerpt(); ?></div>
					</article>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<div class="section-head" data-reveal="up">
				<h2>Nothing found</h2>
				<p>Try a different search, or head back to the <a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color:#fff;text-decoration:underline;">homepage</a>.</p>
			</div>
		<?php endif; ?>
	</div>
</section>
</main>
<?php
get_footer();
