<?php

namespace CSMSL\Includes\Api\Roles;

use WP_REST_Response;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}
class UsersApiHandler
{
    private const VALID_ROLES = ['csmsl_pos_outlet_manager', 'csmsl_pos_cashier', 'csmsl_pos_shop_manager'];
    private const API_NAMESPACE = 'ai-smart-sales/v1';

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        $routes = [
            '/users' => [
                ['GET', [$this, 'get_users']],
                ['POST', [$this, 'create_user']],
            ],
            '/users/(?P<id>\d+)' => [
                ['GET', [$this, 'get_user']],
                ['PUT', [$this, 'update_user']],
                ['DELETE', [$this, 'delete_user']],
            ],
            '/users/logout' => [
                ['POST', [$this, 'logout_user']],
            ],
            '/users/current' => [
                ['GET', [$this, 'get_current_user']],
            ],
            '/roles' => [
                ['GET', [$this, 'get_user_roles']],
            ],
        ];

        foreach ($routes as $route => $endpoints) {
            foreach ($endpoints as [$method, $callback]) {
                // Use separate permission callback for logout endpoint
                $permission_callback = ($route === '/users/logout')
                    ? [$this, 'check_logout_permission']
                    : [$this, 'check_permission'];

                register_rest_route(self::API_NAMESPACE, $route, [
                    'methods' => $method,
                    'callback' => $callback,
                    'permission_callback' => $permission_callback,
                ]);
            }
        }
    }

    public function check_permission($request)
    {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return false;
        }

        // Get current user
        $user = wp_get_current_user();

        // For write operations, require higher privileges
        if (in_array($request->get_method(), ['POST', 'PUT', 'DELETE'])) {
            return current_user_can('administrator') || current_user_can('manage_woocommerce');
        }

        // For read operations, allow authenticated users with POS roles
        $allowed_roles = ['administrator', 'csmsl_pos_outlet_manager', 'csmsl_pos_cashier', 'csmsl_pos_shop_manager'];
        $user_roles = (array) $user->roles;

        return !empty(array_intersect($allowed_roles, $user_roles));
    }

    /**
     * Separate permission check specifically for logout endpoint
     * Allows any authenticated user to logout
     */
    public function check_logout_permission($request)
    {
        // Only check if user is logged in - any authenticated user can logout
        return is_user_logged_in();
    }
    // Format success response
    private function format_success_response($message, $data = [], $statusCode = 200)
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    // Format error response
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

    // Format user response
    private function format_user_response($user)
    {
        // Get user account status from meta, fallback to user_status field
        $status = get_user_meta($user->ID, 'user_account_status', true);
        if (empty($status)) {
            $status = $user->user_status == 0 ? 'active' : 'inactive';
        }

        // Basic user information
        $response = [
            'id'              => $user->ID,
            'username'        => $user->user_login,
            'name'           => $user->user_login,
            'first_name'     => $user->first_name,
            'last_name'      => $user->last_name,
            'display_name'  => $user->display_name,
            'email'          => $user->user_email,
            'roles'          => $user->roles,
            'created_at'     => get_date_from_gmt($user->user_registered),
            'last_login'     => get_user_meta($user->ID, 'last_login', true),
            'status'         => $status,
            'avatar'         => get_avatar_url($user->ID),
            'permissions'    => $this->get_user_permissions($user),
        ];

        // Get outlet information with proper meta keys
        $outlet_id = get_user_meta($user->ID, 'assigned_outlet_id', true);
        if ($outlet_id) {
            $outlet = get_post($outlet_id);
            if ($outlet) {
                // Get counters for this outlet
                $counters = get_posts([
                    'post_type' => 'counter',
                    'posts_per_page' => -1,
                    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                    'meta_query' => [
                        [
                            'key' => 'counter_outlet_id',
                            'value' => $outlet_id,
                            'compare' => '='
                        ]
                    ]
                ]);

                // For cashiers, only include their assigned counter in the outlet's counter list
                if (in_array('csmsl_pos_cashier', $user->roles)) {
                    $assigned_counter_id = get_user_meta($user->ID, 'assigned_counter_id', true);
                    if ($assigned_counter_id) {
                        // Use strict string comparison since WordPress often stores IDs as strings
                        $counters = array_filter($counters, function ($counter) use ($assigned_counter_id) {
                            return (string)$counter->ID === (string)$assigned_counter_id;
                        });
                    }
                }

                // Format counters array
                $formatted_counters = array_map(function ($counter) {
                    $current_user_id = get_post_meta($counter->ID, 'current_assigned_user', true);
                    $assigned_user = $current_user_id ? get_user_by('id', $current_user_id) : null;

                    return [
                        'id' => $counter->ID,
                        'name' => $counter->post_title,
                        'status' => get_post_meta($counter->ID, 'counter_status', true) ?: 'active',
                        'description' => get_post_meta($counter->ID, 'counter_description', true),
                        'position' => get_post_meta($counter->ID, 'counter_position', true),
                        'assigned_user' => $assigned_user ? [
                            'id' => $assigned_user->ID,
                            'name' => $assigned_user->display_name,
                            'email' => $assigned_user->user_email,
                            'assigned_at' => get_post_meta($counter->ID, 'user_assigned_at', true)
                        ] : null,
                        'created_at' => $counter->post_date,
                        'updated_at' => $counter->post_modified,
                    ];
                }, $counters);

                $response['outlet'] = [
                    'id'           => $outlet_id,
                    'name'         => $outlet->post_title,
                    'address'      => get_post_meta($outlet_id, 'outlet_address', true),
                    'phone'        => get_post_meta($outlet_id, 'outlet_phone', true),
                    'email'        => get_post_meta($outlet_id, 'outlet_email', true),
                    'manager_name' => get_post_meta($outlet_id, 'outlet_manager_name', true),
                    'status'       => get_post_meta($outlet_id, 'outlet_status', true) ?: 'active',
                    'counters'     => $formatted_counters,
                    'created_at'   => $outlet->post_date,
                    'updated_at'   => $outlet->post_modified,
                ];
            }
        }

        // Add counter information for cashiers with proper meta keys
        if (in_array('csmsl_pos_cashier', $user->roles)) {
            $counter_id = get_user_meta($user->ID, 'assigned_counter_id', true);
            if ($counter_id) {
                $counter = get_post($counter_id);
                if ($counter) {
                    // Get counter assignment history
                    $assignment_history = get_post_meta($counter_id, 'counter_assignment_history', true) ?: [];

                    // Get counter sales stats
                    $counter_stats = $this->get_counter_stats($counter_id);

                    $response['counter'] = [
                        'id'          => $counter_id,
                        'name'        => $counter->post_title,
                        'status'      => get_post_meta($counter_id, 'counter_status', true) ?: 'active',
                        'description' => get_post_meta($counter_id, 'counter_description', true),
                        'position'    => get_post_meta($counter_id, 'counter_position', true),
                        'outlet_id'   => get_post_meta($counter_id, 'counter_outlet_id', true),
                        'stats'       => $counter_stats,
                        'history'     => $assignment_history,
                        'created_at'  => $counter->post_date,
                        'updated_at'  => $counter->post_modified,
                    ];
                }
            }
        }

        // Get orders with more detailed information
        $response['orders'] = $this->get_user_orders($user->ID);

        // Activity stats with proper query modifications
        $response['stats'] = [
            'total_sales'     => $this->get_user_total_sales($user->ID),
            'total_orders'    => $this->get_user_total_orders($user->ID),
            'last_activity'   => get_user_meta($user->ID, 'last_activity', true) ?: gmdate('Y-m-d H:i:s'),
            'session_status'  => $this->get_user_session_status($user->ID),
            'last_order'     => $this->get_user_last_order($user->ID),
        ];

        return $response;
    }

    // Add new method to get user's orders
    private function get_user_orders($user_id)
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $orders = $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, pm_total.meta_value as total, pm_status.meta_value as status
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} created_by ON created_by.post_id = p.ID 
            AND created_by.meta_key = '_created_by_id'
            LEFT JOIN {$wpdb->postmeta} pm_total ON pm_total.post_id = p.ID 
            AND pm_total.meta_key = '_order_total'
            LEFT JOIN {$wpdb->postmeta} pm_status ON pm_status.post_id = p.ID 
            AND pm_status.meta_key = '_order_status'
            WHERE p.post_type = 'shop_order'
            AND (p.post_author = %d OR created_by.meta_value = %d)
            AND p.post_status IN ('wc-completed', 'wc-processing')
            ORDER BY p.post_date DESC
            LIMIT 10",
            $user_id,
            $user_id
        ));

        return array_map(function ($order) {
            return [
                'id' => $order->ID,
                'date' => $order->post_date,
                'total' => floatval($order->total),
                'status' => $order->status,
            ];
        }, $orders ?: []);
    }

    // Update get_user_last_order to include more details
    private function get_user_last_order($user_id)
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $last_order = $wpdb->get_row($wpdb->prepare(
            "SELECT p.ID, p.post_date, pm_total.meta_value as total
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} created_by ON created_by.post_id = p.ID 
            AND created_by.meta_key = '_created_by_id'
            LEFT JOIN {$wpdb->postmeta} pm_total ON pm_total.post_id = p.ID 
            AND pm_total.meta_key = '_order_total'
            WHERE p.post_type = 'shop_order'
            AND (p.post_author = %d OR created_by.meta_value = %d)
            AND p.post_status IN ('wc-completed', 'wc-processing')
            ORDER BY p.post_date DESC
            LIMIT 1",
            $user_id,
            $user_id
        ));

        return $last_order ? [
            'id' => $last_order->ID,
            'date' => $last_order->post_date,
            'total' => floatval($last_order->total)
        ] : null;
    }

    // Update get_user_total_sales to ensure proper joining
    private function get_user_total_sales($user_id)
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(CAST(pm.meta_value AS DECIMAL(10,2)))
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} created_by ON created_by.post_id = p.ID 
            AND created_by.meta_key = '_created_by_id'
            LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID 
            AND pm.meta_key = '_order_total'
            WHERE p.post_type = 'shop_order'
            AND (p.post_author = %d OR created_by.meta_value = %d)
            AND p.post_status IN ('wc-completed', 'wc-processing')",
            $user_id,
            $user_id
        ));
        return floatval($total) ?: 0;
    }

    // Helper method to get user permissions
    private function get_user_permissions($user)
    {
        $permissions = [];

        if (in_array('administrator', $user->roles)) {
            $permissions = [
                'manage_users'        => true,
                'manage_outlets'      => true,
                'manage_inventory'    => true,
                'manage_settings'     => true,
                'view_reports'        => true,
                'process_sales'       => true,
                'manage_customers'    => true,
                'manage_discounts'    => true,
            ];
        } elseif (in_array('csmsl_pos_outlet_manager', $user->roles)) {
            $permissions = [
                'manage_users'        => false,
                'manage_outlets'      => true,
                'manage_inventory'    => true,
                'manage_settings'     => false,
                'view_reports'        => true,
                'process_sales'       => true,
                'manage_customers'    => true,
                'manage_discounts'    => true,
            ];
        } elseif (in_array('csmsl_pos_cashier', $user->roles)) {
            $permissions = [
                'manage_users'        => false,
                'manage_outlets'      => false,
                'manage_inventory'    => false,
                'manage_settings'     => false,
                'view_reports'        => false,
                'process_sales'       => true,
                'manage_customers'    => true,
                'manage_discounts'    => false,
            ];
        }

        return $permissions;
    }

    // Helper method to get user's total orders
    private function get_user_total_orders($user_id)
    {
        global $wpdb;
        // Count orders where user is either the author or marked as created_by
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} created_by ON created_by.post_id = p.ID AND created_by.meta_key = '_created_by_id'
            WHERE p.post_type = 'shop_order'
            AND (p.post_author = %d OR created_by.meta_value = %d)
            AND p.post_status IN ('wc-completed', 'wc-processing')",
            $user_id,
            $user_id
        ));
        return intval($count) ?: 0;
    }

    // Helper method to get user's session status
    private function get_user_session_status($user_id)
    {
        $session_tokens = get_user_meta($user_id, 'session_tokens', true);
        $last_activity = get_user_meta($user_id, 'last_activity', true);

        if (!empty($session_tokens)) {
            return 'active';
        } elseif (!empty($last_activity) && (time() - strtotime($last_activity) < 3600)) {
            return 'recently_active';
        }
        return 'inactive';
    }

    // New helper methods for additional stats
    private function get_user_last_order_date($user_id)
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $last_order_date = $wpdb->get_var($wpdb->prepare(
            "SELECT p.post_date
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} created_by ON created_by.post_id = p.ID AND created_by.meta_key = '_created_by_id'
            WHERE p.post_type = 'shop_order'
            AND (p.post_author = %d OR created_by.meta_value = %d)
            AND p.post_status IN ('wc-completed', 'wc-processing')
            ORDER BY p.post_date DESC
            LIMIT 1",
            $user_id,
            $user_id
        ));
        return $last_order_date ?: null;
    }

    private function get_user_recent_activity($user_id)
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $recent_orders = $wpdb->get_results($wpdb->prepare(
            "SELECT p.ID as order_id, p.post_date, pm.meta_value as order_total
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} created_by ON created_by.post_id = p.ID AND created_by.meta_key = '_created_by_id'
            LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_order_total'
            WHERE p.post_type = 'shop_order'
            AND (p.post_author = %d OR created_by.meta_value = %d)
            AND p.post_status IN ('wc-completed', 'wc-processing')
            ORDER BY p.post_date DESC
            LIMIT 5",
            $user_id,
            $user_id
        ));

        return array_map(function ($order) {
            return [
                'order_id' => $order->order_id,
                'date' => $order->post_date,
                'total' => floatval($order->order_total)
            ];
        }, $recent_orders);
    }

    // Add new helper method for counter stats
    private function get_counter_stats($counter_id)
    {
        global $wpdb;

        // Get today's date in MySQL format
        $today = date_i18n('Y-m-d', current_time('timestamp'));
        $month_start = date_i18n('Y-m-01', current_time('timestamp'));


        // Get sales for today
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $today_sales = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(CAST(pm_total.meta_value AS DECIMAL(10,2)))
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm_counter ON pm_counter.post_id = p.ID 
            AND pm_counter.meta_key = '_counter_id'
            LEFT JOIN {$wpdb->postmeta} pm_total ON pm_total.post_id = p.ID 
            AND pm_total.meta_key = '_order_total'
            WHERE p.post_type = 'shop_order'
            AND pm_counter.meta_value = %d
            AND DATE(p.post_date) = %s
            AND p.post_status IN ('wc-completed', 'wc-processing')",
            $counter_id,
            $today
        ));

        // Get sales for current month
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $month_sales = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(CAST(pm_total.meta_value AS DECIMAL(10,2)))
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm_counter ON pm_counter.post_id = p.ID 
            AND pm_counter.meta_key = '_counter_id'
            LEFT JOIN {$wpdb->postmeta} pm_total ON pm_total.post_id = p.ID 
            AND pm_total.meta_key = '_order_total'
            WHERE p.post_type = 'shop_order'
            AND pm_counter.meta_value = %d
            AND DATE(p.post_date) >= %s
            AND p.post_status IN ('wc-completed', 'wc-processing')",
            $counter_id,
            $month_start
        ));

        // Get total orders count
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $total_orders = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm_counter ON pm_counter.post_id = p.ID 
            AND pm_counter.meta_key = '_counter_id'
            WHERE p.post_type = 'shop_order'
            AND pm_counter.meta_value = %d
            AND p.post_status IN ('wc-completed', 'wc-processing')",
            $counter_id
        ));

        return [
            'today_sales' => floatval($today_sales) ?: 0,
            'month_sales' => floatval($month_sales) ?: 0,
            'total_orders' => intval($total_orders) ?: 0,
            'last_active' => get_post_meta($counter_id, 'last_active', true),
            'total_cashiers' => count(get_post_meta($counter_id, 'counter_assignment_history', true) ?: [])
        ];
    }

    // Get all users
    public function get_users($request)
    {
        $users = get_users();
        $response_data = [];

        foreach ($users as $user) {
            // Show users with any of our custom POS roles
            if (array_intersect(['csmsl_pos_outlet_manager', 'csmsl_pos_cashier', 'csmsl_pos_shop_manager'], $user->roles)) {
                $response_data[] = $this->format_user_response($user);
            }
        }

        return new WP_REST_Response($this->format_success_response(
            'Users retrieved successfully.',
            $response_data,
            200
        ), 200);
    }

    // Get a single user
    public function get_user($request)
    {
        $user = get_user_by('id', $request['id']);

        if (!$user) {
            return new WP_REST_Response($this->format_error_response(
                'User not found.',
                [
                    'id' => "The user with the ID '{$request['id']}' does not exist.",
                ],
                404,
                $request->get_route()
            ), 404);
        }

        return new WP_REST_Response($this->format_success_response(
            'User retrieved successfully.',
            $this->format_user_response($user),
            200
        ), 200);
    }

    // Create a new user
    public function create_user($request)
    {
        $parameters = $request->get_json_params();

        // Only admin can create users
        if (!current_user_can('administrator')) {
            return new WP_REST_Response($this->format_error_response(
                'Permission denied',
                ['permission' => 'Only administrators can create users'],
                403
            ), 403);
        }

        // Validate role
        $valid_roles = ['csmsl_pos_outlet_manager', 'csmsl_pos_cashier', 'csmsl_pos_shop_manager'];
        if (!in_array($parameters['role'], $valid_roles)) {
            return new WP_REST_Response($this->format_error_response(
                'Invalid role',
                ['role' => 'Role must be either outlet manager, cashier, or shop manager'],
                400
            ), 400);
        }

        // If creating outlet manager, outlet_id is required
        if ($parameters['role'] === 'csmsl_pos_outlet_manager' && empty($parameters['outlet_id'])) {
            return new WP_REST_Response($this->format_error_response(
                'Missing outlet',
                ['outlet_id' => 'Outlet ID is required for outlet managers'],
                400
            ), 400);
        }

        $errors = [];

        // Validate required fields
        $required_fields = ['name', 'email', 'password', 'role'];
        foreach ($required_fields as $field) {
            if (empty($parameters[$field])) {
                $errors[$field] = "The field '{$field}' is required.";
            }
        }

        // Check if the name or email already exists
        if (isset($parameters['name']) && username_exists($parameters['name'])) {
            $errors['name'] = "A user with the username '{$parameters['name']}' already exists.";
        }
        if (isset($parameters['email']) && email_exists($parameters['email'])) {
            $errors['email'] = "A user with the email '{$parameters['email']}' already exists.";
        }

        // If there are validation errors, return them all
        if (!empty($errors)) {
            return new WP_REST_Response($this->format_error_response(
                'Validation failed.',
                $errors,
                400,
                $request->get_route()
            ), 400);
        }

        // Create the user
        $user_id = wp_create_user($parameters['name'], $parameters['password'], $parameters['email']);

        if (is_wp_error($user_id)) {
            return new WP_REST_Response($this->format_error_response(
                'Failed to create user.',
                [
                    'server' => $user_id->get_error_message(),
                ],
                500,
                $request->get_route()
            ), 500);
        }

        // Update user data including display_name, first_name and last_name
        $user_data = [
            'ID'           => $user_id,
            'role'         => $parameters['role'],
            'display_name' => isset($parameters['display_name']) ? $parameters['display_name'] : $parameters['name']
        ];
        
        // Add first_name if provided
        if (isset($parameters['first_name'])) {
            $user_data['first_name'] = $parameters['first_name'];
        }
        
        // Add last_name if provided
        if (isset($parameters['last_name'])) {
            $user_data['last_name'] = $parameters['last_name'];
        }
        
        wp_update_user($user_data);

        if (isset($parameters['outlet_id'])) {
            update_user_meta($user_id, 'assigned_outlet_id', $parameters['outlet_id']);
        }

        // Save counter assignment for cashiers
        if ($parameters['role'] === 'csmsl_pos_cashier' && isset($parameters['counter_id'])) {
            update_user_meta($user_id, 'assigned_counter_id', $parameters['counter_id']);

            // Also update counter's current user assignment
            if ($parameters['counter_id']) {
                update_post_meta($parameters['counter_id'], 'current_assigned_user', $user_id);

                // Add assignment history if not exists
                $assignment_history = get_post_meta($parameters['counter_id'], 'counter_assignment_history', true) ?: [];
                if (empty($assignment_history) || !is_array($assignment_history)) {
                    $assignment_history = [];
                }

                // Add new assignment record
                $assignment_history[] = [
                    'user_id' => $user_id,
                    'counter_id' => $parameters['counter_id'],
                    'outlet_id' => $parameters['outlet_id'],
                    'assigned_at' => current_time('mysql'),
                    'assigned_by' => get_current_user_id()
                ];

                update_post_meta($parameters['counter_id'], 'counter_assignment_history', $assignment_history);
            }
        }

        return new WP_REST_Response($this->format_success_response(
            'User created successfully.',
            $this->format_user_response(get_userdata($user_id)),
            201
        ), 201);
    }

    // Update a user
    public function update_user($request)
    {
        $parameters = $request->get_json_params();
        $user = get_user_by('id', $request['id']);
        $errors = []; // Array to collect all validation errors

        if (!$user) {
            return rest_ensure_response($this->format_error_response(
                'User not found.',
                [
                    'id' => "The user with the ID '{$request['id']}' does not exist.",
                ],
                404,
                $request->get_route()
            ));
        }

        // Validate email if provided
        if (isset($parameters['email']) && email_exists($parameters['email']) && email_exists($parameters['email']) !== $user->ID) {
            $errors['email'] = "A user with the email '{$parameters['email']}' already exists.";
        }

        // Validate roles if provided
        if (isset($parameters['roles']) && is_array($parameters['roles'])) {
            $valid_roles = ['administrator', 'editor', 'author', 'contributor', 'subscriber', 'csmsl_pos_outlet_manager', 'csmsl_pos_cashier', 'csmsl_pos_shop_manager'];
            foreach ($parameters['roles'] as $role) {
                if (!in_array($role, $valid_roles)) {
                    $errors['roles'] = "The role '{$role}' is not valid.";
                }
                if (!get_role($role)) {
                    $errors['roles'] = "The role '{$role}' does not exist.";
                }
            }
        }

        // Validate status if provided
        if (isset($parameters['status']) && !in_array($parameters['status'], ['active', 'inactive'])) {
            $errors['status'] = "Invalid status value. Allowed values are 'active' or 'inactive'.";
        }

        // If there are validation errors, return them all
        if (!empty($errors)) {
            return rest_ensure_response($this->format_error_response(
                'Validation failed.',
                $errors,
                400,
                $request->get_route()
            ));
        }

        // Update user data
        $update_data = array('ID' => $user->ID);

        // Handle name and display_name updates
        if (isset($parameters['name'])) {
            $update_data['display_name'] = $parameters['name'];
        }
        if (isset($parameters['display_name'])) {
            $update_data['display_name'] = $parameters['display_name'];
        }
        if (isset($parameters['email'])) {
            $update_data['user_email'] = $parameters['email'];
        }
        
        // Handle first name and last name updates
        if (isset($parameters['first_name'])) {
            $update_data['first_name'] = $parameters['first_name'];
        }
        if (isset($parameters['last_name'])) {
            $update_data['last_name'] = $parameters['last_name'];
        }

        // Update the user with all changes at once
        wp_update_user($update_data);

        // Handle role updates
        if (isset($parameters['roles']) && is_array($parameters['roles'])) {
            $user->set_role(''); // Remove all roles
            foreach ($parameters['roles'] as $role) {
                $user->add_role($role);
            }
        } else if (isset($parameters['role'])) {
            // Handle single role update
            $user->set_role($parameters['role']);
        }

        // Update outlet assignment
        if (isset($parameters['outlet_id'])) {
            update_user_meta($user->ID, 'assigned_outlet_id', $parameters['outlet_id']);
        }

        // Update counter assignment for cashiers
        if (in_array('csmsl_pos_cashier', $user->roles) && isset($parameters['counter_id'])) {
            // Get previous counter assignment if any
            $previous_counter_id = get_user_meta($user->ID, 'assigned_counter_id', true);

            // If different counter, update assignment
            if ($previous_counter_id != $parameters['counter_id']) {
                // Clear previous counter assignment if exists
                if ($previous_counter_id) {
                    // Remove user from previous counter
                    $current_user = get_post_meta($previous_counter_id, 'current_assigned_user', true);
                    if ($current_user == $user->ID) {
                        delete_post_meta($previous_counter_id, 'current_assigned_user');
                    }
                }

                // Set new counter assignment
                update_user_meta($user->ID, 'assigned_counter_id', $parameters['counter_id']);

                // Update counter's current user
                update_post_meta($parameters['counter_id'], 'current_assigned_user', $user->ID);

                // Add to assignment history
                $assignment_history = get_post_meta($parameters['counter_id'], 'counter_assignment_history', true) ?: [];
                if (!is_array($assignment_history)) {
                    $assignment_history = [];
                }

                // Add new assignment record
                $assignment_history[] = [
                    'user_id' => $user->ID,
                    'counter_id' => $parameters['counter_id'],
                    'outlet_id' => $parameters['outlet_id'] ?? get_user_meta($user->ID, 'assigned_outlet_id', true),
                    'assigned_at' => current_time('mysql'),
                    'assigned_by' => get_current_user_id()
                ];

                update_post_meta($parameters['counter_id'], 'counter_assignment_history', $assignment_history);
            }
        }

        // Update user status
        if (isset($parameters['status'])) {
            $user_status = $parameters['status'] === 'active' ? 0 : 1;
            wp_update_user([
                'ID'            => $user->ID,
                'user_status'   => $user_status
            ]);
            // Also store as user meta for easier querying
            update_user_meta($user->ID, 'user_account_status', $parameters['status']);
        }

        return rest_ensure_response([
            'success' => true,
            'message' => 'User updated successfully.',
            'data'    => $this->format_user_response(get_userdata($user->ID)),
        ]);
    }

    // Delete a user
    public function delete_user($request)
    {
        require_once ABSPATH . 'wp-admin/includes/user.php';

        $user = get_user_by('id', $request['id']);

        if (!$user) {
            return new WP_REST_Response($this->format_error_response(
                'User not found.',
                [
                    'id' => "The user with the ID '{$request['id']}' does not exist.",
                ],
                404,
                $request->get_route()
            ), 404);
        }

        $result = wp_delete_user($user->ID);

        if (!$result) {
            return new WP_REST_Response($this->format_error_response(
                'Failed to delete user.',
                [
                    'server' => 'An error occurred while deleting the user.',
                ],
                500,
                $request->get_route()
            ), 500);
        }

        return new WP_REST_Response($this->format_success_response(
            'User deleted successfully.',
            null,
            200
        ), 200);
    }

    // Get current user
    public function get_current_user($request)
    {
        $user = wp_get_current_user();

        if (!$user->exists()) {
            return new WP_REST_Response($this->format_error_response(
                'User is not logged in.',
                [
                    'authentication' => 'Please log in to access this resource.',
                ],
                401,
                $request->get_route()
            ), 401);
        }

        return new WP_REST_Response($this->format_success_response(
            'Current user retrieved successfully.',
            $this->format_user_response($user),
            200
        ), 200);
    }

    // Logout user
    public function logout_user($request)
    {
        $user = wp_get_current_user();

        if (!$user->exists()) {
            return new WP_REST_Response($this->format_error_response(
                'User is not logged in.',
                [
                    'authentication' => 'Please log in to access this resource.',
                ],
                401,
                $request->get_route()
            ), 401);
        }

        // Update last activity before logout
        update_user_meta($user->ID, 'last_activity', current_time('mysql'));

        // Clear user session tokens to logout from all devices
        $sessions = \WP_Session_Tokens::get_instance($user->ID);
        $sessions->destroy_all();

        // WordPress logout functions
        wp_logout();
        wp_clear_auth_cookie();

        // Clear any additional session data
        if (session_id()) {
            session_destroy();
        }

        return new WP_REST_Response($this->format_success_response(
            'User logged out successfully.',
            [
                    'redirect_url' => home_url('/aipos/auth/login'),
                'logged_out' => true
            ],
            200
        ), 200);
    }

    // Get user roles
    public function get_user_roles($request)
    {
        global $wp_roles;
        $roles = $wp_roles->roles;

        return new WP_REST_Response($this->format_success_response(
            'Roles retrieved successfully.',
            $roles,
            200
        ), 200);
    }
}