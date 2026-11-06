<?php
/**
 * Services grid.
 *
 * Icons stay fixed (hand-tuned SVGs matching the design system) and cycle
 * by index; only title/description are ACF-driven so Cursor can edit copy
 * without needing to manage inline SVG markup in the admin.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icons = array(
	'<svg viewBox="0 0 24 24"><path d="M4 12h11M11 7l5 5-5 5"/><path d="M20 5v14"/></svg>',
	'<svg viewBox="0 0 24 24"><path d="M3 18l4-9 4 6 3-4 7 7"/><circle cx="19" cy="6" r="2"/></svg>',
	'<svg viewBox="0 0 24 24"><path d="M12 2 4 6v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V6z"/><path d="M9 12l2 2 4-4"/></svg>',
	'<svg viewBox="0 0 24 24"><path d="M3 12h4l2-7 4 14 2-7h6"/></svg>',
	'<svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="M4 9h16M9 21V9"/></svg>',
	'<svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/></svg>',
);

$default_services = array(
	array( 'title' => 'Store migrations', 'description' => 'Move platforms with zero downtime and no lost revenue.' ),
	array( 'title' => 'Performance optimisation', 'description' => 'Faster storefronts that convert more of the traffic you already have.' ),
	array( 'title' => 'Security & malware cleanup', 'description' => 'Get back online quickly, then keep the site locked down.' ),
	array( 'title' => 'Ongoing maintenance', 'description' => 'One less thing to worry about, permanently.' ),
	array( 'title' => 'Custom development', 'description' => 'Functionality built around your business, not the other way around.' ),
	array( 'title' => 'Technical SEO & analytics', 'description' => 'Growth you can actually measure and trust.' ),
);
$services = edc_repeater( 'services', $default_services );
?>
<section id="services">
  <div class="wrap">
    <div class="section-head center" data-reveal="up">
      <div class="eyebrow" style="justify-content:center;"><?php echo esc_html( edc_opt( 'services_eyebrow', 'Services' ) ); ?></div>
      <h2><?php echo esc_html( edc_opt( 'services_heading', 'What I help with most' ) ); ?></h2>
      <p><?php echo esc_html( edc_opt( 'services_subheading', 'The jobs agencies and business owners usually call me about.' ) ); ?></p>
    </div>
    <div class="services-grid">
      <?php foreach ( $services as $i => $service ) :
        $title = isset( $service['title'] ) ? $service['title'] : '';
        $desc  = isset( $service['description'] ) ? $service['description'] : '';
        $icon  = $icons[ $i % count( $icons ) ];
      ?>
        <div class="s-card" data-reveal="up" style="--i:<?php echo intval( $i ); ?>">
          <div class="s-icon"><?php echo $icon; /* fixed, trusted inline SVG */ ?></div>
          <h3><?php echo esc_html( $title ); ?></h3><p><?php echo esc_html( $desc ); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
