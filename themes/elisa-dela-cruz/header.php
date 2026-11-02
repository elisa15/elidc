<?php
/**
 * Header template.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<svg width="0" height="0" style="position:absolute">
  <defs><linearGradient id="iconGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#ff2fb1"/><stop offset="55%" stop-color="#8b2fff"/><stop offset="100%" stop-color="#ff8a5c"/></linearGradient></defs>
</svg>

<?php
$loader_image = edc_opt( 'loader_icon', EDC_URI . '/assets/images/elisaarmas-logo-icon-final.png' );
?>
<div class="loader" id="loader"><img src="<?php echo esc_url( $loader_image ); ?>" alt="Loading"></div>
<div class="cursor-glow" id="cursorGlow"></div>
<div class="grain"></div>

<nav id="nav" aria-label="Primary">
  <div class="wrap">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo">
      <?php
      $logo_mark = edc_opt( 'logo_icon', EDC_URI . '/assets/images/elisaarmas-logo-icon-final.png' );
      ?>
      <img src="<?php echo esc_url( $logo_mark ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="logo-mark">
    </a>
    <button type="button" class="nav-toggle" id="navToggle" aria-label="Open menu" aria-controls="navPanel" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>

    <div class="nav-panel" id="navPanel">
      <?php
      if ( has_nav_menu( 'primary' ) ) {
        wp_nav_menu( array(
          'theme_location' => 'primary',
          'container'      => false,
          'items_wrap'     => '<ul>%3$s</ul>',
          'depth'          => 1,
        ) );
      } else {
        edc_primary_menu_fallback();
      }
      ?>
      <?php if ( edc_should_show_section( 'show_cta' ) || edc_should_show_section( 'show_contact_modal' ) ) : ?>
      <a href="#contact" class="btn btn-fill btn-sm js-open-contact" data-ga-event="nav_schedule_call" data-ga-label="Nav Schedule a call"><?php echo esc_html( edc_opt( 'nav_cta_label', 'Schedule a call' ) ); ?></a>
      <?php endif; ?>
    </div>
  </div>
</nav>
