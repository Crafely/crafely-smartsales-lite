<?php

/**
 * Abstract base class for API handlers
 * 
 * @package AI Smart Sales
 */

namespace CSMSL\Includes\Api;

use WP_REST_Response;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

abstract class BaseApiHandler
{

    /**
     * API namespace
     * 
     * @var string
     */
    protected $namespace = 'ai-smart-sales/v1';

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
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
     * @param \WP_REST_Request $request
     * @return bool|\WP_Error
     */
    public function check_permission($request)
    {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_forbidden',
                __('You must be logged in to access this endpoint.', 'crafely-smartsales-lite'),
                ['status' => 401]
            );
        }

        // Get current user
        $user = wp_get_current_user();

        // Check if user has any of our POS roles or is an administrator
        $allowed_roles = ['administrator', 'csmsl_pos_cashier', 'csmsl_pos_outlet_manager', 'csmsl_pos_shop_manager'];
        $user_roles = (array) $user->roles;

        if (empty(array_intersect($allowed_roles, $user_roles))) {
            return new WP_Error(
                'rest_forbidden',
                __('You do not have permission to access this endpoint.', 'crafely-smartsales-lite'),
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * Validate request parameters
     * 
     * @param array $params
     * @param array $rules
     * @return true|\WP_Error
     */
    protected function validate_params($params, $rules)
    {
        foreach ($rules as $field => $rule) {
            $required = isset($rule['required']) ? $rule['required'] : false;
            $type = isset($rule['type']) ? $rule['type'] : 'string';

            // Check required fields
            if ($required && !isset($params[$field])) {
                return new WP_Error(
                    'missing_parameter',
                    // translators: %s is the name of the missing required parameter.
                    sprintf(__('Missing required parameter: %s', 'crafely-smartsales-lite'), $field),
                    ['status' => 400]
                );
            }

            // Validate type if parameter exists
            if (isset($params[$field])) {
                $valid = $this->validate_field_type($params[$field], $type);
                if (!$valid) {
                    return new WP_Error(
                        'invalid_parameter',
                        // translators: %1$s is the parameter name; %2$s is the expected data type.
                        sprintf(__('Invalid parameter type for %1$s. Expected %2$s.', 'crafely-smartsales-lite'), $field, $type),
                        ['status' => 400]
                    );
                }
            }
        }

        return true;
    }

    /**
     * Validate field type
     * 
     * @param mixed $value
     * @param string $type
     * @return bool
     */
    private function validate_field_type($value, $type)
    {
        switch ($type) {
            case 'integer':
                return is_numeric($value);
            case 'string':
                return is_string($value);
            case 'boolean':
                return is_bool($value) || in_array($value, ['true', 'false', '1', '0']);
            case 'array':
                return is_array($value);
            case 'email':
                return is_email($value);
            default:
                return true;
        }
    }

    /**
     * Create success response
     * 
     * @param mixed $data
     * @param string $message
     * @param int $status
     * @return WP_REST_Response
     */
    protected function success_response($data = null, $message = '', $status = 200)
    {
        $response = [
            'success' => true,
            'data' => $data
        ];

        if (!empty($message)) {
            $response['message'] = $message;
        }

        return new WP_REST_Response($response, $status);
    }

    /**
     * Create error response
     * 
     * @param string $message
     * @param int $status
     * @param string $code
     * @return WP_Error
     */
    protected function error_response($message, $status = 400, $code = 'api_error')
    {
        return new WP_Error($code, $message, ['status' => $status]);
    }

    /**
     * Sanitize request data
     * 
     * @param array $data
     * @param array $fields
     * @return array
     */
    protected function sanitize_request_data($data, $fields)
    {
        $sanitized = [];

        foreach ($fields as $field => $type) {
            if (isset($data[$field])) {
                $sanitized[$field] = csmsl_sanitize_data($data[$field], $type);
            }
        }

        return $sanitized;
    }

    /**
     * Log API activity
     * 
     * @param string $action
     * @param array $data
     */
    protected function log_activity($action, $data = [])
    {
        $user = wp_get_current_user();
        $log_data = [
            'user_id' => $user->ID,
            'action' => $action,
            'data' => $data,
            'timestamp' => current_time('mysql')
        ];

        csmsl_log($log_data, 'activity');
    }
}