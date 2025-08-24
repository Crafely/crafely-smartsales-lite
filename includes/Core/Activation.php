<?php
/**
 * Crafely SmartSales Lite
 *
 * @package CrafelySmartSalesLite
 */

namespace CSMSL\Includes\Core;

use CSMSL\Includes\Api\Roles\RolesManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Activation
 *
 * Handles plugin activation and deactivation tasks, including:
 * - Registering and removing custom roles
 * - Creating and cleaning up database tables
 * - Setting and deleting default plugin options
 * - Backing up and restoring user roles
 * - Flushing rewrite rules
 *
 * @package CSMSL\Includes\Core
 */
class Activation {

	/**
	 * Run on plugin activation.
	 *
	 * Registers custom roles, creates necessary tables, sets default options,
	 * backs up user roles, and flushes rewrite rules.
	 *
	 * @return void
	 */
	public static function activate() {
		$roles_manager = new RolesManager();
		$roles_manager->register_custom_roles();
		self::create_tables();
		self::set_default_options();
		self::backup_user_roles();
		flush_rewrite_rules();
	}

	/**
	 * Run on plugin deactivation.
	 *
	 * Cleans up roles, tables, options, restores user roles, removes custom roles,
	 * and flushes rewrite rules. Roles cleanup runs only in development mode.
	 *
	 * @return void
	 */
	public static function deactivate() {
		if ( defined( 'CSMSL_DEV_MODE' ) && CSMSL_DEV_MODE ) {
			self::cleanup_roles();
		}
		self::cleanup_tables();
		self::cleanup_options();
		self::restore_user_roles();
		$roles_manager = new RolesManager();
		$roles_manager->remove_custom_roles();
		flush_rewrite_rules();
	}

	/**
	 * Create custom plugin tables.
	 *
	 * @return void
	 */
	private static function create_tables() {
		global $wpdb;
		// Add table creation logic here.
	}

	/**
	 * Set default plugin options.
	 *
	 * @return void
	 */
	private static function set_default_options() {
		add_option( 'CSMSL_VERSION', CSMSL_VERSION );
	}

	/**
	 * Cleanup custom plugin tables.
	 *
	 * @return void
	 */
	private static function cleanup_tables() {
		global $wpdb;
		// Add table cleanup logic here.
	}

	/**
	 * Delete plugin options.
	 *
	 * @return void
	 */
	private static function cleanup_options() {
		delete_option( 'CSMSL_VERSION' );
	}

	/**
	 * Remove custom roles.
	 *
	 * @return void
	 */
	private static function cleanup_roles() {
		$roles_manager = new RolesManager();
		$roles_manager->remove_custom_roles();
	}

	/**
	 * Backup all users' roles and capabilities.
	 *
	 * Stores a backup in the 'csmsl_user_roles_backup' option.
	 *
	 * @return void
	 */
	private static function backup_user_roles() {
		$users  = get_users();
		$backup = array();

		foreach ( $users as $user ) {
			if ( ! empty( $user->roles ) ) {
				$backup[ $user->ID ] = array(
					'roles'        => $user->roles,
					'capabilities' => get_user_meta( $user->ID, 'wp_capabilities', true ),
				);
			}
		}

		update_option( 'csmsl_user_roles_backup', $backup );
	}

	/**
	 * Restore all users' roles from backup.
	 *
	 * Deletes the 'csmsl_user_roles_backup' option after restoration.
	 *
	 * @return void
	 */
	private static function restore_user_roles() {
		$backup = get_option( 'csmsl_user_roles_backup', array() );

		foreach ( $backup as $user_id => $data ) {
			$user = get_user_by( 'id', $user_id );
			if ( $user ) {
				$user->set_role( '' );

				if ( in_array( 'administrator', $data['roles'], true ) ) {
					$user->set_role( 'administrator' );
				} elseif ( array_intersect( $data['roles'], array( 'editor', 'author', 'contributor', 'subscriber' ) ) ) {
					foreach ( $data['roles'] as $role ) {
						if ( in_array( $role, array( 'editor', 'author', 'contributor', 'subscriber' ), true ) ) {
							$user->set_role( $role );
							break;
						}
					}
				} else {
					$user->set_role( 'subscriber' );
				}
			}
		}

		delete_option( 'csmsl_user_roles_backup' );
	}

	/**
	 * Force flush rewrite rules immediately and set flags to ensure
	 * they are flushed on next page load.
	 *
	 * @return void
	 */
	public static function force_flush_rewrite_rules() {
		update_option( 'csmsl_flush_rewrite_rules', true );
		flush_rewrite_rules();
		update_option( 'csmsl_permalinks_flushed', '' );
	}
}
