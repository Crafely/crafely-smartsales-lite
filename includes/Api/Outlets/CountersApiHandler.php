<?php

namespace CSMSL\Includes\Api\Outlets;

if (!defined('ABSPATH')) {
    exit;
}
class CountersApiHandler
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        register_rest_route('ai-smart-sales/v1', '/outlets/(?P<outlet_id>\d+)/counters', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_counters'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/outlets/(?P<outlet_id>\d+)/counters/(?P<counter_id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_counter'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/outlets/(?P<outlet_id>\d+)/counters', [
            'methods'             => 'POST',
            'callback'            => [$this, 'create_counter'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/outlets/(?P<outlet_id>\d+)/counters/(?P<counter_id>\d+)', [
            'methods'             => 'PUT',
            'callback'            => [$this, 'update_counter'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/outlets/(?P<outlet_id>\d+)/counters/(?P<counter_id>\d+)', [
            'methods'             => 'DELETE',
            'callback'            => [$this, 'delete_counter'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        // Add new route for assigning user to counter
        register_rest_route('ai-smart-sales/v1', '/outlets/(?P<outlet_id>\d+)/counters/(?P<counter_id>\d+)/assign-user', [
            'methods'             => 'POST',
            'callback'            => [$this, 'assign_user_to_counter'],
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
        $allowed_roles = ['administrator', 'csmsl_pos_outlet_manager', 'csmsl_pos_cashier', 'csmsl_pos_shop_manager'];
        $user_roles = (array) $user->roles;

        if (!array_intersect($allowed_roles, $user_roles)) {
            return false;
        }

        return true;
    }

    private function format_counter_response($counter)
    {
        $current_user_id = get_post_meta($counter->ID, 'current_assigned_user', true);
        $response = [
            'id'           => $counter->ID,
            'name'         => $counter->post_title,
            'outlet_id'    => get_post_meta($counter->ID, 'counter_outlet_id', true),
            'status'       => get_post_meta($counter->ID, 'counter_status', true) ?: 'active',
            'description'  => get_post_meta($counter->ID, 'counter_description', true),
            'position'     => get_post_meta($counter->ID, 'counter_position', true),
            'created_at'   => $counter->post_date,
            'updated_at'   => $counter->post_modified,
            'assigned_user' => $current_user_id ? [
                'id' => $current_user_id,
                'name' => get_user_by('id', $current_user_id)->display_name
            ] : null,
        ];
        return $response;
    }

    private function format_error_response($message, $errors = [], $statusCode = 400, $path = '')
    {
        $error = [];
        if (is_array($errors) && !empty($errors) && array_keys($errors) !== range(0, count($errors) - 1)) {
            $error = $errors;
        } else {
            $error = [
                'error' => $message,
            ];
        }

        return [
            'success' => false,
            'message' => $message,
            'data'    => null,
            'error'   => $error,
        ];
    }

    private function is_duplicate_counter($name, $outlet_id, $exclude_id = 0)
    {
        // phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
        $args = [
            'post_type'      => 'csmsl_counter',
            'post_status'    => 'publish',
            'title'          => $name,
            'posts_per_page' => 1,
            // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
            'exclude'        => [$exclude_id],
            'meta_query'     => [
                [
                    'key'     => 'counter_outlet_id',
                    'value'   => $outlet_id,
                    'compare' => '='
                ]
            ]
        ];

        $existing_counter = new \WP_Query($args);
        // phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
        return $existing_counter->have_posts();
    }

    public function get_counters($request)
    {
        $outlet_id = $request['outlet_id'];

        $args = [
            'post_type'      => 'csmsl_counter',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'     => 'counter_outlet_id',
                    'value'   => $outlet_id,
                    'compare' => '='
                ]
            ]
        ];

        $counters = get_posts($args);

        if (is_wp_error($counters)) {
            return rest_ensure_response($this->format_error_response(
                'Failed to retrieve counters.',
                ['server' => 'An internal server error occurred.'],
                500,
                $request->get_route()
            ));
        }

        $formatted_counters = array_map([$this, 'format_counter_response'], $counters);

        return rest_ensure_response([
            'success' => true,
            'message' => 'Counters retrieved successfully.',
            'data'    => $formatted_counters,
        ]);
    }

    public function get_counter($request)
    {
        $counter = get_post($request['counter_id']);

        if (!$counter || get_post_meta($counter->ID, 'counter_outlet_id', true) != $request['outlet_id']) {
            return rest_ensure_response($this->format_error_response(
                'Counter not found.',
                ['id' => "Counter not found for the specified outlet."],
                404,
                $request->get_route()
            ));
        }

        return rest_ensure_response([
            'success' => true,
            'message' => 'Counter retrieved successfully.',
            'data'    => $this->format_counter_response($counter),
        ]);
    }

    public function create_counter($request)
    {
        $data = $request->get_json_params();
        $outlet_id = $request['outlet_id'];

        $errors = [];

        if (!isset($data['name']) || trim($data['name']) === '') {
            $errors['name'] = 'Counter name cannot be empty.';
        }

        if ($this->is_duplicate_counter($data['name'], $outlet_id)) {
            $errors['name'] = 'A counter with this name already exists in this outlet.';
        }

        if (!empty($errors)) {
            return rest_ensure_response($this->format_error_response(
                'Invalid input provided.',
                $errors,
                400,
                $request->get_route()
            ));
        }

        $counter_id = wp_insert_post([
            'post_type'   => 'csmsl_counter',
            'post_title'  => $data['name'],
            'post_status' => 'publish',
        ]);

        if (is_wp_error($counter_id)) {
            return rest_ensure_response($this->format_error_response(
                'Failed to create counter.',
                ['server' => 'An internal server error occurred.'],
                500,
                $request->get_route()
            ));
        }

        update_post_meta($counter_id, 'counter_outlet_id', $outlet_id);
        update_post_meta($counter_id, 'counter_status', $data['status'] ?? 'active');
        update_post_meta($counter_id, 'counter_description', $data['description'] ?? '');
        update_post_meta($counter_id, 'counter_position', $data['position'] ?? '');

        return rest_ensure_response([
            'success' => true,
            'message' => 'Counter created successfully.',
            'data'    => $this->format_counter_response(get_post($counter_id)),
        ]);
    }

    public function update_counter($request)
    {
        $data = $request->get_json_params();
        $counter_id = $request['counter_id'];
        $outlet_id = $request['outlet_id'];

        $counter = get_post($counter_id);
        if (!$counter || get_post_meta($counter->ID, 'counter_outlet_id', true) != $outlet_id) {
            return rest_ensure_response($this->format_error_response(
                'Counter not found.',
                ['id' => "Counter not found for the specified outlet."],
                404,
                $request->get_route()
            ));
        }

        $errors = [];
        if (isset($data['name']) && empty($data['name'])) {
            $errors['name'] = 'Counter name cannot be empty.';
        }

        if (isset($data['name']) && $this->is_duplicate_counter($data['name'], $outlet_id, $counter_id)) {
            $errors['name'] = 'A counter with this name already exists in this outlet.';
        }

        if (!empty($errors)) {
            return rest_ensure_response($this->format_error_response(
                'Invalid input provided.',
                $errors,
                400,
                $request->get_route()
            ));
        }

        if (isset($data['name'])) {
            wp_update_post([
                'ID'         => $counter_id,
                'post_title' => $data['name'],
            ]);
        }

        if (isset($data['status'])) {
            update_post_meta($counter_id, 'counter_status', $data['status']);
        }
        if (isset($data['description'])) {
            update_post_meta($counter_id, 'counter_description', $data['description']);
        }
        if (isset($data['position'])) {
            update_post_meta($counter_id, 'counter_position', $data['position']);
        }

        return rest_ensure_response([
            'success' => true,
            'message' => 'Counter updated successfully.',
            'data'    => $this->format_counter_response(get_post($counter_id)),
        ]);
    }

    public function delete_counter($request)
    {
        $counter_id = $request['counter_id'];
        $outlet_id = $request['outlet_id'];

        $counter = get_post($counter_id);
        if (!$counter || get_post_meta($counter->ID, 'counter_outlet_id', true) != $outlet_id) {
            return rest_ensure_response($this->format_error_response(
                'Counter not found.',
                ['id' => "Counter not found for the specified outlet."],
                404,
                $request->get_route()
            ));
        }

        $deleted = wp_delete_post($counter_id, true);
        if (!$deleted) {
            return rest_ensure_response($this->format_error_response(
                'Failed to delete counter.',
                ['server' => 'An internal server error occurred.'],
                500,
                $request->get_route()
            ));
        }

        return rest_ensure_response([
            'success' => true,
            'message' => 'Counter deleted successfully.',
            'data'    => null,
        ]);
    }

    private function can_manage_counter($user_id, $counter_id)
    {
        if (user_can($user_id, 'administrator')) return true;

        $user = get_user_by('id', $user_id);
        if (!$user) return false;

        if (user_can($user, 'csmsl_pos_outlet_manager')) {
            $counter_outlet = get_post_meta($counter_id, 'counter_outlet_id', true);
            $user_outlet = get_user_meta($user_id, 'assigned_outlet_id', true);
            return $counter_outlet == $user_outlet;
        }

        return false;
    }

    public function assign_user_to_counter($request)
    {
        $current_user_id = get_current_user_id();
        if (!$this->can_manage_counter($current_user_id, $request['counter_id'])) {
            return rest_ensure_response($this->format_error_response(
                'Permission denied',
                ['permission' => 'You do not have permission to manage this counter'],
                403
            ));
        }

        $counter_id = $request['counter_id'];
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

        // Check if user exists and has cashier role
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return rest_ensure_response($this->format_error_response(
                'Invalid user',
                ['user' => 'User not found'],
                400
            ));
        }

        // Get user roles
        $user_roles = $user->roles;

        // Check if user has cashier role (using the correct role slug)
        if (!in_array('csmsl_pos_cashier', $user_roles)) {
            return rest_ensure_response($this->format_error_response(
                'Invalid user',
                ['user' => 'User must have cashier role'],
                400
            ));
        }

        // Validate counter belongs to outlet
        $counter = get_post($counter_id);
        if (!$counter || get_post_meta($counter->ID, 'counter_outlet_id', true) != $outlet_id) {
            $errors['counter'] = 'Counter not found in the specified outlet.';
        }

        // Check if user is assigned to the outlet
        $user_outlet_id = get_user_meta($user_id, 'assigned_outlet_id', true);
        if (!$user_outlet_id) {
            // Auto-assign user to the outlet if not assigned
            update_user_meta($user_id, 'assigned_outlet_id', $outlet_id);
        } else if ($user_outlet_id != $outlet_id) {
            $errors['user'] = 'User is already assigned to a different outlet.';
        }

        // Check if user is already assigned to another counter
        $existing_counter = get_user_meta($user_id, 'assigned_counter_id', true);
        if ($existing_counter && $existing_counter != $counter_id) {
            $errors['assignment'] = 'User is already assigned to another counter.';
        }

        if (!empty($errors)) {
            return rest_ensure_response($this->format_error_response(
                'Invalid input provided.',
                $errors,
                400,
                $request->get_route()
            ));
        }

        // Update user's counter assignment
        update_user_meta($user_id, 'assigned_counter_id', $counter_id);

        // Store the assignment history
        $assignment_history = [
            'user_id' => $user_id,
            'counter_id' => $counter_id,
            'outlet_id' => $outlet_id,
            'assigned_at' => current_time('mysql'),
            'assigned_by' => get_current_user_id()
        ];

        add_post_meta($counter_id, 'counter_assignment_history', $assignment_history);

        // Update counter's current user
        update_post_meta($counter_id, 'current_assigned_user', $user_id);

        return rest_ensure_response([
            'success' => true,
            'message' => 'User assigned to counter successfully.',
            'data'    => [
                'user_id' => $user_id,
                'counter_id' => $counter_id,
                'outlet_id' => $outlet_id,
                'assigned_at' => current_time('mysql')
            ]
        ]);
    }

    /**
     * Check if a user has an assigned counter
     * 
     * @param int $user_id The user ID to check
     * @return bool Whether the user has an assigned counter
     */
    public function user_has_assigned_counter($user_id)
    {
        $assigned_counter_id = get_user_meta($user_id, 'assigned_counter_id', true);
        if (!$assigned_counter_id) {
            return false;
        }

        // Verify the counter still exists and is active
        $counter = get_post($assigned_counter_id);
        if (!$counter || $counter->post_type !== 'csmsl_counter') {
            return false;
        }

        // Check if counter is active
        $counter_status = get_post_meta($assigned_counter_id, 'counter_status', true);
        if ($counter_status !== 'active') {
            return false;
        }

        // Verify user is still assigned as the current user of this counter
        $current_assigned_user = get_post_meta($assigned_counter_id, 'current_assigned_user', true);
        if ((int)$current_assigned_user !== (int)$user_id) {
            return false;
        }

        return true;
    }
}