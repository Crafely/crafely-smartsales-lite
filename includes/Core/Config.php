<?php

/**
 * Configuration manager for AI Smart Sales
 *
 * @package AI Smart Sales
 */

namespace CSMSL\Includes\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Config {

	/**
	 * Plugin configuration
	 *
	 * @var array
	 */
	private static $config = null;

	/**
	 * Get configuration value
	 *
	 * @param string $key
	 * @param mixed  $fallback
	 * @return mixed
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
	 * @param string $key
	 * @param mixed  $value
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
	 * Get nested configuration value using dot notation
	 *
	 * @param array  $args
	 * @param string $key
	 * @param mixed  $fallback
	 * @return mixed
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
	 * Set nested configuration value using dot notation
	 *
	 * @param array  &$args
	 * @param string $key
	 * @param mixed  $value
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
	 * Check if configuration key exists
	 *
	 * @param string $key
	 * @return bool
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
	 * Check if feature is enabled
	 *
	 * @param string $feature
	 * @return bool
	 */
	public static function is_feature_enabled( $feature ) {
		return (bool) self::get( "features.{$feature}", false );
	}
}
