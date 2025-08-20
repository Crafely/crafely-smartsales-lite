<?php

namespace CSMSL\Includes\DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AssistancesDB {


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

	public static function drop_table() {
		global $wpdb;

		$table_name = esc_sql( $wpdb->prefix . 'ai_smart_sales_assistances' );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- This table is removed intentionally on uninstall/reset.
		$wpdb->query( "DROP TABLE IF EXISTS `$table_name`" );
	}
}
