<?php
/**
 * /projects/ archive: kind and platform filters (plain links, filtered server-side
 * in inc/projects-query.php), then the Featured, Selected work and Technical
 * highlights tiers. See docs/redesign/04-build-spec.md §3.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$filters = edc_project_filters();
$archive = get_post_type_archive_link( 'project' );

$tiers = array(
	'featured'  => array(),
	'selected'  => array(),
	'highlight' => array(),
);
while ( have_posts() ) {
	the_post();
	$tier = (string) edc_field( 'tier', 'selected', get_the_ID() );
	if ( ! isset( $tiers[ $tier ] ) ) {
		$tier = 'selected';
	}
	$tiers[ $tier ][] = get_the_ID();
}
rewind_posts();

/*
 * One filter row per taxonomy, built from terms that have at least one project.
 * Each link keeps the other filter's value.
 */
$filter_rows = array(
	'type'     => array(
		'taxonomy' => 'project_type',
		'label'    => 'Kind of work',
		'all'      => 'All',
		'order'    => array( 'built', 'solved' ),
	),
	'platform' => array(
		'taxonomy' => 'project_platform',
		'label'    => 'Platform',
		'all'      => 'All platforms',
		'order'    => array( 'wordpress', 'shopify', 'woocommerce', 'elementor' ),
	),
);
foreach ( $filter_rows as $param => $row ) {
	$terms = get_terms( array(
		'taxonomy'   => $row['taxonomy'],
		'hide_empty' => true,
	) );
	$terms = is_wp_error( $terms ) ? array() : $terms;
	$order = array_flip( $row['order'] );
	usort( $terms, function ( $a, $b ) use ( $order ) {
		$x = isset( $order[ $a->slug ] ) ? $order[ $a->slug ] : 99;
		$y = isset( $order[ $b->slug ] ) ? $order[ $b->slug ] : 99;
		return $x - $y;
	} );
	$filter_rows[ $param ]['terms'] = $terms;
}

$filter_url = function ( $param, $slug ) use ( $filters, $archive ) {
	$args           = array_filter( $filters );
	$args[ $param ] = $slug;
	return add_query_arg( array_filter( $args ), $archive );
};

$has_posts = (bool) array_filter( $tiers );

get_header();
?>
<main id="main">
<section class="page-hero">
  <div class="wrap">
    <?php get_template_part( 'template-parts/common/path-eyebrow', null, array( 'path' => 'work' ) ); ?>
    <h1>The work <em class="voice">behind</em> the websites</h1>
    <p class="lede">Builds you can see, and fixes you mostly can't.</p>
  </div>
</section>

<section class="sec light archive-work">
  <div class="wrap">
    <?php foreach ( $filter_rows as $param => $row ) : ?>
      <?php if ( count( $row['terms'] ) > 1 ) : ?>
    <nav class="filters" aria-label="<?php echo esc_attr( 'Filter by ' . strtolower( $row['label'] ) ); ?>">
      <a href="<?php echo esc_url( $filter_url( $param, '' ) ); ?>"<?php echo '' === $filters[ $param ] ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $row['all'] ); ?></a>
        <?php foreach ( $row['terms'] as $term ) : ?>
      <a href="<?php echo esc_url( $filter_url( $param, $term->slug ) ); ?>"<?php echo $term->slug === $filters[ $param ] ? ' aria-current="true"' : ''; ?> data-ga-event="filter_change" data-ga-label="<?php echo esc_attr( $param . ':' . $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></a>
        <?php endforeach; ?>
    </nav>
      <?php endif; ?>
    <?php endforeach; ?>

    <?php if ( ! $has_posts ) : ?>
    <p class="empty-note">Nothing here yet for that filter. <a class="link-arrow" href="<?php echo esc_url( $archive ); ?>">See all projects <span class="arr" aria-hidden="true">→</span></a></p>
    <?php endif; ?>

    <?php if ( $tiers['featured'] ) : ?>
    <h2 class="tier-h">Featured</h2>
    <div class="work-grid">
      <?php
      foreach ( $tiers['featured'] as $i => $post_id ) {
        get_template_part( 'template-parts/project/card', null, array(
          'post_id' => $post_id,
          'context' => 'archive',
          'index'   => $i,
        ) );
      }
      ?>
    </div>
    <?php endif; ?>

    <?php if ( $tiers['selected'] ) : ?>
    <h2 class="tier-h">Selected work</h2>
    <div class="compact-grid">
      <?php
      foreach ( $tiers['selected'] as $post_id ) {
        get_template_part( 'template-parts/project/compact', null, array( 'post_id' => $post_id ) );
      }
      ?>
    </div>
    <?php endif; ?>

    <?php if ( $tiers['highlight'] ) : ?>
    <h2 class="tier-h">Technical highlights</h2>
    <div class="highlights">
      <?php
      foreach ( $tiers['highlight'] as $post_id ) {
        get_template_part( 'template-parts/project/highlight', null, array( 'post_id' => $post_id ) );
      }
      ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php
get_template_part( 'template-parts/common/cta-strip', null, array(
	'heading' => 'Got one like these? Tell me about it.',
	'label'   => 'Projects archive',
) );
?>
</main>
<?php
get_footer();
