<?php
/**
 * Crafely SmartSales Lite Inventory API Handler
 *
 * @package CrafelySmartSalesLite
 */

namespace CSMSL\Includes\Api\Inventory;

use CSMSL\Includes\Api\BaseApiHandler;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * InventoryApiHandler class
 * Handles REST API requests related to inventory.
 */
class InventoryApiHandler extends BaseApiHandler {

	/**
	 * Register REST API routes for inventory management.
	 */
	public function register_routes() {
		register_rest_route(
			'ai-smart-sales/v1',
			'/inventory/stock',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'list_all_stock' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/inventory/stock/(?P<product_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_stock_for_product' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/inventory/stock/(?P<product_id>\d+)/(?P<outlet_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_stock_for_product_at_outlet' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/inventory/stock/adjust',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'adjust_stock' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/inventory/stock/adjust/bulk',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'bulk_adjust_stock' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/inventory/stock/transfer',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'transfer_stock' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/inventory/report/low-stock',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_low_stock_report' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/inventory/report/movements',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_stock_movement_history' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	/**
	 * Check if the user has permission to access the API endpoints.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return bool|WP_Error True if the user has permission, false otherwise.
	 */
	public function check_permission( $request ) {
		// Check if user is logged in and has appropriate capabilities.
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You must be logged in to access this endpoint.', 'crafely-smartsales-lite' ),
				array( 'status' => 401 )
			);
		}

		// Get current user.
		$user = wp_get_current_user();

		// Define allowed roles for inventory management.
		$allowed_roles = array( 'administrator', 'csmsl_pos_outlet_manager' );
		$user_roles    = (array) $user->roles;

		// For 'adjust_stock' and 'transfer_stock', only administrator and csmsl_pos_outlet_manager are allowed.
		if ( in_array( $request->get_route(), array( 'ai-smart-sales/v1/inventory/stock/adjust', 'ai-smart-sales/v1/inventory/stock/transfer', 'ai-smart-sales/v1/inventory/stock/adjust/bulk' ), true ) ) {
			if ( ! array_intersect( $allowed_roles, $user_roles ) ) {
				return new WP_Error(
					'rest_forbidden',
					__( 'You do not have permission to adjust or transfer stock.', 'crafely-smartsales-lite' ),
					array( 'status' => 403 )
				);
			}
		}

		// For viewing endpoints, cashiers can view for their assigned outlet/counter.
		// This logic will be further refined in the specific endpoint methods.
		$view_roles = array_merge( $allowed_roles, array( 'csmsl_pos_cashier' ) );
		if ( in_array( $request->get_route(), array( 'ai-smart-sales/v1/inventory/stock', 'ai-smart-sales/v1/inventory/stock/(?P<product_id>\d+)', 'ai-smart-sales/v1/inventory/stock/(?P<product_id>\d+)/(?P<outlet_id>\d+)', 'ai-smart-sales/v1/inventory/report/low-stock', 'ai-smart-sales/v1/inventory/report/movements' ), true ) ) {
			if ( ! array_intersect( $view_roles, $user_roles ) ) {
				return new WP_Error(
					'rest_forbidden',
					__( 'You do not have permission to view this report.', 'crafely-smartsales-lite' ),
					array( 'status' => 403 )
				);
			}
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
		$error_data = array();

		if ( is_array( $errors ) && ! empty( $errors ) && array_keys( $errors ) !== range( 0, count( $errors ) - 1 ) ) {
			$error_data = $errors;
		} else {
			$error_data = array(
				'error' => $message,
			);
		}

		$response_data = array(
			'success' => false,
			'message' => $message,
			'data'    => array(),
			'error'   => $error_data,
			'status'  => $statusCode,
			'path'    => $path,
		);

		return new WP_REST_Response( $response_data, $statusCode );
	}

	/**
	 * List all stock records.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The response containing stock data or an error message.
	 * @since 1.0.0
	 */
	public function list_all_stock( WP_REST_Request $request ) {
		global $wpdb;
		$table_name_inventory = $wpdb->prefix . 'smartsales_inventory';

		$product_id   = $request->get_param( 'product_id' ) ? intval( $request->get_param( 'product_id' ) ) : 0;
		$outlet_id    = $request->get_param( 'outlet_id' ) ? intval( $request->get_param( 'outlet_id' ) ) : 0;
		$current_page = $request->get_param( 'current_page' ) ? intval( $request->get_param( 'current_page' ) ) : 1;
		$per_page     = $request->get_param( 'per_page' ) ? intval( $request->get_param( 'per_page' ) ) : 10;

		$wc_query_args = array(
			'status'         => 'publish',
			'limit'          => -1, // Get all products for initial filtering.
			'return'         => 'ids',
			'paginate'       => false,
			'order_by'       => 'ID',
			'order'          => 'ASC',
		);

		if ( ! empty( $product_id ) ) {
			$wc_query_args['include'] = array( intval( $product_id ) );
		}

		$all_wc_product_ids = wc_get_products( $wc_query_args );

		if ( empty( $all_wc_product_ids ) ) {
			return new WP_REST_Response(
				array(
					'success'    => true,
					'message'    => 'No products found.',
					'data'       => array(),
					'pagination' => array(
						'total_items'  => 0,
						'total_pages'  => 0,
						'current_page' => $current_page,
						'per_page'     => $per_page,
					),
				),
				200
			);
		}

		$final_stock_data = array();
		$assigned_outlets = array();

		$user       = wp_get_current_user();
		$is_cashier = in_array( 'csmsl_pos_cashier', (array) $user->roles, true );

		if ( $is_cashier ) {
			/**
			 * Filters the assigned outlet IDs for a user.
			 *
			 * @param array $assigned_outlets The assigned outlet IDs.
			 * @param int   $user_id          The user ID.
			 */
			$assigned_outlets = (array) get_user_meta( $user->ID, 'csmsl_assigned_outlet_ids', true );

			if ( empty( $assigned_outlets ) ) {
				return new WP_REST_Response(
					$this->format_error_response(
						'Access denied. No outlets assigned.',
						array(),
						403,
						$request->get_route()
					),
					403
				);
			}
		}

		foreach ( $all_wc_product_ids as $p_id ) {
			$product = wc_get_product( $p_id );

			if ( ! $product ) {
				continue;
			}

			$wc_product_stock = $product->get_manage_stock() ? intval( $product->get_stock_quantity() ) : null;

			$inventory_where = array( 'product_id = %d' );
			$inventory_args  = array( $p_id );

			if ( ! empty( $outlet_id ) ) {
				$inventory_where[] = 'outlet_id = %d';
				$inventory_args[]  = intval( $outlet_id );
			}

			if ( $is_cashier && ! empty( $assigned_outlets ) ) {
				$assigned_outlets_string = implode( ', ', array_map( 'intval', $assigned_outlets ) );
				$inventory_where[]       = 'outlet_id IN ( ' . $assigned_outlets_string . ' )';
			}

			$inventory_where_sql = count( $inventory_where ) > 0 ? ' WHERE ' . implode( ' AND ', $inventory_where ) : '';

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectSql.DirectSql,
			// WordPress.DB.SlowDBQuery.slow_db_query_Unprepared
			$inventory_records = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT stock, threshold, outlet_id FROM {$table_name_inventory}{$inventory_where_sql}",
					$inventory_args
				),
				ARRAY_A
			);

			$product_entry = array(
				'product_id'   => $p_id,
				'product_name' => $product->get_name(),
				'global_woocommerce_stock' => $wc_product_stock, // Always include global WooCommerce stock.
				'outlets_stock'      => array(),
			);

			if ( ! empty( $inventory_records ) ) {
				foreach ( $inventory_records as $record ) {
					$product_entry['outlets_stock'][] = array(
						'outlet_id' => intval( $record['outlet_id'] ),
						'stock'     => intval( $record['stock'] ),
						'threshold' => intval( $record['threshold'] ),
					);
				}
			} // Removed the elseif for global_woocommerce_stock as it's now always included.

			// Always add the product to the final results.
			$final_stock_data[] = $product_entry;
		}

		$total_items = count( $final_stock_data );
		$total_pages = ceil( $total_items / $per_page );

		$offset            = ( $current_page - 1 ) * $per_page;
		$paginated_results = array_slice( $final_stock_data, $offset, $per_page );

		if ( empty( $paginated_results ) ) {
			return new WP_REST_Response(
				array(
					'success'    => true,
					'message'    => 'No stock records found.',
					'data'       => array(),
					'pagination' => array(
						'total_items'  => 0,
						'total_pages'  => 0,
						'current_page' => $current_page,
						'per_page'     => $per_page,
					),
				),
				200
			);
		}

		return new WP_REST_Response(
			array(
				'success'    => true,
				'message'    => 'Stock records retrieved successfully.',
				'data'       => $paginated_results,
				'pagination' => array(
					'total_items'  => (int) $total_items,
					'total_pages'  => (int) $total_pages,
					'current_page' => (int) $current_page,
					'per_page'     => (int) $per_page,
				),
			),
			200
		);
	}

	/**
	 * Adjust stock for a product at a specific outlet.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The response containing success or error information.
	 * @since 1.0.0
	 */
	public function adjust_stock( WP_REST_Request $request ) {
		global $wpdb;
		$table_name_inventory = $wpdb->prefix . 'smartsales_inventory';
		$table_name_movements = $wpdb->prefix . 'smartsales_inventory_movements';

		$data = $request->get_json_params();

		$product_id = isset( $data['product_id'] ) ? intval( $data['product_id'] ) : 0;
		$outlet_id  = isset( $data['outlet_id'] ) ? intval( $data['outlet_id'] ) : 0;
		$quantity   = isset( $data['quantity'] ) ? intval( $data['quantity'] ) : 0;
		$reason     = isset( $data['reason'] ) ? sanitize_text_field( $data['reason'] ) : '';
		$threshold  = isset( $data['threshold'] ) ? intval( $data['threshold'] ) : null;

		$errors = array();

		if ( empty( $product_id ) ) {
			$errors['product_id'] = 'Product ID is required.';
		}
		if ( empty( $outlet_id ) ) {
			$errors['outlet_id'] = 'Outlet ID is required.';
		}
		if ( empty( $quantity ) || ! is_numeric( $quantity ) ) {
			$errors['quantity'] = 'Quantity must be a non-zero number. Use negative values for removals.';
		}
		if ( empty( $reason ) ) {
			$errors['reason'] = 'Reason for adjustment is required.';
		}

		// If threshold is not provided, use WooCommerce default.
		if ( is_null( $threshold ) ) {
			$threshold = intval( get_option( 'woocommerce_notify_low_stock_amount' ) );
		}

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

		// Check if product and outlet exist (optional, but good practice).
		// For simplicity, we'll assume they exist for now.

		// Update or insert inventory record.
		// phpcs:ignore WordPress.DB.DirectSql.DirectSql
		$existing_stock = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, stock FROM {$table_name_inventory} WHERE product_id = %d AND outlet_id = %d",
				$product_id,
				$outlet_id
			)
		);

		if ( $existing_stock ) {
			$new_stock = $existing_stock->stock + $quantity;

			if ( $new_stock < 0 ) {
				return new WP_REST_Response(
					$this->format_error_response(
						'Insufficient stock.',
						array(
							'available_stock' => $existing_stock->stock,
							'requested_adjustment' => $quantity,
						),
						400,
						$request->get_route()
					),
					400
				);
			}

			// phpcs:ignore WordPress.DB.DirectSql.DirectSql
			$updated = $wpdb->update(
				$table_name_inventory,
				array( 'stock' => $new_stock, 'threshold' => $threshold ),
				array(
					'id' => $existing_stock->id,
				),
				array( '%d' ),
				array( '%d' )
			);
		} else {
			$new_stock = $quantity;
			// phpcs:ignore WordPress.DB.DirectSql.DirectSql
			$updated = $wpdb->insert(
				$table_name_inventory,
				array(
					'product_id' => $product_id,
					'outlet_id'  => $outlet_id,
					'stock'      => $new_stock,
					'threshold'  => $threshold,
				),
				array( '%d', '%d', '%d', '%d' )
			);
		}

		if ( false === $updated ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to adjust stock.',
					array(
						'db_error' => $wpdb->last_error,
					),
					500,
					$request->get_route()
				),
				500
			);
		}

		// Log stock movement.
		$user_id = get_current_user_id();
		// phpcs:ignore WordPress.DB.DirectSql.DirectSql
		$logged = $wpdb->insert(
			$table_name_movements,
			array(
				'product_id'  => $product_id,
				'outlet_id'   => $outlet_id,
				'type'        => 'adjustment',
				'quantity'    => $quantity,
				'reason'      => $reason,
				'user_id'     => $user_id,
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%d', '%s', '%d', '%s' )
		);

		if ( false === $logged ) {
			// Log the error, but don't fail the entire request as stock was adjusted.
			csmsl_log(
				'Failed to log stock adjustment movement for product ' . $product_id . ' at outlet ' . $outlet_id . ': ' . $wpdb->last_error,
				'error'
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => "Stock adjusted for product {$product_id} at outlet {$outlet_id}. New stock: {$new_stock}",
				'data'    => array(
					'product_id' => $product_id,
					'outlet_id'  => $outlet_id,
					'new_stock'  => $new_stock,
				),
			),
			200
		);
	}

	/**
	 * Transfer stock between outlets.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The response containing success or error information.
	 * @since 1.0.0
	 */
	public function transfer_stock( WP_REST_Request $request ) {
		global $wpdb;
		$table_name_inventory = $wpdb->prefix . 'smartsales_inventory';
		$table_name_movements = $wpdb->prefix . 'smartsales_inventory_movements';

		$data = $request->get_json_params();

		$product_id     = isset( $data['product_id'] ) ? intval( $data['product_id'] ) : 0;
		$from_outlet_id = isset( $data['from_outlet_id'] ) ? intval( $data['from_outlet_id'] ) : 0;
		$to_outlet_id   = isset( $data['to_outlet_id'] ) ? intval( $data['to_outlet_id'] ) : 0;
		$quantity       = isset( $data['quantity'] ) ? intval( $data['quantity'] ) : 0;
		$reason         = isset( $data['reason'] ) ? sanitize_text_field( $data['reason'] ) : '';

		$errors = array();

		if ( empty( $product_id ) ) {
			$errors['product_id'] = 'Product ID is required.';
		}
		if ( empty( $from_outlet_id ) ) {
			$errors['from_outlet_id'] = 'From Outlet ID is required.';
		}
		if ( empty( $to_outlet_id ) ) {
			$errors['to_outlet_id'] = 'To Outlet ID is required.';
		}
		if ( empty( $quantity ) || ! is_numeric( $quantity ) || $quantity <= 0 ) {
			$errors['quantity'] = 'Quantity must be a positive number.';
		}
		if ( empty( $reason ) ) {
			$errors['reason'] = 'Reason for transfer is required.';
		}
		if ( $from_outlet_id === $to_outlet_id ) {
			$errors['outlets'] = 'Source and destination outlets cannot be the same.';
		}

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

		// Check stock at source outlet.
		// phpcs:ignore WordPress.DB.DirectSql.DirectSql
		$source_stock = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, stock FROM {$table_name_inventory} WHERE product_id = %d AND outlet_id = %d",
				$product_id,
				$from_outlet_id
			)
		);

		if ( ! $source_stock || $source_stock->stock < $quantity ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Insufficient stock at source outlet.',
					array(
						'available_stock' => $source_stock ? $source_stock->stock : 0,
						'requested_quantity' => $quantity,
					),
					400,
					$request->get_route()
				),
				400
			);
		}

		// Start transaction (if supported).
		// $wpdb->query( 'START TRANSACTION' ); // Not fully reliable in WordPress.

		$new_source_stock = $source_stock->stock - $quantity;
		// Deduct from source.
		// phpcs:ignore WordPress.DB.DirectSql.DirectSql
		$updated_source = $wpdb->update(
			$table_name_inventory,
			array( 'stock' => $new_source_stock ),
			array(
				'id' => $source_stock->id,
			),
			array( '%d' ),
			array( '%d' )
		);

		// Add to destination.
		// phpcs:ignore WordPress.DB.DirectSql.DirectSql
		$destination_stock = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, stock FROM {$table_name_inventory} WHERE product_id = %d AND outlet_id = %d",
				$product_id,
				$to_outlet_id
			)
		);

		if ( $destination_stock ) {
			$new_destination_stock = $destination_stock->stock + $quantity;
			// phpcs:ignore WordPress.DB.DirectSql.DirectSql
			$updated_destination = $wpdb->update(
				$table_name_inventory,
				array( 'stock' => $new_destination_stock ),
				array(
					'id' => $destination_stock->id,
				),
				array( '%d' ),
				array( '%d' )
			);
		} else {
			$new_destination_stock = $quantity;
			// phpcs:ignore WordPress.DB.DirectSql.DirectSql
			$updated_destination = $wpdb->insert(
				$table_name_inventory,
				array(
					'product_id' => $product_id,
					'outlet_id'  => $to_outlet_id,
					'stock'      => $new_destination_stock,
				),
				array( '%d', '%d', '%d' )
			);
		}

		if ( false === $updated_source || false === $updated_destination ) {
			// $wpdb->query( 'ROLLBACK' ); // Not fully reliable.
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to transfer stock due to database error.',
					array(
						'db_error' => $wpdb->last_error,
					),
					500,
					$request->get_route()
				),
				500
			);
		}

		// Log stock movement for source.
		$user_id = get_current_user_id();
		// phpcs:ignore WordPress.DB.DirectSql.DirectSql
		$logged_source = $wpdb->insert(
			$table_name_movements,
			array(
				'product_id'        => $product_id,
				'outlet_id'         => $from_outlet_id,
				'type'              => 'transfer',
				'quantity'          => -$quantity,
				'reason'            => $reason,
				'user_id'           => $user_id,
				'created_at'        => current_time( 'mysql' ),
				'related_outlet_id' => $to_outlet_id,
			),
			array( '%d', '%d', '%s', '%d', '%s', '%d', '%s', '%d' )
		);

		// Log stock movement for destination.
		// phpcs:ignore WordPress.DB.DirectSql.DirectSql
		$logged_destination = $wpdb->insert(
			$table_name_movements,
			array(
				'product_id'        => $product_id,
				'outlet_id'         => $to_outlet_id,
				'type'              => 'transfer',
				'quantity'          => $quantity,
				'reason'            => $reason,
				'user_id'           => $user_id,
				'created_at'        => current_time( 'mysql' ),
				'related_outlet_id' => $from_outlet_id,
			),
			array( '%d', '%d', '%s', '%d', '%s', '%d', '%s', '%d' )
		);

		if ( false === $logged_source || false === $logged_destination ) {
			csmsl_log(
				'Failed to log stock transfer movement for product ' . $product_id . ': ' . $wpdb->last_error,
				'error'
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => "Transferred {$quantity} units of product {$product_id} from outlet {$from_outlet_id} to {$to_outlet_id}.",
				'data'    => array(
					'product_id'        => $product_id,
					'from_outlet_id'    => $from_outlet_id,
					'to_outlet_id'      => $to_outlet_id,
					'quantity'          => $quantity,
					'new_source_stock'  => $new_source_stock,
					'new_destination_stock' => $new_destination_stock,
				),
			),
			200
		);
	}

	/**
	 * Get low stock report.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The response containing low stock data or an error message.
	 * @since 1.0.0
	 */
	public function get_low_stock_report( WP_REST_Request $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'smartsales_inventory';

		$product_id   = $request->get_param( 'product_id' ) ? intval( $request->get_param( 'product_id' ) ) : 0;
		$outlet_id    = $request->get_param( 'outlet_id' ) ? intval( $request->get_param( 'outlet_id' ) ) : 0;
		$current_page = $request->get_param( 'current_page' ) ? intval( $request->get_param( 'current_page' ) ) : 1;
		$per_page     = $request->get_param( 'per_page' ) ? intval( $request->get_param( 'per_page' ) ) : 10;
		$offset       = ( $current_page - 1 ) * $per_page;

		$where_clauses = array();
		$prepare_args  = array();

		// Role-based access for cashiers.
		$user       = wp_get_current_user();
		$is_cashier = in_array( 'csmsl_pos_cashier', (array) $user->roles, true );

		$assigned_outlets = array();

		if ( $is_cashier ) {
			$assigned_outlets = (array) get_user_meta( $user->ID, 'csmsl_assigned_outlet_ids', true );
			if ( empty( $assigned_outlets ) ) {
				return new WP_REST_Response(
					$this->format_error_response(
						'Access denied. No outlets assigned.',
						array(),
						403,
						$request->get_route()
					),
					403
				);
			}
		}

		$final_low_stock_data = array();

		// Get WooCommerce's low stock amount threshold.
		$low_stock_threshold = get_option( 'woocommerce_notify_low_stock_amount' );

		// Scenario 1: Fetch all low stock products from WooCommerce if no specific product or outlet is requested.
		if ( empty( $product_id ) && empty( $outlet_id ) ) {
			$wc_products_args = array(
				'status'         => 'publish',
				'limit'          => -1, // Fetch all for internal filtering.
				'return'         => 'ids',
				'manage_stock'   => true, // Only consider products that manage stock.
			);

			$wc_all_managed_product_ids = wc_get_products( $wc_products_args );

			$wc_low_stock_product_ids = array();
			if ( ! empty( $wc_all_managed_product_ids ) ) {
				foreach ( $wc_all_managed_product_ids as $p_id ) {
					$product = wc_get_product( $p_id );
					if ( $product && $product->get_manage_stock() && intval( $product->get_stock_quantity() ) <= $low_stock_threshold ) {
						$wc_low_stock_product_ids[] = $p_id;
					}
				}
			}

			if ( ! empty( $wc_low_stock_product_ids ) ) {
				foreach ( $wc_low_stock_product_ids as $p_id ) {
					$product = wc_get_product( $p_id );

					if ( ! $product ) {
						continue;
					}

					$product_global_stock = $product->get_manage_stock() ? intval( $product->get_stock_quantity() ) : null;

					$inventory_where     = array( 'product_id = %d' );
					$inventory_args      = array( $p_id );

					if ( $is_cashier && ! empty( $assigned_outlets ) ) {
						$placeholders      = implode( ', ', array_fill( 0, count( $assigned_outlets ), '%d' ) );
						$inventory_where[] = "outlet_id IN ( {$placeholders} )";
						$inventory_args    = array_merge( $inventory_args, array_map( 'intval', $assigned_outlets ) );
					}

					$inventory_where_sql = count( $inventory_where ) > 0 ? ' WHERE ' . implode( ' AND ', $inventory_where ) : '';

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectSql.DirectSql,
					// WordPress.DB.SlowDBQuery.slow_db_query_Unprepared
					$inventory_records = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT outlet_id, stock, threshold FROM {$table_name}{$inventory_where_sql}",
							$inventory_args
						),
						ARRAY_A
					);

					$has_low_stock_in_any_outlet = false;
					$outlets_stock_data          = array();

					if ( ! empty( $inventory_records ) ) {
						foreach ( $inventory_records as $record ) {
							$outlet_stock = intval( $record['stock'] );
							$threshold    = intval( $record['threshold'] );

							if ( $outlet_stock < $threshold || $outlet_stock <= $low_stock_threshold ) {
								$has_low_stock_in_any_outlet = true;
							}
							$outlets_stock_data[] = array(
								'outlet_id' => intval( $record['outlet_id'] ),
								'stock'     => $outlet_stock,
								'threshold' => $threshold,
							);
						}
					}

					if ( $product_global_stock <= $low_stock_threshold || $has_low_stock_in_any_outlet ) {
						$final_low_stock_data[ $p_id ] = array(
							'product_id'             => $p_id,
							'product_name'           => $product->get_name(),
							'global_woocommerce_stock' => $product_global_stock,
							'outlets_stock'          => $outlets_stock_data,
						);
					}
				}
			}
		} else { // Scenario 2: Specific product_id or outlet_id provided.
			$where_clauses[] = '( stock < threshold OR stock <= ' . intval( $low_stock_threshold ) . ' ) ';

			if ( ! empty( $product_id ) ) {
				$where_clauses[] = 'product_id = %d';
				$prepare_args[]  = $product_id;
			}

			if ( ! empty( $outlet_id ) ) {
				$where_clauses[] = 'outlet_id = %d';
				$prepare_args[]  = $outlet_id;
			}

			if ( $is_cashier && ! empty( $assigned_outlets ) ) {
				$placeholders    = implode( ', ', array_fill( 0, count( $assigned_outlets ), '%d' ) );
				$where_clauses[] = "outlet_id IN ( {$placeholders} )";
				$prepare_args    = array_merge( $prepare_args, array_map( 'intval', $assigned_outlets ) );
			}

			$where_sql = count( $where_clauses ) > 0 ? ' WHERE ' . implode( ' AND ', $where_clauses ) : '';

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectSql.DirectSql,
			// WordPress.DB.SlowDBQuery.slow_db_query_Unprepared
			$inventory_records = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT product_id, outlet_id, stock, threshold FROM {$table_name}{$where_sql}",
					$prepare_args
				),
				ARRAY_A
			);

			if ( ! empty( $inventory_records ) ) {
				foreach ( $inventory_records as $record ) {
					$p_id    = intval( $record['product_id'] );
					$product = wc_get_product( $p_id );

					if ( ! $product ) {
						continue;
					}

					$product_name           = $product->get_name();
					$product_global_stock   = $product->get_manage_stock() ? intval( $product->get_stock_quantity() ) : null;
					$outlet_stock           = intval( $record['stock'] );
					$outlet_threshold       = intval( $record['threshold'] );

					$final_low_stock_data[ $p_id ] = array(
						'product_id'             => $p_id,
						'product_name'           => $product_name,
						'global_woocommerce_stock' => $product_global_stock,
						'outlets_stock'          => array( array( 'outlet_id' => intval( $record['outlet_id'] ), 'stock' => $outlet_stock, 'threshold' => $outlet_threshold ) ),
					);
				}
			}
		}

		$final_low_stock_data = array_values( $final_low_stock_data );
		$total_items          = count( $final_low_stock_data );
		$total_pages          = ceil( $total_items / $per_page );

		$paginated_results = array_slice( $final_low_stock_data, $offset, $per_page );

		if ( empty( $paginated_results ) ) {
			return new WP_REST_Response(
				array(
					'success'    => true,
					'message'    => 'No low stock records found.',
					'data'       => array(),
					'pagination' => array(
						'total_items'  => 0,
						'total_pages'  => 0,
						'current_page' => $current_page,
						'per_page'     => $per_page,
					),
				),
				200
			);
		}

		return new WP_REST_Response(
			array(
				'success'    => true,
				'message'    => 'Low stock records retrieved successfully.',
				'data'       => $paginated_results,
				'pagination' => array(
					'total_items'  => (int) $total_items,
					'total_pages'  => (int) $total_pages,
					'current_page' => (int) $current_page,
					'per_page'     => (int) $per_page,
				),
			),
			200
		);
	}

	/**
	 * Get stock movement history report.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The response containing movement history data or an error message.
	 * @since 1.0.0
	 */
	public function get_stock_movement_history( WP_REST_Request $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'smartsales_inventory_movements';

		$product_id   = $request->get_param( 'product_id' ) ? intval( $request->get_param( 'product_id' ) ) : 0;
		$outlet_id    = $request->get_param( 'outlet_id' ) ? intval( $request->get_param( 'outlet_id' ) ) : 0;
		$type         = $request->get_param( 'type' );
		$user_id      = $request->get_param( 'user_id' ) ? intval( $request->get_param( 'user_id' ) ) : 0;
		$start_date   = $request->get_param( 'start_date' );
		$end_date     = $request->get_param( 'end_date' );
		$current_page = $request->get_param( 'current_page' ) ? intval( $request->get_param( 'current_page' ) ) : 1;
		$per_page     = $request->get_param( 'per_page' ) ? intval( $request->get_param( 'per_page' ) ) : 10;
		$offset       = ( $current_page - 1 ) * $per_page;

		$where_clauses = array();
		$prepare_args  = array();

		if ( ! empty( $product_id ) ) {
			$where_clauses[] = 'product_id = %d';
			$prepare_args[]  = intval( $product_id );
		}

		if ( ! empty( $outlet_id ) ) {
			$where_clauses[] = 'outlet_id = %d';
			$prepare_args[]  = intval( $outlet_id );
		}

		if ( ! empty( $type ) ) {
			$where_clauses[] = 'type = %s';
			$prepare_args[]  = sanitize_text_field( $type );
		}

		if ( ! empty( $user_id ) ) {
			$where_clauses[] = 'user_id = %d';
			$prepare_args[]  = intval( $user_id );
		}

		if ( ! empty( $start_date ) ) {
			$where_clauses[] = 'created_at >= %s';
			$prepare_args[]  = sanitize_text_field( $start_date );
		}

		if ( ! empty( $end_date ) ) {
			$where_clauses[] = 'created_at <= %s';
			$prepare_args[]  = sanitize_text_field( $end_date ) . ' 23:59:59';
		}

		// Role-based access for cashiers.
		$user = wp_get_current_user();
		if ( in_array( 'csmsl_pos_cashier', (array) $user->roles, true ) ) {
			/**
			 * Filters the assigned outlet IDs for a user.
			 *
			 * @param array $assigned_outlets The assigned outlet IDs.
			 * @param int   $user_id          The user ID.
			 */
			$assigned_outlets = (array) get_user_meta( $user->ID, 'csmsl_assigned_outlet_ids', true );
			if ( empty( $assigned_outlets ) ) {
				return new WP_REST_Response(
					$this->format_error_response(
						'Access denied. No outlets assigned.',
						array(),
						403,
						$request->get_route()
					),
					403
				);
			}
			$placeholders    = implode( ', ', array_fill( 0, count( $assigned_outlets ), '%d' ) );
			$where_clauses[] = "outlet_id IN ( {$placeholders} )";
			$prepare_args    = array_merge( $prepare_args, array_map( 'intval', $assigned_outlets ) );
		}

		$where_sql = count( $where_clauses ) > 0 ? ' WHERE ' . implode( ' AND ', $where_clauses ) : '';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectSql.DirectSql,
		// WordPress.DB.SlowDBQuery.slow_db_query_Unprepared
		$total_items = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$table_name}{$where_sql}", $prepare_args ) );
		$total_pages = ceil( $total_items / $per_page );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectSql.DirectSql,
		// WordPress.DB.SlowDBQuery.slow_db_query_Unprepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT product_id, outlet_id, type, quantity, reason, user_id, created_at, related_outlet_id FROM {$table_name}{$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d",
				array_merge( $prepare_args, array( $per_page, $offset ) )
			),
			ARRAY_A
		);

		if ( empty( $results ) ) {
			return new WP_REST_Response(
				array(
					'success'    => true,
					'message'    => 'No stock movements found.',
					'data'       => array(),
					'pagination' => array(
						'total_items'  => 0,
						'total_pages'  => 0,
						'current_page' => $current_page,
						'per_page'     => $per_page,
					),
				),
				200
			);
		}

		return new WP_REST_Response(
			array(
				'success'    => true,
				'message'    => 'Stock movements retrieved successfully.',
				'data'       => $results,
				'pagination' => array(
					'total_items'  => (int) $total_items,
					'total_pages'  => (int) $total_pages,
					'current_page' => (int) $current_page,
					'per_page'     => (int) $per_page,
				),
			),
			200
		);
	}

	/**
	 * Get stock for a specific product across all outlets.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The response containing stock data or an error message.
	 * @since 1.0.0
	 */
	public function get_stock_for_product( WP_REST_Request $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'smartsales_inventory';

		$product_id   = $request->get_param( 'product_id' ) ? intval( $request->get_param( 'product_id' ) ) : 0;
		$current_page = $request->get_param( 'current_page' ) ? intval( $request->get_param( 'current_page' ) ) : 1;
		$per_page     = $request->get_param( 'per_page' ) ? intval( $request->get_param( 'per_page' ) ) : 10;
		$offset       = ( $current_page - 1 ) * $per_page;

		if ( empty( $product_id ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Product ID is required.',
					array(),
					400,
					$request->get_route()
				),
				400
			);
		}

		$where_clauses = array( 'product_id = %d' );
		$prepare_args  = array( $product_id );

		// Role-based access for cashiers.
		$user = wp_get_current_user();
		if ( in_array( 'csmsl_pos_cashier', (array) $user->roles, true ) ) {
			/**
			 * Filters the assigned outlet IDs for a user.
			 *
			 * @param array $assigned_outlets The assigned outlet IDs.
			 * @param int   $user_id          The user ID.
			 */
			$assigned_outlets = (array) get_user_meta( $user->ID, 'csmsl_assigned_outlet_ids', true );
			if ( empty( $assigned_outlets ) ) {
				return new WP_REST_Response(
					$this->format_error_response(
						'Access denied. No outlets assigned.',
						array(),
						403,
						$request->get_route()
					),
					403
				);
			}
			$where_clauses[] = 'outlet_id IN ( ' . implode( ', ', array_map( 'intval', $assigned_outlets ) ) . ' )';
		}

		$where_sql = count( $where_clauses ) > 0 ? ' WHERE ' . implode( ' AND ', $where_clauses ) : '';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectSql.DirectSql,
		// WordPress.DB.SlowDBQuery.slow_db_query_Unprepared
		$total_items = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$table_name}{$where_sql}", $prepare_args ) );
		$total_pages = ceil( $total_items / $per_page );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectSql.DirectSql,
		// WordPress.DB.SlowDBQuery.slow_db_query_Unprepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT product_id, outlet_id, stock, threshold FROM {$table_name}{$where_sql} LIMIT %d OFFSET %d",
				array_merge( $prepare_args, array( $per_page, $offset ) )
			),
			ARRAY_A
		);

		if ( empty( $results ) ) {
			return new WP_REST_Response(
				array(
					'success'    => true,
					'message'    => 'No stock records found for this product.',
					'data'       => array(),
					'pagination' => array(
						'total_items'  => 0,
						'total_pages'  => 0,
						'current_page' => $current_page,
						'per_page'     => $per_page,
					),
				),
				200
			);
		}

		return new WP_REST_Response(
			array(
				'success'    => true,
				'message'    => 'Stock records for product retrieved successfully.',
				'data'       => $results,
				'pagination' => array(
					'total_items'  => (int) $total_items,
					'total_pages'  => (int) $total_pages,
					'current_page' => (int) $current_page,
					'per_page'     => (int) $per_page,
				),
			),
			200
		);
	}

	/**
	 * Get stock for a specific product at a specific outlet.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The response containing stock data or an error message.
	 * @since 1.0.0
	 */
	public function get_stock_for_product_at_outlet( WP_REST_Request $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'smartsales_inventory';

		$product_id = $request->get_param( 'product_id' ) ? intval( $request->get_param( 'product_id' ) ) : 0;
		$outlet_id  = $request->get_param( 'outlet_id' ) ? intval( $request->get_param( 'outlet_id' ) ) : 0;

		$errors = array();

		if ( empty( $product_id ) ) {
			$errors['product_id'] = 'Product ID is required.';
		}
		if ( empty( $outlet_id ) ) {
			$errors['outlet_id'] = 'Outlet ID is required.';
		}

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

		$where_clauses = array(
			'product_id = %d',
			'outlet_id = %d',
		);
		$prepare_args  = array( $product_id, $outlet_id );

		// Role-based access for cashiers.
		$user = wp_get_current_user();
		if ( in_array( 'csmsl_pos_cashier', (array) $user->roles, true ) ) {
			/**
			 * Filters the assigned outlet IDs for a user.
			 *
			 * @param array $assigned_outlets The assigned outlet IDs.
			 * @param int   $user_id          The user ID.
			 */
			$assigned_outlets = (array) get_user_meta( $user->ID, 'csmsl_assigned_outlet_ids', true );
			if ( empty( $assigned_outlets ) || ! in_array( $outlet_id, $assigned_outlets, true ) ) {
				return new WP_REST_Response(
					$this->format_error_response(
						'Access denied. Not assigned to this outlet.',
						array(),
						403,
						$request->get_route()
					),
					403
				);
			}
		}

		$where_sql = count( $where_clauses ) > 0 ? ' WHERE ' . implode( ' AND ', $where_clauses ) : '';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectSql.DirectSql,
		// WordPress.DB.SlowDBQuery.slow_db_query_Unprepared
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT product_id, outlet_id, stock, threshold FROM {$table_name}{$where_sql}",
				$prepare_args
			),
			ARRAY_A
		);

		if ( empty( $result ) ) {
			return new WP_REST_Response(
				array(
					'success' => true,
					'message' => 'No stock record found for this product at this outlet.',
					'data'    => array(),
				),
				200
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Stock record retrieved successfully.',
				'data'    => $result,
			),
			200
		);
	}

	/**
	 * Bulk adjust stock for multiple products at various outlets.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response The response containing success or error information.
	 * @since 1.0.0
	 */
	public function bulk_adjust_stock( WP_REST_Request $request ) {
		global $wpdb;
		$table_name_inventory = $wpdb->prefix . 'smartsales_inventory';
		$table_name_movements = $wpdb->prefix . 'smartsales_inventory_movements';

		$data        = $request->get_json_params();
		$adjustments = isset( $data['adjustments'] ) ? $data['adjustments'] : array();
	

		if ( ! is_array( $adjustments ) || empty( $adjustments ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Invalid adjustments data.',
					array( 'adjustments' => 'Adjustments must be a non-empty array.' ),
					400,
					$request->get_route()
				),
				400
			);
		}

		$results = array();
		$errors  = array();
		$user_id = get_current_user_id();

		foreach ( $adjustments as $index => $adjustment ) {
			$product_id = isset( $adjustment['product_id'] ) ? intval( $adjustment['product_id'] ) : 0;
			$outlet_id  = isset( $adjustment['outlet_id'] ) ? intval( $adjustment['outlet_id'] ) : 0;
			$quantity   = isset( $adjustment['quantity'] ) ? intval( $adjustment['quantity'] ) : 0;
			$reason     = isset( $adjustment['reason'] ) ? sanitize_text_field( $adjustment['reason'] ) : '';
			$threshold  = isset( $adjustment['threshold'] ) ? intval( $adjustment['threshold'] ) : null;

			$item_errors = array();

			if ( empty( $product_id ) ) {
				$item_errors['product_id'] = 'Product ID is required.';
			}
			if ( empty( $outlet_id ) ) {
				$item_errors['outlet_id'] = 'Outlet ID is required.';
			}
			if ( empty( $quantity ) || ! is_numeric( $quantity ) ) {
				$item_errors['quantity'] = 'Quantity must be a non-zero number. Use negative values for removals.';
			}
			if ( empty( $reason ) ) {
				$item_errors['reason'] = 'Reason for adjustment is required.';
			}

			// If threshold is not provided, use WooCommerce default.
			if ( is_null( $threshold ) ) {
				$threshold = intval( get_option( 'woocommerce_notify_low_stock_amount' ) );
			}

			if ( ! empty( $item_errors ) ) {
				$errors[ $index ] = $item_errors;
				continue;
			}
			// Update or insert inventory record.
			// phpcs:ignore WordPress.DB.DirectSql.DirectSql
			$existing_stock = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT id, stock FROM {$table_name_inventory} WHERE product_id = %d AND outlet_id = %d",
					$product_id,
					$outlet_id
				)
			);

			$new_stock = 0;
			$updated   = false;

			if ( $existing_stock ) {
				$new_stock = $existing_stock->stock + $quantity;

				if ( $new_stock < 0 ) {
					$errors[ $index ] = array(
						'insufficient_stock' => 'Insufficient stock.',
						'available_stock'    => $existing_stock->stock,
						'requested_adjustment' => $quantity,
					);
					continue;
				}
			

				// phpcs:ignore WordPress.DB.DirectSql.DirectSql
				$updated = $wpdb->update(
					$table_name_inventory,
					array( 'stock' => $new_stock, 'threshold' => $threshold ),
					array(
						'id' => $existing_stock->id,
					),
					array( '%d' ),
					array( '%d' )
				);
			} else {
				$new_stock = $quantity;
				// phpcs:ignore WordPress.DB.DirectSql.DirectSql
				$updated = $wpdb->insert(
					$table_name_inventory,
					array(
						'product_id' => $product_id,
						'outlet_id'  => $outlet_id,
						'stock'      => $new_stock,
						'threshold'  => $threshold,
					),
					array( '%d', '%d', '%d', '%d' )
				);
			}

			if ( false === $updated ) {
				$errors[ $index ] = array( 'db_error' => $wpdb->last_error );
				continue;
			}

			// Log stock movement.
			// phpcs:ignore WordPress.DB.DirectSql.DirectSql
			$logged = $wpdb->insert(
				$table_name_movements,
				array(
					'product_id' => $product_id,
					'outlet_id'  => $outlet_id,
					'type'       => 'adjustment',
					'quantity'   => $quantity,
					'reason'     => $reason,
					'user_id'    => $user_id,
					'created_at' => current_time( 'mysql' ),
				),
				array( '%d', '%d', '%s', '%d', '%s', '%d', '%s' )
			);

			if ( false === $logged ) {
				csmsl_log(
					'Failed to log bulk stock adjustment movement for product ' . $product_id . ' at outlet ' . $outlet_id . ': ' . $wpdb->last_error,
					'error'
				);
			}

			$results[] = array(
				'product_id' => $product_id,
				'outlet_id'  => $outlet_id,
				'new_stock'  => $new_stock,
			);
		}

		if ( ! empty( $errors ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Some adjustments failed.',
					$errors,
					207,
					$request->get_route()
				),
				207
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Bulk stock adjustments completed successfully.',
				'data'    => $results,
			),
			200
		);
	}
}

new InventoryApiHandler();
