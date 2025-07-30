<?php

namespace AISMARTSALES\Includes\Api\Outlets;

if (!defined('ABSPATH')) {
    exit;
}
class OutletsApiHandler
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        register_rest_route('ai-smart-sales/v1', '/outlets', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_outlets'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/outlets/(?P<outlet_id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_outlet'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/outlets', [
            'methods'             => 'POST',
            'callback'            => [$this, 'create_outlet'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/outlets/(?P<outlet_id>\d+)', [
            'methods'             => 'PUT',
            'callback'            => [$this, 'update_outlet'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/outlets/(?P<outlet_id>\d+)', [
            'methods'             => 'DELETE',
            'callback'            => [$this, 'delete_outlet'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/outlets/(?P<outlet_id>\d+)/assign-outlet', [
            'methods'             => 'POST',
            'callback'            => [$this, 'assign_outlet_to_user'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        // Add new route for assigning user to outlet
        register_rest_route('ai-smart-sales/v1', '/outlets/(?P<outlet_id>\d+)/assign-user', [
            'methods'             => 'POST',
            'callback'            => [$this, 'assign_outlet_to_user'],
            'permission_callback' => [$this, 'check_permission'],
        ]);
    }

    public function check_permission($request)
    {
        // Check if user is logged in and has appropriate capabilities
        if (!is_user_logged_in()) {
            return false;
        }

        // Get current user
        $user = wp_get_current_user();

        // Check if user has any of our POS roles or is an administrator
        $allowed_roles = ['administrator', 'aipos_outlet_manager', 'aipos_cashier', 'aipos_shop_manager'];
        $user_roles = (array) $user->roles;

        if (!array_intersect($allowed_roles, $user_roles)) {
            return false;
        }

        return true;
    }

    private function format_outlet_response($outlet)
    {
        // Get all counters for this outlet
        $counters = get_posts([
            'post_type' => 'smartsales_counter',
            'posts_per_page' => -1,
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
            'meta_query' => [
                [
                    'key' => 'counter_outlet_id',
                    'value' => $outlet->ID,
                    'compare' => '='
                ]
            ]
        ]);

        // Format counter data
        $formatted_counters = array_map(function ($counter) {
            $current_user_id = get_post_meta($counter->ID, 'current_assigned_user', true);
            return [
                'id' => $counter->ID,
                'name' => $counter->post_title,
                'status' => get_post_meta($counter->ID, 'counter_status', true) ?: 'active',
                'description' => get_post_meta($counter->ID, 'counter_description', true),
                'position' => get_post_meta($counter->ID, 'counter_position', true),
                'assigned_user' => $current_user_id ? [
                    'id' => $current_user_id,
                    'name' => get_user_by('id', $current_user_id)->display_name
                ] : null,
            ];
        }, $counters);


        return [
            'id'              => $outlet->ID,
            'name'            => $outlet->post_title,
            'slug'            => $outlet->post_name,
            'address'         => get_post_meta($outlet->ID, 'outlet_address', true),
            'phone'          => get_post_meta($outlet->ID, 'outlet_phone', true),
            'email'          => get_post_meta($outlet->ID, 'outlet_email', true),
            'operating_hours' => get_post_meta($outlet->ID, 'outlet_operating_hours', true),
            'manager_name'   => get_post_meta($outlet->ID, 'outlet_manager_name', true),
            'status'         => get_post_meta($outlet->ID, 'outlet_status', true) ?: 'active',
            'created_at'     => $outlet->post_date,
            'updated_at'     => $outlet->post_modified,
            'counters'       => $formatted_counters
        ];
    }

    private function format_error_response($message, $errors = [], $statusCode = 400, $path = '')
    {
        $error = [];

        // If $errors is an associative array, use it as-is
        if (is_array($errors) && !empty($errors) && array_keys($errors) !== range(0, count($errors) - 1)) {
            $error = $errors; // Use the associative array directly
        } else {
            // Otherwise, use a generic error structure
            $error = [
                'error' => $message, // Fallback for non-associative errors
            ];
        }

        return [
            'success' => false,
            'message' => $message,
            'data' => null,
            'error' => $error,
        ];
    }

    public function get_outlets($request)
    {
        $outlets = get_posts([
            'post_type'      => 'smartsales_outlet',
            'posts_per_page' => -1,
        ]);

        if (is_wp_error($outlets)) {
            return rest_ensure_response($this->format_error_response(
                'Failed to retrieve outlets.',
                [
                    'server' => 'An internal server error occurred while trying to retrieve outlets.',
                ],
                500,
                $request->get_route()
            ));
        }

        $formatted_outlets = array_map([$this, 'format_outlet_response'], $outlets);

        $response = [
            'success' => true,
            'message' => 'Outlets retrieved successfully.',
            'data'    => $formatted_outlets,
        ];

        return rest_ensure_response($response);
    }

    public function get_outlet($request)
    {
        $outlet = get_post($request['outlet_id']);

        if (!$outlet) {
            return rest_ensure_response($this->format_error_response(
                'The requested resource was not found.',
                [
                    'id' => "The outlet with the ID '{$request['outlet_id']}' does not exist in our records.",
                ],
                404,
                $request->get_route()
            ));
        }

        $response = [
            'success' => true,
            'message' => 'Outlet retrieved successfully.',
            'data'    => $this->format_outlet_response($outlet),
        ];

        return rest_ensure_response($response);
    }

    public function create_outlet($request)
    {
        $data = $request->get_json_params();

        // Check if user has permission
        if (!current_user_can('administrator')) {
            return rest_ensure_response($this->format_error_response(
                'Permission denied',
                ['permission' => 'Only administrators can create outlets'],
                403
            ));
        }

        $errors = [];

        // Validate outlet name
        if (!isset($data['name']) || trim($data['name']) === '') {
            $errors['name'] = 'Outlet name cannot be empty or contain only whitespace.';
        }

        // Validate email
        if (isset($data['email']) && !empty($data['email']) && !is_email($data['email'])) {
            $errors['email'] = 'Please provide a valid email address.';
        }

        // Validate phone
        if (isset($data['phone']) && !empty($data['phone']) && !preg_match('/^[0-9+\-\s()]*$/', $data['phone'])) {
            $errors['phone'] = 'Please provide a valid phone number.';
        }

        // If there are validation errors, return them
        if (!empty($errors)) {
            return rest_ensure_response($this->format_error_response(
                'Invalid input provided.',
                $errors,
                400,
                $request->get_route()
            ));
        }

        // Create the outlet
        $outlet_id = wp_insert_post([
            'post_type'   => 'smartsales_outlet',
            'post_title'  => $data['name'],
            'post_status' => 'publish',
        ]);

        if (is_wp_error($outlet_id)) {
            return rest_ensure_response($this->format_error_response(
                'Failed to create outlet.',
                ['server' => 'An internal server error occurred while trying to create the outlet.'],
                500,
                $request->get_route()
            ));
        }

        // Create default counter
        $counter_id = wp_insert_post([
            'post_type'   => 'smartsales_counter',
            'post_title'  => 'Main Counter',
            'post_status' => 'publish',
        ]);

        update_post_meta($counter_id, 'counter_outlet_id', $outlet_id);
        update_post_meta($counter_id, 'counter_status', 'active');
        update_post_meta($counter_id, 'is_default', true);

        // Update outlet meta data
        $meta_fields = [
            'outlet_address'          => 'address',
            'outlet_phone'           => 'phone',
            'outlet_email'           => 'email',
            'outlet_operating_hours' => 'operating_hours',
            'outlet_manager_name'    => 'manager_name',
            'outlet_status'          => 'status'
        ];

        foreach ($meta_fields as $meta_key => $data_key) {
            if (isset($data[$data_key])) {
                update_post_meta($outlet_id, $meta_key, $data[$data_key]);
            }
        }

        $response = [
            'success' => true,
            'message' => 'Outlet created successfully.',
            'data'    => $this->format_outlet_response(get_post($outlet_id)),
        ];

        return rest_ensure_response($response);
    }

    public function update_outlet($request)
    {
        $data = $request->get_json_params();
        $outlet_id = $request['outlet_id'];

        $errors = [];

        // Validate outlet name
        if (isset($data['name']) && trim($data['name']) === '') {
            $errors['name'] = 'Outlet name cannot be empty or contain only whitespace.';
        }

        // Validate email
        if (isset($data['email']) && !empty($data['email']) && !is_email($data['email'])) {
            $errors['email'] = 'Please provide a valid email address.';
        }

        // Validate phone
        if (isset($data['phone']) && !empty($data['phone']) && !preg_match('/^[0-9+\-\s()]*$/', $data['phone'])) {
            $errors['phone'] = 'Please provide a valid phone number.';
        }

        if (!empty($errors)) {
            return rest_ensure_response($this->format_error_response(
                'Invalid input provided.',
                $errors,
                400,
                $request->get_route()
            ));
        }

        // Update the post
        if (isset($data['name'])) {
            wp_update_post([
                'ID'         => $outlet_id,
                'post_title' => $data['name'],
            ]);
        }

        // Update outlet meta data
        $meta_fields = [
            'outlet_address'          => 'address',
            'outlet_phone'           => 'phone',
            'outlet_email'           => 'email',
            'outlet_operating_hours' => 'operating_hours',
            'outlet_manager_name'    => 'manager_name',
            'outlet_status'          => 'status'
        ];

        foreach ($meta_fields as $meta_key => $data_key) {
            if (isset($data[$data_key])) {
                update_post_meta($outlet_id, $meta_key, $data[$data_key]);
            }
        }

        $response = [
            'success' => true,
            'message' => 'Outlet updated successfully.',
            'data'    => $this->format_outlet_response(get_post($outlet_id)),
        ];

        return rest_ensure_response($response);
    }

    public function delete_outlet($request)
    {
        $outlet_id = $request['outlet_id'];

        // Check if this is the default outlet
        $is_default = get_post_meta($outlet_id, 'is_default', true);

        if ($is_default) {
            return rest_ensure_response($this->format_error_response(
                'Cannot delete default outlet',
                [
                    'outlet' => 'The default outlet cannot be deleted. This is a system requirement to ensure basic functionality.',
                ],
                403,
                $request->get_route()
            ));
        }

        $outlet_id = wp_delete_post($outlet_id, true);

        if (is_wp_error($outlet_id)) {
            return rest_ensure_response($this->format_error_response(
                'Failed to delete outlet.',
                [
                    'server' => 'An internal server error occurred while trying to delete the outlet.',
                ],
                500,
                $request->get_route()
            ));
        }

        $response = [
            'success' => true,
            'message' => 'Outlet deleted successfully.',
            'data'    => null,
        ];

        return rest_ensure_response($response);
    }

    public function assign_outlet_to_user($request)
    {
        $outlet_id = $request['outlet_id'];
        $data = $request->get_json_params();
        $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;

        $errors = [];

        // Validate user ID
        if (empty($user_id)) {
            $errors['user_id'] = 'User ID is required.';
        } elseif (!get_user_by('id', $user_id)) {
            $errors['user_id'] = 'Invalid user ID.';
        }

        // Validate outlet
        $outlet = get_post($outlet_id);
        if (!$outlet) {
            $errors['outlet'] = 'Outlet not found.';
        }

        if (!empty($errors)) {
            return rest_ensure_response($this->format_error_response(
                'Invalid input provided.',
                $errors,
                400,
                $request->get_route()
            ));
        }

        // Update user's outlet assignment
        update_user_meta($user_id, 'assigned_outlet_id', $outlet_id);

        // Store the assignment history
        $assignment_history = [
            'user_id' => $user_id,
            'outlet_id' => $outlet_id,
            'assigned_at' => current_time('mysql'),
            'assigned_by' => get_current_user_id()
        ];

        add_post_meta($outlet_id, 'outlet_assignment_history', $assignment_history);

        return rest_ensure_response([
            'success' => true,
            'message' => 'User assigned to outlet successfully.',
            'data'    => [
                'user_id' => $user_id,
                'outlet_id' => $outlet_id,
                'assigned_at' => current_time('mysql')
            ]
        ]);
    }

    public function user_has_assigned_outlet($user_id)
    {
        $assigned_outlet_id = get_user_meta($user_id, 'assigned_outlet_id', true);
        return !empty($assigned_outlet_id);
    }

    private function can_manage_outlet($user_id, $outlet_id)
    {
        if (user_can($user_id, 'administrator')) return true;

        $user = get_user_by('id', $user_id);
        if (!$user) return false;

        if (user_can($user, 'aipos_outlet_manager')) {
            $assigned_outlet = get_user_meta($user_id, 'assigned_outlet_id', true);
            return $assigned_outlet == $outlet_id;
        }

        return false;
    }
}