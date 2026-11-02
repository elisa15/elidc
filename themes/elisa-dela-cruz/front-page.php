<?php
/**
 * Front page — assembles the one-page home from template-parts.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="site-main">
<?php
$sections = array(
	array( 'show_hero', 'Hero', 'section-hero' ),
	array( 'show_proof', 'Proof strip', 'section-proof-testi' ),
	array( 'show_tabs', 'Why clients keep coming back', 'section-hire' ),
	array( 'show_believe', 'What I believe', 'section-believe' ),
	array( 'show_toolbox', 'Toolbox', 'section-toolbox' ),
	array( 'show_services', 'Services', 'section-services' ),
	array( 'show_map', 'Global map', 'section-map' ),
	array( 'show_work', 'Selected work', 'section-work' ),
	array( 'show_help', 'Sound familiar?', 'section-help' ),
	array( 'show_testimonials', 'Testimonials', 'section-testimonials' ),
	array( 'show_cta', 'Final CTA', 'section-cta' ),
);

foreach ( $sections as $section ) {
	list( $field, $label, $part ) = $section;
	if ( ! edc_should_show_section( $field ) ) {
		continue;
	}
	edc_section_open( $field, $label );
	get_template_part( 'template-parts/' . $part );
	edc_section_close();
}
?>
</main>
<?php
get_footer();
