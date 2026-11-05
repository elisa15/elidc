<?php
/**
 * Archive "Selected work" card: small visual, name, platform · region, role, summary
 * and a live link for non-confidential projects. Args: post_id.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$project = edc_project( isset( $args['post_id'] ) ? $args['post_id'] : 0 );
if ( ! $project ) {
	return;
}

$name     = $project['client'] ? $project['client'] : $project['title'];
$platform = $project['platforms'] ? $project['platforms'][0]['name'] : '';
$detail   = implode( ' · ', array_filter( array( $platform, $project['region'] ) ) );
$live     = edc_link_is_usable( $project['live_url'] ) ? $project['live_url'] : '';
// Without a screenshot, illustration or terminal line the card is text only (no empty gradient box).
$has_visual = 'none' !== $project['visual']['mode'];
?>
<article class="compact<?php echo $has_visual ? '' : ' no-visual'; ?>" id="project-<?php echo esc_attr( $project['slug'] ); ?>" data-reveal>
  <?php if ( $has_visual ) : ?>
  <div class="shot">
    <div class="shot-bar" aria-hidden="true"><i></i><i></i><i></i><?php if ( $project['visual']['browser_label'] ) : ?><span><?php echo esc_html( $project['visual']['browser_label'] ); ?></span><?php endif; ?></div>
    <?php get_template_part( 'template-parts/project/visual', null, array( 'project' => $project ) ); ?>
  </div>
  <?php endif; ?>
  <div class="compact-body">
    <h3>
      <?php if ( $project['url'] ) : ?>
      <a href="<?php echo esc_url( $project['url'] ); ?>" data-ga-event="case_study_open" data-ga-label="<?php echo esc_attr( $project['title'] ); ?>"><?php echo esc_html( $name ); ?></a>
      <?php else : ?>
      <?php echo esc_html( $name ); ?>
      <?php endif; ?>
    </h3>
    <?php if ( $detail ) : ?>
    <p class="detail"><?php echo esc_html( $detail ); ?></p>
    <?php endif; ?>
    <?php if ( $project['role'] ) : ?>
    <p class="role"><?php echo esc_html( $project['role'] ); ?></p>
    <?php endif; ?>
    <?php if ( $project['summary'] ) : ?>
    <p><?php echo esc_html( $project['summary'] ); ?></p>
    <?php endif; ?>
    <?php if ( $live ) : ?>
    <a class="link-arrow" href="<?php echo esc_url( $live ); ?>" target="_blank" rel="noopener">Visit site <span class="arr" aria-hidden="true">↗</span><span class="sr-only"> (<?php echo esc_html( $name ); ?>, opens in a new tab)</span></a>
    <?php endif; ?>
  </div>
</article>
