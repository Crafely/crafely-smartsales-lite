<?php

namespace CSMSL\Includes\Api\Customers;

use WP_REST_Response;
use WP_Error;
use WC_Customer_Data_Store;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class CustomersApiHandler {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		// Get all customers
		register_rest_route(
			'ai-smart-sales/v1',
			'/customers',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_customers' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Get a single customer
		register_rest_route(
			'ai-smart-sales/v1',
			'/customers/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_customer' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Update a customer
		register_rest_route(
			'ai-smart-sales/v1',
			'/customers/(?P<id>\d+)',
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_customer' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Delete a customer
		register_rest_route(
			'ai-smart-sales/v1',
			'/customers/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_customer' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Create a new customer
		register_rest_route(
			'ai-smart-sales/v1',
			'/customers',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_customer' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	public function check_permission( $request ) {
		// Check if user is logged in and has appropriate capabilities
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Get current user
		$user = wp_get_current_user();

		// Check if user has any of our POS roles or is an administrator
		$allowed_roles = array( 'administrator', 'csmsl_pos_outlet_manager', 'csmsl_pos_cashier', 'csmsl_pos_shop_manager' );
		$user_roles    = (array) $user->roles;

		if ( ! array_intersect( $allowed_roles, $user_roles ) ) {
			return false;
		}

		return true;
	}

	// Format success response
	private function format_success_response( $message, $data = array(), $statusCode = 200 ) {
		return array(
			'success' => true,
			'message' => $message,
			'data'    => $data,
		);
	}

	// Format error response
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

	// Get all customers using WooCommerce standard approach
	public function get_customers( $request ) {
		// Get pagination parameters
		$current_page = $request->get_param( 'current_page' ) ? intval( $request->get_param( 'current_page' ) ) : 1;
		$per_page     = $request->get_param( 'per_page' ) ? intval( $request->get_param( 'per_page' ) ) : 10;
		$search       = $request->get_param( 'search' ) ? sanitize_text_field( $request->get_param( 'search' ) ) : '';

		// Find all user IDs who have placed WooCommerce orders and collect guest customers
		$order_query = array(
			'limit'  => -1,
			'return' => 'ids',
		);
		if ( ! empty( $search ) ) {
			$order_query['search'] = '*' . $search . '*';
		}
		$customer_ids = array();
		$guest_customers = array();
		if ( class_exists( 'WC_Order_Query' ) ) {
			$orders = wc_get_orders( $order_query );
		} else {
			$orders = array();
		}
		if ( ! empty( $orders ) ) {
			foreach ( $orders as $order_id ) {
				$order = wc_get_order( $order_id );
				if ( $order ) {
					$customer_id = $order->get_customer_id();
					if ( $customer_id ) {
						if ( ! in_array( $customer_id, $customer_ids ) ) {
							$customer_ids[] = $customer_id;
						}
					} else {
						// Guest order: use billing email as unique key
						$email = $order->get_billing_email();
						if ( $email ) {
							$guest_id = 'guest_' . md5( $email );
							if ( ! isset( $guest_customers[ $guest_id ] ) ) {
								$guest_customers[ $guest_id ] = array(
									'id' => $guest_id,
									'email' => $email,
									'first_name' => $order->get_billing_first_name(),
									'last_name' => $order->get_billing_last_name(),
									'full_name' => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
									'phone' => $order->get_billing_phone(),
									'billing' => array(
										'first_name' => $order->get_billing_first_name(),
										'last_name' => $order->get_billing_last_name(),
										'company' => $order->get_billing_company(),
										'address_1' => $order->get_billing_address_1(),
										'address_2' => $order->get_billing_address_2(),
										'city' => $order->get_billing_city(),
										'state' => $order->get_billing_state(),
										'postcode' => $order->get_billing_postcode(),
										'country' => $order->get_billing_country(),
										'email' => $email,
										'phone' => $order->get_billing_phone(),
									),
									'shipping' => array(
										'first_name' => $order->get_shipping_first_name(),
										'last_name' => $order->get_shipping_last_name(),
										'company' => $order->get_shipping_company(),
										'address_1' => $order->get_shipping_address_1(),
										'address_2' => $order->get_shipping_address_2(),
										'city' => $order->get_shipping_city(),
										'state' => $order->get_shipping_state(),
										'postcode' => $order->get_shipping_postcode(),
										'country' => $order->get_shipping_country(),
									),
									'profile_image' => CSMSL_URL . 'assets/images/avatar.png',
									'total_orders' => 1,
									'orders' => array(
										array(
											'order_id' => $order->get_id(),
											'total' => $order->get_total(),
											'status' => $order->get_status(),
											'date' => $order->get_date_created()->date( 'Y-m-d H:i:s' ),
										),
									),
									'is_guest' => true,
								);
							} else {
								// Add order to guest's order list
								$guest_customers[ $guest_id ]['orders'][] = array(
									'order_id' => $order->get_id(),
									'total' => $order->get_total(),
									'status' => $order->get_status(),
									'date' => $order->get_date_created()->date( 'Y-m-d H:i:s' ),
								);
								if (!isset($guest_customers[$guest_id]['total_orders']) || !is_int($guest_customers[$guest_id]['total_orders'])) {
									$guest_customers[$guest_id]['total_orders'] = 1;
								} else {
									$guest_customers[$guest_id]['total_orders'] += 1;
								}
							}
						}
					}
				}
			}
		}

		// Also include users with 'customer' role (created via API or WooCommerce)
		$args = array(
			'role'   => 'customer',
			'fields' => 'ID',
		);
		if ( ! empty( $search ) ) {
			$args['search']         = '*' . $search . '*';
			$args['search_columns'] = array( 'user_login', 'user_email', 'user_nicename', 'display_name' );
		}
		$role_customers = get_users( $args );
		foreach ( $role_customers as $id ) {
			if ( ! in_array( $id, $customer_ids ) ) {
				$customer_ids[] = $id;
			}
		}

		// Remove duplicates and sort
		$customer_ids = array_unique( $customer_ids );
		// Sort by registration date descending
		usort(
			$customer_ids,
			function ( $a, $b ) {
				$a_data = get_userdata( $a );
				$b_data = get_userdata( $b );
				return strtotime( $b_data->user_registered ) - strtotime( $a_data->user_registered );
			}
		);

		// Pagination for registered users
		$total_customers = count( $customer_ids ) + count( $guest_customers );
		$total_pages     = ceil( $total_customers / $per_page );
		$offset          = ( $current_page - 1 ) * $per_page;

		// Merge registered and guest customers for pagination
		$all_customers = array();
		foreach ( $customer_ids as $customer_id ) {
			$all_customers[] = $this->format_customer_data( $customer_id );
		}
		foreach ( $guest_customers as $guest ) {
			$all_customers[] = $guest;
		}

		// Paginate
		$paged_customers = array_slice( $all_customers, $offset, $per_page );

		return new WP_REST_Response(
			array(
				'success'    => true,
				'message'    => 'Customers retrieved successfully.',
				'data'       => $paged_customers,
				'pagination' => array(
					'total_customers' => $total_customers,
					'total_pages'     => $total_pages,
					'current_page'    => $current_page,
					'per_page'        => $per_page,
				),
			),
			200
		);
	}

	// Format customer data using WooCommerce customer object
	private function format_customer_data( $user_id ) {
		// Get user data
		$user_data = get_userdata( $user_id );
		if ( ! $user_data ) {
			return null;
		}

		// Get WooCommerce customer object if available
		$wc_customer = null;
		if ( class_exists( 'WC_Customer' ) ) {
			try {
				$wc_customer = new \WC_Customer( $user_id );
			} catch ( \Exception $e ) {
				// Customer might not exist in WooCommerce, use WordPress user data only
			}
		}

		// Get profile image
		$profile_image_id  = get_user_meta( $user_id, 'profile_image', true );
		$profile_image_url = $profile_image_id ? wp_get_attachment_url( $profile_image_id ) : CSMSL_URL . 'assets/images/avatar.png';

		// Get order data if WooCommerce is available
		$order_data   = array();
		$total_orders = 0;

		if ( class_exists( 'WooCommerce' ) && function_exists( 'wc_get_orders' ) ) {
			$orders = wc_get_orders(
				array(
					'customer_id' => $user_id,
					'status'      => array( 'completed', 'processing', 'on-hold' ),
					'limit'       => -1,
				)
			);

			if ( ! is_wp_error( $orders ) ) {
				$total_orders = count( $orders );
				foreach ( $orders as $order ) {
					$order_data[] = array(
						'order_id' => $order->get_id(),
						'total'    => $order->get_total(),
						'status'   => $order->get_status(),
						'date'     => $order->get_date_created()->date( 'Y-m-d H:i:s' ),
					);
				}
			}
		}

		// Use WooCommerce customer data if available, otherwise fall back to user meta
		if ( $wc_customer ) {
			$first_name = $wc_customer->get_first_name() ?: get_user_meta( $user_id, 'first_name', true );
			$last_name  = $wc_customer->get_last_name() ?: get_user_meta( $user_id, 'last_name', true );
			$billing    = array(
				'first_name' => $wc_customer->get_billing_first_name(),
				'last_name'  => $wc_customer->get_billing_last_name(),
				'company'    => $wc_customer->get_billing_company(),
				'address_1'  => $wc_customer->get_billing_address_1(),
				'address_2'  => $wc_customer->get_billing_address_2(),
				'city'       => $wc_customer->get_billing_city(),
				'state'      => $wc_customer->get_billing_state(),
				'postcode'   => $wc_customer->get_billing_postcode(),
				'country'    => $wc_customer->get_billing_country(),
				'email'      => $wc_customer->get_billing_email() ?: $user_data->user_email,
				'phone'      => $wc_customer->get_billing_phone(),
			);
			$shipping   = array(
				'first_name' => $wc_customer->get_shipping_first_name() ?: $billing['first_name'],
				'last_name'  => $wc_customer->get_shipping_last_name() ?: $billing['last_name'],
				'company'    => $wc_customer->get_shipping_company() ?: $billing['company'],
				'address_1'  => $wc_customer->get_shipping_address_1() ?: $billing['address_1'],
				'address_2'  => $wc_customer->get_shipping_address_2() ?: $billing['address_2'],
				'city'       => $wc_customer->get_shipping_city() ?: $billing['city'],
				'state'      => $wc_customer->get_shipping_state() ?: $billing['state'],
				'postcode'   => $wc_customer->get_shipping_postcode() ?: $billing['postcode'],
				'country'    => $wc_customer->get_shipping_country() ?: $billing['country'],
			);
		} else {
			// Fallback to user meta
			$first_name = get_user_meta( $user_id, 'first_name', true ) ?: get_user_meta( $user_id, 'billing_first_name', true );
			$last_name  = get_user_meta( $user_id, 'last_name', true ) ?: get_user_meta( $user_id, 'billing_last_name', true );
			$billing    = array(
				'first_name' => get_user_meta( $user_id, 'billing_first_name', true ),
				'last_name'  => get_user_meta( $user_id, 'billing_last_name', true ),
				'company'    => get_user_meta( $user_id, 'billing_company', true ),
				'address_1'  => get_user_meta( $user_id, 'billing_address_1', true ),
				'address_2'  => get_user_meta( $user_id, 'billing_address_2', true ),
				'city'       => get_user_meta( $user_id, 'billing_city', true ),
				'state'      => get_user_meta( $user_id, 'billing_state', true ),
				'postcode'   => get_user_meta( $user_id, 'billing_postcode', true ),
				'country'    => get_user_meta( $user_id, 'billing_country', true ),
				'email'      => get_user_meta( $user_id, 'billing_email', true ) ?: $user_data->user_email,
				'phone'      => get_user_meta( $user_id, 'billing_phone', true ),
			);
			$shipping   = array(
				'first_name' => get_user_meta( $user_id, 'shipping_first_name', true ) ?: $billing['first_name'],
				'last_name'  => get_user_meta( $user_id, 'shipping_last_name', true ) ?: $billing['last_name'],
				'company'    => get_user_meta( $user_id, 'shipping_company', true ) ?: $billing['company'],
				'address_1'  => get_user_meta( $user_id, 'shipping_address_1', true ) ?: $billing['address_1'],
				'address_2'  => get_user_meta( $user_id, 'shipping_address_2', true ) ?: $billing['address_2'],
				'city'       => get_user_meta( $user_id, 'shipping_city', true ) ?: $billing['city'],
				'state'      => get_user_meta( $user_id, 'shipping_state', true ) ?: $billing['state'],
				'postcode'   => get_user_meta( $user_id, 'shipping_postcode', true ) ?: $billing['postcode'],
				'country'    => get_user_meta( $user_id, 'shipping_country', true ) ?: $billing['country'],
			);
		}

		return array(
			'id'            => $user_id,
			'username'      => $user_data->user_login,
			'email'         => $user_data->user_email,
			'first_name'    => $first_name,
			'last_name'     => $last_name,
			'full_name'     => trim( $first_name . ' ' . $last_name ),
			'phone'         => $billing['phone'],
			'billing'       => $billing,
			'shipping'      => $shipping,
			'profile_image' => $profile_image_url,
			'total_orders'  => $total_orders,
			'orders'        => $order_data,
			'is_guest'      => false,
		);
	}

	// Get a single customer
	public function get_customer( $request ) {
		$user_id = $request['id'];

		// Check if the user exists and is in our customer list (placed an order or has 'customer' role)
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Customer not found.',
					array( 'id' => "The customer with the ID '{$user_id}' does not exist." ),
					404,
					$request->get_route()
				),
				404
			);
		}

		// Check if user is in the customer list
		$is_customer = false;
		// Check if user has 'customer' role
		if ( in_array( 'customer', (array) $user->roles ) ) {
			$is_customer = true;
		} else {
			// Check if user has placed an order
			if ( class_exists( 'WC_Order_Query' ) ) {
				$orders = wc_get_orders(
					array(
						'customer_id' => $user_id,
						'limit'       => 1,
					)
				);
				if ( ! empty( $orders ) ) {
					$is_customer = true;
				}
			}
		}
		if ( ! $is_customer ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Customer not found.',
					array( 'id' => "The customer with the ID '{$user_id}' does not exist or has not placed any orders." ),
					404,
					$request->get_route()
				),
				404
			);
		}

		return new WP_REST_Response(
			$this->format_success_response(
				'Customer retrieved successfully.',
				$this->format_customer_data( $user_id ),
				200
			),
			200
		);
	}

	// Create a new customer
	public function create_customer( $request ) {
		$data   = $request->get_json_params();
		$errors = array(); // Array to collect all validation errors

		// Validate required fields
		$required_fields = array( 'username', 'first_name', 'last_name', 'phone' );
		foreach ( $required_fields as $field ) {
			if ( empty( $data[ $field ] ) ) {
				$errors[ $field ] = "The field '{$field}' is required.";
			}
		}

		// Check if username already exists
		if ( isset( $data['username'] ) && username_exists( $data['username'] ) ) {
			$errors['username'] = "A customer with the username '{$data['username']}' already exists.";
		}

		// Check if email already exists
		if ( isset( $data['email'] ) && email_exists( $data['email'] ) ) {
			$errors['email'] = "A customer with the email '{$data['email']}' already exists.";
		}

		// If there are validation errors, return them all
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

		// Create the customer
		$user_id = wp_insert_user(
			array(
				'user_login' => sanitize_user( $data['username'] ),
				'user_email' => $data['email'] ?? '',
				'user_pass'  => wp_generate_password(),
				'role'       => 'customer',
				'first_name' => sanitize_text_field( $data['first_name'] ),
				'last_name'  => sanitize_text_field( $data['last_name'] ),
			)
		);

		if ( is_wp_error( $user_id ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to create customer.',
					$user_id->get_error_message(),
					500,
					$request->get_route()
				),
				500
			);
		}

		// Update phone number
		update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $data['phone'] ) );

		// Update billing details if provided
		if ( isset( $data['billing'] ) ) {
			foreach ( $data['billing'] as $key => $value ) {
				update_user_meta( $user_id, 'billing_' . $key, sanitize_text_field( $value ) );
			}
		}

		// Update shipping details if provided
		if ( isset( $data['shipping'] ) ) {
			foreach ( $data['shipping'] as $key => $value ) {
				update_user_meta( $user_id, 'shipping_' . $key, sanitize_text_field( $value ) );
			}
		}

		return new WP_REST_Response(
			$this->format_success_response(
				'Customer created successfully.',
				$this->format_customer_data( $user_id ),
				201
			),
			201
		);
	}

	// Update a customer
	public function update_customer( $request ) {
		$data    = $request->get_json_params();
		$user_id = $request['id'];
		$errors  = array(); // Array to collect all validation errors

		// Check if user exists and is a customer (placed order or has 'customer' role)
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Customer not found.',
					array( 'id' => "The customer with the ID '{$user_id}' does not exist." ),
					404,
					$request->get_route()
				),
				404
			);
		}
		$is_customer = false;
		if ( in_array( 'customer', (array) $user->roles ) ) {
			$is_customer = true;
		} elseif ( class_exists( 'WC_Order_Query' ) ) {
				$orders = wc_get_orders(
					array(
						'customer_id' => $user_id,
						'limit'       => 1,
					)
				);
			if ( ! empty( $orders ) ) {
				$is_customer = true;
			}
		}
		if ( ! $is_customer ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Customer not found.',
					array( 'id' => "The customer with the ID '{$user_id}' does not exist or has not placed any orders." ),
					404,
					$request->get_route()
				),
				404
			);
		}

		// Prevent changing username
		if ( isset( $data['username'] ) && $data['username'] !== $user->user_login ) {
			$errors['username'] = 'Username cannot be changed.';
		}

		// Prevent changing email
		if ( isset( $data['email'] ) && $data['email'] !== $user->user_email ) {
			$errors['email'] = 'Email cannot be changed.';
		}

		// If there are validation errors, return them all
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

		// Update fields if provided using WooCommerce customer object if available
		if ( class_exists( 'WC_Customer' ) ) {
			try {
				$wc_customer = new \WC_Customer( $user_id );

				if ( isset( $data['first_name'] ) ) {
					$wc_customer->set_first_name( sanitize_text_field( $data['first_name'] ) );
				}
				if ( isset( $data['last_name'] ) ) {
					$wc_customer->set_last_name( sanitize_text_field( $data['last_name'] ) );
				}
				if ( isset( $data['phone'] ) ) {
					$wc_customer->set_billing_phone( sanitize_text_field( $data['phone'] ) );
				}

				// Update billing details if provided
				if ( isset( $data['billing'] ) ) {
					foreach ( $data['billing'] as $key => $value ) {
						$method = 'set_billing_' . $key;
						if ( method_exists( $wc_customer, $method ) ) {
							$wc_customer->$method( sanitize_text_field( $value ) );
						}
					}
				}

				// Update shipping details if provided
				if ( isset( $data['shipping'] ) ) {
					foreach ( $data['shipping'] as $key => $value ) {
						$method = 'set_shipping_' . $key;
						if ( method_exists( $wc_customer, $method ) ) {
							$wc_customer->$method( sanitize_text_field( $value ) );
						}
					}
				}

				$wc_customer->save();
			} catch ( \Exception $e ) {
				// Fallback to user meta updates
				$this->update_customer_meta( $user_id, $data );
			}
		} else {
			// Fallback to user meta updates
			$this->update_customer_meta( $user_id, $data );
		}

		return new WP_REST_Response(
			$this->format_success_response(
				'Customer updated successfully.',
				$this->format_customer_data( $user_id ),
				200
			),
			200
		);
	}

	// Helper method to update customer meta directly
	private function update_customer_meta( $user_id, $data ) {
		if ( isset( $data['first_name'] ) ) {
			update_user_meta( $user_id, 'first_name', sanitize_text_field( $data['first_name'] ) );
		}
		if ( isset( $data['last_name'] ) ) {
			update_user_meta( $user_id, 'last_name', sanitize_text_field( $data['last_name'] ) );
		}
		if ( isset( $data['phone'] ) ) {
			update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $data['phone'] ) );
		}

		// Update billing details if provided
		if ( isset( $data['billing'] ) ) {
			foreach ( $data['billing'] as $key => $value ) {
				update_user_meta( $user_id, 'billing_' . $key, sanitize_text_field( $value ) );
			}
		}

		// Update shipping details if provided
		if ( isset( $data['shipping'] ) ) {
			foreach ( $data['shipping'] as $key => $value ) {
				update_user_meta( $user_id, 'shipping_' . $key, sanitize_text_field( $value ) );
			}
		}
	}

	// Delete a customer
	public function delete_customer( $request ) {
		$user_id = $request['id'];

		// Check if user exists and is a customer (placed order or has 'customer' role)
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Customer not found.',
					array( 'id' => "The customer with the ID '{$user_id}' does not exist." ),
					404,
					$request->get_route()
				),
				404
			);
		}
		$is_customer = false;
		if ( in_array( 'customer', (array) $user->roles ) ) {
			$is_customer = true;
		} elseif ( class_exists( 'WC_Order_Query' ) ) {
				$orders = wc_get_orders(
					array(
						'customer_id' => $user_id,
						'limit'       => 1,
					)
				);
			if ( ! empty( $orders ) ) {
				$is_customer = true;
			}
		}
		if ( ! $is_customer ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Customer not found.',
					array( 'id' => "The customer with the ID '{$user_id}' does not exist or has not placed any orders." ),
					404,
					$request->get_route()
				),
				404
			);
		}

		$result = wp_delete_user( $user_id );

		if ( ! $result ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to delete customer.',
					array( 'server' => 'An error occurred while deleting the customer.' ),
					500,
					$request->get_route()
				),
				500
			);
		}

		return new WP_REST_Response(
			$this->format_success_response(
				'Customer deleted successfully.',
				array( 'user_id' => $user_id ),
				200
			),
			200
		);
	}
}
