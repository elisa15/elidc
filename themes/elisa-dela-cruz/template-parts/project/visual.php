<?php
/**
 * Project visual inside a .shot frame. Args: project.
 * Confidential projects never reach the screenshot branches (edc_project_visual() forces another mode).
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$project = isset( $args['project'] ) ? $args['project'] : array();
if ( ! $project ) {
	return;
}
$visual = $project['visual'];

$image = function ( $id ) use ( $project ) {
	$alt = trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );
	if ( '' === $alt ) {
		$alt = trim( $project['client'] . ' homepage' );
	}
	return wp_get_attachment_image( $id, 'large', false, array( 'loading' => 'lazy', 'alt' => $alt ) );
};

$badge = $visual['badge'] ? '<span class="badge">' . esc_html( $visual['badge'] ) . '</span>' : '';

switch ( $visual['mode'] ) {
	case 'screenshot':
		echo '<div class="shot-img">' . $image( $visual['images'][0] ) . $badge . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core image markup + escaped badge.
		break;

	case 'split':
		echo '<div class="shot-img split">' . $image( $visual['images'][0] ) . $image( $visual['images'][1] ) . $badge . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core image markup + escaped badge.
		break;

	case 'illustration':
		echo '<div class="shot-img">';
		get_template_part( 'template-parts/project/visual-' . $visual['illustration'] );
		echo $badge . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		break;

	case 'terminal':
		echo '<div class="shot-img"><div class="mock" aria-hidden="true"></div><span class="mock-label">' . esc_html( $visual['terminal_line'] ) . '</span>' . $badge . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		break;

	default:
		$start = sanitize_hex_color( $visual['gradient']['start'] );
		$end   = sanitize_hex_color( $visual['gradient']['end'] );
		$style = ( $start && $end ) ? ' style="background:linear-gradient(135deg,' . $start . ',' . $end . ')"' : '';
		echo '<div class="shot-img" aria-hidden="true"' . $style . '>' . $badge . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitised colours + escaped badge.
}
