<?php
/**
 * Global helper functions for Crafely SmartSales Lite
 *
 * @package CrafelySmartSalesLite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'csmsl_is_woocommerce_active' ) ) {
	/**
	 * Check if WooCommerce is active.
	 * This function checks if WooCommerce is active by looking for its main plugin file in the list of active plugins.
	 * * @return bool True if WooCommerce is active, false otherwise.
	 */
	function csmsl_is_woocommerce_active() {
		return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true );
	}
}

if ( ! function_exists( 'csmsl_is_active' ) ) {
	/**
	 * Check if Crafely SmartSales Lite is active.
	 *
	 * @return bool
	 */
	function csmsl_is_active() {
		return is_plugin_active( plugin_basename( CSMSL_DIR . 'crafely-smartsales-lite.php' ) );
	}
}


if ( ! function_exists( 'csmsl' ) ) {
	/**
	 * Get the Crafely SmartSales Lite plugin instance.
	 *
	 * @return CSMSL\Includes\Core\Plugin
	 */
	function csmsl() {
		return CSMSL\Includes\Core\Plugin::instance();
	}
}

if ( ! function_exists( 'csmsl_get_version' ) ) {
	/**
	 * Get the Crafely SmartSales Lite plugin version.
	 *
	 * @return string
	 */
	function csmsl_get_version() {
		return defined( 'CSMSL_VERSION' ) ? CSMSL_VERSION : '1.0.0';
	}
}

if ( ! function_exists( 'csmsl_get_plugin_path' ) ) {
	/**
	 * Get the Crafely SmartSales Lite plugin directory path.
	 *
	 * @param string $path Optional path to append.
	 * @return string
	 */
	function csmsl_get_plugin_path( $path = '' ) {
		return CSMSL_DIR . ltrim( $path, '/' );
	}
}


if ( ! function_exists( 'csmsl_get_plugin_url' ) ) {
	/**
	 * Get the Crafely SmartSales Lite plugin directory URL.
	 *
	 * @param string $path Optional path to append.
	 * @return string
	 */
	function csmsl_get_plugin_url( $path = '' ) {
		return CSMSL_URL . ltrim( $path, '/' );
	}
}


if ( ! function_exists( 'csmsl_log' ) ) {
	/**
	 * Log messages for debugging.
	 * This function logs messages to a debug file if CSMSL_DEV_MODE is enabled.
	 *
	 * @param mixed  $message The message to log.
	 * @param string $level The log level (e.g., 'info', 'error').
	 */
	function csmsl_log( $message, $level = 'info' ) {
		if ( ! defined( 'CSMSL_DEV_MODE' ) || ! CSMSL_DEV_MODE ) {
			return;
		}
	}
}

if ( ! function_exists( 'csmsl_user_has_pos_access' ) ) {
	/**
	 * Check if the current user has access to the POS system.
	 * This function checks if the user is logged in and has one of the required POS roles.
	 */
	function csmsl_user_has_pos_access() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$user      = wp_get_current_user();
		$pos_roles = array( 'csmsl_pos_cashier', 'csmsl_pos_outlet_manager', 'csmsl_pos_shop_manager' );

		return ! empty( array_intersect( $pos_roles, (array) $user->roles ) );
	}
}

if ( ! function_exists( 'csmsl_format_currency' ) ) {

	/**
	 * Format a currency amount.
	 * This function formats a given amount as a currency string using WooCommerce's wc_price function
	 * if WooCommerce is active, otherwise formats it as a simple dollar amount.
	 *
	 * @param float $amount The amount to format.
	 */
	function csmsl_format_currency( $amount ) {
		if ( function_exists( 'wc_price' ) ) {
			return wc_price( $amount );
		}

		return '$' . number_format( $amount, 2 );
	}
}

if ( ! function_exists( 'csmsl_sanitize_data' ) ) {
	/**
	 * Sanitize and validate data based on type.
	 * This function sanitizes input data based on the specified type.
	 * It supports various types such as 'email', 'url', 'int', 'float', 'html', and 'text'.
	 *
	 * @param mixed  $data The data to sanitize.
	 * @param string $type The type of data to sanitize (default is 'text').
	 */
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

if ( ! function_exists( 'csmsl_ajax_response' ) ) {
	/**
	 * Handle AJAX responses consistently.
	 * This function formats and sends a JSON response for AJAX requests.
	 *
	 * @param mixed  $data The data to include in the response.
	 * @param string $message A message to include in the response.
	 * @param bool   $success Whether the request was successful or not.
	 */
	function csmsl_ajax_response( $data = null, $message = '', $success = true ) {
		$response = array(
			'success' => $success,
			'message' => $message,
			'data'    => $data,
		);

		wp_send_json( $response );
	}
}


if ( ! function_exists( 'csmsl_get_template' ) ) {
	/**
	 * Get template part.
	 * This function loads a template file from the plugin's templates directory or the theme's directory.
	 *
	 * @param string $template_name The name of the template file to load.
	 * @param array  $args Optional. Arguments to pass to the template.
	 * @param string $template_path Optional. Path to the template directory.
	 */
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


if ( ! function_exists( 'csmsl_locate_template' ) ) {
	/**
	 * Locate template file.
	 * This function searches for a template file in the theme directory first, then in the plugin's templates directory.
	 *
	 * @param string $template_name The name of the template file to locate.
	 * @param string $template_path Optional. Path to the template directory.
	 * @return string The path to the located template file.
	 */
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
