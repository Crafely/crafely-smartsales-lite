<?php

namespace AISMARTSALES\Includes\Api\Orders;

use WP_REST_Response;
use WP_Error;
use WC_Order_Item_Fee;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class OrdersApiHandler {

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		// Basic CRUD operations
		register_rest_route(
			'ai-smart-sales/v1',
			'/orders',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_orders' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'create_order' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/orders/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_order' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'update_order' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'delete_order' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Bulk operations
		register_rest_route(
			'ai-smart-sales/v1',
			'/orders/restore',
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'bulk_restore_orders' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/orders/bulk-delete',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'bulk_delete_orders' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Trash operations
		register_rest_route(
			'ai-smart-sales/v1',
			'/orders/trash',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_trash_orders' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	/**
	 * Check if the request has valid permission
	 */
	public function check_permission( $request ) {
		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Get current user
		$user = wp_get_current_user();

		// Define role-based permissions
		$allowed_roles = array( 'administrator', 'aipos_outlet_manager', 'aipos_cashier', 'aipos_shop_manager' );
		$user_roles    = (array) $user->roles;

		// Check if user has appropriate role
		if ( ! array_intersect( $allowed_roles, $user_roles ) ) {
			return false;
		}

		// For destructive operations, require higher privileges
		if (
			in_array( $request->get_method(), array( 'DELETE', 'PUT' ) ) &&
			! array_intersect( array( 'administrator', 'aipos_outlet_manager' ), $user_roles )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Check if an order is a refund
	 *
	 * @param object $order WC_Order object
	 * @return bool
	 */
	private function is_refund( $order ) {
		return is_a( $order, 'Automattic\WooCommerce\Admin\Overrides\OrderRefund' ) ||
			is_a( $order, 'WC_Order_Refund' );
	}

	/**
	 * Response formatting methods
	 */
	private function format_success_response( $message, $data = array(), $statusCode = 200 ) {
		return array(
			'success' => true,
			'message' => $message,
			'data'    => $data,
		);
	}

	private function format_error_response( $message, $errors = array(), $statusCode = 400, $path = '' ) {
		$error = array();

		// If $errors is an associative array, use it as-is
		if ( is_array( $errors ) && ! empty( $errors ) && array_keys( $errors ) !== range( 0, count( $errors ) - 1 ) ) {
			$error = $errors; // Use the associative array directly
		} else {
			// Otherwise, use a generic error structure
			$error = array(
				'error' => $message, // Fallback for non-associative errors
			);
		}

		return array(
			'success' => false,
			'message' => $message,
			'data'    => null,
			'error'   => $error,
		);
	}

	private function format_order_response( $order ) {
		// Skip refund orders - they don't have the methods we need
		if ( $this->is_refund( $order ) ) {
			return array(
				'id'            => (int) $order->get_id(),
				'type'          => 'refund',
				'number'        => $order->get_id(),
				'refund_amount' => (float) $order->get_amount(),
				'parent_id'     => $order->get_parent_id(),
				'reason'        => $order->get_reason(),
				'created_at'    => $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d H:i:s' ) : null,
			);
		}

		$customer_id    = $order->get_customer_id();
		$customer       = $customer_id ? get_userdata( $customer_id ) : null;
		$discount_total = 0;

		foreach ( $order->get_items( 'fee' ) as $fee ) {
			// Check for exact 'Discount' name or any name containing 'discount' (case-insensitive)
			if ( $fee->get_name() === 'Discount' || stripos( $fee->get_name(), 'discount' ) !== false ) {
				$discount_total += abs( $fee->get_total() );
			}
		}

		// Get created_by_id from order meta
		$created_by_id        = $order->get_meta( '_created_by_id' );
		$created_by_outlet_id = $order->get_meta( '_created_by_outlet_id' ); // Get the stored outlet ID

		// Check if this is a website order
		$channels = wp_get_post_terms( $order->get_id(), 'crafsmli_channel', array( 'fields' => 'slugs' ) );

		// Handle potential WP_Error from wp_get_post_terms
		if ( is_wp_error( $channels ) ) {
			$channels = array();
		}

		$is_website_order = empty( $created_by_id ) || in_array( 'website', $channels );

		if ( $is_website_order ) {
			$creator_data = array(
				'id'     => 0,
				'name'   => 'Storefront',
				'outlet' => 'Online Store',
			);
		} else {
			$creator      = get_userdata( $created_by_id );
			$creator_name = '';

			if ( $creator ) {
				// First try to get the full name
				if ( ! empty( $creator->first_name ) && ! empty( $creator->last_name ) ) {
					$creator_name = $creator->first_name . ' ' . $creator->last_name;
				}
				// If no full name, try display name
				elseif ( ! empty( $creator->display_name ) ) {
					$creator_name = $creator->display_name;
				}
				// Finally, fall back to username
				else {
					$creator_name = $creator->user_login;
				}
			}

			// Use the stored outlet ID instead of current assigned outlet
			$creator_outlet      = $created_by_outlet_id ? get_post( $created_by_outlet_id ) : null;
			$creator_outlet_name = $creator_outlet ? $creator_outlet->post_title : '';

			$creator_data = array(
				'id'     => (int) $created_by_id,
				'name'   => $creator_name,
				'outlet' => $creator_outlet_name,
			);
		}

		// Format customer data similar to CustomerApiHandler
		$customer_data = array();
		if ( $customer_id && $customer ) {
			// Registered customer
			$profile_image_id  = get_user_meta( $customer_id, 'profile_image', true );
			$profile_image_url = $profile_image_id ? wp_get_attachment_url( $profile_image_id ) : SMARTSALES_URL . 'assets/images/avatar.png';

			$customer_data = array(
				'id'            => (int) $customer_id,
				'username'      => $customer->user_login,
				'email'         => $customer->user_email,
				'first_name'    => get_user_meta( $customer_id, 'first_name', true ),
				'last_name'     => get_user_meta( $customer_id, 'last_name', true ),
				'full_name'     => get_user_meta( $customer_id, 'first_name', true ) . ' ' . get_user_meta( $customer_id, 'last_name', true ),
				'phone'         => get_user_meta( $customer_id, 'billing_phone', true ),
				'billing'       => array(
					'first_name' => get_user_meta( $customer_id, 'billing_first_name', true ),
					'last_name'  => get_user_meta( $customer_id, 'billing_last_name', true ),
					'company'    => get_user_meta( $customer_id, 'billing_company', true ),
					'address_1'  => get_user_meta( $customer_id, 'billing_address_1', true ),
					'address_2'  => get_user_meta( $customer_id, 'billing_address_2', true ),
					'city'       => get_user_meta( $customer_id, 'billing_city', true ),
					'state'      => get_user_meta( $customer_id, 'billing_state', true ),
					'postcode'   => get_user_meta( $customer_id, 'billing_postcode', true ),
					'country'    => get_user_meta( $customer_id, 'billing_country', true ),
					'email'      => get_user_meta( $customer_id, 'billing_email', true ),
					'phone'      => get_user_meta( $customer_id, 'billing_phone', true ),
				),
				'shipping'      => array(
					'first_name' => get_user_meta( $customer_id, 'shipping_first_name', true ),
					'last_name'  => get_user_meta( $customer_id, 'shipping_last_name', true ),
					'company'    => get_user_meta( $customer_id, 'shipping_company', true ),
					'address_1'  => get_user_meta( $customer_id, 'shipping_address_1', true ),
					'address_2'  => get_user_meta( $customer_id, 'shipping_address_2', true ),
					'city'       => get_user_meta( $customer_id, 'shipping_city', true ),
					'state'      => get_user_meta( $customer_id, 'shipping_state', true ),
					'postcode'   => get_user_meta( $customer_id, 'shipping_postcode', true ),
					'country'    => get_user_meta( $customer_id, 'shipping_country', true ),
				),
				'profile_image' => $profile_image_url,
				'is_guest'      => false,
			);
		} else {
			// Guest customer
			$email         = $order->get_billing_email();
			$customer_data = array(
				'id'            => 'guest_' . md5( $email ),
				'username'      => 'guest',
				'email'         => $email,
				'first_name'    => $order->get_billing_first_name(),
				'last_name'     => $order->get_billing_last_name(),
				'full_name'     => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				'phone'         => $order->get_billing_phone(),
				'billing'       => array(
					'first_name' => $order->get_billing_first_name(),
					'last_name'  => $order->get_billing_last_name(),
					'company'    => $order->get_billing_company(),
					'address_1'  => $order->get_billing_address_1(),
					'address_2'  => $order->get_billing_address_2(),
					'city'       => $order->get_billing_city(),
					'state'      => $order->get_billing_state(),
					'postcode'   => $order->get_billing_postcode(),
					'country'    => $order->get_billing_country(),
					'email'      => $order->get_billing_email(),
					'phone'      => $order->get_billing_phone(),
				),
				'shipping'      => array(
					'first_name' => $order->get_shipping_first_name(),
					'last_name'  => $order->get_shipping_last_name(),
					'company'    => $order->get_shipping_company(),
					'address_1'  => $order->get_shipping_address_1(),
					'address_2'  => $order->get_shipping_address_2(),
					'city'       => $order->get_shipping_city(),
					'state'      => $order->get_shipping_state(),
					'postcode'   => $order->get_shipping_postcode(),
					'country'    => $order->get_shipping_country(),
				),
				'profile_image' => SMARTSALES_URL . 'assets/images/avatar.png',
				'is_guest'      => true,
			);
		}

		// Get split payment information and payment details
		$split_payments  = $order->get_meta( '_split_payments' );
		$payment_details = $split_payments ?
			array(
				'payment_method' => null,
				'split_payments' => json_decode( $split_payments, true ),
			) :
			array(
				'payment_method' => $order->get_payment_method(),
				'split_payments' => null,
			);

		// Convert line items to array
		$line_items = array();
		foreach ( $order->get_items() as $item ) {
			$product_id   = $item->get_product_id();
			$line_items[] = array(
				'product_id' => (int) $product_id,
				'price'      => (float) get_post_meta( $product_id, '_price', true ),
				'name'       => $item->get_name(),
				'quantity'   => (int) $item->get_quantity(),
				'total'      => (float) $item->get_total(),
			);
		}

		return array(
			'id'              => (int) $order->get_id(),
			'number'          => (int) $order->get_order_number(),
			'order_key'       => $order->get_order_key(),
			'status'          => $order->get_status(),
			'total'           => (float) $order->get_total(),
			'payment_details' => $payment_details,
			'discount_total'  => (float) $discount_total,
			'currency'        => $order->get_currency(),
			'customer'        => $customer_data,
			'created_by'      => $creator_data,
			'created_at'      => $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d H:i:s' ) : null,
			'updated_at'      => $order->get_date_modified() ? $order->get_date_modified()->date( 'Y-m-d H:i:s' ) : null,
			'line_items'      => $line_items,
			'channel'         => $channels,
			'customer_note'   => $order->get_customer_note(), // Add this line
		);
	}

	/**
	 * Order listing methods
	 */
	public function get_orders( $request ) {
		$current_page = $request->get_param( 'current_page' ) ? intval( $request->get_param( 'current_page' ) ) : 1;
		$per_page     = $request->get_param( 'per_page' ) ? intval( $request->get_param( 'per_page' ) ) : 10;

		// Check user role and apply restrictions for cashiers
		$current_user = wp_get_current_user();
		$user_roles   = (array) $current_user->roles;
		$is_cashier   = in_array( 'aipos_cashier', $user_roles ) &&
			! array_intersect( array( 'administrator', 'aipos_outlet_manager', 'aipos_shop_manager' ), $user_roles );

		// Query args for getting total count
		$count_args = array(
			'limit'  => -1,
			'return' => 'ids',
			'type'   => 'shop_order', // Only count orders, not refunds
		);

		// If user is a cashier, restrict to orders they created
		if ( $is_cashier ) {
			// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$count_args['meta_query'] = array(
				array(
					'key'     => '_created_by_id',
					'value'   => get_current_user_id(),
					'compare' => '=',
				),
			);
		}

		if ( $status = $request->get_param( 'status' ) ) {
			$count_args['status'] = sanitize_text_field( $status );
		}
		if ( $customer_id = $request->get_param( 'customer_id' ) ) {
			$count_args['customer_id'] = intval( $customer_id );
		}

		// Get total count
		$count_query  = new \WC_Order_Query( $count_args );
		$total_orders = count( $count_query->get_orders() ); // This will return an array of IDs

		// Query args for paginated results
		$query_args = array(
			'limit'   => $per_page,
			'offset'  => ( $current_page - 1 ) * $per_page,
			'orderby' => 'date',
			'order'   => 'DESC',
			'return'  => 'objects',
			'type'    => 'shop_order', // Only get orders, not refunds
		);

		// If user is a cashier, restrict to orders they created
		if ( $is_cashier ) {
			$query_args['meta_query'] = array(
				array(
					'key'     => '_created_by_id',
					'value'   => get_current_user_id(),
					'compare' => '=',
				),
			);
		}

		if ( $status ) {
			$query_args['status'] = sanitize_text_field( $status );
		}
		if ( $customer_id ) {
			$query_args['customer_id'] = intval( $customer_id );
		}

		// Get paginated orders
		$order_query = new \WC_Order_Query( $query_args );
		$orders      = $order_query->get_orders();

		if ( is_wp_error( $orders ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to retrieve orders.',
					$orders->get_error_message(),
					500,
					$request->get_route()
				),
				500
			);
		}

		$total_pages = ceil( $total_orders / $per_page );
		$data        = array_map( array( $this, 'format_order_response' ), $orders );

		return new WP_REST_Response(
			array(
				'success'    => true,
				'message'    => 'Orders retrieved successfully.',
				'data'       => $data,
				'pagination' => array(
					'total_orders' => $total_orders,
					'total_pages'  => $total_pages,
					'current_page' => $current_page,
					'per_page'     => $per_page,
				),
			),
			200
		);
	}

	public function get_trash_orders( $request ) {
		$current_page = $request->get_param( 'current_page' ) ? intval( $request->get_param( 'current_page' ) ) : 1;
		$per_page     = $request->get_param( 'per_page' ) ? intval( $request->get_param( 'per_page' ) ) : 10;

		// Check user role and apply restrictions for cashiers
		$current_user = wp_get_current_user();
		$user_roles   = (array) $current_user->roles;
		$is_cashier   = in_array( 'aipos_cashier', $user_roles ) &&
			! array_intersect( array( 'administrator', 'aipos_outlet_manager', 'aipos_shop_manager' ), $user_roles );

		// Query args for getting total count of trash orders
		$count_args = array(
			'limit'  => -1,
			'return' => 'ids',
			'status' => 'trash', // Specifically get trash orders
			'type'   => 'shop_order', // Only count orders, not refunds
		);

		// If user is a cashier, restrict to orders they created
		if ( $is_cashier ) {
			$count_args['meta_query'] = array(
				array(
					'key'     => '_created_by_id',
					'value'   => get_current_user_id(),
					'compare' => '=',
				),
			);
		}

		// Get total count
		$count_query  = new \WC_Order_Query( $count_args );
		$total_orders = count( $count_query->get_orders() );

		// Query args for paginated trash orders
		$query_args = array(
			'limit'   => $per_page,
			'offset'  => ( $current_page - 1 ) * $per_page,
			'orderby' => 'date',
			'order'   => 'DESC',
			'return'  => 'objects',
			'status'  => 'trash',
			'type'    => 'shop_order', // Only get orders, not refunds
		);

		// If user is a cashier, restrict to orders they created
		if ( $is_cashier ) {
			$query_args['meta_query'] = array(
				array(
					'key'     => '_created_by_id',
					'value'   => get_current_user_id(),
					'compare' => '=',
				),
			);
		}

		// Get paginated trash orders
		$order_query = new \WC_Order_Query( $query_args );
		$orders      = $order_query->get_orders();

		if ( is_wp_error( $orders ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to retrieve trash orders.',
					$orders->get_error_message(),
					500,
					$request->get_route()
				),
				500
			);
		}

		$total_pages = ceil( $total_orders / $per_page );
		$data        = array_map( array( $this, 'format_order_response' ), $orders );

		return new WP_REST_Response(
			array(
				'success'    => true,
				'message'    => 'Trash orders retrieved successfully.',
				'data'       => $data,
				'pagination' => array(
					'total_orders' => $total_orders,
					'total_pages'  => $total_pages,
					'current_page' => $current_page,
					'per_page'     => $per_page,
				),
			),
			200
		);
	}

	/**
	 * Single order operations
	 */
	public function get_order( $request ) {
		$order_id = intval( $request->get_param( 'id' ) );
		$order    = wc_get_order( $order_id );

		if ( ! $order ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Order not found.',
					array(
						'id' => "The order with the ID '{$order_id}' does not exist.",
					),
					404,
					$request->get_route()
				),
				404
			);
		}

		// Check user role and apply restrictions for cashiers
		$current_user = wp_get_current_user();
		$user_roles   = (array) $current_user->roles;
		$is_cashier   = in_array( 'aipos_cashier', $user_roles ) &&
			! array_intersect( array( 'administrator', 'aipos_outlet_manager', 'aipos_shop_manager' ), $user_roles );

		// If user is a cashier, check if they created this order
		if ( $is_cashier ) {
			$created_by_id = $order->get_meta( '_created_by_id' );
			if ( $created_by_id != get_current_user_id() ) {
				return new WP_REST_Response(
					$this->format_error_response(
						'Access denied.',
						array(
							'authorization' => 'You do not have permission to view this order.',
						),
						403,
						$request->get_route()
					),
					403
				);
			}
		}

		return new WP_REST_Response(
			$this->format_success_response(
				'Order retrieved successfully.',
				$this->format_order_response( $order ),
				200
			),
			200
		);
	}

	public function create_order( $request ) {
		$data   = $request->get_json_params();
		$errors = array(); // Array to collect all validation errors

		// Validate customer_id
		$customer_id = intval( $data['customer_id'] ?? 0 );
		if ( ! $customer_id || $customer_id <= 0 ) {
			$errors['customer_id'] = 'Customer ID must be a valid, non-zero integer.';
		} else {
			$customer = get_userdata( $customer_id );
			if ( ! $customer ) {
				$errors['customer_id'] = "The customer with the ID '{$customer_id}' does not exist.";
			}
		}

		// Only validate payment_method if split_payments is not provided
		if ( ! isset( $data['split_payments'] ) ) {
			$payment_method        = strtolower( sanitize_text_field( $data['payment_method'] ?? '' ) );
			$valid_payment_methods = array( 'cash', 'card', 'bank_transfer', 'paypal', 'upi', 'cryptocurrency', 'cod' );
			if ( empty( $payment_method ) || ! in_array( $payment_method, $valid_payment_methods ) ) {
				$errors['payment_method'] = "The payment method '{$payment_method}' is not supported.";
			}
		}

		// Validate line_items
		if ( empty( $data['line_items'] ) || ! is_array( $data['line_items'] ) ) {
			$errors['line_items'] = 'At least one line item is required.';
		} else {
			foreach ( $data['line_items'] as $index => $item ) {
				$product_id = intval( $item['product_id'] ?? 0 );
				$quantity   = intval( $item['quantity'] ?? 1 );

				if ( ! $product_id || $product_id <= 0 ) {
					$errors[ "line_items.{$index}.product_id" ] = 'Product ID must be a valid, non-zero integer.';
				} else {
					$product_object = wc_get_product( $product_id );
					if ( ! $product_object ) {
						$errors[ "line_items.{$index}.product_id" ] = "The product with the ID '{$product_id}' does not exist.";
					}
				}

				if ( $quantity < 1 ) {
					$errors[ "line_items.{$index}.quantity" ] = 'Quantity must be at least 1.';
				}
			}
		}

		// Validate discount_total
		if ( isset( $data['discount_total'] ) ) {
			$discount_total = floatval( $data['discount_total'] );
			if ( $discount_total < 0 ) {
				$errors['discount_total'] = 'Discount total must be a non-negative number.';
			}
		}

		// Validate current user (creator)
		$current_user_id = get_current_user_id();
		if ( ! $current_user_id ) {
			$errors['authentication'] = 'Please log in to create an order.';
		} else {
			$current_user = get_userdata( $current_user_id );
			if ( ! $current_user ) {
				$errors['created_by_id'] = "The user with the ID '{$current_user_id}' does not exist.";
			}
		}

		// Validate split payments if provided
		if ( isset( $data['split_payments'] ) ) {
			if ( ! is_array( $data['split_payments'] ) ) {
				$errors['split_payments'] = 'Split payments must be an array.';
			} else {
				$total_split_amount = 0;
				foreach ( $data['split_payments'] as $index => $payment ) {
					if ( ! isset( $payment['method'] ) || ! isset( $payment['amount'] ) ) {
						$errors[ "split_payments.{$index}" ] = 'Each split payment must have a method and amount.';
						continue;
					}

					$method = strtolower( sanitize_text_field( $payment['method'] ) );
					$amount = floatval( $payment['amount'] );

					if ( ! in_array( $method, array( 'cash', 'card', 'bank_transfer', 'paypal', 'upi', 'cryptocurrency', 'cod' ) ) ) {
						$errors[ "split_payments.{$index}.method" ] = "Payment method '{$method}' is not supported.";
					}

					if ( $amount <= 0 ) {
						$errors[ "split_payments.{$index}.amount" ] = 'Amount must be greater than 0.';
					}

					$total_split_amount += $amount;
				}
			}
		}

		// If there are validation errors, return them all
		if ( ! empty( $errors ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Validation failed: Missing required fields.',
					$errors,
					400,
					$request->get_route()
				),
				400
			);
		}

		// Create temporary order to calculate total
		$temp_order = wc_create_order();
		$temp_order->set_customer_id( $customer_id );

		// Add billing address if provided
		if ( isset( $data['customer']['billing_address'] ) ) {
			$billing = $data['customer']['billing_address'];
			$temp_order->set_billing_address_1( $billing['address'] ?? '' );
			$temp_order->set_billing_address_2( $billing['address_2'] ?? '' );
			$temp_order->set_billing_city( $billing['city'] ?? '' );
			$temp_order->set_billing_state( $billing['state'] ?? '' );
			$temp_order->set_billing_country( $billing['country'] ?? '' );
			$temp_order->set_billing_postcode( $billing['postcode'] ?? '' );
			$temp_order->set_billing_company( $billing['company'] ?? '' );
			$temp_order->set_billing_email( $billing['email'] ?? '' );
			$temp_order->set_billing_phone( $billing['phone'] ?? '' );

			// If shipping address is not provided, copy billing address
			if ( ! isset( $data['customer']['shipping_address'] ) ) {
				$temp_order->set_shipping_address_1( $billing['address'] ?? '' );
				$temp_order->set_shipping_address_2( $billing['address_2'] ?? '' );
				$temp_order->set_shipping_city( $billing['city'] ?? '' );
				$temp_order->set_shipping_state( $billing['state'] ?? '' );
				$temp_order->set_shipping_country( $billing['country'] ?? '' );
				$temp_order->set_shipping_postcode( $billing['postcode'] ?? '' );
				$temp_order->set_shipping_company( $billing['company'] ?? '' );
			}
		}

		// Add shipping address if provided separately
		if ( isset( $data['customer']['shipping_address'] ) ) {
			$shipping = $data['customer']['shipping_address'];
			$temp_order->set_shipping_address_1( $shipping['address'] ?? '' );
			$temp_order->set_shipping_address_2( $shipping['address_2'] ?? '' );
			$temp_order->set_shipping_city( $shipping['city'] ?? '' );
			$temp_order->set_shipping_state( $shipping['state'] ?? '' );
			$temp_order->set_shipping_country( $shipping['country'] ?? '' );
			$temp_order->set_shipping_postcode( $shipping['postcode'] ?? '' );
			$temp_order->set_shipping_company( $shipping['company'] ?? '' );
		}

		// Add line items to temp order
		foreach ( $data['line_items'] as $item ) {
			$product_id     = intval( $item['product_id'] );
			$quantity       = intval( $item['quantity'] );
			$product_object = wc_get_product( $product_id );
			$temp_order->add_product( $product_object, $quantity );
		}

		// Apply discount if provided
		if ( ! empty( $data['discount_total'] ) ) {
			$discount_total = floatval( $data['discount_total'] );
			$discount       = new WC_Order_Item_Fee();
			$discount->set_name( 'Discount' );
			$discount->set_amount( -1 * $discount_total );
			$discount->set_total( -1 * $discount_total );
			$discount->set_tax_status( 'none' );
			$temp_order->add_item( $discount );
		}

		// Calculate total
		$temp_order->calculate_totals();
		$order_total = $temp_order->get_total();

		// Validate split payments against calculated total
		if ( isset( $data['split_payments'] ) ) {
			$total_split_amount = array_sum( array_column( $data['split_payments'], 'amount' ) );
			if ( abs( $total_split_amount - $order_total ) > 0.01 ) {
				// Delete temporary order
				$temp_order->delete( true );

				return new WP_REST_Response(
					$this->format_error_response(
						'Split payment validation failed.',
						array(
							'split_payments' => "Total split payment amount ({$total_split_amount}) must equal order total ({$order_total}).",
						),
						400,
						$request->get_route()
					),
					400
				);
			}
		}

		// If we made it here, validation passed - use the temp order as the real order
		$order = $temp_order;

		// Store the current user's outlet ID at the time of order creation
		$current_user_id   = get_current_user_id();
		$current_outlet_id = get_user_meta( $current_user_id, 'assigned_outlet_id', true );

		// Add metadata and finish order setup
		$order->update_meta_data( '_created_by_id', $current_user_id );
		$order->update_meta_data( '_created_by_outlet_id', $current_outlet_id ); // Store the outlet ID

		if ( isset( $data['split_payments'] ) ) {
			$order->update_meta_data( '_split_payments', wp_json_encode( $data['split_payments'] ) );
			$order->set_payment_method( 'split_payment' ); // Set a generic identifier
			$order->set_status( 'completed' );
		} else {
			$order->set_payment_method( $payment_method );
			$order->set_status( 'completed' );
		}

		// Add this after creating the order but before saving
		if ( isset( $data['customer_note'] ) ) {
			$order->set_customer_note( sanitize_textarea_field( $data['customer_note'] ) );
		}

		// Assign channel for POS users
		$current_user = get_userdata( $current_user_id );
		if ( in_array( 'aipos_outlet_manager', $current_user->roles ) ) {
			$pos_channel = get_term_by( 'slug', 'pos-system', 'crafsmli_channel' );
			if ( $pos_channel ) {
				wp_set_object_terms( $order->get_id(), $pos_channel->term_id, 'crafsmli_channel' );
			}
		}

		// Save the order
		$order_id = $order->save();

		if ( ! $order_id ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to save order.',
					array(
						'server' => 'The order could not be saved.',
					),
					500,
					$request->get_route()
				),
				500
			);
		}

		// Return the response with current user's details
		return new WP_REST_Response(
			$this->format_success_response(
				'Order created successfully.',
				$this->format_order_response( $order ),
				201
			),
			201
		);
	}

	public function update_order( $request ) {
		$order_id = intval( $request->get_param( 'id' ) );
		$order    = wc_get_order( $order_id );

		if ( ! $order ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Order not found.',
					array(
						'id' => "The order with the ID '{$order_id}' does not exist.",
					),
					404,
					$request->get_route()
				),
				404
			);
		}

		// Check user role and apply restrictions for cashiers
		$current_user = wp_get_current_user();
		$user_roles   = (array) $current_user->roles;
		$is_cashier   = in_array( 'aipos_cashier', $user_roles ) &&
			! array_intersect( array( 'administrator', 'aipos_outlet_manager', 'aipos_shop_manager' ), $user_roles );

		// If user is a cashier, check if they created this order
		if ( $is_cashier ) {
			$created_by_id = $order->get_meta( '_created_by_id' );
			if ( $created_by_id != get_current_user_id() ) {
				return new WP_REST_Response(
					$this->format_error_response(
						'Access denied.',
						array(
							'authorization' => 'You do not have permission to update this order.',
						),
						403,
						$request->get_route()
					),
					403
				);
			}
		}

		$data = $request->get_json_params();

		// Update payment method if provided
		if ( isset( $data['payment_method'] ) ) {
			$payment_method        = sanitize_text_field( $data['payment_method'] );
			$valid_payment_methods = array( 'cash', 'card', 'bank_transfer', 'paypal', 'upi', 'cryptocurrency', 'cod' );
			if ( in_array( $payment_method, $valid_payment_methods ) ) {
				$order->set_payment_method( $payment_method );
			}
		}

		// Update status if provided
		if ( isset( $data['status'] ) ) {
			$old_status = $order->get_status();
			$new_status = sanitize_text_field( $data['status'] );

			// Only trigger if status actually changed
			if ( $old_status !== $new_status ) {
				// First set the new status
				$order->set_status( $new_status );

				// Check if we should trigger the email
				$should_send_email = apply_filters( 'ai_smart_sales_should_send_status_email', true, $order, $old_status, $new_status );

				if ( $should_send_email ) {
					// Get mailer from WooCommerce
					$mailer = WC()->mailer();

					/**
					 * @var array<string, \WC_Email> $emails
					 * Suppress Intelephense warning for ->trigger() method
					 */
					$emails = $mailer->get_emails();

					// Send the email based on the new status
					switch ( $new_status ) {
						case 'completed':
							if ( isset( $emails['WC_Email_Customer_Completed_Order'] ) ) {
								$emails['WC_Email_Customer_Completed_Order']->trigger( $order->get_id() );
							}
							break;

						case 'processing':
							if ( isset( $emails['WC_Email_Customer_Processing_Order'] ) ) {
								$emails['WC_Email_Customer_Processing_Order']->trigger( $order->get_id() );
							}
							break;

						case 'cancelled':
							if ( isset( $emails['WC_Email_Cancelled_Order'] ) ) {
								$emails['WC_Email_Cancelled_Order']->trigger( $order->get_id() );
							}
							break;

						case 'on-hold':
							if ( isset( $emails['WC_Email_Customer_On_Hold_Order'] ) ) {
								$emails['WC_Email_Customer_On_Hold_Order']->trigger( $order->get_id() );
							}
							break;

						case 'failed':
							if ( isset( $emails['WC_Email_Failed_Order'] ) ) {
								$emails['WC_Email_Failed_Order']->trigger( $order->get_id() );
							}
							break;

							// Add more status cases as needed
					}

					// Also notify admin about the status change
					if ( isset( $emails['WC_Email_Admin_Order_Status_Changed'] ) ) {
						$emails['WC_Email_Admin_Order_Status_Changed']->trigger( $order->get_id(), $old_status, $new_status );
					}
				}
			}
		}

		// Update customer addresses if provided
		if ( isset( $data['customer']['billing_address'] ) ) {
			$billing = $data['customer']['billing_address'];
			$order->set_billing_address_1( $billing['address'] ?? '' );
			$order->set_billing_address_2( $billing['address_2'] ?? '' );
			$order->set_billing_city( $billing['city'] ?? '' );
			$order->set_billing_state( $billing['state'] ?? '' );
			$order->set_billing_country( $billing['country'] ?? '' );
			$order->set_billing_postcode( $billing['postcode'] ?? '' );
			$order->set_billing_company( $billing['company'] ?? '' );
			$order->set_billing_email( $billing['email'] ?? '' );
			$order->set_billing_phone( $billing['phone'] ?? '' );

			// If shipping address is not provided, copy billing address
			if ( ! isset( $data['customer']['shipping_address'] ) ) {
				$order->set_shipping_address_1( $billing['address'] ?? '' );
				$order->set_shipping_address_2( $billing['address_2'] ?? '' );
				$order->set_shipping_city( $billing['city'] ?? '' );
				$order->set_shipping_state( $billing['state'] ?? '' );
				$order->set_shipping_country( $billing['country'] ?? '' );
				$order->set_shipping_postcode( $billing['postcode'] ?? '' );
				$order->set_shipping_company( $billing['company'] ?? '' );
			}
		}

		if ( isset( $data['customer']['shipping_address'] ) ) {
			$shipping = $data['customer']['shipping_address'];
			$order->set_shipping_address_1( $shipping['address'] ?? '' );
			$order->set_shipping_address_2( $shipping['address_2'] ?? '' );
			$order->set_shipping_city( $shipping['city'] ?? '' );
			$order->set_shipping_state( $shipping['state'] ?? '' );
			$order->set_shipping_country( $shipping['country'] ?? '' );
			$order->set_shipping_postcode( $shipping['postcode'] ?? '' );
			$order->set_shipping_company( $shipping['company'] ?? '' );
		}

		// Update line items if provided
		if ( ! empty( $data['line_items'] ) ) {
			// Remove existing line items
			foreach ( $order->get_items() as $item_id => $item ) {
				wc_delete_order_item( $item_id );
			}

			// Add new line items
			foreach ( $data['line_items'] as $item ) {
				$product_id = intval( $item['product_id'] );
				$quantity   = intval( $item['quantity'] );
				$product    = wc_get_product( $product_id );

				if ( $product ) {
					$order->add_product( $product, $quantity );
				}
			}
		}

		// Update split payments if provided
		if ( isset( $data['split_payments'] ) ) {
			if ( is_array( $data['split_payments'] ) ) {
				$order->update_meta_data( '_split_payments', wp_json_encode( $data['split_payments'] ) );
				$order->set_payment_method( 'split_payment' );
			}
		}

		// Apply discount total if provided
		if ( isset( $data['discount_total'] ) ) {
			$discount_total = floatval( $data['discount_total'] );

			// Remove ALL existing discount fees in a single pass
			$items_to_remove = array();
			foreach ( $order->get_items( 'fee' ) as $item_id => $fee ) {
				// Check for exact 'Discount' name or any name containing 'discount' (case-insensitive)
				if ( $fee->get_name() === 'Discount' || stripos( $fee->get_name(), 'discount' ) !== false ) {
					$items_to_remove[] = $item_id;
				}
			}

			// Remove collected items
			foreach ( $items_to_remove as $item_id ) {
				$order->remove_item( $item_id );
			}

			// Add new discount fee only if amount is greater than 0
			if ( $discount_total > 0 ) {
				$discount = new WC_Order_Item_Fee();
				$discount->set_name( 'Discount' );
				$discount->set_amount( -1 * $discount_total );
				$discount->set_total( -1 * $discount_total );
				$discount->set_tax_status( 'none' );
				$order->add_item( $discount );
			}
		}

		// Add this block before order->save()
		if ( isset( $data['customer_note'] ) ) {
			$order->set_customer_note( sanitize_textarea_field( $data['customer_note'] ) );
		}

		// Recalculate totals and save
		$order->calculate_totals();
		$order->save();

		// Prepare response with potential warning about total mismatch
		$response_data    = $this->format_order_response( $order );
		$response_message = 'Order updated successfully.';

		// Check if provided total differs from calculated total and add warning
		if ( isset( $data['total'] ) ) {
			$provided_total   = floatval( $data['total'] );
			$calculated_total = floatval( $order->get_total() );
			if ( abs( $provided_total - $calculated_total ) > 0.01 ) { // Allow for minor floating point differences
				$response_data['warning'] = "Note: The provided total ({$provided_total}) was different from the calculated total ({$calculated_total}). WooCommerce automatically calculates totals based on line items, fees, and discounts.";
			}
		}

		return new WP_REST_Response(
			$this->format_success_response(
				$response_message,
				$response_data,
				200
			),
			200
		);
	}

	public function delete_order( $request ) {
		$order_id = intval( $request->get_param( 'id' ) );
		$order    = wc_get_order( $order_id );

		if ( ! $order ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Order not found.',
					array(
						'id' => "The order with the ID '{$order_id}' does not exist.",
					),
					404,
					$request->get_route()
				),
				404
			);
		}

		// Set force parameter to false to move to trash instead of permanent deletion
		$order->delete( false );

		return new WP_REST_Response(
			$this->format_success_response(
				'Order moved to trash successfully.',
				null,
				200
			),
			200
		);
	}

	/**
	 * Bulk order operations
	 */
	public function bulk_restore_orders( $request ) {
		$data = $request->get_json_params();

		// Validate order IDs
		if ( empty( $data['ids'] ) || ! is_array( $data['ids'] ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Validation failed: Missing required fields.',
					array(
						'ids' => 'Order IDs must be provided as an array.',
					),
					400,
					$request->get_route()
				),
				400
			);
		}

		$order_ids       = array_map( 'intval', $data['ids'] );
		$restored_orders = array();
		$errors          = array();

		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				$errors[] = "Order with ID {$order_id} not found.";
				continue;
			}

			try {
				if ( $order->get_status() !== 'trash' ) {
					$errors[] = "Order with ID {$order_id} not in trash.";
					continue;
				}

				$order->set_status( 'draft' );
				$order->save();

				$restored_orders[] = $order_id; // Just store the ID instead of full order data
			} catch ( Exception $e ) {
				$errors[] = "Failed to restore order with ID {$order_id}: " . $e->getMessage();
			}
		}

		// If no orders were restored
		if ( empty( $restored_orders ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Some orders could not be restored.',
					'data'    => array(
						'restored_orders' => array(),
						'errors'          => $errors,
					),
				),
				400
			);
		}

		// All orders restored (with or without errors)
		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => sprintf( '%d order(s) restored successfully.', count( $restored_orders ) ),
				'data'    => array(
					'restored_orders' => $restored_orders,
					'errors'          => $errors ?: null,
				),
			),
			200
		);
	}

	public function bulk_delete_orders( $request ) {
		$data = $request->get_json_params();

		// Validate order IDs
		if ( empty( $data['ids'] ) || ! is_array( $data['ids'] ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Validation failed: Missing required fields.',
					array(
						'ids' => 'Order IDs must be provided as an array.',
					),
					400,
					$request->get_route()
				),
				400
			);
		}

		$order_ids      = array_map( 'intval', $data['ids'] );
		$deleted_orders = array();
		$errors         = array();

		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				$errors[] = "Order with ID {$order_id} not found.";
				continue;
			}

			try {
				$deleted = $order->delete( false ); // Always move to trash

				if ( $deleted ) {
					$deleted_orders[] = $order_id;
				} else {
					$errors[] = "Failed to move order with ID {$order_id} to trash.";
				}
			} catch ( Exception $e ) {
				$errors[] = "Failed to move order with ID {$order_id} to trash: " . $e->getMessage();
			}
		}

		// If no orders were moved to trash
		if ( empty( $deleted_orders ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Some orders could not be moved to trash.',
					'data'    => array(
						'deleted_orders' => array(),
						'errors'         => $errors,
					),
				),
				400
			);
		}

		// If some orders were moved to trash but others failed
		if ( ! empty( $errors ) ) {
			return new WP_REST_Response(
				array(
					'success' => true,
					'message' => sprintf( '%d order(s) moved to trash successfully.', count( $deleted_orders ) ),
					'data'    => array(
						'deleted_orders' => $deleted_orders,
						'errors'         => $errors,
					),
				),
				200
			);
		}

		// All orders moved to trash successfully
		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => sprintf( '%d order(s) moved to trash successfully.', count( $deleted_orders ) ),
				'data'    => array(
					'deleted_orders' => $deleted_orders,
					'errors'         => null,
				),
			),
			200
		);
	}
}
