<?php

namespace AISMARTSALES\Includes\Api\Channels;

use WP_Query;
use WP_REST_Response;
use WP_REST_Request;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

class ChannelsApiHandler
{
    private $taxonomy = 'generic_channel';

    public function __construct()
    {
        add_action('init', [$this, 'register_taxonomy']);
        add_action('rest_api_init', [$this, 'register_routes']);
        add_action('init', [$this, 'create_predefined_channels']);
    }

    /**
     * Register taxonomy
     */
    public function register_taxonomy()
    {
        if (!taxonomy_exists($this->taxonomy)) {
            register_taxonomy($this->taxonomy, ['product', 'shop_order', 'customer'], [
                'label' => __('Channels', 'crafely-smartsales-lite'),
                'hierarchical' => true,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => [
                    'slug' => 'generic-channel',
                    'with_front' => false,
                ],
            ]);
        }
    }

    /**
     * Register REST API routes
     */
    public function register_routes()
    {
        register_rest_route('ai-smart-sales/v1', '/channels', [
            'methods' => 'GET',
            'callback' => [$this, 'get_channels'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/channels/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_channel'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/channels', [
            'methods' => 'POST',
            'callback' => [$this, 'create_channel'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/channels/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'update_channel'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        register_rest_route('ai-smart-sales/v1', '/channels/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete_channel'],
            'permission_callback' => [$this, 'check_permission'],
        ]);
    }

    /**
     * Check permissions
     */
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

    /**
     * Format success response
     */
    private function format_success_response($message, $data = [], $statusCode = 200)
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * Format error response
     */
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

    /**
     * Format channel response
     */
    private function format_channel_response($term)
    {
        return [
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
            'description' => $term->description,
            'count' => $term->count,
            'parent' => $term->parent,
        ];
    }

    /**
     * Get all channels
     */
    public function get_channels(WP_REST_Request $request)
    {
        $args = [
            'taxonomy' => $this->taxonomy,
            'hide_empty' => false,
        ];

        $terms = get_terms($args);

        if (is_wp_error($terms)) {
            return new WP_REST_Response($this->format_error_response(
                'Failed to retrieve channels.',
                [
                    'server' => $terms->get_error_message(),
                ],
                400,
                $request->get_route()
            ), 400);
        }

        $channels = array_map([$this, 'format_channel_response'], $terms);

        return new WP_REST_Response($this->format_success_response(
            'Channels retrieved successfully.',
            $channels,
            200
        ), 200);
    }

    /**
     * Get a single channel
     */
    public function get_channel(WP_REST_Request $request)
    {
        $channel_id = $request['id'];
        $term = get_term($channel_id, $this->taxonomy);

        if (is_wp_error($term) || !$term) {
            return new WP_REST_Response($this->format_error_response(
                'Channel not found.',
                [
                    'id' => "The channel with the ID '{$channel_id}' does not exist.",
                ],
                404,
                $request->get_route()
            ), 404);
        }

        return new WP_REST_Response($this->format_success_response(
            'Channel retrieved successfully.',
            $this->format_channel_response($term),
            200
        ), 200);
    }

    /**
     * Create a channel
     */
    public function create_channel(WP_REST_Request $request)
    {
        $data = $request->get_json_params();
        $errors = []; // Array to collect all validation errors

        // Validate required fields
        if (empty($data['name'])) {
            $errors['name'] = 'Channel name is required.';
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

        $args = [
            'description' => $data['description'] ?? '',
            'parent' => $data['parent'] ?? 0,
            'slug' => $data['slug'] ?? '',
        ];

        $term = wp_insert_term($data['name'], $this->taxonomy, $args);

        if (is_wp_error($term)) {
            return new WP_REST_Response($this->format_error_response(
                'Failed to create channel.',
                [
                    'server' => $term->get_error_message(),
                ],
                400,
                $request->get_route()
            ), 400);
        }

        $new_term = get_term($term['term_id'], $this->taxonomy);

        return new WP_REST_Response($this->format_success_response(
            'Channel created successfully.',
            $this->format_channel_response($new_term),
            201
        ), 201);
    }

    /**
     * Update a channel
     */
    public function update_channel(WP_REST_Request $request)
    {
        $channel_id = $request['id'];
        $data = $request->get_json_params();
        $errors = []; // Array to collect all validation errors

        // Validate channel ID
        if (!term_exists($channel_id, $this->taxonomy)) {
            return new WP_REST_Response($this->format_error_response(
                'Channel not found.',
                [
                    'id' => "The channel with the ID '{$channel_id}' does not exist.",
                ],
                404,
                $request->get_route()
            ), 404);
        }

        $args = [];
        if (isset($data['name'])) $args['name'] = $data['name'];
        if (isset($data['description'])) $args['description'] = $data['description'];
        if (isset($data['slug'])) $args['slug'] = $data['slug'];
        if (isset($data['parent'])) $args['parent'] = $data['parent'];

        $updated = wp_update_term($channel_id, $this->taxonomy, $args);

        if (is_wp_error($updated)) {
            return new WP_REST_Response($this->format_error_response(
                'Failed to update channel.',
                [
                    'server' => $updated->get_error_message(),
                ],
                400,
                $request->get_route()
            ), 400);
        }

        $term = get_term($channel_id, $this->taxonomy);

        return new WP_REST_Response($this->format_success_response(
            'Channel updated successfully.',
            $this->format_channel_response($term),
            200
        ), 200);
    }

    /**
     * Delete a channel
     */
    public function delete_channel(WP_REST_Request $request)
    {
        $channel_id = intval($request['id']); // Ensure ID is numeric

        // Fetch the term to ensure it exists and belongs to the correct taxonomy
        $term = get_term($channel_id, $this->taxonomy);

        // Check if the term exists and is valid
        if (!$term || is_wp_error($term)) {
            return new WP_REST_Response($this->format_error_response(
                'Channel not found.',
                [
                    'id' => "The channel with the ID '{$channel_id}' does not exist.",
                ],
                404,
                $request->get_route()
            ), 404);
        }

        // Attempt to delete the term
        $deleted = wp_delete_term($channel_id, $this->taxonomy);

        if (is_wp_error($deleted)) {
            return new WP_REST_Response($this->format_error_response(
                'Failed to delete channel.',
                [
                    'server' => $deleted->get_error_message(),
                ],
                400,
                $request->get_route()
            ), 400);
        }

        // Check if the deletion returned valid data
        if (!$deleted) {
            return new WP_REST_Response($this->format_error_response(
                'Failed to delete the channel.',
                [
                    'server' => 'An unknown error occurred while deleting the channel.',
                ],
                500,
                $request->get_route()
            ), 500);
        }

        return new WP_REST_Response($this->format_success_response(
            'Channel deleted successfully.',
            [
                'channel_id' => $channel_id,
            ],
            200
        ), 200);
    }

    public function create_predefined_channels()
    {
        $predefined_channels = [
            'POS System' => [
                'description' => 'Orders created via the POS system.',
                'slug' => 'pos-system',
            ],
            'Web' => [
                'description' => 'Orders created via the web store.',
                'slug' => 'web',
            ],
            'Social Media' => [
                'description' => 'Orders created via social media platforms.',
                'slug' => 'social-media',
            ],
        ];

        foreach ($predefined_channels as $name => $args) {
            if (!term_exists($name, $this->taxonomy)) {
                wp_insert_term($name, $this->taxonomy, $args);
            }
        }
    }
}