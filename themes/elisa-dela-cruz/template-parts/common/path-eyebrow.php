<?php
/**
 * Path eyebrow: ~/elisa/{path}. Args: path.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$path = isset( $args['path'] ) ? (string) $args['path'] : '';
?>
<div class="eyebrow path"><b>~/elisa</b><?php echo '' !== $path ? '/' . esc_html( $path ) : ''; ?></div>
