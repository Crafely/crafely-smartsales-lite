<?php
/**
 * Global helper functions for Crafely SmartSales Lite
 *
 * @package Crafely SmartSales Lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if WooCommerce is active
 *
 * @return bool
 */
if ( ! function_exists( 'csmsl_is_woocommerce_active' ) ) {

	function csmsl_is_woocommerce_active() {
		return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true );
	}
}

/**
 * Check if Crafely SmartSales Lite is active.
 *
 * @return bool
 */
if ( ! function_exists( 'csmsl_is_active' ) ) {

	function csmsl_is_active() {
		return is_plugin_active( plugin_basename( CSMSL_DIR . 'crafely-smartsales-lite.php' ) );
	}
}

/**
 * Get plugin instance
 *
 * @return CSMSL\Includes\Core\Plugin
 */
if ( ! function_exists( 'csmsl' ) ) {

	function csmsl() {
		return CSMSL\Includes\Core\Plugin::instance();
	}
}

/**
 * Get plugin version
 *
 * @return string
 */
if ( ! function_exists( 'csmsl_get_version' ) ) {

	function csmsl_get_version() {
		return defined( 'CSMSL_VERSION' ) ? CSMSL_VERSION : '1.0.0';
	}
}

/**
 * Get plugin directory path
 *
 * @param string $path Optional path to append
 * @return string
 */
if ( ! function_exists( 'csmsl_get_plugin_path' ) ) {

	function csmsl_get_plugin_path( $path = '' ) {
		return CSMSL_DIR . ltrim( $path, '/' );
	}
}

/**
 * Get plugin directory URL
 *
 * @param string $path Optional path to append
 * @return string
 */
if ( ! function_exists( 'csmsl_get_plugin_url' ) ) {

	function csmsl_get_plugin_url( $path = '' ) {
		return CSMSL_URL . ltrim( $path, '/' );
	}
}

/**
 * Log messages for debugging
 *
 * @param mixed $message
 * @param string $level
 */
if ( ! function_exists( 'csmsl_log' ) ) {

	function csmsl_log( $message, $level = 'info' ) {
		if ( ! defined( 'CSMSL_DEV_MODE' ) || ! CSMSL_DEV_MODE ) {
			return;
		}
	}
}

/**
 * Check if current user has POS access
 *
 * @return bool
 */
if ( ! function_exists( 'csmsl_user_has_pos_access' ) ) {

	function csmsl_user_has_pos_access() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$user      = wp_get_current_user();
		$pos_roles = array( 'csmsl_pos_cashier', 'csmsl_pos_outlet_manager', 'csmsl_pos_shop_manager' );

		return ! empty( array_intersect( $pos_roles, (array) $user->roles ) );
	}
}

/**
 * Get formatted currency amount
 *
 * @param float $amount
 * @return string
 */
if ( ! function_exists( 'csmsl_format_currency' ) ) {

	function csmsl_format_currency( $amount ) {
		if ( function_exists( 'wc_price' ) ) {
			return wc_price( $amount );
		}

		return '$' . number_format( $amount, 2 );
	}
}

/**
 * Sanitize and validate data
 *
 * @param mixed $data
 * @param string $type
 * @return mixed
 */
if ( ! function_exists( 'csmsl_sanitize_data' ) ) {

	function csmsl_sanitize_data( $data, $type = 'text' ) {
		switch ( $type ) {
			case 'email':
				return sanitize_email( $data );
			case 'url':
				return esc_url_raw( $data );
			case 'int':
				return absint( $data );
			case 'float':
				return floatval( $data );
			case 'html':
				return wp_kses_post( $data );
			case 'text':
			default:
				return sanitize_text_field( $data );
		}
	}
}

/**
 * Handle AJAX responses consistently
 *
 * @param mixed $data
 * @param string $message
 * @param bool $success
 */
if ( ! function_exists( 'csmsl_ajax_response' ) ) {

	function csmsl_ajax_response( $data = null, $message = '', $success = true ) {
		$response = array(
			'success' => $success,
			'message' => $message,
			'data'    => $data,
		);

		wp_send_json( $response );
	}
}

/**
 * Get template part
 *
 * @param string $template_name
 * @param array $args
 * @param string $template_path
 */
if ( ! function_exists( 'csmsl_get_template' ) ) {

	function csmsl_get_template( $template_name, $args = array(), $template_path = '' ) {
		$defaults = array();
		$args     = wp_parse_args( $args, $defaults );

		$located = csmsl_locate_template( $template_name, $template_path );

		if ( ! file_exists( $located ) ) {
			csmsl_log( "Template not found: {$template_name}", 'error' );
			return;
		}

		include $located;
	}
}

/**
 * Locate template file
 *
 * @param string $template_name
 * @param string $template_path
 * @return string
 */
if ( ! function_exists( 'csmsl_locate_template' ) ) {

	function csmsl_locate_template( $template_name, $template_path = '' ) {
		if ( ! $template_path ) {
			$template_path = 'crafely-smartsales-lite/';
		}

		// Look in theme first.
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			)
		);

		// Get default template.
		if ( ! $template ) {
			$template = csmsl_get_plugin_path( 'templates/' . $template_name );
		}

		return apply_filters( 'csmsl_locate_template', $template, $template_name, $template_path );
	}
}
