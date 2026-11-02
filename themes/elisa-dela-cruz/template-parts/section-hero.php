<?php
/**
 * Hero section.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header class="hero" id="top">
  <div class="blob-field">
    <div class="blob blob1"></div>
    <div class="blob blob2"></div>
    <div class="blob blob3"></div>
  </div>
  <div class="particles">
    <span class="particle" style="left:8%;top:70%;animation-duration:16s;animation-delay:-2s;"></span>
    <span class="particle" style="left:18%;top:40%;animation-duration:20s;animation-delay:-9s;"></span>
    <span class="particle" style="left:28%;top:80%;animation-duration:14s;animation-delay:-4s;"></span>
    <span class="particle" style="left:42%;top:55%;animation-duration:22s;animation-delay:-12s;"></span>
    <span class="particle" style="left:58%;top:30%;animation-duration:18s;animation-delay:-6s;"></span>
    <span class="particle" style="left:68%;top:75%;animation-duration:15s;animation-delay:-1s;"></span>
    <span class="particle" style="left:78%;top:45%;animation-duration:24s;animation-delay:-10s;"></span>
    <span class="particle" style="left:88%;top:65%;animation-duration:17s;animation-delay:-7s;"></span>
    <span class="particle" style="left:50%;top:20%;animation-duration:19s;animation-delay:-3s;"></span>
    <span class="particle" style="left:95%;top:30%;animation-duration:21s;animation-delay:-14s;"></span>
  </div>
  <div class="hero-inner">
    <div class="hero-grid">
      <div class="parallax-layer" id="heroText">
        <div class="eyebrow"><?php echo esc_html( edc_opt( 'hero_eyebrow', 'Shopify & WordPress consultant' ) ); ?></div>
        <h1><?php echo wp_kses_post( edc_opt( 'hero_heading', 'I turn ideas into<br><em class="grad-text">unforgettable</em> websites.' ) ); ?></h1>
        <p><?php echo wp_kses_post( edc_opt( 'hero_paragraph', "I've spent 13+ years helping agencies and businesses with Shopify and WordPress. Migrations, custom builds, speed work, cleanup jobs, ongoing support. I focus on sites that stay fast, stay secure, and keep doing their job." ) ); ?></p>
        <?php
        $hero_btn_primary_label = trim( (string) edc_opt( 'hero_btn_primary', 'Schedule a call' ) );
        $hero_btn_primary_url   = trim( (string) edc_opt( 'hero_btn_primary_url', '#contact' ) );
        $hero_btn_secondary_label = trim( (string) edc_opt( 'hero_btn_secondary', 'View my work' ) );
        $hero_btn_secondary_url   = trim( (string) edc_opt( 'hero_btn_secondary_url', '#work' ) );
        $show_primary   = ( $hero_btn_primary_label !== '' && $hero_btn_primary_url !== '' );
        $show_secondary = ( $hero_btn_secondary_label !== '' && $hero_btn_secondary_url !== '' );
        ?>
        <?php if ( $show_primary || $show_secondary ) : ?>
        <div class="actions">
          <?php if ( $show_primary ) :
            $primary_classes = 'btn btn-fill';
            if ( '#contact' === $hero_btn_primary_url || false !== strpos( $hero_btn_primary_url, '#contact' ) ) {
              $primary_classes .= ' js-open-contact';
            }
            ?>
            <a href="<?php echo esc_url( $hero_btn_primary_url ); ?>" class="<?php echo esc_attr( $primary_classes ); ?>" data-ga-event="hero_primary_cta" data-ga-label="<?php echo esc_attr( $hero_btn_primary_label ); ?>"><?php echo esc_html( $hero_btn_primary_label ); ?></a>
          <?php endif; ?>
          <?php if ( $show_secondary ) : ?>
            <a href="<?php echo esc_url( $hero_btn_secondary_url ); ?>" class="btn btn-outline" data-ga-event="hero_secondary_cta" data-ga-label="<?php echo esc_attr( $hero_btn_secondary_label ); ?>"><?php echo esc_html( $hero_btn_secondary_label ); ?></a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
      <div class="parallax-layer" id="heroTerminal">
        <div class="terminal">
          <div class="terminal-bar"><i></i><i></i><i></i><span class="terminal-title mono"><?php echo esc_html( edc_opt( 'terminal_title', '~/elisa' ) ); ?></span></div>
          <div class="terminal-body mono" id="typedBody"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="marquee-clip">
    <div class="marquee-track">
      <?php
      $marquee = edc_opt( 'marquee_text', 'Shopify migrations · WordPress rescues · Performance audits · Malware cleanup · Technical SEO · Client consulting' );
      ?>
      <span><?php echo esc_html( $marquee ); ?></span>
      <span><?php echo esc_html( $marquee ); ?></span>
    </div>
  </div>
</header>
