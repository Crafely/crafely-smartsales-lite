<?php
/**
 * Crafely SmartSales Lite Database Schema
 *
 * @package CrafelySmartSalesLite
 */

namespace CSMSL\Includes\DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schema class
 * Handles database schema creation and updates.
 */
class InventorySchema {

	/**
	 * Create custom database tables.
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name_inventory = $wpdb->prefix . 'smartsales_inventory';
		$table_name_movements = $wpdb->prefix . 'smartsales_inventory_movements';

		$sql = "CREATE TABLE $table_name_inventory (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            outlet_id bigint(20) NOT NULL,
            stock int(11) NOT NULL DEFAULT 0,
            threshold int(11) NOT NULL DEFAULT 0,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY product_outlet_unique (product_id, outlet_id),
            KEY outlet_id (outlet_id)
        ) $charset_collate;

        CREATE TABLE $table_name_movements (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            outlet_id bigint(20) NOT NULL,
            type varchar(50) NOT NULL,
            quantity int(11) NOT NULL,
            reason varchar(255) DEFAULT NULL,
            user_id bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            related_outlet_id bigint(20) DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY product_id (product_id),
            KEY outlet_id (outlet_id),
            KEY user_id (user_id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Drop custom database tables.
	 */
	public static function drop_tables() {
		global $wpdb;

		$table_name_inventory = esc_sql( $wpdb->prefix . 'smartsales_inventory' );
		$table_name_movements = esc_sql( $wpdb->prefix . 'smartsales_inventory_movements' );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- These tables are removed intentionally on uninstall/reset.
		$wpdb->query( "DROP TABLE IF EXISTS `{$table_name_inventory}`" );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- These tables are removed intentionally on uninstall/reset.
		$wpdb->query( "DROP TABLE IF EXISTS `{$table_name_movements}`" );
	}
}
