<?php
/**
 * Crafely SmartSales Lite Post Types
 *
 * @package CrafelySmartSalesLite
 */

namespace CSMSL\Includes\CPT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class PostTypes
 * Handles the registration of custom post types for Crafely SmartSales Lite.
 */
class PostTypes {

	/**
	 * Constructor.
	 * Initializes the custom post types by hooking into the 'init' action.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	/**
	 * Registers custom post types for the Crafely SmartSales Lite plugin.
	 */
	public function register_post_types() {
		// Register Outlet post type.
		register_post_type(
			'csmsl_outlet',
			array(
				'labels'       => array(
					'name'          => __( 'Outlets', 'crafely-smartsales-lite' ),
					'singular_name' => __( 'Outlet', 'crafely-smartsales-lite' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'pos',
				'supports'     => array( 'title' ),
				'capabilities' => array(
					'edit_post'          => 'csmsl_manage_outlet',
					'read_post'          => 'csmsl_manage_outlet',
					'delete_post'        => 'csmsl_manage_outlet',
					'edit_posts'         => 'csmsl_manage_outlet',
					'edit_others_posts'  => 'csmsl_manage_outlet',
					'publish_posts'      => 'csmsl_manage_outlet',
					'read_private_posts' => 'csmsl_manage_outlet',
				),
			)
		);

		// Register Counter post type.
		register_post_type(
			'csmsl_counter',
			array(
				'labels'       => array(
					'name'          => __( 'Counters', 'crafely-smartsales-lite' ),
					'singular_name' => __( 'Counter', 'crafely-smartsales-lite' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'pos',
				'supports'     => array( 'title' ),
				'capabilities' => array(
					'edit_post'          => 'csmsl_manage_counters',
					'read_post'          => 'csmsl_manage_counters',
					'delete_post'        => 'csmsl_manage_counters',
					'edit_posts'         => 'csmsl_manage_counters',
					'edit_others_posts'  => 'csmsl_manage_counters',
					'publish_posts'      => 'csmsl_manage_counters',
					'read_private_posts' => 'csmsl_manage_counters',
				),
			)
		);

		// Register Assignment History post type.
		register_post_type(
			'smsl_assign_hist',
			array(
				'labels'       => array(
					'name'          => __( 'Assignment History', 'crafely-smartsales-lite' ),
					'singular_name' => __( 'Assignment History', 'crafely-smartsales-lite' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'pos',
				'supports'     => array( 'title', 'editor' ),
			)
		);

		// Register Invoice post type.
		register_post_type(
			'csmsl_invoice',
			array(
				'labels'       => array(
					'name'          => __( 'Invoices', 'crafely-smartsales-lite' ),
					'singular_name' => __( 'Invoice', 'crafely-smartsales-lite' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'pos',
				'show_in_rest' => true,
				'supports'     => array( 'title' ),
				'capabilities' => array(
					'edit_post'          => 'csmsl_manage_invoices',
					'read_post'          => 'csmsl_manage_invoices',
					'delete_post'        => 'csmsl_manage_invoices',
					'edit_posts'         => 'csmsl_manage_invoices',
					'edit_others_posts'  => 'csmsl_manage_invoices',
					'publish_posts'      => 'csmsl_manage_invoices',
					'read_private_posts' => 'csmsl_manage_invoices',
				),
			)
		);
	}
}
