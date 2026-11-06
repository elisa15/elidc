<?php
/**
 * Project card (front + behind panel).
 * Args: project (from edc_project()) or post_id; context (home|archive); span (2|3|4|6, home only).
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$project = isset( $args['project'] ) ? $args['project'] : edc_project( isset( $args['post_id'] ) ? $args['post_id'] : 0 );
if ( ! $project ) {
	return;
}

$context = isset( $args['context'] ) ? $args['context'] : 'archive';
$span    = isset( $args['span'] ) ? (int) $args['span'] : 2;
$flip    = ! empty( $project['behind_rows'] );
$slug    = $project['slug'];

$classes = array( 'card' );
if ( $flip ) {
	$classes[] = 'flip';
}
if ( 'home' === $context ) {
	if ( 'wide' === $project['span'] && 4 === $span ) {
		$classes[] = 'lead';
	}
	if ( in_array( $span, array( 3, 4, 6 ), true ) ) {
		$classes[] = 'span-' . $span;
	}
}
?>
<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" id="project-<?php echo esc_attr( $slug ); ?>" data-type="<?php echo esc_attr( implode( ' ', wp_list_pluck( $project['kinds'], 'slug' ) ) ); ?>" data-platform="<?php echo esc_attr( implode( ' ', wp_list_pluck( $project['platforms'], 'slug' ) ) ); ?>"<?php echo ! empty( $args['hidden'] ) ? ' hidden' : ''; ?> data-reveal>
  <div class="shot">
    <div class="shot-bar" aria-hidden="true"><i></i><i></i><i></i><?php if ( $project['visual']['browser_label'] ) : ?><span><?php echo esc_html( $project['visual']['browser_label'] ); ?></span><?php endif; ?></div>
    <?php get_template_part( 'template-parts/project/visual', null, array( 'project' => $project ) ); ?>
  </div>
  <div class="card-body">
    <?php if ( $project['kinds'] || $project['platforms'] || $project['chips'] ) : ?>
    <div class="tags">
      <?php foreach ( $project['kinds'] as $kind ) : ?><span class="tag <?php echo esc_attr( $kind['slug'] ); ?>"><?php echo esc_html( $kind['name'] ); ?></span><?php endforeach; ?>
      <?php foreach ( $project['platforms'] as $platform ) : ?><span class="tag"><?php echo esc_html( $platform['name'] ); ?></span><?php endforeach; ?>
      <?php foreach ( $project['chips'] as $chip ) : ?><span class="tag"><?php echo esc_html( $chip ); ?></span><?php endforeach; ?>
    </div>
    <?php endif; ?>
    <h3>
      <?php if ( $project['url'] ) : ?>
      <a href="<?php echo esc_url( $project['url'] ); ?>" data-ga-event="case_study_open" data-ga-label="<?php echo esc_attr( $project['title'] ); ?>"><?php echo esc_html( $project['title'] ); ?></a>
      <?php else : ?>
      <?php echo esc_html( $project['title'] ); ?>
      <?php endif; ?>
    </h3>
    <?php if ( $project['summary'] ) : ?>
    <p><?php echo esc_html( $project['summary'] ); ?></p>
    <?php endif; ?>
    <?php if ( $project['names'] ) : ?>
    <div class="names">
      <?php foreach ( $project['names'] as $name ) : ?><span><?php echo esc_html( $name['name'] . ( $name['note'] ? ' · ' . $name['note'] : '' ) ); ?></span><?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if ( $project['role_line'] ) : ?>
    <div class="role"><?php echo esc_html( $project['role_line'] ); ?></div>
    <?php endif; ?>
    <?php if ( $flip ) : ?>
    <button class="behind-toggle" type="button" aria-expanded="false" aria-controls="behind-<?php echo esc_attr( $slug ); ?>">What's behind it ↓</button>
    <?php endif; ?>
  </div>
  <?php get_template_part( 'template-parts/project/behind', null, array( 'project' => $project ) ); ?>
</article>
