<?php
/**
 * Abstract base class for API handlers
 *
 * @package crafelySmartsalesLite
 */

namespace CSMSL\Includes\Api;

use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * BaseApiHandler
 * This class provides common functionality for API handlers in the Crafely SmartSales Lite plugin.
 */
abstract class BaseApiHandler {

	/**
	 * API namespace
	 *
	 * @var string
	 */
	protected $namespace = 'ai-smart-sales/v1';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register routes - must be implemented by child classes
	 *
	 * @return void
	 */
	abstract public function register_routes();

	/**
	 * Check permission for API access
	 *
	 * @param \WP_REST_Request $request object containing the request data.
	 * @return bool|\WP_Error
	 */
	public function check_permission( $request ) {
		// Check if user is logged in.
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You must be logged in to access this endpoint.', 'crafely-smartsales-lite' ),
				array( 'status' => 401 )
			);
		}

		// Get current user.
		$user = wp_get_current_user();

		// Check if user has any of our POS roles or is an administrator.
		$allowed_roles = array( 'administrator', 'csmsl_pos_cashier', 'csmsl_pos_outlet_manager', 'csmsl_pos_shop_manager' );
		$user_roles    = (array) $user->roles;

		if ( empty( array_intersect( $allowed_roles, $user_roles ) ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to access this endpoint.', 'crafely-smartsales-lite' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Validate request parameters
	 *
	 * @param array $params the request parameters.
	 * @param array $rules the validation rules.
	 * @return true|\WP_Error
	 */
	protected function validate_params( $params, $rules ) {
		foreach ( $rules as $field => $rule ) {
			$required = isset( $rule['required'] ) ? $rule['required'] : false;
			$type     = isset( $rule['type'] ) ? $rule['type'] : 'string';

			// Check required fields.
			if ( $required && ! isset( $params[ $field ] ) ) {
				return new WP_Error(
					'missing_parameter',
					// translators: %s is the name of the missing required parameter.
					sprintf( __( 'Missing required parameter: %s', 'crafely-smartsales-lite' ), $field ),
					array( 'status' => 400 )
				);
			}

			// Validate type if parameter exists.
			if ( isset( $params[ $field ] ) ) {
				$valid = $this->validate_field_type( $params[ $field ], $type );
				if ( ! $valid ) {
					return new WP_Error(
						'invalid_parameter',
						// translators: %1$s is the parameter name; %2$s is the expected data type.
						sprintf( __( 'Invalid parameter type for %1$s. Expected %2$s.', 'crafely-smartsales-lite' ), $field, $type ),
						array( 'status' => 400 )
					);
				}
			}
		}

		return true;
	}

	/**
	 * Validate field type
	 *
	 * @param mixed  $value the value to validate.
	 * @param string $type the expected type.
	 * @return bool
	 */
	private function validate_field_type( $value, $type ) {
		switch ( $type ) {
			case 'integer':
				return is_numeric( $value );
			case 'string':
				return is_string( $value );
			case 'boolean':
				return is_bool( $value ) || in_array( $value, array( 'true', 'false', '1', '0' ), true );
			case 'array':
				return is_array( $value );
			case 'email':
				return is_email( $value );
			default:
				return true;
		}
	}

	/**
	 * Create success response
	 *
	 * @param mixed  $data the data to include in the response.
	 * @param string $message the message to include in the response.
	 * @param int    $status the HTTP status code (default is 200).
	 * @return WP_REST_Response
	 */
	protected function success_response( $data = null, $message = '', $status = 200 ) {
		$response = array(
			'success' => true,
			'data'    => $data,
		);

		if ( ! empty( $message ) ) {
			$response['message'] = $message;
		}

		return new WP_REST_Response( $response, $status );
	}

	/**
	 * Create error response
	 *
	 * @param string $message the error message to include in the response.
	 * @param int    $status the HTTP status code (default is 400).
	 * @param string $code the error code (default is 'api_error').
	 * @return WP_Error
	 */
	protected function error_response( $message, $status = 400, $code = 'api_error' ) {
		return new WP_Error( $code, $message, array( 'status' => $status ) );
	}

	/**
	 * Sanitize request data
	 *
	 * @param array $data the request data to sanitize.
	 * @param array $fields the fields to sanitize with their expected types.
	 * @return array
	 */
	protected function sanitize_request_data( $data, $fields ) {
		$sanitized = array();

		foreach ( $fields as $field => $type ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = csmsl_sanitize_data( $data[ $field ], $type );
			}
		}

		return $sanitized;
	}

	/**
	 * Log API activity
	 *
	 * @param string $action the action being logged.
	 * @param array  $data additional data to log.
	 */
	protected function log_activity( $action, $data = array() ) {
		$user     = wp_get_current_user();
		$log_data = array(
			'user_id'   => $user->ID,
			'action'    => $action,
			'data'      => $data,
			'timestamp' => current_time( 'mysql' ),
		);

		csmsl_log( $log_data, 'activity' );
	}
}
