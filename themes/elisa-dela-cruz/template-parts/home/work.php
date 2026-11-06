<?php
/**
 * Homepage Work section: featured project cards and the "Also built" tile.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$projects = array_filter( array_map( 'edc_project', edc_home_featured_project_ids() ) );
if ( ! $projects ) {
	return;
}
$projects = array_values( $projects );

$more = array();
foreach ( (array) edc_opt( 'home_more_projects', array() ) as $id ) {
	$id = (int) ( is_object( $id ) ? $id->ID : $id );
	if ( $id && 'publish' === get_post_status( $id ) ) {
		$item = edc_project( $id );
		if ( $item && $item['client'] ) {
			$more[] = $item;
		}
	}
}

/*
 * Pack cards into rows of six columns (wide = 4, half = 3, otherwise 2). When the
 * next card would not fit, the previous normal card widens to close the gap if the
 * stylesheet has a width for it; the "Also built" tile takes what is left of the last row.
 */
$widths = array( 'wide' => 4, 'half' => 3, 'auto' => 2 );
$spans  = array();
$used   = 0;
$last   = null;
foreach ( $projects as $i => $project ) {
	$span = $widths[ $project['span'] ];
	if ( $used + $span > 6 ) {
		$gap = 6 - $used;
		if ( null !== $last && 2 === $spans[ $last ] && in_array( 2 + $gap, array( 3, 4 ), true ) ) {
			$spans[ $last ] += $gap;
		}
		$used = 0;
	}
	$spans[ $i ] = $span;
	$used        = ( $used + $span ) % 6;
	$last        = 0 === $used ? null : $i;
}
$more_span = 0 === $used ? 6 : 6 - $used;
if ( $more && 1 === $more_span && null !== $last && 2 === $spans[ $last ] ) {
	$spans[ $last ] = 3;
	$more_span      = 6;
}

$kinds = array();
$flips = false;
foreach ( $projects as $project ) {
	$kinds = array_merge( $kinds, wp_list_pluck( $project['kinds'], 'slug' ) );
	$flips = $flips || ! empty( $project['behind_rows'] );
}
$show_filters = in_array( 'built', $kinds, true ) && in_array( 'solved', $kinds, true );
$archive      = get_post_type_archive_link( 'project' );

$heading      = edc_opt( 'home_work_heading' );
$intro        = edc_opt( 'home_work_intro' );
$more_heading = edc_opt( 'home_more_heading' );
?>
<section class="sec light" id="work">
  <div class="wrap">
    <div class="sec-head" data-reveal>
      <?php get_template_part( 'template-parts/common/path-eyebrow', null, array( 'path' => 'work' ) ); ?>
      <?php if ( $heading ) : ?>
      <h2><?php echo edc_inline_html( $heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses. ?></h2>
      <?php endif; ?>
      <?php if ( $intro ) : ?>
      <p><?php echo esc_html( $intro ); ?></p>
      <?php endif; ?>
    </div>
    <?php if ( $show_filters ) : ?>
    <nav class="filters" aria-label="Filter projects">
      <a href="<?php echo esc_url( $archive ); ?>" data-type="all" aria-current="true">All</a>
      <a href="<?php echo esc_url( add_query_arg( 'type', 'built', $archive ) ); ?>" data-type="built">Built</a>
      <a href="<?php echo esc_url( add_query_arg( 'type', 'solved', $archive ) ); ?>" data-type="solved">Solved</a>
    </nav>
    <p class="sr-only" id="filter-status" aria-live="polite"></p>
    <?php endif; ?>
    <?php if ( $flips ) : ?>
    <p class="hint">Hover or focus a project to see what's behind it.</p>
    <?php endif; ?>
    <p class="swipe-hint" aria-hidden="true">← swipe for more →</p>
    <div class="work-grid swipe v2" tabindex="0" role="region" aria-label="Featured projects">
      <?php
      foreach ( $projects as $i => $project ) {
        get_template_part( 'template-parts/project/card', null, array(
          'project' => $project,
          'context' => 'home',
          'span'    => $spans[ $i ],
        ) );
      }
      ?>
      <?php if ( $more ) : ?>
      <article class="card more-tile<?php echo in_array( $more_span, array( 3, 4, 6 ), true ) ? ' span-' . (int) $more_span : ''; ?>" data-type="all" data-reveal>
        <?php get_template_part( 'template-parts/common/path-eyebrow', null, array( 'path' => 'work --more' ) ); ?>
        <?php if ( $more_heading ) : ?>
        <h3><?php echo esc_html( $more_heading ); ?></h3>
        <?php endif; ?>
        <ul>
          <?php
          foreach ( $more as $item ) :
            $platform = $item['platforms'] ? $item['platforms'][0]['name'] : '';
            $detail   = implode( ' · ', array_filter( array( $platform, $item['region'] ? $item['region'] : $item['role'] ) ) );
            ?>
          <li><b><?php echo esc_html( $item['client'] ); ?></b><?php if ( $detail ) : ?><span><?php echo esc_html( $detail ); ?></span><?php endif; ?></li>
          <?php endforeach; ?>
        </ul>
        <a class="link-arrow" href="<?php echo esc_url( $archive ); ?>">All projects <span class="arr" aria-hidden="true">→</span></a>
      </article>
      <?php endif; ?>
    </div>
  </div>
</section>
