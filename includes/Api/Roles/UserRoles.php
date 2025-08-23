<?php
/**
 * Crafely SmartSales Lite User Roles
 *
 * This class handles the creation of user roles for the Crafely SmartSales Lite plugin.
 *
 * @package CrafelySmartSalesLite
 */

namespace CSMSL\Includes\Api\Roles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * UserRoles Class
 * This class is responsible for creating and managing user roles specific to the Crafely SmartSales Lite plugin.
 */
class UserRoles {
	/**
	 * Constructor
	 * Initializes the user roles.
	 */
	public function create_outlet_manager_role() {
		if ( ! get_role( 'csmsl_pos_outlet_manager' ) ) {
			// Log role creation attempt.

			add_role(
				'csmsl_pos_outlet_manager',
				'Pos Outlet Manager',
				array(
					// WordPress Admin Capabilities.
					'read'                 => true,
					'edit_posts'           => true,
					'delete_posts'         => true,
					'edit_published_posts' => true,
					'publish_posts'        => true,
					'upload_files'         => true,
					'edit_pages'           => true,
					'read_private_pages'   => true,
					'read_private_posts'   => true,
					'edit_private_posts'   => true,
					'edit_others_posts'    => true,
					'moderate_comments'    => true,
					'manage_categories'    => true,
					'manage_links'         => true,
					'list_users'           => true,

					// POS Specific Capabilities.
					'manage_products'      => true,
					'manage_orders'        => true,
					'manage_customers'     => true,
					'view_sales_reports'   => true,
					'manage_pos'           => true,
					'manage_outlets'       => true,
					'access_admin'         => true,

					// User levels.
					'level_0'              => true,
					'level_1'              => true,
					'level_2'              => true,
					'level_3'              => true,
					'level_4'              => true,
					'level_5'              => true,
				)
			);
		}
	}
	/**
	 * Create the Cashier role.
	 * This role is for users who can perform basic POS operations.
	 */
	public function create_cashier_role() {
		// Cashiers only get basic POS access.
		if ( ! get_role( 'csmsl_pos_cashier' ) ) {
			add_role(
				'csmsl_pos_cashier',
				'Pos Cashier',
				array(
					'read'             => true,
					'upload_files'     => true,
					'manage_orders'    => true,
					'manage_customers' => true,
					'manage_pos'       => true,
				)
			);
		}
	}
	/**
	 * Create the Shop Manager role.
	 * This role is for users who manage the shop and have broader capabilities.
	 */
	public function create_shop_manager_role() {
		if ( ! get_role( 'csmsl_pos_shop_manager' ) ) {
			add_role(
				'csmsl_pos_shop_manager',
				'Pos Shop Manager',
				array(
					// WordPress Admin Capabilities.
					'read'                   => true,
					'edit_posts'             => true,
					'delete_posts'           => true,
					'edit_published_posts'   => true,
					'publish_posts'          => true,
					'edit_pages'             => true,
					'read_private_pages'     => true,
					'read_private_posts'     => true,
					'edit_private_posts'     => true,
					'edit_others_posts'      => true,
					'edit_published_pages'   => true,
					'publish_pages'          => true,
					'delete_pages'           => true,
					'delete_private_pages'   => true,
					'delete_published_pages' => true,
					'delete_others_pages'    => true,
					'edit_private_pages'     => true,
					'edit_others_pages'      => true,
					'manage_categories'      => true,
					'manage_links'           => true,
					'moderate_comments'      => true,
					'upload_files'           => true,
					'export'                 => true,
					'import'                 => true,
					'list_users'             => true,
					'edit_theme_options'     => true,
					'manage_options'         => true,
					'administrator'          => true,

					// POS Specific Capabilities.
					'manage_products'        => true,
					'manage_orders'          => true,
					'manage_customers'       => true,
					'view_sales_reports'     => true,
					'manage_pos'             => true,
					'manage_outlets'         => true,
					'access_admin'           => true,

					// User levels from 0 to 10.
					'level_0'                => true,
					'level_1'                => true,
					'level_2'                => true,
					'level_3'                => true,
					'level_4'                => true,
					'level_5'                => true,
					'level_6'                => true,
					'level_7'                => true,
					'level_8'                => true,
					'level_9'                => true,
					'level_10'               => true,
				)
			);
		}
	}
}
