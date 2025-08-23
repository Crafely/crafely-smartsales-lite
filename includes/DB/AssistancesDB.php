<?php
/**
 * AssistancesDB Class
 *
 * This class handles the database operations for assistances in the Crafely SmartSales Lite plugin.
 *
 * @package CrafelySmartSalesLite
 */

namespace CSMSL\Includes\DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class AssistancesDB
 *
 * This class provides methods to create and drop the assistances table in the database.
 */
class AssistancesDB {

	/**
	 * Create the assistances table in the database.
	 *
	 * This method creates a table to store assistances with fields for user ID, thread ID, title, page, and AI configuration.
	 * It uses the dbDelta function to ensure the table is created with the correct structure.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'ai_smart_sales_assistances';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9) NOT NULL,
            thread_id varchar(255) NOT NULL,
            title varchar(255) NOT NULL,
            page varchar(255) NOT NULL,
            ai_config json DEFAULT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
	/**
	 * Drop the assistances table from the database.
	 *
	 * This method removes the assistances table if it exists.
	 * It is typically used during plugin uninstallation or reset.
	 */
	public static function drop_table() {
		global $wpdb;

		$table_name = esc_sql( $wpdb->prefix . 'ai_smart_sales_assistances' );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- This table is removed intentionally on uninstall/reset.
		$wpdb->query( "DROP TABLE IF EXISTS `$table_name`" );
	}
}
