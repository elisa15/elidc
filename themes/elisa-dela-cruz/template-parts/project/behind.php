<?php
/**
 * Behind panel for a project card. Args: project. Renders nothing without rows.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$project = isset( $args['project'] ) ? $args['project'] : array();
if ( empty( $project['behind_rows'] ) ) {
	return;
}
?>
<div class="behind" id="behind-<?php echo esc_attr( $project['slug'] ); ?>">
  <div class="head"><span>$ cat behind.log</span><?php if ( $project['behind_meta'] ) : ?><span><?php echo esc_html( $project['behind_meta'] ); ?></span><?php endif; ?></div>
  <dl>
    <?php foreach ( $project['behind_rows'] as $row ) : ?><dt><?php echo esc_html( $row['key'] ); ?>:</dt><dd><?php echo esc_html( $row['value'] ); ?></dd><?php endforeach; ?>
  </dl>
  <button class="behind-close" type="button">Close ×</button>
</div>
