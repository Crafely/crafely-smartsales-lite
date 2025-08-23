<?php
/**
 * Crafely SmartSales Lite Media API Handler
 *
 * This file handles the REST API endpoints for media management in the Crafely SmartSales Lite plugin.
 *
 * @package CrafelySmartSalesLite
 */

namespace CSMSL\Includes\Api\Media;

use WP_REST_Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MediaApiHandler
 *
 * Handles media-related REST API requests.
 */
class MediaApiHandler {

	/**
	 * Constructor.
	 *
	 * Initializes the REST API routes for media management.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes for media management.
	 *
	 * This method registers the routes for getting, uploading, and deleting media.
	 */
	public function register_routes() {
		register_rest_route(
			'ai-smart-sales/v1',
			'/media',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_media' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/media/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_single_media' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/media',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'upload_media' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/media/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_media' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	/**
	 * Check if the user has permission to access the media endpoints.
	 *
	 * This method checks if the user is logged in and has the appropriate capabilities.
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
		$allowed_roles = array( 'administrator', 'csmsl_spos_outlet_manager', 'csmsl_pos_cashier', 'csmsl_pos_shop_manager' );
		$user_roles    = (array) $user->roles;

		if ( ! array_intersect( $allowed_roles, $user_roles ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Format success response.
	 * This method formats a successful response for the REST API.
	 *
	 * @param string $message The success message.
	 * @param array  $data    Optional. Additional data to include in the response.
	 * @param int    $statusCode Optional. HTTP status code for the response.
	 * @return array The formatted success response.
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
	 * Format error response.
	 * This method formats an error response for the REST API.
	 *
	 * @param string $message The error message.
	 * @param array  $errors Optional. Additional error details.
	 * @param int    $statusCode Optional. HTTP status code for the response.
	 * @param string $path Optional. The path of the request that caused the error.
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
	 * Get all media.
	 * This method retrieves all media items from the WordPress media library.
	 *
	 * @param WP_REST_Request $request The REST API request object.
	 * @return WP_REST_Response The response containing the media items or an error message.
	 */
	public function get_media( $request ) {
		$args  = array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
		);
		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'No media found.',
					array(
						'media' => 'There are no media items in the library.',
					),
					404,
					$request->get_route()
				),
				404
			);
		}

		$media = array();
		foreach ( $query->posts as $post ) {
			$media[] = array(
				'id'   => $post->ID,
				'url'  => wp_get_attachment_url( $post->ID ),
				'type' => get_post_mime_type( $post->ID ),
			);
		}

		return new WP_REST_Response(
			$this->format_success_response(
				'Media retrieved successfully.',
				$media,
				200
			),
			200
		);
	}

	/**
	 * Get single media.
	 * This method retrieves a single media item by its ID.
	 *
	 * @param WP_REST_Request $request The REST API request object.
	 * @return WP_REST_Response The response containing the media item or an error message.
	 */
	public function get_single_media( $request ) {
		$id    = $request['id'];
		$media = get_post( $id );

		if ( ! $media || 'attachment' !== $media->post_type ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Media not found.',
					array(
						'id' => "The media with the ID '{$id}' does not exist.",
					),
					404,
					$request->get_route()
				),
				404
			);
		}

		return new WP_REST_Response(
			$this->format_success_response(
				'Media retrieved successfully.',
				array(
					'id'   => $media->ID,
					'url'  => wp_get_attachment_url( $media->ID ),
					'type' => get_post_mime_type( $media->ID ),
				),
				200
			),
			200
		);
	}

	/**
	 * Upload media.
	 * This method handles the upload of media files to the WordPress media library.
	 *
	 * @param WP_REST_Request $request The REST API request object.
	 * @return WP_REST_Response The response containing the uploaded media item or an error message
	 */
	public function upload_media( $request ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$files = $request->get_file_params();
		if ( empty( $files['file'] ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'No file uploaded.',
					array(
						'file' => 'Please provide a file to upload.',
					),
					400,
					$request->get_route()
				),
				400
			);
		}

		$file   = $files['file'];
		$upload = wp_handle_upload( $file, array( 'test_form' => false ) );

		if ( isset( $upload['error'] ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'File upload failed.',
					array(
						'upload' => $upload['error'],
					),
					500,
					$request->get_route()
				),
				500
			);
		}

		$attachment = array(
			'post_mime_type' => $upload['type'],
			'post_title'     => sanitize_file_name( $file['name'] ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attachment_id = wp_insert_attachment( $attachment, $upload['file'] );
		if ( is_wp_error( $attachment_id ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to create attachment.',
					array(
						'attachment' => $attachment_id->get_error_message(),
					),
					500,
					$request->get_route()
				),
				500
			);
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attach_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
		wp_update_attachment_metadata( $attachment_id, $attach_data );

		return new WP_REST_Response(
			$this->format_success_response(
				'Media uploaded successfully.',
				array(
					'id'  => $attachment_id,
					'url' => wp_get_attachment_url( $attachment_id ),
				),
				201
			),
			201
		);
	}

	/**
	 * Delete media.
	 * This method deletes a media item from the WordPress media library.
	 *
	 * @param WP_REST_Request $request The REST API request object.
	 * @return WP_REST_Response The response indicating success or failure of the deletion.
	 */
	public function delete_media( $request ) {
		$id         = $request['id'];
		$attachment = get_post( $id );

		if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Invalid media ID.',
					array(
						'id' => "The media with the ID '{$id}' does not exist.",
					),
					404,
					$request->get_route()
				),
				404
			);
		}

		$deleted = wp_delete_attachment( $id, true );
		if ( ! $deleted ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to delete media.',
					array(
						'id' => "The media with the ID '{$id}' could not be deleted.",
					),
					500,
					$request->get_route()
				),
				500
			);
		}

		return new WP_REST_Response(
			$this->format_success_response(
				'Media deleted successfully.',
				array( 'id' => $id ),
				200
			),
			200
		);
	}
}

// Initialize the API handler.
new MediaApiHandler();
