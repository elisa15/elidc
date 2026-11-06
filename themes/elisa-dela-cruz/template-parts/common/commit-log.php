<?php
/**
 * Career timeline as a git log. Args: entries (edc_career_log()), visible (0 = show all).
 * Native <details>, so it works without JS; site.js collapses entries past "visible".
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$entries = isset( $args['entries'] ) ? (array) $args['entries'] : array();
$visible = isset( $args['visible'] ) ? max( 0, (int) $args['visible'] ) : 0;
if ( ! $entries ) {
	return;
}
$collapse = $visible > 0 && count( $entries ) > $visible;

$range = '';
if ( $collapse ) {
	$years = array();
	foreach ( array_slice( $entries, $visible ) as $entry ) {
		if ( preg_match( '/(\d{4})(?:\s*[–-]\s*(\d{2,4}))?/u', isset( $entry['when'] ) ? $entry['when'] : '', $m ) ) {
			$years[] = (int) $m[1];
			if ( ! empty( $m[2] ) ) {
				$years[] = 2 === strlen( $m[2] ) ? (int) ( substr( $m[1], 0, 2 ) . $m[2] ) : (int) $m[2];
			}
		}
	}
	if ( $years ) {
		$range = min( $years ) === max( $years ) ? (string) min( $years ) : min( $years ) . '–' . max( $years );
	}
}
?>
<p class="log-head" data-reveal>$ git log --career --oneline</p>
<ol class="log"<?php echo $collapse ? ' data-visible="' . (int) $visible . '"' : ''; ?> data-reveal>
  <?php
  foreach ( $entries as $i => $entry ) :
    $classes = array();
    if ( ! empty( $entry['is_head'] ) ) {
      $classes[] = 'head';
    }
    if ( $collapse && $i >= $visible ) {
      $classes[] = 'older';
    }
    $value = function ( $key ) use ( $entry ) {
      return isset( $entry[ $key ] ) ? trim( (string) $entry[ $key ] ) : '';
    };
    ?>
  <li<?php echo $classes ? ' class="' . esc_attr( implode( ' ', $classes ) ) . '"' : ''; ?>><details<?php echo ! empty( $entry['is_head'] ) ? ' open' : ''; ?>><summary><span class="hash"><?php echo esc_html( $entry['hash'] ); ?></span><span class="msg"><?php if ( $value( 'type' ) ) : ?><b><?php echo esc_html( $value( 'type' ) ); ?>:</b> <?php endif; ?><?php echo esc_html( $value( 'message' ) ); ?><?php if ( $value( 'context' ) ) : ?> <i>(<?php echo esc_html( $value( 'context' ) ); ?>)</i><?php endif; ?></span><?php if ( $value( 'when' ) ) : ?><span class="when"><?php echo esc_html( $value( 'when' ) ); ?></span><?php endif; ?></summary>
    <?php if ( $value( 'meta' ) || $value( 'body' ) ) : ?>
    <div class="body"><?php if ( $value( 'meta' ) ) : ?><div class="meta"><?php echo esc_html( $value( 'meta' ) ); ?></div><?php endif; ?><?php echo esc_html( $value( 'body' ) ); ?></div>
    <?php endif; ?></details></li>
  <?php endforeach; ?>
</ol>
<?php if ( $collapse ) : ?>
<button type="button" class="log-more" aria-expanded="false" hidden>$ git log --all <span>(show <?php echo esc_html( $range ? $range : 'older' ); ?>)</span></button>
<?php endif; ?>
