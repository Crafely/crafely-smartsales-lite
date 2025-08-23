<?php
/**
 * RolesManager class for managing custom roles in Crafely SmartSales Lite.
 *
 * @package CrafelySmartSalesLite
 */

namespace CSMSL\Includes\Api\Roles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class RolesManager
 *
 * This class handles the registration and management of custom roles for the Crafely SmartSales Lite plugin.
 */
class RolesManager {

	// Define role capabilities as Manager.
	private const OUTLET_MANAGER_CAPS = array(
		'read'               => true,
		'upload_files'       => true,
		'manage_products'    => true,
		'manage_orders'      => true,
		'manage_customers'   => true,
		'view_sales_reports' => true,
		'manage_pos'         => true,
		'manage_outlets'     => true,
		'manage_counters'    => true,
		'assign_cashiers'    => true,
	);
	// Define role capabilities for the cashier.
	private const CASHIER_CAPS = array(
		'read'            => true,
		'upload_files'    => true,
		'operate_counter' => true,
		'process_sales'   => true,
		'manage_pos'      => true,
	);
	// Define role capabilities for the shop manager.
	private const SHOP_MANAGER_CAPS = array(
		'read'               => true,
		'upload_files'       => true,
		'manage_products'    => true,
		'manage_orders'      => true,
		'manage_customers'   => true,
		'view_sales_reports' => true,
		'manage_pos'         => true,
		'manage_outlets'     => true,
	);

	/**
	 * List of custom roles to manage.
	 * This array contains all custom roles that the RolesManager will handle.
	 *
	 * @var array
	 * @access private
	 */
	private $custom_roles = array(
		'csmsl_pos_outlet_manager',
		'csmsl_pos_cashier',
		'csmsl_pos_shop_manager',
	);

	/**
	 * Constructor to initialize the RolesManager.
	 * This sets up the action hooks for role registration and removal.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_custom_roles' ) );
	}

	/**
	 * Registers custom roles and capabilities.
	 * This method creates the roles if they do not already exist and assigns capabilities.
	 */
	public function register_custom_roles() {
		// Backup existing users' roles before creating new ones.
		$this->backup_user_roles();

		$this->create_outlet_manager_role();
		$this->create_cashier_role();
		$this->create_shop_manager_role();
		$this->add_admin_capabilities();
	}
	/**
	 * Removes custom roles and capabilities.
	 * This method is used to clean up roles when the plugin is deactivated.
	 */
	public function remove_custom_roles() {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			return;
		}

		// Remove custom roles while preserving core capabilities.
		foreach ( $this->custom_roles as $role ) {
			$role_obj = get_role( $role );
			if ( $role_obj ) {
				// Remove the role but don't touch core capabilities.
				remove_role( $role );
			}
		}
	}

	/**
	 * Creates the Outlet Manager role with specific capabilities.
	 * This method checks if the role already exists before creating it.
	 */
	private function create_outlet_manager_role() {
		if ( ! get_role( 'csmsl_pos_outlet_manager' ) ) {
			add_role( 'csmsl_pos_outlet_manager', 'Pos Outlet Manager', self::OUTLET_MANAGER_CAPS );
		}
	}
	/**
	 * Creates the Cashier role with specific capabilities.
	 * This method checks if the role already exists before creating it.
	 */
	private function create_cashier_role() {
		if ( ! get_role( 'csmsl_pos_cashier' ) ) {
			add_role( 'csmsl_pos_cashier', 'Pos Cashier', self::CASHIER_CAPS );
		}
	}
	/**
	 * Creates the Shop Manager role with specific capabilities.
	 * This method checks if the role already exists before creating it.
	 */
	private function create_shop_manager_role() {
		if ( ! get_role( 'csmsl_pos_shop_manager' ) ) {
			add_role( 'csmsl_pos_shop_manager', 'Pos Shop Manager', self::SHOP_MANAGER_CAPS );
		}
	}

	/**
	 * Adds additional capabilities to the Administrator role.
	 * This method ensures that the Administrator role has all necessary capabilities for managing POS.
	 */
	private function add_admin_capabilities() {
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			foreach ( array_merge( self::OUTLET_MANAGER_CAPS, self::CASHIER_CAPS, self::SHOP_MANAGER_CAPS ) as $cap => $grant ) {
				$admin->add_cap( $cap );
			}
		}
	}
	/**
	 * Backs up users with custom roles to prevent data loss.
	 * This method stores the current users with custom roles in an option.
	 */
	private function backup_user_roles() {
		$users_with_custom_roles = array();

		foreach ( $this->custom_roles as $role ) {
			$users = get_users( array( 'role' => $role ) );
			foreach ( $users as $user ) {
				$users_with_custom_roles[ $user->ID ] = $role;
			}
		}

		// Store the backup with a timestamp.
		update_option(
			'csmsl_backed_up_roles',
			array(
				'timestamp' => current_datetime()->getTimestamp(),
				'roles'     => $users_with_custom_roles,
			)
		);
	}

	/**
	 * Reassigns users with custom roles to the Subscriber role.
	 * This method is used to clean up user roles when the plugin is deactivated.
	 */
	private function reassign_users_to_subscriber() {
		$backup = get_option( 'csmsl_backed_up_roles', array() );
		if ( ! empty( $backup['roles'] ) ) {
			foreach ( $backup['roles'] as $user_id => $role ) {
				$user = get_user_by( 'id', $user_id );
				if ( $user ) {
					$user->set_role( 'subscriber' );
				}
			}
		}
	}
}
