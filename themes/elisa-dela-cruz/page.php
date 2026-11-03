<?php
/**
 * Generic page template.
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
	?>
	<section style="padding-top:170px;">
		<div class="wrap" style="max-width:760px;">
			<div class="section-head" data-reveal="up">
				<h1 style="font-size:clamp(30px,4vw,46px);font-weight:700;letter-spacing:-.015em;color:#fff;"><?php the_title(); ?></h1>
			</div>
			<div style="color:var(--dim);font-size:16px;line-height:1.8;">
				<?php the_content(); ?>
			</div>
		</div>
	</section>
	<?php
endwhile;
?>
</main>
<?php
get_footer();
