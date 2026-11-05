<?php
/**
 * Generic page: night title, content on cream.
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
<section class="page-hero">
  <div class="wrap">
    <h1><?php the_title(); ?></h1>
  </div>
</section>
	<?php if ( '' !== trim( wp_strip_all_tags( get_the_content() ) ) ) : ?>
<section class="sec light page-sec">
  <div class="wrap">
    <div class="prose entry"><?php the_content(); ?></div>
  </div>
</section>
	<?php endif; ?>
	<?php
endwhile;
?>
</main>
<?php
get_footer();
