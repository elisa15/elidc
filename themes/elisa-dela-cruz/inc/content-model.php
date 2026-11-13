<?php
/**
 * Content model: post types, taxonomies, seeded terms and editor notices.
 * See docs/redesign/03-acf-schema.md §2.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EDC_TERMS_VERSION', '1' );

function edc_register_project_cpt() {
	$labels = array(
		'name'                  => __( 'Projects', 'elisa-dela-cruz' ),
		'singular_name'         => __( 'Project', 'elisa-dela-cruz' ),
		'menu_name'             => __( 'Projects', 'elisa-dela-cruz' ),
		'add_new'               => __( 'Add New', 'elisa-dela-cruz' ),
		'add_new_item'          => __( 'Add New Project', 'elisa-dela-cruz' ),
		'edit_item'             => __( 'Edit Project', 'elisa-dela-cruz' ),
		'new_item'              => __( 'New Project', 'elisa-dela-cruz' ),
		'view_item'             => __( 'View Project', 'elisa-dela-cruz' ),
		'view_items'            => __( 'View Projects', 'elisa-dela-cruz' ),
		'search_items'          => __( 'Search Projects', 'elisa-dela-cruz' ),
		'not_found'             => __( 'No projects found', 'elisa-dela-cruz' ),
		'not_found_in_trash'    => __( 'No projects found in Trash', 'elisa-dela-cruz' ),
		'all_items'             => __( 'All Projects', 'elisa-dela-cruz' ),
		'archives'              => __( 'Project Archives', 'elisa-dela-cruz' ),
		'featured_image'        => __( 'Project Screenshot', 'elisa-dela-cruz' ),
		'set_featured_image'    => __( 'Set project screenshot', 'elisa-dela-cruz' ),
		'remove_featured_image' => __( 'Remove project screenshot', 'elisa-dela-cruz' ),
	);

	register_post_type( 'project', array(
		'labels'        => $labels,
		'public'        => true,
		'has_archive'   => true,
		'rewrite'       => array( 'slug' => 'projects', 'with_front' => false ),
		'menu_icon'     => 'dashicons-portfolio',
		'menu_position' => 5,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'taxonomies'    => array( 'project_type', 'project_platform' ),
		'show_in_rest'  => true,
		'show_ui'       => true,
		'show_in_menu'  => true,
	) );
}
add_action( 'init', 'edc_register_project_cpt' );

function edc_register_project_taxonomies() {
	register_taxonomy( 'project_type', 'project', array(
		'labels'            => array(
			'name'          => __( 'Kinds of work', 'elisa-dela-cruz' ),
			'singular_name' => __( 'Kind of work', 'elisa-dela-cruz' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => false,
		'query_var'         => false,
	) );

	register_taxonomy( 'project_platform', 'project', array(
		'labels'            => array(
			'name'          => __( 'Platforms', 'elisa-dela-cruz' ),
			'singular_name' => __( 'Platform', 'elisa-dela-cruz' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => false,
		'query_var'         => false,
	) );
}
add_action( 'init', 'edc_register_project_taxonomies', 5 );

/**
 * Create the fixed terms once. Re-runs only when EDC_TERMS_VERSION changes.
 */
function edc_seed_project_terms() {
	if ( get_option( 'edc_terms_version' ) === EDC_TERMS_VERSION ) {
		return;
	}

	$terms = array(
		'project_type'     => array(
			'built'  => 'Built',
			'solved' => 'Solved',
		),
		'project_platform' => array(
			'wordpress'   => 'WordPress',
			'shopify'     => 'Shopify',
			'woocommerce' => 'WooCommerce',
			'elementor'   => 'Elementor',
		),
	);

	foreach ( $terms as $taxonomy => $items ) {
		foreach ( $items as $slug => $name ) {
			if ( ! term_exists( $slug, $taxonomy ) ) {
				wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
			}
		}
	}

	update_option( 'edc_terms_version', EDC_TERMS_VERSION, false );
}
add_action( 'init', 'edc_seed_project_terms', 20 );

function edc_register_testimonial_cpt() {
	register_post_type( 'testimonial', array(
		'labels'        => array(
			'name'          => __( 'Testimonials', 'elisa-dela-cruz' ),
			'singular_name' => __( 'Testimonial', 'elisa-dela-cruz' ),
			'add_new_item'  => __( 'Add New Testimonial', 'elisa-dela-cruz' ),
			'edit_item'     => __( 'Edit Testimonial', 'elisa-dela-cruz' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => true,
		'menu_icon'     => 'dashicons-format-quote',
		'menu_position' => 6,
		'supports'      => array( 'title' ),
		'show_in_rest'  => false,
	) );
}
add_action( 'init', 'edc_register_testimonial_cpt' );

/**
 * Warn editors when a saved project has no kind of work (Built / Solved).
 */
function edc_project_kind_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'post' !== $screen->base || 'project' !== $screen->post_type ) {
		return;
	}

	$post = get_post();
	if ( ! $post || 'auto-draft' === $post->post_status ) {
		return;
	}

	$kinds = get_the_terms( $post, 'project_type' );
	if ( ! empty( $kinds ) && ! is_wp_error( $kinds ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__( 'This project has no kind of work. Tick Built, Solved or both on the Card tab so it shows up in the filters.', 'elisa-dela-cruz' )
	);
}
add_action( 'admin_notices', 'edc_project_kind_notice' );
