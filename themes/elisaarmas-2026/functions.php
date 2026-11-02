<?php
/**
 * Elisa Armas 2026 theme functions.
 *
 * @package ElisaArmas2026
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ELISAARMAS_2026_VERSION', '0.1.0' );
define( 'ELISAARMAS_2026_DIR', get_template_directory() );
define( 'ELISAARMAS_2026_URI', get_template_directory_uri() );

require_once ELISAARMAS_2026_DIR . '/inc/setup.php';
require_once ELISAARMAS_2026_DIR . '/inc/enqueue.php';
