<?php
/**
 * Front page — assembles the homepage from template-parts/home/.
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
	array( 'show_hero', 'Hero', 'template-parts/home/hero' ),
	array( 'show_home_bridge', 'Bridge', 'template-parts/home/bridge' ),
	array( 'show_work', 'Work', 'template-parts/home/work' ),
	array( 'show_home_called', 'Called in for', 'template-parts/home/called' ),
	array( 'show_home_tools', 'Toolbox', 'template-parts/home/tools' ),
	array( 'show_home_about', 'Behind the code', 'template-parts/home/about' ),
	array( 'show_home_record', 'Track record', 'template-parts/home/record' ),
	array( 'show_cta', 'Contact', 'template-parts/home/contact' ),
);

foreach ( $sections as $section ) {
	list( $field, $label, $part ) = $section;
	if ( ! edc_should_show_section( $field ) ) {
		continue;
	}
	edc_section_open( $field, $label );
	get_template_part( $part );
	edc_section_close();
}
?>
</main>
<?php
get_footer();
