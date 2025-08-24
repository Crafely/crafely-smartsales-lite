<?php
/**
 * Crafely Smart Sales Lite
 *
 * @package CrafelySmartSalesLite
 */

namespace CSMSL\Includes\Api\Categories;

use WP_REST_Response;
use WP_Term;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CategoriesApiHandler
 *
 * Handles REST API requests for product categories.
 * Supports listing, retrieving, creating, updating, and deleting categories.
 *
 * Routes:
 * - GET    /ai-smart-sales/v1/categories
 * - GET    /ai-smart-sales/v1/categories/{id}
 * - POST   /ai-smart-sales/v1/categories
 * - PUT    /ai-smart-sales/v1/categories/{id}
 * - DELETE /ai-smart-sales/v1/categories/{id}
 *
 * @package CrafelySmartSalesLite
 */
class CategoriesApiHandler {

	/**
	 * Constructor.
	 *
	 * Hooks into 'rest_api_init' to register API routes.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers the REST API routes for product categories.
	 * This method defines the endpoints for listing, retrieving, creating, updating, and deleting categories.
	 */
	public function register_routes() {
		register_rest_route(
			'ai-smart-sales/v1',
			'/categories',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_categories' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/categories/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_category' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/categories',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_category' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/categories/(?P<id>\d+)',
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_category' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/categories/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_category' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	/**
	 * Check user permission for API requests.
	 *
	 * Read operations are allowed for authenticated POS roles.
	 * Write operations (POST, PUT, DELETE) require 'administrator' or 'manage_woocommerce' capability.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return bool True if the user has permission, false otherwise.
	 */
	public function check_permission( $request ) {
		// Check if user is logged in.
		if ( ! is_user_logged_in() ) {
			return false;
		}
		$user = wp_get_current_user();

		// For write operations, require higher privileges.
		if ( in_array( $request->get_method(), array( 'POST', 'PUT', 'DELETE' ), true ) ) {
			return current_user_can( 'administrator' ) || current_user_can( 'manage_woocommerce' );
		}

		// For read operations, allow authenticated users with POS roles.
		$allowed_roles = array( 'administrator', 'csmsl_pos_outlet_manager', 'csmsl_pos_cashier', 'csmsl_pos_shop_manager' );
		$user_roles    = (array) $user->roles;

		return ! empty( array_intersect( $allowed_roles, $user_roles ) );
	}

	/**
	 * Formats an error response for the API.
	 *
	 * @param string $message The error message.
	 * @param array  $errors  Optional. An associative array of error details.
	 * @param int    $statusCode Optional. HTTP status code for the response.
	 * @param string $path Optional. The API endpoint path.
	 * @return array Formatted error response.
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
	 * Formats a category response for the API.
	 * This method converts a WP_Term object into a structured array for API responses.
	 *
	 * @param WP_Term $category The category term object.
	 * @return array Formatted category data.
	 */
	private function format_category_response( $category ) {
		return array(
			'id'          => $category->term_id,
			'name'        => $category->name,
			'slug'        => $category->slug,
			'description' => $category->description,
			'count'       => $category->count,
			'parent'      => $category->parent,
		);
	}

	/**
	 * Retrieves a list of product categories.
	 * Supports filtering by hide_empty, orderby, order, and limit parameters.
	 * Returns a formatted response with category data or an error message.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response Formatted response with category data or error message.
	 */
	public function get_categories( $request ) {
		$args = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => $request->get_param( 'hide_empty' ) ? $request->get_param( 'hide_empty' ) : false,
			'orderby'    => $request->get_param( 'orderby' ) ? $request->get_param( 'orderby' ) : 'name',
			'order'      => $request->get_param( 'order' ) ? $request->get_param( 'order' ) : 'ASC',
			'number'     => $request->get_param( 'limit' ) ? $request->get_param( 'limit' ) : 0,
		);

		$categories = get_terms( $args );

		if ( is_wp_error( $categories ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to retrieve categories.',
					array(
						'error' => $categories->get_error_message(),
					),
					500,
					$request->get_route()
				),
				500
			);
		}

		if ( empty( $categories ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'No categories found.',
					array(
						'categories' => 'No categories match the specified criteria.',
					),
					404,
					$request->get_route()
				),
				404
			);
		}

		$formatted_categories = array_map( array( $this, 'format_category_response' ), $categories );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Categories retrieved successfully.',
				'data'    => $formatted_categories,
			),
			200
		);
	}
	/**
	 * Retrieves a single product category by ID.
	 * Returns a formatted response with category data or an error message.
	 *
	 * @param array $data The request data containing the category ID.
	 */
	public function get_category( $data ) {
		$category_id = $data['id'];
		$category    = get_term( $category_id, 'product_cat' );

		if ( is_wp_error( $category ) || ! $category ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Category not found.',
					array(
						'id' => "The category with the ID '{$category_id}' does not exist.",
					),
					404,
					'/ai-smart-sales/v1/product-categories/' . $category_id
				),
				404
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Category retrieved successfully.',
				'data'    => $this->format_category_response( $category ),
			),
			200
		);
	}

	/**
	 * Creates a new product category.
	 * Validates required fields and returns a formatted response with the created category data or an error message.
	 *
	 * @param \WP_REST_Request $request The REST request object containing category data.
	 * @return \WP_REST_Response Formatted response with category data or error message.
	 */
	public function create_category( $request ) {
		$data = $request->get_json_params();

		// Define required fields and their error messages.
		$required_fields = array(
			'name' => 'name is required.',
		);

		$errors = array();

		// Check for missing required fields.
		foreach ( $required_fields as $field => $error_message ) {
			if ( ! isset( $data[ $field ] ) || empty( $data[ $field ] ) ) {
				$errors[ $field ] = $error_message;
			}
		}

		// If there are missing fields, return a comprehensive error response.
		if ( ! empty( $errors ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Missing required fields: ' . implode( ', ', array_keys( $errors ) ),
					'data'    => null,
					'error'   => $errors,
				),
				400
			);
		}

		// Create the category.
		$category = wp_insert_term(
			$data['name'],
			'product_cat',
			array(
				'description' => $data['description'] ?? '',
				'slug'        => $data['slug'] ?? '',
				'parent'      => $data['parent'] ?? 0,
			)
		);

		if ( is_wp_error( $category ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to create category.',
					array(
						'error' => $category->get_error_message(),
					),
					500,
					$request->get_route()
				),
				500
			);
		}

		$category = get_term( $category['term_id'], 'product_cat' );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Category created successfully.',
				'data'    => $this->format_category_response( $category ),
			),
			201
		);
	}
	/**
	 * Updates an existing product category.
	 * Validates the category ID and updates the category with provided data.
	 * Returns a formatted response with the updated category data or an error message.
	 *
	 * @param \WP_REST_Request $request The REST request object containing category ID and data.
	 * @return \WP_REST_Response Formatted response with updated category data or error message.
	 */
	public function update_category( $request ) {
		$category_id = $request->get_param( 'id' );
		$data        = $request->get_json_params();

		$category = get_term( $category_id, 'product_cat' );

		if ( is_wp_error( $category ) || ! $category ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Category not found.',
					array(
						'id' => "The category with the ID '{$category_id}' does not exist.",
					),
					404,
					'/ai-smart-sales/v1/product-categories/' . $category_id
				),
				404
			);
		}

		// Update the category.
		$updated_category = wp_update_term(
			$category_id,
			'product_cat',
			array(
				'name'        => $data['name'] ?? $category->name,
				'description' => $data['description'] ?? $category->description,
				'slug'        => $data['slug'] ?? $category->slug,
				'parent'      => $data['parent'] ?? $category->parent,
			)
		);

		if ( is_wp_error( $updated_category ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to update category.',
					array(
						'error' => $updated_category->get_error_message(),
					),
					500,
					$request->get_route()
				),
				500
			);
		}

		$updated_category = get_term( $updated_category['term_id'], 'product_cat' );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Category updated successfully.',
				'data'    => $this->format_category_response( $updated_category ),
			),
			200
		);
	}
	/**
	 * Deletes a product category by ID.
	 * Validates the category ID and deletes the category if it exists.
	 * Returns a formatted response indicating success or failure.
	 *
	 * @param \WP_REST_Request $request The REST request object containing the category ID.
	 * @return \WP_REST_Response Formatted response indicating success or failure.
	 */
	public function delete_category( $request ) {
		$category_id = $request->get_param( 'id' );
		$category    = get_term( $category_id, 'product_cat' );

		if ( is_wp_error( $category ) || ! $category ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Category not found.',
					array(
						'id' => "The category with the ID '{$category_id}' does not exist.",
					),
					404,
					'/ai-smart-sales/v1/product-categories/' . $category_id
				),
				404
			);
		}

		$deleted = wp_delete_term( $category_id, 'product_cat' );

		if ( is_wp_error( $deleted ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to delete category.',
					array(
						'error' => $deleted->get_error_message(),
					),
					500,
					$request->get_route()
				),
				500
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Category deleted successfully.',
				'data'    => array( 'category_id' => $category_id ),
			),
			200
		);
	}
}

new CategoriesApiHandler();
