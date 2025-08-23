<?php
/**
 * Exception classes for AI Smart Sales
 *
 * @package AI Smart Sales
 */

namespace CSMSL\Includes\Exceptions;

use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base plugin exception
 */
class PluginException extends Exception {

	/**
	 * Error code
	 *
	 * @var string
	 */
	protected $error_code;

	/**
	 * Additional data
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Constructor
	 *
	 * @param string    $message
	 * @param string    $error_code
	 * @param array     $data
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct( $message = '', $error_code = '', $data = array(), $code = 0, Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );

		$this->error_code = $error_code;
		$this->data       = $data;

		// Log the error
		$this->log_error();
	}

	/**
	 * Get error code
	 *
	 * @return string
	 */
	public function get_error_code() {
		return $this->error_code;
	}

	/**
	 * Get additional data
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Log the error
	 */
	private function log_error() {
		$error_data = array(
			'message'    => $this->getMessage(),
			'error_code' => $this->error_code,
			'file'       => $this->getFile(),
			'line'       => $this->getLine(),
			'trace'      => $this->getTraceAsString(),
			'data'       => $this->data,
		);

		csmsl_log( $error_data, 'error' );
	}
}

/**
 * API Exception
 */
class ApiException extends PluginException {

	/**
	 * HTTP status code
	 *
	 * @var int
	 */
	protected $status_code;

	/**
	 * Constructor
	 *
	 * @param string $message
	 * @param int    $status_code
	 * @param string $error_code
	 * @param array  $data
	 */
	public function __construct( $message = '', $status_code = 400, $error_code = 'api_error', $data = array() ) {
		$this->status_code = $status_code;
		parent::__construct( $message, $error_code, $data, $status_code );
	}

	/**
	 * Get HTTP status code
	 *
	 * @return int
	 */
	public function get_status_code() {
		return $this->status_code;
	}
}

/**
 * Validation Exception
 */
class ValidationException extends PluginException {

	/**
	 * Validation errors
	 *
	 * @var array
	 */
	protected $validation_errors;

	/**
	 * Constructor
	 *
	 * @param array  $validation_errors
	 * @param string $message
	 */
	public function __construct( $validation_errors = array(), $message = 'Validation failed' ) {
		$this->validation_errors = $validation_errors;
		parent::__construct( $message, 'validation_error', array( 'validation_errors' => $validation_errors ) );
	}

	/**
	 * Get validation errors
	 *
	 * @return array
	 */
	public function get_validation_errors() {
		return $this->validation_errors;
	}
}

/**
 * Database Exception
 */
class DatabaseException extends PluginException {

	/**
	 * Constructor
	 *
	 * @param string $message
	 * @param array  $data
	 */
	public function __construct( $message = 'Database error occurred', $data = array() ) {
		parent::__construct( $message, 'database_error', $data );
	}
}

/**
 * Authentication Exception
 */
class AuthenticationException extends PluginException {

	/**
	 * Constructor
	 *
	 * @param string $message
	 * @param array  $data
	 */
	public function __construct( $message = 'Authentication failed', $data = array() ) {
		parent::__construct( $message, 'auth_error', $data, 401 );
	}
}

/**
 * Authorization Exception
 */
class AuthorizationException extends PluginException {

	/**
	 * Constructor
	 *
	 * @param string $message
	 * @param array  $data
	 */
	public function __construct( $message = 'Access denied', $data = array() ) {
		parent::__construct( $message, 'access_denied', $data, 403 );
	}
}

/**
 * Configuration Exception
 */
class ConfigurationException extends PluginException {

	/**
	 * Constructor
	 *
	 * @param string $message
	 * @param array  $data
	 */
	public function __construct( $message = 'Configuration error', $data = array() ) {
		parent::__construct( $message, 'config_error', $data );
	}
}
