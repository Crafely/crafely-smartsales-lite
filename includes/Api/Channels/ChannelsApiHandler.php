<?php
/**
 * Crafely SmartSales Lite Channels API Handler
 *
 * This class handles the REST API endpoints for managing channels in the Crafely SmartSales Lite plugin.
 *
 * @package CrafelySmartSalesLite
 */

namespace CSMSL\Includes\Api\Channels;

use WP_Query;
use WP_REST_Response;
use WP_REST_Request;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ChannelsApiHandler class
 */
class ChannelsApiHandler {

	/**
	 * The taxonomy used for channels.
	 *
	 * @var string
	 */

	private $taxonomy = 'csmsl_channel';

	/**
	 * Constructor for the ChannelsApiHandler class.
	 * Initializes the REST API routes and registers the taxonomy.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'init', array( $this, 'create_predefined_channels' ) );
	}

	/**
	 * Register taxonomy
	 */
	public function register_taxonomy() {
		if ( ! taxonomy_exists( $this->taxonomy ) ) {
			register_taxonomy(
				$this->taxonomy,
				array( 'product', 'shop_order', 'customer' ),
				array(
					'label'             => __( 'Channels', 'crafely-smartsales-lite' ),
					'hierarchical'      => true,
					'show_ui'           => true,
					'show_admin_column' => true,
					'query_var'         => true,
					'rewrite'           => array(
						'slug'       => 'csmsl-channel',
						'with_front' => false,
					),
				)
			);
		}
	}

	/**
	 * Register REST API routes
	 */
	public function register_routes() {
		register_rest_route(
			'ai-smart-sales/v1',
			'/channels',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_channels' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/channels/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_channel' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/channels',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_channel' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/channels/(?P<id>\d+)',
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_channel' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/channels/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_channel' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	/**
	 * Check permissions
	 * This method checks if the current user has the necessary permissions to access the API endpoints.
	 * It returns true if the user is logged in and has one of the allowed roles,
	 * otherwise it returns false.
	 *
	 * @return bool True if the user has permission, false otherwise.
	 */
	public function check_permission() {
		// Check if user is logged in and has appropriate capabilities.
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Get current user.
		$user = wp_get_current_user();

		// Check if user has any of our POS roles or is an administrator.
		$allowed_roles = array( 'administrator', 'csmsl_pos_outlet_manager', 'csmsl_pos_cashier', 'csmsl_pos_shop_manager' );
		$user_roles    = (array) $user->roles;

		if ( ! array_intersect( $allowed_roles, $user_roles ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Format success response
	 * This method formats a successful response for the API.
	 * It takes a message, optional data, and an HTTP status code,
	 * and returns an associative array with the success status, message, and data.
	 *
	 * @param string $message The success message.
	 * @param array  $data Optional data to include in the response.
	 * @param int    $statusCode Optional HTTP status code (default is 200).
	 * @return array An associative array containing the success status, message, and data.
	 */
	private function format_success_response( $message, $data = array(), $statusCode = 200 ) {
		return array(
			'success' => true,
			'message' => $message,
			'data'    => $data,
			'status'  => $statusCode,
		);
	}

	/**
	 * Formats a eror response for the API.
	 *
	 * @param string $message The success message.
	 * @param array  $errors Additional data to include in the response.
	 * @param int    $statusCode HTTP status code for the response.
	 * @param string $path The path of the API endpoint.
	 * @return array The formatted response.
	 */
	private function format_error_response( $message, $errors = array(), $statusCode = 400, $path = '' ) {
		$error = array();

		// If $errors is an associative array, use it as-is.
		if ( is_array( $errors ) && ! empty( $errors ) && array_keys( $errors ) !== range( 0, count( $errors ) - 1 ) ) {
			$error = $errors;
		} else {
			// Otherwise, use a generic error structure.
			$error = array(
				'error' => $message,
			);
		}

		return array(
			'success' => false,
			'message' => $message,
			'data'    => null,
			'error'   => $error,
			'status'  => $statusCode,
			'path'    => $path,

		);
	}

	/**
	 * Format channel response
	 * This method formats a channel term object into a structured array.
	 * It extracts the term ID, name, slug, description, count, and parent ID
	 * and returns them in an associative array.
	 *
	 * @param object $term The term object representing the channel.
	 * @return array An associative array containing the channel details.
	 */
	private function format_channel_response( $term ) {
		return array(
			'id'          => $term->term_id,
			'name'        => $term->name,
			'slug'        => $term->slug,
			'description' => $term->description,
			'count'       => $term->count,
			'parent'      => $term->parent,
		);
	}

	/**
	 * Get all channels
	 * This method retrieves all channels from the specified taxonomy.
	 * It uses the `get_terms` function to fetch terms from the taxonomy,
	 * and formats the response using the `format_channel_response` method.
	 * If there is an error retrieving the terms, it returns an error response.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response A response object containing the channels or an error message
	 */
	public function get_channels( WP_REST_Request $request ) {
		$args = array(
			'taxonomy'   => $this->taxonomy,
			'hide_empty' => false,
		);

		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to retrieve channels.',
					array(
						'server' => $terms->get_error_message(),
					),
					400,
					$request->get_route()
				),
				400
			);
		}

		$channels = array_map( array( $this, 'format_channel_response' ), $terms );

		return new WP_REST_Response(
			$this->format_success_response(
				'Channels retrieved successfully.',
				$channels,
				200
			),
			200
		);
	}

	/**
	 * Get a single channel
	 * This method retrieves a single channel by its ID from the specified taxonomy.
	 * It uses the `get_term` function to fetch the term object,
	 * and formats the response using the `format_channel_response` method.
	 * If the term does not exist or there is an error, it returns an error response.
	 *
	 * @param WP_REST_Request $request The REST request object containing the channel ID.
	 * @return WP_REST_Response A response object containing the channel or an error message
	 */
	public function get_channel( WP_REST_Request $request ) {
		$channel_id = $request['id'];
		$term       = get_term( $channel_id, $this->taxonomy );

		if ( is_wp_error( $term ) || ! $term ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Channel not found.',
					array(
						'id' => "The channel with the ID '{$channel_id}' does not exist.",
					),
					404,
					$request->get_route()
				),
				404
			);
		}

		return new WP_REST_Response(
			$this->format_success_response(
				'Channel retrieved successfully.',
				$this->format_channel_response( $term ),
				200
			),
			200
		);
	}

	/**
	 * Create a channel
	 * This method creates a new channel in the specified taxonomy.
	 * It validates the required fields from the request data,
	 * and if validation passes, it inserts the term using `wp_insert_term`.
	 * If there are validation errors or if the term creation fails,
	 * it returns an error response.
	 *
	 * @param WP_REST_Request $request The REST request object containing the channel data.
	 * @return WP_REST_Response A response object containing the created channel or an error message
	 */
	public function create_channel( WP_REST_Request $request ) {
		$data   = $request->get_json_params();
		$errors = array();

		// Validate required fields.
		if ( empty( $data['name'] ) ) {
			$errors['name'] = 'Channel name is required.';
		}

		// If there are validation errors, return them all.
		if ( ! empty( $errors ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Validation failed.',
					$errors,
					400,
					$request->get_route()
				),
				400
			);
		}

		$args = array(
			'description' => $data['description'] ?? '',
			'parent'      => $data['parent'] ?? 0,
			'slug'        => $data['slug'] ?? '',
		);

		$term = wp_insert_term( $data['name'], $this->taxonomy, $args );

		if ( is_wp_error( $term ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to create channel.',
					array(
						'server' => $term->get_error_message(),
					),
					400,
					$request->get_route()
				),
				400
			);
		}

		$new_term = get_term( $term['term_id'], $this->taxonomy );

		return new WP_REST_Response(
			$this->format_success_response(
				'Channel created successfully.',
				$this->format_channel_response( $new_term ),
				201
			),
			201
		);
	}

	/**
	 * Update a channel
	 * This method updates an existing channel in the specified taxonomy.
	 * It validates the channel ID and the fields to be updated,
	 * and if validation passes, it updates the term using `wp_update_term`
	 * If there are validation errors or if the term update fails,
	 * it returns an error response.
	 *
	 * @param WP_REST_Request $request The REST request object containing the channel ID and data.
	 * @return WP_REST_Response A response object containing the updated channel or an error
	 */
	public function update_channel( WP_REST_Request $request ) {
		$channel_id = $request['id'];
		$data       = $request->get_json_params();
		$errors     = array();

		// Validate channel ID.
		if ( ! term_exists( $channel_id, $this->taxonomy ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Channel not found.',
					array(
						'id' => "The channel with the ID '{$channel_id}' does not exist.",
					),
					404,
					$request->get_route()
				),
				404
			);
		}

		$args = array();
		if ( isset( $data['name'] ) ) {
			$args['name'] = $data['name'];
		}
		if ( isset( $data['description'] ) ) {
			$args['description'] = $data['description'];
		}
		if ( isset( $data['slug'] ) ) {
			$args['slug'] = $data['slug'];
		}
		if ( isset( $data['parent'] ) ) {
			$args['parent'] = $data['parent'];
		}

		$updated = wp_update_term( $channel_id, $this->taxonomy, $args );

		if ( is_wp_error( $updated ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to update channel.',
					array(
						'server' => $updated->get_error_message(),
					),
					400,
					$request->get_route()
				),
				400
			);
		}

		$term = get_term( $channel_id, $this->taxonomy );

		return new WP_REST_Response(
			$this->format_success_response(
				'Channel updated successfully.',
				$this->format_channel_response( $term ),
				200
			),
			200
		);
	}

	/**
	 * Delete a channel
	 * This method deletes a channel from the specified taxonomy.
	 * It checks if the channel exists, and if it does, it attempts to delete it
	 * using `wp_delete_term`. If the deletion is successful, it returns a success response
	 * or an error response if the deletion fails.
	 *
	 * @param WP_REST_Request $request The REST request object containing the channel ID.
	 *
	 * @return WP_REST_Response A response object indicating the success or failure of the deletion
	 */
	public function delete_channel( WP_REST_Request $request ) {
		$channel_id = intval( $request['id'] );

		// Fetch the term to ensure it exists and belongs to the correct taxonomy.
		$term = get_term( $channel_id, $this->taxonomy );

		// Check if the term exists and is valid.
		if ( ! $term || is_wp_error( $term ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Channel not found.',
					array(
						'id' => "The channel with the ID '{$channel_id}' does not exist.",
					),
					404,
					$request->get_route()
				),
				404
			);
		}

		// Attempt to delete the term.
		$deleted = wp_delete_term( $channel_id, $this->taxonomy );

		if ( is_wp_error( $deleted ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to delete channel.',
					array(
						'server' => $deleted->get_error_message(),
					),
					400,
					$request->get_route()
				),
				400
			);
		}

		// Check if the deletion returned valid data.
		if ( ! $deleted ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to delete the channel.',
					array(
						'server' => 'An unknown error occurred while deleting the channel.',
					),
					500,
					$request->get_route()
				),
				500
			);
		}

		return new WP_REST_Response(
			$this->format_success_response(
				'Channel deleted successfully.',
				array(
					'channel_id' => $channel_id,
				),
				200
			),
			200
		);
	}

	/**
	 * Create predefined channels
	 * This method creates predefined channels in the taxonomy if they do not already exist.
	 * It defines a set of predefined channels with their names, descriptions, and slugs,
	 * and inserts them into the taxonomy using `wp_insert_term`.
	 */
	public function create_predefined_channels() {
		$predefined_channels = array(
			'POS System'   => array(
				'description' => 'Orders created via the POS system.',
				'slug'        => 'pos-system',
			),
			'Web'          => array(
				'description' => 'Orders created via the web store.',
				'slug'        => 'web',
			),
			'Social Media' => array(
				'description' => 'Orders created via social media platforms.',
				'slug'        => 'social-media',
			),
		);

		foreach ( $predefined_channels as $name => $args ) {
			if ( ! term_exists( $name, $this->taxonomy ) ) {
				wp_insert_term( $name, $this->taxonomy, $args );
			}
		}
	}
}
