<?php
/**
 * Footer template.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<footer>
  <div class="wrap">
    <div class="footer-top">
      <?php
      $footer_logo = edc_opt( 'footer_logo', EDC_URI . '/assets/images/footer-new-logo.png' );
      ?>
      <img src="<?php echo esc_url( $footer_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> , Shopify & WordPress consultant" class="footer-logo" loading="lazy" decoding="async">
      <p class="footer-tagline"><?php echo esc_html( edc_opt( 'footer_tagline', 'Shopify & WordPress consultant helping agencies and businesses launch, rescue and scale websites that grow.' ) ); ?></p>

      <div class="footer-nav">
        <?php
        if ( has_nav_menu( 'footer' ) ) {
          wp_nav_menu( array(
            'theme_location' => 'footer',
            'container'      => false,
            'items_wrap'     => '%3$s',
            'depth'          => 1,
          ) );
        } else {
          ?>
          <?php if ( edc_should_show_section( 'show_tabs' ) ) : ?><a href="#hire">About</a><?php endif; ?>
          <?php if ( edc_should_show_section( 'show_services' ) ) : ?><a href="#services">Services</a><?php endif; ?>
          <?php if ( edc_should_show_section( 'show_work' ) ) : ?><a href="#work">Work</a><?php endif; ?>
          <?php if ( edc_should_show_section( 'show_cta' ) ) : ?><a href="#contact">Contact</a><?php endif; ?>
          <?php
        }
        ?>
      </div>

      <div class="footer-social">
        <?php
        $default_socials = array(
          array( 'type' => 'email',   'url' => 'mailto:hello@elisaarmas.com' ),
        );
        $socials = edc_repeater( 'footer_social', $default_socials );
        $icons = array(
          'email'    => '<svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>',
          'linkedin' => '<svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>',
          'github'   => '<svg viewBox="0 0 24 24"><path d="M9 19c-4.3 1.4-4.3-2.5-6-3m12 5v-3.5c0-1 .1-1.4-.5-2 2.8-.3 5.5-1.4 5.5-6a4.6 4.6 0 0 0-1.3-3.2 4.2 4.2 0 0 0-.1-3.2s-1.1-.3-3.5 1.3a12.3 12.3 0 0 0-6.2 0C6.5 3.5 5.4 3.8 5.4 3.8a4.2 4.2 0 0 0-.1 3.2A4.6 4.6 0 0 0 4 10.2c0 4.6 2.7 5.7 5.5 6-.6.6-.6 1.2-.5 2V22"/></svg>',
        );
        foreach ( $socials as $social ) {
          $type = isset( $social['type'] ) ? $social['type'] : 'email';
          $url  = isset( $social['url'] ) ? $social['url'] : '';
          if ( ! edc_link_is_usable( $url ) ) {
            continue; // never print a dead "#" icon
          }
          $icon = isset( $icons[ $type ] ) ? $icons[ $type ] : $icons['email'];
          echo '<a href="' . esc_url( $url ) . '" aria-label="' . esc_attr( ucfirst( $type ) ) . '">' . $icon . '</a>';
        }
        ?>
      </div>
    </div>
    <div class="footer-bottom">
      <span><?php echo esc_html( edc_opt( 'copyright_text', '© ' . date( 'Y' ) . ' Elisa A. Dela Cruz' ) ); ?></span>
      <span><?php echo esc_html( edc_opt( 'footer_domain_label', preg_replace( '#^https?://(www\.)?#', '', home_url() ) ) ); ?></span>
    </div>
  </div>
</footer>

<?php
if ( edc_should_show_section( 'show_contact_modal' ) ) {
	get_template_part( 'template-parts/modal-contact' );
}
?>

<a href="#top" class="back-to-top" id="backToTop" aria-label="Back to top" data-ga-event="back_to_top" data-ga-label="Back to top">
  <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 5l-7 7h4.5v7h5v-7H19z"/></svg>
</a>

<?php wp_footer(); ?>
</body>
</html>
