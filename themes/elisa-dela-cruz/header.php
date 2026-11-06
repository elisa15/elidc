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
<a class="skip" href="#main">Skip to content</a>

<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <defs><linearGradient id="iconGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#ff2fb1"/><stop offset="55%" stop-color="#8b2fff"/><stop offset="100%" stop-color="#ff8a5c"/></linearGradient></defs>
</svg>

<header class="nav" id="nav">
  <div class="wrap nav-in">
    <?php $logo_mark = edc_opt( 'logo_icon', EDC_URI . '/assets/images/elisaarmas-logo-icon-final.png' ); ?>
    <a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
      <img src="<?php echo esc_url( $logo_mark ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"<?php echo edc_image_size_attrs( $logo_mark ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- integers only. ?>>
    </a>
    <button class="menu-btn" type="button" aria-expanded="false" aria-controls="menu" aria-label="Open menu"><span aria-hidden="true">☰</span></button>
    <nav aria-label="Primary">
      <?php
      if ( has_nav_menu( 'primary' ) ) {
        wp_nav_menu( array(
          'theme_location' => 'primary',
          'container'      => false,
          'items_wrap'     => '<ul id="menu">%3$s' . str_replace( '%', '%%', edc_nav_cta_item() ) . '</ul>',
          'depth'          => 1,
        ) );
      } else {
        edc_primary_menu_fallback();
      }
      ?>
    </nav>
  </div>
</header>
