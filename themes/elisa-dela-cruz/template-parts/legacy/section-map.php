<?php
/**
 * "Where I work" dot-matrix world map.
 *
 * Land dots and the 5 route/particle SVG paths are hand-tuned to the
 * viewBox="0 0 1000 500" pixel grid and stay static. Node CONTENT (flag,
 * country name, industries, services) is ACF-driven via the 'map_nodes'
 * repeater on the Options page, matched by index to these 5 fixed
 * positions/routes — see SCHEMA.md for why positions aren't dynamic.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$default_nodes = array(
	array( 'flag' => '🇺🇸', 'country' => 'United States', 'left' => '22.8', 'top' => '28.3', 'industries' => 'Fashion, Retail', 'services' => 'Shopify, Custom Dev, Performance' ),
	array( 'flag' => '🇬🇧', 'country' => 'United Kingdom', 'left' => '50.0', 'top' => '21.4', 'industries' => 'Fashion, Hospitality', 'services' => 'WordPress, Maintenance, Technical SEO' ),
	array( 'flag' => '🇿🇦', 'country' => 'South Africa', 'left' => '57.8', 'top' => '64.4', 'industries' => 'Wine, Agriculture', 'services' => 'WooCommerce, Migration, Security' ),
	array( 'flag' => '🇦🇺', 'country' => 'Australia', 'left' => '91.9', 'top' => '68.8', 'industries' => 'Wine, Wholesale', 'services' => 'Shopify, WordPress, Maintenance' ),
	array( 'flag' => '🇵🇭', 'country' => 'Philippines', 'left' => '83.6', 'top' => '41.9', 'industries' => 'Retail, Hospitality', 'services' => 'Shopify, Performance, Support' ),
);
$nodes = edc_repeater( 'map_nodes', $default_nodes );
// Positions/routes are fixed to exactly 5 nodes; if Cursor adds/removes
// rows, pad or trim so the hardcoded SVG routes never break.
if ( count( $nodes ) !== 5 ) {
	$nodes = array_slice( array_pad( $nodes, 5, end( $default_nodes ) ), 0, 5 );
}

$default_metrics = array(
	array( 'number' => '13+', 'label' => 'Years Experience' ),
	array( 'number' => '150+', 'label' => 'Projects Delivered' ),
	array( 'number' => '30+', 'label' => 'Industries Served' ),
	array( 'number' => '100%', 'label' => 'Commitment to Quality' ),
);
$metrics = edc_repeater( 'map_metrics', $default_metrics );
?>
<section class="map-section" id="map">
  <div class="mesh-glow a"></div>
  <div class="mesh-glow b"></div>
  <div class="wrap">
    <div class="section-head center" data-reveal="up">
      <div class="eyebrow" style="justify-content:center;"><?php echo esc_html( edc_opt( 'map_eyebrow', 'Where I work' ) ); ?></div>
      <h2><?php echo esc_html( edc_opt( 'map_heading', 'Clients I work with around the world' ) ); ?></h2>
    </div>
  </div>
  <div class="map-wrap" data-reveal="scale">
    <div class="world-map">
      <svg viewBox="0 0 1000 500" class="map-svg" preserveAspectRatio="xMidYMid meet">
      <circle cx="41.7" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="75.0" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="108.3" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="141.7" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="175.0" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="208.3" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="241.7" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="275.0" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="308.3" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="641.7" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="675.0" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="708.3" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="741.7" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="775.0" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="808.3" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="841.7" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="875.0" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="908.3" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="941.7" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="975.0" cy="41.7" r="2.3" class="land-dot"/>
          <circle cx="58.3" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="91.7" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="125.0" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="158.3" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="191.7" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="225.0" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="258.3" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="291.7" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="325.0" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="525.0" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="558.3" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="591.7" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="625.0" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="658.3" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="691.7" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="725.0" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="758.3" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="791.7" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="825.0" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="858.3" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="891.7" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="925.0" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="958.3" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="991.7" cy="58.3" r="2.3" class="land-dot"/>
          <circle cx="41.7" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="75.0" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="108.3" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="141.7" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="175.0" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="208.3" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="241.7" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="275.0" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="308.3" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="375.0" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="408.3" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="441.7" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="541.7" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="575.0" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="608.3" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="641.7" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="675.0" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="708.3" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="741.7" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="775.0" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="808.3" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="841.7" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="875.0" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="908.3" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="941.7" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="975.0" cy="75.0" r="2.3" class="land-dot"/>
          <circle cx="58.3" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="91.7" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="125.0" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="158.3" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="191.7" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="225.0" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="258.3" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="291.7" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="325.0" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="491.7" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="525.0" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="558.3" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="591.7" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="625.0" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="658.3" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="691.7" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="725.0" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="758.3" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="791.7" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="825.0" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="858.3" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="891.7" cy="91.7" r="2.3" class="land-dot"/>
          <circle cx="41.7" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="75.0" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="108.3" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="141.7" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="175.0" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="208.3" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="241.7" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="275.0" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="308.3" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="341.7" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="475.0" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="508.3" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="541.7" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="575.0" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="608.3" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="641.7" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="675.0" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="708.3" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="741.7" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="775.0" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="808.3" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="841.7" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="875.0" cy="108.3" r="2.3" class="land-dot"/>
          <circle cx="158.3" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="191.7" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="225.0" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="258.3" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="291.7" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="491.7" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="525.0" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="558.3" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="591.7" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="625.0" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="658.3" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="691.7" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="725.0" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="758.3" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="791.7" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="825.0" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="858.3" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="891.7" cy="125.0" r="2.3" class="land-dot"/>
          <circle cx="175.0" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="208.3" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="241.7" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="275.0" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="475.0" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="508.3" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="541.7" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="575.0" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="608.3" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="641.7" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="675.0" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="708.3" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="741.7" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="775.0" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="808.3" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="841.7" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="875.0" cy="141.7" r="2.3" class="land-dot"/>
          <circle cx="158.3" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="191.7" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="225.0" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="258.3" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="291.7" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="458.3" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="491.7" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="525.0" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="558.3" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="591.7" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="625.0" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="658.3" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="691.7" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="725.0" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="758.3" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="791.7" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="825.0" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="858.3" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="891.7" cy="158.3" r="2.3" class="land-dot"/>
          <circle cx="175.0" cy="175.0" r="2.3" class="land-dot"/>
          <circle cx="208.3" cy="175.0" r="2.3" class="land-dot"/>
          <circle cx="241.7" cy="175.0" r="2.3" class="land-dot"/>
          <circle cx="475.0" cy="175.0" r="2.3" class="land-dot"/>
          <circle cx="508.3" cy="175.0" r="2.3" class="land-dot"/>
          <circle cx="541.7" cy="175.0" r="2.3" class="land-dot"/>
          <circle cx="575.0" cy="175.0" r="2.3" class="land-dot"/>
          <circle cx="608.3" cy="175.0" r="2.3" class="land-dot"/>
          <circle cx="641.7" cy="175.0" r="2.3" class="land-dot"/>
          <circle cx="675.0" cy="175.0" r="2.3" class="land-dot"/>
          <circle cx="708.3" cy="175.0" r="2.3" class="land-dot"/>
          <circle cx="741.7" cy="175.0" r="2.3" class="land-dot"/>
          <circle cx="775.0" cy="175.0" r="2.3" class="land-dot"/>
          <circle cx="808.3" cy="175.0" r="2.3" class="land-dot"/>
          <circle cx="191.7" cy="191.7" r="2.3" class="land-dot"/>
          <circle cx="225.0" cy="191.7" r="2.3" class="land-dot"/>
          <circle cx="258.3" cy="191.7" r="2.3" class="land-dot"/>
          <circle cx="458.3" cy="191.7" r="2.3" class="land-dot"/>
          <circle cx="491.7" cy="191.7" r="2.3" class="land-dot"/>
          <circle cx="525.0" cy="191.7" r="2.3" class="land-dot"/>
          <circle cx="558.3" cy="191.7" r="2.3" class="land-dot"/>
          <circle cx="591.7" cy="191.7" r="2.3" class="land-dot"/>
          <circle cx="625.0" cy="191.7" r="2.3" class="land-dot"/>
          <circle cx="658.3" cy="191.7" r="2.3" class="land-dot"/>
          <circle cx="691.7" cy="191.7" r="2.3" class="land-dot"/>
          <circle cx="725.0" cy="191.7" r="2.3" class="land-dot"/>
          <circle cx="758.3" cy="191.7" r="2.3" class="land-dot"/>
          <circle cx="791.7" cy="191.7" r="2.3" class="land-dot"/>
          <circle cx="825.0" cy="191.7" r="2.3" class="land-dot"/>
          <circle cx="241.7" cy="208.3" r="2.3" class="land-dot"/>
          <circle cx="275.0" cy="208.3" r="2.3" class="land-dot"/>
          <circle cx="475.0" cy="208.3" r="2.3" class="land-dot"/>
          <circle cx="508.3" cy="208.3" r="2.3" class="land-dot"/>
          <circle cx="541.7" cy="208.3" r="2.3" class="land-dot"/>
          <circle cx="575.0" cy="208.3" r="2.3" class="land-dot"/>
          <circle cx="608.3" cy="208.3" r="2.3" class="land-dot"/>
          <circle cx="641.7" cy="208.3" r="2.3" class="land-dot"/>
          <circle cx="675.0" cy="208.3" r="2.3" class="land-dot"/>
          <circle cx="708.3" cy="208.3" r="2.3" class="land-dot"/>
          <circle cx="741.7" cy="208.3" r="2.3" class="land-dot"/>
          <circle cx="775.0" cy="208.3" r="2.3" class="land-dot"/>
          <circle cx="808.3" cy="208.3" r="2.3" class="land-dot"/>
          <circle cx="258.3" cy="225.0" r="2.3" class="land-dot"/>
          <circle cx="291.7" cy="225.0" r="2.3" class="land-dot"/>
          <circle cx="325.0" cy="225.0" r="2.3" class="land-dot"/>
          <circle cx="358.3" cy="225.0" r="2.3" class="land-dot"/>
          <circle cx="458.3" cy="225.0" r="2.3" class="land-dot"/>
          <circle cx="491.7" cy="225.0" r="2.3" class="land-dot"/>
          <circle cx="525.0" cy="225.0" r="2.3" class="land-dot"/>
          <circle cx="558.3" cy="225.0" r="2.3" class="land-dot"/>
          <circle cx="591.7" cy="225.0" r="2.3" class="land-dot"/>
          <circle cx="625.0" cy="225.0" r="2.3" class="land-dot"/>
          <circle cx="791.7" cy="225.0" r="2.3" class="land-dot"/>
          <circle cx="825.0" cy="225.0" r="2.3" class="land-dot"/>
          <circle cx="858.3" cy="225.0" r="2.3" class="land-dot"/>
          <circle cx="891.7" cy="225.0" r="2.3" class="land-dot"/>
          <circle cx="308.3" cy="241.7" r="2.3" class="land-dot"/>
          <circle cx="341.7" cy="241.7" r="2.3" class="land-dot"/>
          <circle cx="475.0" cy="241.7" r="2.3" class="land-dot"/>
          <circle cx="508.3" cy="241.7" r="2.3" class="land-dot"/>
          <circle cx="541.7" cy="241.7" r="2.3" class="land-dot"/>
          <circle cx="575.0" cy="241.7" r="2.3" class="land-dot"/>
          <circle cx="608.3" cy="241.7" r="2.3" class="land-dot"/>
          <circle cx="775.0" cy="241.7" r="2.3" class="land-dot"/>
          <circle cx="808.3" cy="241.7" r="2.3" class="land-dot"/>
          <circle cx="841.7" cy="241.7" r="2.3" class="land-dot"/>
          <circle cx="875.0" cy="241.7" r="2.3" class="land-dot"/>
          <circle cx="291.7" cy="258.3" r="2.3" class="land-dot"/>
          <circle cx="325.0" cy="258.3" r="2.3" class="land-dot"/>
          <circle cx="358.3" cy="258.3" r="2.3" class="land-dot"/>
          <circle cx="391.7" cy="258.3" r="2.3" class="land-dot"/>
          <circle cx="558.3" cy="258.3" r="2.3" class="land-dot"/>
          <circle cx="591.7" cy="258.3" r="2.3" class="land-dot"/>
          <circle cx="791.7" cy="258.3" r="2.3" class="land-dot"/>
          <circle cx="825.0" cy="258.3" r="2.3" class="land-dot"/>
          <circle cx="858.3" cy="258.3" r="2.3" class="land-dot"/>
          <circle cx="891.7" cy="258.3" r="2.3" class="land-dot"/>
          <circle cx="275.0" cy="275.0" r="2.3" class="land-dot"/>
          <circle cx="308.3" cy="275.0" r="2.3" class="land-dot"/>
          <circle cx="341.7" cy="275.0" r="2.3" class="land-dot"/>
          <circle cx="375.0" cy="275.0" r="2.3" class="land-dot"/>
          <circle cx="541.7" cy="275.0" r="2.3" class="land-dot"/>
          <circle cx="575.0" cy="275.0" r="2.3" class="land-dot"/>
          <circle cx="608.3" cy="275.0" r="2.3" class="land-dot"/>
          <circle cx="775.0" cy="275.0" r="2.3" class="land-dot"/>
          <circle cx="808.3" cy="275.0" r="2.3" class="land-dot"/>
          <circle cx="841.7" cy="275.0" r="2.3" class="land-dot"/>
          <circle cx="875.0" cy="275.0" r="2.3" class="land-dot"/>
          <circle cx="291.7" cy="291.7" r="2.3" class="land-dot"/>
          <circle cx="325.0" cy="291.7" r="2.3" class="land-dot"/>
          <circle cx="358.3" cy="291.7" r="2.3" class="land-dot"/>
          <circle cx="391.7" cy="291.7" r="2.3" class="land-dot"/>
          <circle cx="558.3" cy="291.7" r="2.3" class="land-dot"/>
          <circle cx="591.7" cy="291.7" r="2.3" class="land-dot"/>
          <circle cx="625.0" cy="291.7" r="2.3" class="land-dot"/>
          <circle cx="825.0" cy="291.7" r="2.3" class="land-dot"/>
          <circle cx="858.3" cy="291.7" r="2.3" class="land-dot"/>
          <circle cx="891.7" cy="291.7" r="2.3" class="land-dot"/>
          <circle cx="925.0" cy="291.7" r="2.3" class="land-dot"/>
          <circle cx="308.3" cy="308.3" r="2.3" class="land-dot"/>
          <circle cx="341.7" cy="308.3" r="2.3" class="land-dot"/>
          <circle cx="541.7" cy="308.3" r="2.3" class="land-dot"/>
          <circle cx="575.0" cy="308.3" r="2.3" class="land-dot"/>
          <circle cx="841.7" cy="308.3" r="2.3" class="land-dot"/>
          <circle cx="875.0" cy="308.3" r="2.3" class="land-dot"/>
          <circle cx="908.3" cy="308.3" r="2.3" class="land-dot"/>
          <circle cx="291.7" cy="325.0" r="2.3" class="land-dot"/>
          <circle cx="325.0" cy="325.0" r="2.3" class="land-dot"/>
          <circle cx="558.3" cy="325.0" r="2.3" class="land-dot"/>
          <circle cx="591.7" cy="325.0" r="2.3" class="land-dot"/>
          <circle cx="825.0" cy="325.0" r="2.3" class="land-dot"/>
          <circle cx="858.3" cy="325.0" r="2.3" class="land-dot"/>
          <circle cx="891.7" cy="325.0" r="2.3" class="land-dot"/>
          <circle cx="925.0" cy="325.0" r="2.3" class="land-dot"/>
          <circle cx="308.3" cy="341.7" r="2.3" class="land-dot"/>
          <circle cx="341.7" cy="341.7" r="2.3" class="land-dot"/>
          <circle cx="541.7" cy="341.7" r="2.3" class="land-dot"/>
          <circle cx="575.0" cy="341.7" r="2.3" class="land-dot"/>
          <circle cx="841.7" cy="341.7" r="2.3" class="land-dot"/>
          <circle cx="875.0" cy="341.7" r="2.3" class="land-dot"/>
          <circle cx="908.3" cy="341.7" r="2.3" class="land-dot"/>
          <circle cx="291.7" cy="358.3" r="2.3" class="land-dot"/>
          <circle cx="325.0" cy="358.3" r="2.3" class="land-dot"/>
          <circle cx="825.0" cy="358.3" r="2.3" class="land-dot"/>
          <circle cx="858.3" cy="358.3" r="2.3" class="land-dot"/>
          <circle cx="891.7" cy="358.3" r="2.3" class="land-dot"/>
          <circle cx="925.0" cy="358.3" r="2.3" class="land-dot"/>
          <circle cx="991.7" cy="358.3" r="2.3" class="land-dot"/>
          <circle cx="308.3" cy="375.0" r="2.3" class="land-dot"/>
          <circle cx="975.0" cy="375.0" r="2.3" class="land-dot"/>
          <circle cx="291.7" cy="391.7" r="2.3" class="land-dot"/>
          <circle cx="325.0" cy="391.7" r="2.3" class="land-dot"/>
      <path class="route" d="M228,141.5 Q365,80 500,107" fill="none" stroke="url(#iconGrad)" stroke-width="1.2" opacity=".35"/>
          <path class="route" d="M500,107 Q560,210 578,322" fill="none" stroke="url(#iconGrad)" stroke-width="1.2" opacity=".35"/>
          <path class="route" d="M578,322 Q750,360 919,344" fill="none" stroke="url(#iconGrad)" stroke-width="1.2" opacity=".35"/>
          <path class="route" d="M919,344 Q890,270 836,209.5" fill="none" stroke="url(#iconGrad)" stroke-width="1.2" opacity=".35"/>
          <path class="route" d="M836,209.5 Q700,150 578,322" fill="none" stroke="url(#iconGrad)" stroke-width="1.2" opacity=".35"/>
      <circle r="3" class="travel-particle"><animateMotion dur="7s" begin="0s" repeatCount="indefinite" path="M228,141.5 Q365,80 500,107"/></circle>
          <circle r="3" class="travel-particle"><animateMotion dur="8s" begin="1.4s" repeatCount="indefinite" path="M500,107 Q560,210 578,322"/></circle>
          <circle r="3" class="travel-particle"><animateMotion dur="6.5s" begin="2.8s" repeatCount="indefinite" path="M578,322 Q750,360 919,344"/></circle>
          <circle r="3" class="travel-particle"><animateMotion dur="7.5s" begin="4.1s" repeatCount="indefinite" path="M919,344 Q890,270 836,209.5"/></circle>
          <circle r="3" class="travel-particle"><animateMotion dur="9s" begin="5.5s" repeatCount="indefinite" path="M836,209.5 Q700,150 578,322"/></circle>
      </svg>
        <?php foreach ( $nodes as $node ) :
          $flag       = isset( $node['flag'] ) ? $node['flag'] : '';
          $country    = isset( $node['country'] ) ? $node['country'] : '';
          $left       = isset( $node['left'] ) ? $node['left'] : '0';
          $top        = isset( $node['top'] ) ? $node['top'] : '0';
          $industries = isset( $node['industries'] ) ? array_filter( array_map( 'trim', explode( ',', $node['industries'] ) ) ) : array();
          $services   = isset( $node['services'] ) ? array_filter( array_map( 'trim', explode( ',', $node['services'] ) ) ) : array();
        ?>
        <div class="map-node" style="left:<?php echo esc_attr( $left ); ?>%;top:<?php echo esc_attr( $top ); ?>%;">
          <span class="node-pulse"></span><span class="node-pulse d2"></span><span class="node-core"></span>
          <div class="map-tooltip">
            <div class="tt-head"><?php echo esc_html( $flag . ' ' . $country ); ?></div>
            <div class="tt-label">Industries</div>
            <div class="tt-tags"><?php foreach ( $industries as $tag ) : ?><span><?php echo esc_html( $tag ); ?></span><?php endforeach; ?></div>
            <div class="tt-label">Services</div>
            <div class="tt-tags"><?php foreach ( $services as $tag ) : ?><span><?php echo esc_html( $tag ); ?></span><?php endforeach; ?></div>
          </div>
        </div>
        <?php endforeach; ?>
    </div>
  </div>
  <div class="wrap">
    <p class="trust-line" data-reveal="up"><?php echo wp_kses_post( edc_opt( 'trust_line', "From Australia to the US, I've worked with businesses in five countries to build, migrate and look after sites that help them grow." ) ); ?></p>
    <div class="metric-row" data-reveal="up">
      <?php foreach ( $metrics as $metric ) :
        $number = isset( $metric['number'] ) ? $metric['number'] : '';
        $label  = isset( $metric['label'] ) ? $metric['label'] : '';
      ?>
        <div class="metric"><div class="num"><?php echo esc_html( $number ); ?></div><div class="lbl"><?php echo esc_html( $label ); ?></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
