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

// Selected work: projects with a screenshot or visual first, text-only cards after (menu order kept within each group).
$with_visual = array();
$text_only   = array();
foreach ( $tiers['selected'] as $post_id ) {
	$visual = edc_project_visual( $post_id, (bool) edc_field( 'confidential', false, $post_id ) );
	if ( 'none' === $visual['mode'] ) {
		$text_only[] = $post_id;
	} else {
		$with_visual[] = $post_id;
	}
}
$tiers['selected'] = array_merge( $with_visual, $text_only );

/*
 * Filters, shown as a shell command: `$ ls ~/elisa/work --kind … --platform …`.
 * Every project is in the page; the ones that don't match carry `hidden`, so plain
 * links work without JS and site.js can re-filter in place with no reload.
 */
$flags = array(
	'type'     => array(
		'flag'     => '--kind',
		'label'    => 'Kind of work',
		'taxonomy' => 'project_type',
		'order'    => array( 'built', 'solved' ),
	),
	'platform' => array(
		'flag'     => '--platform',
		'label'    => 'Platform',
		'taxonomy' => 'project_platform',
		'order'    => array( 'wordpress', 'shopify', 'woocommerce', 'elementor' ),
	),
);

// Kind and platform slugs for every project, used for matching and counts.
$meta = array();
foreach ( $tiers as $tier_ids ) {
	foreach ( $tier_ids as $post_id ) {
		$meta[ $post_id ] = array(
			'type'     => wp_list_pluck( edc_post_term_list( $post_id, 'project_type' ), 'slug' ),
			'platform' => wp_list_pluck( edc_post_term_list( $post_id, 'project_platform' ), 'slug' ),
		);
	}
}
$matches = function ( $post_id, $want ) use ( $meta ) {
	foreach ( $want as $param => $slug ) {
		if ( '' !== $slug && ! in_array( $slug, $meta[ $post_id ][ $param ], true ) ) {
			return false;
		}
	}
	return true;
};
$count_for = function ( $param, $slug ) use ( $meta, $filters, $matches ) {
	$want           = $filters;
	$want[ $param ] = $slug;
	return count( array_filter( array_keys( $meta ), function ( $id ) use ( $matches, $want ) {
		return $matches( $id, $want );
	} ) );
};

foreach ( $flags as $param => $flag ) {
	$terms = get_terms( array(
		'taxonomy'   => $flag['taxonomy'],
		'hide_empty' => true,
	) );
	$terms = is_wp_error( $terms ) ? array() : $terms;
	$order = array_flip( $flag['order'] );
	usort( $terms, function ( $a, $b ) use ( $order ) {
		$x = isset( $order[ $a->slug ] ) ? $order[ $a->slug ] : 99;
		$y = isset( $order[ $b->slug ] ) ? $order[ $b->slug ] : 99;
		return $x - $y;
	} );
	$flags[ $param ]['terms'] = $terms;
}

$filter_url = function ( $param, $slug ) use ( $filters, $archive ) {
	$args           = array_filter( $filters );
	$args[ $param ] = $slug;
	return add_query_arg( array_filter( $args ), $archive );
};

$visible = array();
foreach ( $tiers as $tier => $tier_ids ) {
	$visible[ $tier ] = array_values( array_filter( $tier_ids, function ( $id ) use ( $matches, $filters ) {
		return $matches( $id, $filters );
	} ) );
}
$total = count( array_merge( $visible['featured'], $visible['selected'], $visible['highlight'] ) );

$tier_parts = array(
	'featured'  => array( 'Featured', 'work-grid', 'template-parts/project/card' ),
	'selected'  => array( 'Selected work', 'compact-grid', 'template-parts/project/compact' ),
	'highlight' => array( 'Technical highlights', 'highlights', 'template-parts/project/highlight' ),
);

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
    <nav class="cmd-filter" aria-label="Filter projects" data-archive="<?php echo esc_url( $archive ); ?>">
      <div class="cmd-bar" aria-hidden="true"><i></i><i></i><i></i><span>zsh · ~/elisa/work</span></div>
      <p class="cmd-line">
        <span class="cmd-prompt" aria-hidden="true">$ ls ~/elisa/work</span>
        <?php foreach ( $flags as $param => $flag ) : ?>
          <?php if ( count( $flag['terms'] ) > 1 ) : ?>
        <span class="cmd-flag" role="group" aria-label="<?php echo esc_attr( $flag['label'] ); ?>">
          <span class="cmd-name" aria-hidden="true"><?php echo esc_html( $flag['flag'] ); ?></span>
          <a class="cmd-arg" href="<?php echo esc_url( $filter_url( $param, '' ) ); ?>" data-param="<?php echo esc_attr( $param ); ?>" data-value=""<?php echo '' === $filters[ $param ] ? ' aria-current="true"' : ''; ?>>all</a>
            <?php foreach ( $flag['terms'] as $term ) : ?>
          <a class="cmd-arg" href="<?php echo esc_url( $filter_url( $param, $term->slug ) ); ?>" data-param="<?php echo esc_attr( $param ); ?>" data-value="<?php echo esc_attr( $term->slug ); ?>"<?php echo $term->slug === $filters[ $param ] ? ' aria-current="true"' : ''; ?> data-ga-event="filter_change" data-ga-label="<?php echo esc_attr( $param . ':' . $term->slug ); ?>"><?php echo esc_html( strtolower( $term->name ) ); ?><sup data-count><?php echo (int) $count_for( $param, $term->slug ); ?></sup></a>
            <?php endforeach; ?>
        </span>
          <?php endif; ?>
        <?php endforeach; ?>
      </p>
      <p class="cmd-out" aria-live="polite"><span class="cmd-arrow" aria-hidden="true">→</span> <b data-total><?php echo (int) $total; ?></b> <span data-noun><?php echo 1 === $total ? 'project' : 'projects'; ?></span><span class="cmd-caret" aria-hidden="true"></span></p>
    </nav>

    <p class="empty-note"<?php echo $total ? ' hidden' : ''; ?>>Nothing here yet for that filter. <a class="link-arrow" href="<?php echo esc_url( $archive ); ?>" data-reset>See all projects <span class="arr" aria-hidden="true">→</span></a></p>

    <?php
    foreach ( $tier_parts as $tier => $part ) :
      if ( ! $tiers[ $tier ] ) {
        continue;
      }
      list( $heading, $grid, $template ) = $part;
      ?>
    <div class="tier" data-tier="<?php echo esc_attr( $tier ); ?>"<?php echo $visible[ $tier ] ? '' : ' hidden'; ?>>
      <h2 class="tier-h"><?php echo esc_html( $heading ); ?></h2>
      <div class="<?php echo esc_attr( $grid ); ?>">
        <?php
        foreach ( $tiers[ $tier ] as $i => $post_id ) {
          get_template_part( $template, null, array(
            'post_id' => $post_id,
            'context' => 'archive',
            'index'   => $i,
            'hidden'  => ! in_array( $post_id, $visible[ $tier ], true ),
          ) );
        }
        ?>
      </div>
    </div>
    <?php endforeach; ?>
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
