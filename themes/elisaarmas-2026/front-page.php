<?php
/**
 * Front page template.
 *
 * @package ElisaArmas2026
 */

get_header();
?>
<main id="main" class="site-main site-main--front">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content', 'page' );
	endwhile;
	?>
</main>
<?php
get_footer();
