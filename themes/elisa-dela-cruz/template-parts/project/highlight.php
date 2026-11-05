<?php
/**
 * Archive "Technical highlights" entry: a mono block with the title and the
 * behind-panel rows, no visual. Args: post_id.
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
?>
<article class="highlight" id="project-<?php echo esc_attr( $project['slug'] ); ?>" data-reveal>
  <div class="highlight-head">
    <h3>
      <?php if ( $project['url'] ) : ?>
      <a href="<?php echo esc_url( $project['url'] ); ?>" data-ga-event="case_study_open" data-ga-label="<?php echo esc_attr( $project['title'] ); ?>"><?php echo esc_html( $project['title'] ); ?></a>
      <?php else : ?>
      <?php echo esc_html( $project['title'] ); ?>
      <?php endif; ?>
    </h3>
    <?php if ( $project['behind_meta'] ) : ?>
    <span class="meta"><?php echo esc_html( $project['behind_meta'] ); ?></span>
    <?php endif; ?>
  </div>
  <?php if ( $project['behind_rows'] ) : ?>
  <dl>
    <?php foreach ( $project['behind_rows'] as $row ) : ?>
    <div><dt><?php echo esc_html( $row['key'] ); ?>:</dt><dd><?php echo esc_html( $row['value'] ); ?></dd></div>
    <?php endforeach; ?>
  </dl>
  <?php elseif ( $project['summary'] ) : ?>
  <p><?php echo esc_html( $project['summary'] ); ?></p>
  <?php endif; ?>
</article>
