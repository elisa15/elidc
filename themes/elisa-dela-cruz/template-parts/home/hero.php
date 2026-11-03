<?php
/**
 * Homepage hero: title line, headline, intro, calls to action and the terminal.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title_line  = edc_opt( 'home_title_line' );
$headline    = edc_opt( 'home_h1' );
$lede        = edc_opt( 'home_lede' );
$cta_primary = edc_opt( 'home_cta_primary' );
$cta_second  = edc_opt( 'home_cta_secondary' );
$cv_url      = edc_cv_url();
$cv_prompt   = edc_opt( 'home_cv_prompt' );
$term_title  = edc_opt( 'home_terminal_title' );
$term_lines  = edc_filled_rows( edc_repeater( 'home_terminal', array() ), array( 'text' ) );

if ( edc_section_is_public( 'show_work' ) ) {
	$work_url = '#work';
} else {
	$counts   = wp_count_posts( 'project' );
	$work_url = ( $counts && ! empty( $counts->publish ) ) ? get_post_type_archive_link( 'project' ) : '';
}
$contact_url = edc_contact_url();
$has_modal   = edc_should_show_section( 'show_contact_modal' );

$last_ok = -1;
foreach ( $term_lines as $i => $line ) {
	if ( isset( $line['kind'] ) && 'ok' === $line['kind'] ) {
		$last_ok = $i;
	}
}
?>
<section class="hero" id="top">
  <div class="grain" aria-hidden="true"></div>
  <div class="wrap hero-grid">
    <div>
      <h1>
        <?php if ( $title_line ) : ?>
        <span class="title-line rise"><?php echo esc_html( $title_line ); ?></span>
        <?php endif; ?>
        <span class="rise d1"><?php echo edc_inline_html( $headline ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses. ?></span>
      </h1>
      <?php if ( $lede ) : ?>
      <p class="lede rise d2"><?php echo edc_inline_html( $lede ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses. ?></p>
      <?php endif; ?>
      <?php if ( ( $cta_primary && $work_url ) || ( $cta_second && ( $contact_url || $has_modal ) ) ) : ?>
      <div class="hero-ctas rise d3">
        <?php if ( $cta_primary && $work_url ) : ?>
        <a class="btn btn-primary" href="<?php echo esc_url( $work_url ); ?>"><?php echo esc_html( $cta_primary ); ?> <span class="arr" aria-hidden="true">→</span></a>
        <?php endif; ?>
        <?php if ( $cta_second && ( $contact_url || $has_modal ) ) : ?>
        <a class="btn btn-ghost<?php echo $has_modal ? ' js-open-contact' : ''; ?>" href="<?php echo esc_url( $contact_url ? $contact_url : home_url( '/' ) ); ?>" data-ga-event="cta_start_project" data-ga-label="Hero <?php echo esc_attr( $cta_second ); ?>"><?php echo esc_html( $cta_second ); ?></a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <?php if ( $cv_url ) : ?>
      <p class="hero-sub rise d4"><?php echo $cv_prompt ? esc_html( $cv_prompt ) . ' ' : ''; ?><a href="<?php echo esc_url( $cv_url ); ?>" download data-ga-event="cv_download" data-ga-label="Hero CV">Download my CV ↓</a></p>
      <?php endif; ?>
    </div>

    <?php if ( $term_lines ) : ?>
    <div class="terminal rise d2" role="group" aria-label="Summary">
      <div class="term-bar" aria-hidden="true"><i></i><i></i><i></i><?php if ( $term_title ) : ?><span><?php echo esc_html( $term_title ); ?></span><?php endif; ?></div>
      <div class="term-body">
        <?php
        foreach ( $term_lines as $i => $line ) :
          $kind = isset( $line['kind'] ) && in_array( $line['kind'], array( 'p', 'o', 'ok' ), true ) ? $line['kind'] : 'o';
          $text = trim( $line['text'] );
          $link = isset( $line['link'] ) && is_string( $line['link'] ) ? trim( $line['link'] ) : '';
          ?>
        <div class="ln <?php echo esc_attr( $kind ); ?>" style="--i:<?php echo (int) $i; ?>">
          <?php
          if ( edc_link_is_usable( $link ) ) {
            $prefix = '';
            if ( 0 === strpos( $text, '✔ ' ) ) {
              $prefix = '✔ ';
              $text   = substr( $text, strlen( '✔ ' ) );
            }
            printf(
              '%s<a href="%s" data-ga-event="terminal_link_click" data-ga-label="%s">%s</a>',
              esc_html( $prefix ),
              esc_url( $link ),
              esc_attr( $text ),
              esc_html( $text )
            );
          } else {
            echo esc_html( $text );
          }
          if ( $i === $last_ok ) {
            echo '<span class="cursor" aria-hidden="true"></span>';
          }
          ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
