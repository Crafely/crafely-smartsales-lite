<?php
/**
 * Crafely SmartSales Lite Product API Handler
 *
 * @package CrafelySmartSalesLite
 */

namespace CSMSL\Includes\Api\Products;

use CSMSL\Includes\Api\BaseApiHandler;
use WP_Query;
use WP_REST_Response;
use WC_Product_Simple;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * ProductApiHandler class
 * Handles REST API requests related to products.
 */
class ProductApiHandler extends BaseApiHandler {

	/**
	 * Register REST API routes for product management.
	 */
	public function register_routes() {
		register_rest_route(
			'ai-smart-sales/v1',
			'/products',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_products' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/products/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_product' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/products/(?P<id>\d+)/variations',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_product_variations' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/products/(?P<id>\d+)',
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_product' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/products/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_product' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/products',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_product' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/products/bulk-delete',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'bulk_delete_products' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}
	/**
	 * Check if the user has permission to access the API endpoints.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return bool True if the user has permission, false otherwise.
	 */
	public function check_permission( $request ) {
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
	 * Format error response for REST API.
	 *
	 * @param string $message The error message.
	 * @param array  $errors Optional. Additional error details.
	 * @param int    $statusCode Optional. HTTP status code. Default is 400.
	 * @param string $path Optional. The API endpoint path.
	 */
	private function format_error_response( $message, $errors = array(), $statusCode = 400, $path = '' ) {
		$error = array();

		if ( is_array( $errors ) && ! empty( $errors ) && array_keys( $errors ) !== range( 0, count( $errors ) - 1 ) ) {
			$error = $errors;
		} else {
			$error = array(
				'error' => $message,
			);
		}

		return array(
			'success' => false,
			'message' => $message,
			'data'    => array(),
			'error'   => $error,
			'status'  => $statusCode,
			'path'    => $path,
		);
	}
	/**
	 * Format product response for REST API.
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return array Formatted product data.
	 */
	private function format_product_response( $product ) {
		$default_image_url = CSMSL_URL . 'assets/images/product.png';
		$default_sku       = 'N/A';
		$product_image_id  = $product->get_image_id();

		$image_url = $product_image_id ? wp_get_attachment_url( $product_image_id ) : $default_image_url;
		if ( empty( $image_url ) ) {
			$image_url = $default_image_url;
		}
		$sale_price = intval( $product->get_sale_price() );

		return array(
			'id'                => $product->get_id(),
			'name'              => $product->get_name(),
			'price'             => intval( $product->get_price() ),
			'regular_price'     => intval( $product->get_regular_price() ),
			'sale_price'        => $sale_price ? $sale_price : intval( $product->get_regular_price() ),
			// 'currency' => get_woocommerce_currency(),
			'stock'             => $product->get_manage_stock() ? intval( $product->get_stock_quantity() ) : null,
			'sku'               => $product->get_sku() ? $product->get_sku() : $default_sku,
			'featured'          => $product->is_featured(),
			'description'       => $product->get_description(),
			'short_description' => $product->get_short_description(),
			'status'            => $product->get_status(),
			'categories'        => wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'ids' ) ),
			'tags'              => $product->get_tag_ids(),
			'image_url'         => $image_url,
		);
	}
	/**
	 * Get products with pagination and search functionality.
	 *
	 * @param \WP_REST_Request $request The REST API request object.
	 */
	public function get_products( $request ) {
		$current_page = $request->get_param( 'current_page' ) ? intval( $request->get_param( 'current_page' ) ) : 1;
		$per_page     = $request->get_param( 'per_page' ) ? intval( $request->get_param( 'per_page' ) ) : 10;
		$search_query = $request->get_param( 'q' );

		$args = array(
			'post_type'      => 'product',
			'post_status'    => $request->get_param( 'status' ) ? $request->get_param( 'status' ) : 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $current_page,
			'orderby'        => $request->get_param( 'orderby' ) ? $request->get_param( 'orderby' ) : 'date',
			'order'          => $request->get_param( 'order' ) ? $request->get_param( 'order' ) : 'DESC',
		);

		// Add search functionality.
		if ( ! empty( $search_query ) ) {
			$args['s'] = sanitize_text_field( $search_query );

			// Add meta query for SKU search.
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query.
			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => '_sku',
					'value'   => $search_query,
					'compare' => 'LIKE',
				),
			);

			// Remove default WP search behavior.
			remove_filter( 'posts_search', 'relevanssi_prevent_default_request', 10 );

			// Add custom search filter.
			add_filter(
				'posts_search',
				function ( $search, $query ) use ( $search_query ) {
					global $wpdb;

					if ( ! empty( $search ) && ! empty( $query->query_vars['s'] ) ) {
						$like = '%' . $wpdb->esc_like( $search_query ) . '%';

						// Search in title, excerpt, content, and SKU.
						$search = $wpdb->prepare(
							" AND (
                            {$wpdb->posts}.post_title LIKE %s 
                            OR {$wpdb->posts}.post_excerpt LIKE %s 
                            OR {$wpdb->posts}.post_content LIKE %s 
                            OR EXISTS (
                                SELECT 1 
                                FROM {$wpdb->postmeta} 
                                WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID 
                                AND {$wpdb->postmeta}.meta_key = '_sku' 
                                AND {$wpdb->postmeta}.meta_value LIKE %s
                            )
                        )",
							$like,
							$like,
							$like,
							$like
						);
					}
					return $search;
				},
				10,
				2
			);

			// Also search in product categories and tags.
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query.
			$args['tax_query'] = array(
				'relation' => 'OR',
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'name',
					'terms'    => $search_query,
					'operator' => 'LIKE',
				),
				array(
					'taxonomy' => 'product_tag',
					'field'    => 'name',
					'terms'    => $search_query,
					'operator' => 'LIKE',
				),
			);
		}

		// Validate orderby parameter.
		$valid_orderby_values = array( 'date', 'title', 'price', 'popularity' );
		if ( ! in_array( $args['orderby'], $valid_orderby_values, true ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Invalid orderby value.',
					array(
						'orderby' => "The orderby value '{$args['orderby']}' is not supported.",
					),
					400,
					$request->get_route()
				),
				400
			);
		}

		// Validate order parameter.
		$valid_order_values = array( 'ASC', 'DESC' );
		if ( ! in_array( strtoupper( $args['order'] ), $valid_order_values, true ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Invalid order value.',
					array(
						'order' => "The order value '{$args['order']}' is not supported.",
					),
					400,
					$request->get_route()
				),
				400
			);
		}

		// Validate category parameter.
		$category = $request->get_param( 'category');
		if ( ! empty( $category ) ) {
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query.
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => $category,
				),
			);
		}

		$query    = new WP_Query( $args );
		$products = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$product    = wc_get_product( get_the_ID() );
				$products[] = $this->format_product_response( $product );
			}
			wp_reset_postdata();
		}

		if ( empty( $products ) ) {
			return new WP_REST_Response(
				array(
					'success'    => true,
					'message'    => 'No products found.',
					'data'       => array(),
					'pagination' => array(
						'total_products' => 0,
						'total_pages'    => 0,
						'current_page'   => $current_page,
						'per_page'       => $per_page,
					),
				),
				200
			);
		}
		$total_products = $query->found_posts;
		$total_pages    = $query->max_num_pages;

		return new WP_REST_Response(
			array(
				'success'    => true,
				'message'    => 'Products retrieved successfully.',
				'data'       => $products,
				'pagination' => array(
					'total_products' => $total_products,
					'total_pages'    => $total_pages,
					'current_page'   => $current_page,
					'per_page'       => $per_page,
				),
			),
			200
		);
	}

	/**
	 * Get a single product by ID.
	 *
	 * @param array $data The request data containing the product ID.
	 *
	 * @return WP_REST_Response The response containing the product data or an error message.
	 */
	public function get_product( $data ) {
		$product_id = $data['id'];
		$product    = wc_get_product( $product_id );

		if ( ! $product ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Product not found.',
					array(
						'id' => "The product with the ID '{$product_id}' does not exist.",
					),
					404,
					'/ai-smart-sales/v1/products/' . $product_id
				),
				404
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Product retrieved successfully.',
				'data'    => $this->format_product_response( $product ),
			),
			200
		);
	}
	/**
	 * Get product variations by product ID.
	 *
	 * @param \WP_REST_Request $request The REST API request object containing the product ID and update data.
	 *
	 * @return WP_REST_Response The response containing the product variations or an error message.
	 */
	public function create_product( $request ) {
		$data = $request->get_json_params();

		// Define required fields and their error messages.
		$required_fields = array(
			'name'          => 'name is required.',
			'sku'           => 'sku is required.',
			'regular_price' => 'regular_price is required.',
			'stock'         => 'stock is required.',
			'status'        => 'status is required.',
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
					'data'    => array(),
					'error'   => $errors,
				),
				400
			);
		}

		// Validate regular_price and sale_price.
		if ( ! is_numeric( $data['regular_price'] ) || $data['regular_price'] < 0 ) {
			$errors['regular_price'] = 'Regular price must be a non-negative number.';
		}

		if ( isset( $data['sale_price'] ) && ( ! is_numeric( $data['sale_price'] ) || $data['sale_price'] < 0 ) ) {
			$errors['sale_price'] = 'Sale price must be a non-negative number.';
		}

		// Validate sale_price against regular_price.
		if ( isset( $data['sale_price'] ) && $data['sale_price'] >= $data['regular_price'] ) {
			$errors['sale_price'] = 'Sale price must be less than the regular price.';
		}

		// Validate status.
		$valid_statuses = array( 'draft', 'pending', 'private', 'publish' );
		if ( ! in_array( $data['status'], $valid_statuses, true ) ) {
			$errors['status'] = "The status '{$data['status']}' is not supported.";
		}

		// Check if SKU already exists.
		$existing_product_id = wc_get_product_id_by_sku( $data['sku'] );
		if ( $existing_product_id ) {
			$errors['sku'] = "A product with the SKU '{$data['sku']}' already exists.";
		}

		// If there are validation errors, return them.
		if ( ! empty( $errors ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Validation failed.',
					'data'    => array(),
					'error'   => $errors,
				),
				400
			);
		}

		// Create the product.
		$product = new WC_Product_Simple();
		$product->set_name( $data['name'] );
		$product->set_sku( $data['sku'] );
		$product->set_regular_price( $data['regular_price'] );

		// Set sale price if provided and valid.
		if ( isset( $data['sale_price'] ) && $data['sale_price'] < $data['regular_price'] ) {
			$product->set_sale_price( $data['sale_price'] );
		} else {
			$product->set_sale_price( '' );
		}

		$product->set_status( $data['status'] );

		// Enable stock management and set stock quantity (only if product supports it).
		if ( $product->supports( 'stock' ) ) {
			$product->set_manage_stock( true );
			$product->set_stock_quantity( $data['stock'] );
		}

		// Set optional fields if provided.
		if ( isset( $data['description'] ) ) {
			$product->set_description( $data['description'] );
		}
		if ( isset( $data['short_description'] ) ) {
			$product->set_short_description( $data['short_description'] );
		}
		if ( isset( $data['categories'] ) && is_array( $data['categories'] ) ) {
			$product->set_category_ids( $data['categories'] );
		}
		if ( isset( $data['tags'] ) && is_array( $data['tags'] ) ) {
			$product->set_tag_ids( $data['tags'] );
		}
		if ( isset( $data['attributes'] ) && is_array( $data['attributes'] ) ) {
			$product->set_attributes( $data['attributes'] );
		}
		if ( isset( $data['image'] ) && ( is_array( $data['image'] ) || is_numeric( $data['image'] ) ) ) {
			$image_id = is_array( $data['image'] ) ? $data['image'][0] : $data['image'];
			$product->set_image_id( $image_id );
		}
		if ( isset( $data['featured'] ) ) {
			$product->set_featured( $data['featured'] );
		}

		// Save the product.
		$product_id = $product->save();

		if ( ! $product_id ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Failed to create product.',
					'data'    => array(),
					'error'   => array(
						'server' => 'The product could not be created.',
					),
				),
				500
			);
		}

		// Refresh the product object to ensure all data is up-to-date.
		$product = wc_get_product( $product_id );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Product created successfully.',
				'data'    => $this->format_product_response( $product ),
			),
			201
		);
	}
	/**
	 * Update a WooCommerce product by product ID.
	 *
	 * Handles REST API requests to update product data.
	 *
	 * @param \WP_REST_Request $request The REST API request object containing the product ID and update data.
	 *
	 * @return \WP_REST_Response The response containing success or error information.
	 */
	public function update_product( $request ) {
		$product_id = $request->get_param( 'id' );
		$product    = wc_get_product( $product_id );

		if ( ! $product ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Product not found.',
					array(
						'id' => "The product with the ID '{$product_id}' does not exist.",
					),
					404,
					'/ai-smart-sales/v1/products/' . $product_id
				),
				404
			);
		}

		$data = $request->get_json_params();

		// Fix: Ensure regular_price and sale_price are properly updated.
		if ( isset( $data['regular_price'] ) && is_numeric( $data['regular_price'] ) && $data['regular_price'] > 0 ) {
			$product->set_regular_price( (float) $data['regular_price'] );
		} elseif ( $product->get_regular_price() === '' || $product->get_regular_price() === null ) {
			$product->set_regular_price( 0 );
		}

		if ( isset( $data['sale_price'] ) && is_numeric( $data['sale_price'] ) && $data['sale_price'] > 0 ) {
			$product->set_sale_price( (float) $data['sale_price'] );
		} elseif ( $product->get_sale_price() === '' || $product->get_sale_price() === null ) {
			$product->set_sale_price( '' );
		}

		// Other updates.
		if ( isset( $data['name'] ) ) {
			$product->set_name( $data['name'] );
		}

		// Fix stock update logic.
		if ( isset( $data['stock'] ) ) {
			$product->set_manage_stock( true );
			$product->set_stock_quantity( (int) $data['stock'] );
			$product->set_stock_status( 'instock' );
		}

		if ( isset( $data['sku'] ) && ! empty( $data['sku'] ) ) {
			$product->set_sku( $data['sku'] );
		}
		if ( isset( $data['description'] ) ) {
			$product->set_description( $data['description'] );
		}
		if ( isset( $data['short_description'] ) ) {
			$product->set_short_description( $data['short_description'] );
		}
		if ( isset( $data['status'] ) ) {
			$product->set_status( $data['status'] );
		}
		if ( isset( $data['categories'] ) ) {
			$product->set_category_ids( $data['categories'] );
		}
		if ( isset( $data['tags'] ) ) {
			$product->set_tag_ids( $data['tags'] );
		}
		if ( isset( $data['attributes'] ) ) {
			$product->set_attributes( $data['attributes'] );
		}
		if ( isset( $data['image'] ) && ( is_array( $data['image'] ) || is_numeric( $data['image'] ) ) ) {
			$image_id = is_array( $data['image'] ) ? $data['image'][0] : $data['image'];
			$product->set_image_id( $image_id );
		}
		if ( isset( $data['featured'] ) ) {
			$product->set_featured( $data['featured'] );
		}

		// Save the product.
		$updated_product_id = $product->save();
		wc_delete_product_transients( $product_id );

		if ( ! $updated_product_id ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to update product.',
					array(
						'server' => 'The product could not be updated.',
					),
					500,
					$request->get_route()
				),
				500
			);
		}

		// Refresh the product object to ensure all data is up-to-date.
		$product = wc_get_product( $updated_product_id );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Product updated successfully.',
				'data'    => $this->format_product_response( $product ),
			),
			200
		);
	}
	/**
	 * Delete a WooCommerce product by product ID.
	 *
	 * Handles REST API requests to delete a product.
	 *
	 * @param \WP_REST_Request $request The REST API request object containing the product ID.
	 *
	 * @return \WP_REST_Response The response containing success or error information.
	 */
	public function delete_product( $request ) {
		$product_id = $request->get_param( 'id' );
		$product    = wc_get_product( $product_id );

		if ( ! $product ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Product not found.',
					array(
						'id' => "The product with the ID '{$product_id}' does not exist.",
					),
					404,
					'/ai-smart-sales/v1/products/' . $product_id
				),
				404
			);
		}

		$product->delete();

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Product deleted successfully.',
				'data'    => array( 'product_id' => $product_id ),
			),
			200
		);
	}
	/**
	 * Get product variations by product ID.
	 *
	 * @param array $data The request data containing the product ID.
	 *
	 * @return WP_REST_Response The response containing the product variations or an error message.
	 */
	public function get_product_variations( $data ) {
		$product_id = $data['id'];
		$product    = wc_get_product( $product_id );

		if ( ! $product ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Product not found.',
					array(
						'id' => "The product with the ID '{$product_id}' does not exist.",
					),
					404,
					'/ai-smart-sales/v1/products/' . $product_id . '/variations'
				),
				404
			);
		}

		if ( ! $product->is_type( 'variable' ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Not a variable product.',
					array(
						'type' => "The product with the ID '{$product_id}' is not a variable product.",
					),
					400,
					'/ai-smart-sales/v1/products/' . $product_id . '/variations'
				),
				400
			);
		}

		$variations = $product->get_children();
		$data       = array();

		foreach ( $variations as $variation_id ) {
			$variation = wc_get_product( $variation_id );
			$data[]    = array(
				'id'         => $variation_id,
				'name'       => $variation->get_name(),
				'price'      => $variation->get_price(),
				'stock'      => $variation->get_stock_quantity(),
				'sku'        => $variation->get_sku(),
				'attributes' => $variation->get_attributes(),
				'image_url'  => wp_get_attachment_url( $variation->get_image_id() ),
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Product variations retrieved successfully.',
				'data'    => $data,
			),
			200
		);
	}

	/**
	 * Bulk delete products by IDs.
	 *
	 * @param WP_REST_Request $request The REST request object containing product IDs.
	 * @return WP_REST_Response The response containing success or error information.
	 */
	public function bulk_delete_products( $request ) {
		$product_ids = $request->get_param( 'ids' );

		if ( empty( $product_ids ) || ! is_array( $product_ids ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Invalid product IDs.',
					array(
						'ids' => 'Product IDs must be provided as an array.',
					),
					400,
					$request->get_route()
				),
				400
			);
		}

		$deleted_products = array();
		$errors           = array();

		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( $product ) {
				$product->delete();
				$deleted_products[] = $product_id;
			} else {
				$errors[] = "Product with ID {$product_id} not found.";
			}
		}

		if ( ! empty( $errors ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Some products could not be deleted.',
					'data'    => array(
						'deleted_products' => $deleted_products,
						'errors'           => $errors,
					),
				),
				207
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Products deleted successfully.',
				'data'    => array(
					'deleted_products' => $deleted_products,
				),
			),
			200
		);
	}
}

new ProductApiHandler();
