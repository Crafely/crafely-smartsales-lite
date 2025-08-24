<?php
/**
 * Configuration manager for Crafely SmartSales Lite plugin.
 *
 * @package CrafelySmartSalesLite
 */

namespace CSMSL\Includes\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Config
 *
 * Manages plugin configuration settings.
 */
class Config {

	/**
	 * Plugin configuration
	 *
	 * @var array
	 */
	private static $config = null;

	/**
	 * Get configuration value
	 * Retrieves a configuration value by key. If the key is not found, the fallback value is returned.
	 *
	 * @param string $key  The configuration key to retrieve (supports dot notation for nested values).
	 * @param mixed  $fallback The default value to return if the key is not found.
	 *
	 * @return mixed The configuration value or the fallback if not set.
	 */
	public static function get( $key, $fallback = null ) {
		if ( null === self::$config ) {
			self::load();
		}

		return self::get_nested_value( self::$config, $key, $fallback );
	}

	/**
	 * Set configuration value
	 *
	 * @param string $key   The configuration key to set (supports dot notation for nested values).
	 * @param mixed  $value The value to store for the given key.
	 */
	public static function set( $key, $value ) {
		if ( null === self::$config ) {
			self::load();
		}

		self::set_nested_value( self::$config, $key, $value );
	}

	/**
	 * Load configuration
	 */
	private static function load() {
		self::$config = array(
			'plugin'   => array(
				'name'            => 'crafely-smartsales-lite',
				'version'         => CSMSL_VERSION,
				'text_domain'     => 'crafely-smartsales-lite',
				'namespace'       => 'CSMSL',
				'min_wp_version'  => '5.0',
				'min_wc_version'  => '5.0',
				'min_php_version' => '7.4',
			),
			'api'      => array(
				'namespace'  => 'ai-smart-sales/v1',
				'version'    => 'v1',
				'rate_limit' => 100, // requests per minute.
				'cache_ttl'  => 300,   // 5 minutes.
			),
			'pos'      => array(
				'roles'           => array(
					'csmsl_pos_cashier',
					'csmsl_pos_outlet_manager',
					'csmsl_pos_shop_manager',
				),
				'urls'            => array(
					'base'   => '/smart-pos',
					'login'  => '/smart-pos/auth/login',
					'logout' => '/smart-pos/auth/logout',
				),
				'session_timeout' => 3600, // 1 hour.
				'auto_logout'     => true,
			),
			'security' => array(
				'nonce_action'       => 'csmsl_nonce',
				'allowed_file_types' => array( 'jpg', 'jpeg', 'png', 'gif', 'pdf' ),
				'max_file_size'      => 5242880, // 5MB.
				'enable_logging'     => defined( 'CSMSL_DEV_MODE' ) && CSMSL_DEV_MODE,
			),
			'database' => array(
				'tables'  => array(
					'assistances',
					'activity_logs',
					'settings',
				),
				'charset' => 'utf8mb4',
				'collate' => 'utf8mb4_unicode_ci',
			),
			'cache'    => array(
				'enabled'     => true,
				'default_ttl' => 300,
				'groups'      => array(
					'products' => 600,
					'orders'   => 300,
					'reports'  => 900,
				),
			),
			'features' => array(
				'ai_assistance'        => true,
				'analytics'            => true,
				'inventory_management' => true,
				'multi_outlet'         => true,
				'offline_mode'         => false,
			),
		);

		// Apply filters to allow customization.
		self::$config = apply_filters( 'csmsl_config', self::$config );
	}

	/**
	 * Get nested configuration value using dot notation.
	 *
	 * Traverses an array using "dot notation" to access nested values.
	 * Returns the fallback value if the key does not exist.
	 *
	 * @param array  $args     The configuration array to search in.
	 * @param string $key      The configuration key, supports dot notation for nested values.
	 * @param mixed  $fallback The value to return if the key is not found.
	 *
	 * @return mixed The configuration value or the fallback if the key is missing.
	 */
	private static function get_nested_value( $args, $key, $fallback = null ) {
		if ( false === strpos( $key, '.' ) ) {
			return isset( $args[ $key ] ) ? $args[ $key ] : $fallback;
		}

		$keys  = explode( '.', $key );
		$value = $args;

		foreach ( $keys as $k ) {
			if ( ! is_array( $value ) || ! isset( $value[ $k ] ) ) {
				return $fallback;
			}
			$value = $value[ $k ];
		}

		return $value;
	}

	/**
	 * Set nested configuration value using dot notation.
	 *
	 * Updates an array using "dot notation" to set nested values.
	 * If the key does not use dot notation, the value is set directly.
	 *
	 * @param array  $args  The configuration array to modify (passed by reference).
	 * @param string $key   The configuration key, supports dot notation for nested values.
	 * @param mixed  $value The value to assign to the given key.
	 *
	 * @return void
	 */
	private static function set_nested_value( &$args, $key, $value ) {
		if ( strpos( $key, '.' ) === false ) {
			$args[ $key ] = $value;
			return;
		}

		$keys    = explode( '.', $key );
		$current = &$args;

		foreach ( $keys as $k ) {
			if ( ! isset( $current[ $k ] ) || ! is_array( $current[ $k ] ) ) {
				$current[ $k ] = array();
			}
			$current = &$current[ $k ];
		}

		$current = $value;
	}

	/**
	 * Get all configuration
	 *
	 * @return array
	 */
	public static function all() {
		if ( null === self::$config ) {
			self::load();
		}

		return self::$config;
	}

	/**
	 * Check if a configuration key exists.
	 *
	 * Determines whether the given configuration key has a value set.
	 *
	 * @param string $key The configuration key to check (supports dot notation for nested values).
	 *
	 * @return bool True if the configuration key exists and is not null, false otherwise.
	 */
	public static function has( $key ) {
		return self::get( $key ) !== null;
	}

	/**
	 * Get API configuration
	 *
	 * @return array
	 */
	public static function api() {
		return self::get( 'api', array() );
	}

	/**
	 * Get POS configuration
	 *
	 * @return array
	 */
	public static function pos() {
		return self::get( 'pos', array() );
	}

	/**
	 * Get security configuration
	 *
	 * @return array
	 */
	public static function security() {
		return self::get( 'security', array() );
	}

	/**
	 * Check if a feature is enabled.
	 *
	 * Looks up the configuration to determine whether the given feature flag is enabled.
	 *
	 * @param string $feature The feature key to check in the configuration.
	 *
	 * @return bool True if the feature is enabled, false otherwise.
	 */
	public static function is_feature_enabled( $feature ) {
		return (bool) self::get( "features.{$feature}", false );
	}
}
