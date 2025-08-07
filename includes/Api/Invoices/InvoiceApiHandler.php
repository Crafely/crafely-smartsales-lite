<?php

namespace CSMSL\Includes\Api\Invoices;

use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class InvoiceApiHandler {

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		// Check if WooCommerce is active
		if ( ! function_exists( 'wc_get_product' ) ) {
			add_action(
				'admin_notices',
				function () {
					echo '<div class="error"><p>AI Smart Sales requires WooCommerce to be installed and activated.</p></div>';
				}
			);
			return;
		}
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		// Basic CRUD operations
		register_rest_route(
			'ai-smart-sales/v1',
			'/invoices',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_invoices' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'create_invoice' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/invoices/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_invoice' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'update_invoice' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'delete_invoice' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		// Trash operations
		register_rest_route(
			'ai-smart-sales/v1',
			'/invoices/trash',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_trash_invoices' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Bulk operations
		register_rest_route(
			'ai-smart-sales/v1',
			'/invoices/restore',
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'bulk_restore_invoices' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/invoices/bulk-delete',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'bulk_delete_invoices' ),
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
		return array(
			'success' => false,
			'message' => $message,
			'data'    => null,
			'error'   => $errors,
		);
	}

	private function format_invoice_response( $invoice ) {
		$outlet_id  = get_post_meta( $invoice->ID, 'outlet_id', true );
		$outlet     = $outlet_id ? get_post( $outlet_id ) : null;
		$line_items = json_decode( get_post_meta( $invoice->ID, 'line_items', true ), true );

		// --- Build customer object in the same format as CustomersApiHandler ---
		$customer_id  = (int) get_post_meta( $invoice->ID, 'customer_id', true );
		$customer     = get_userdata( $customer_id );
		$customer_obj = null;
		if ( $customer ) {
			// Profile image
			$profile_image_id  = get_user_meta( $customer_id, 'profile_image', true );
			$profile_image_url = $profile_image_id ? wp_get_attachment_url( $profile_image_id ) : ( defined( 'SMARTSALES_URL' ) ? SMARTSALES_URL . 'assets/images/avatar.png' : '' );

			// WooCommerce customer object
			$wc_customer = null;
			if ( class_exists( 'WC_Customer' ) ) {
				try {
					$wc_customer = new \WC_Customer( $customer_id );
				} catch ( \Exception $e ) {
				}
			}

			// Orders
			$order_data   = array();
			$total_orders = 0;
			if ( class_exists( 'WooCommerce' ) && function_exists( 'wc_get_orders' ) ) {
				$orders = wc_get_orders(
					array(
						'customer_id' => $customer_id,
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
							'date'     => $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d H:i:s' ) : null,
						);
					}
				}
			}

			// Billing & shipping
			if ( $wc_customer ) {
				$first_name = $wc_customer->get_first_name() ?: get_user_meta( $customer_id, 'first_name', true );
				$last_name  = $wc_customer->get_last_name() ?: get_user_meta( $customer_id, 'last_name', true );
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
					'email'      => $wc_customer->get_billing_email() ?: $customer->user_email,
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
				$first_name = get_user_meta( $customer_id, 'first_name', true ) ?: get_user_meta( $customer_id, 'billing_first_name', true );
				$last_name  = get_user_meta( $customer_id, 'last_name', true ) ?: get_user_meta( $customer_id, 'billing_last_name', true );
				$billing    = array(
					'first_name' => get_user_meta( $customer_id, 'billing_first_name', true ),
					'last_name'  => get_user_meta( $customer_id, 'billing_last_name', true ),
					'company'    => get_user_meta( $customer_id, 'billing_company', true ),
					'address_1'  => get_user_meta( $customer_id, 'billing_address_1', true ),
					'address_2'  => get_user_meta( $customer_id, 'billing_address_2', true ),
					'city'       => get_user_meta( $customer_id, 'billing_city', true ),
					'state'      => get_user_meta( $customer_id, 'billing_state', true ),
					'postcode'   => get_user_meta( $customer_id, 'billing_postcode', true ),
					'country'    => get_user_meta( $customer_id, 'billing_country', true ),
					'email'      => get_user_meta( $customer_id, 'billing_email', true ) ?: $customer->user_email,
					'phone'      => get_user_meta( $customer_id, 'billing_phone', true ),
				);
				$shipping   = array(
					'first_name' => get_user_meta( $customer_id, 'shipping_first_name', true ) ?: $billing['first_name'],
					'last_name'  => get_user_meta( $customer_id, 'shipping_last_name', true ) ?: $billing['last_name'],
					'company'    => get_user_meta( $customer_id, 'shipping_company', true ) ?: $billing['company'],
					'address_1'  => get_user_meta( $customer_id, 'shipping_address_1', true ) ?: $billing['address_1'],
					'address_2'  => get_user_meta( $customer_id, 'shipping_address_2', true ) ?: $billing['address_2'],
					'city'       => get_user_meta( $customer_id, 'shipping_city', true ) ?: $billing['city'],
					'state'      => get_user_meta( $customer_id, 'shipping_state', true ) ?: $billing['state'],
					'postcode'   => get_user_meta( $customer_id, 'shipping_postcode', true ) ?: $billing['postcode'],
					'country'    => get_user_meta( $customer_id, 'shipping_country', true ) ?: $billing['country'],
				);
			}
			$customer_obj = array(
				'id'            => $customer_id,
				'username'      => $customer->user_login,
				'email'         => $customer->user_email,
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
		// --- End customer object ---

		// Process line items to include both original and custom data
		$processed_line_items = array_map(
			function ( $item ) {
				$product   = get_post( $item['product_id'] );
				$line_item = array(
					'product_id'           => (int) $item['product_id'],
					'quantity'             => (int) $item['quantity'],
					'original_name'        => $item['original_name'],
					'original_price'       => number_format( (float) $item['original_price'], 2, '.', '' ),
					'original_description' => $item['original_description'],
				);

				// Include custom fields if they exist
				if ( isset( $item['custom_name'] ) ) {
					$line_item['custom_name'] = $item['custom_name'];
				}
				if ( isset( $item['custom_price'] ) ) {
					$line_item['custom_price'] = number_format( (float) $item['custom_price'], 2, '.', '' );
				}
				if ( isset( $item['custom_description'] ) ) {
					$line_item['custom_description'] = $item['custom_description'];
				}

				// Calculate totals
				$price                   = isset( $item['custom_price'] ) ? (float) $item['custom_price'] : (float) $item['original_price'];
				$line_item['line_total'] = number_format( $price * $item['quantity'], 2, '.', '' );

				return $line_item;
			},
			is_array( $line_items ) ? $line_items : array()
		);

		// Calculate subtotal with proper formatting
		$subtotal = array_sum(
			array_map(
				function ( $item ) {
					return (float) $item['line_total'];
				},
				$processed_line_items
			)
		);

		return array(
			'id'           => (int) $invoice->ID,
			'customer_id'  => $customer_id,
			'customer'     => $customer_obj,
			'outlet_id'    => $outlet_id ? (int) $outlet_id : null,
			'channel'      => get_post_meta( $invoice->ID, 'channel', true ),
			'billing_from' => array(
				'site_name'     => $outlet ? $outlet->post_title : '',
				'store_email'   => $outlet_id ? get_post_meta( $outlet_id, 'store_email', true ) : '',
				'store_address' => $outlet_id ? get_post_meta( $outlet_id, 'store_address', true ) : '',
			),
			'line_items'   => $processed_line_items,
			'vat'          => number_format( (float) get_post_meta( $invoice->ID, 'vat', true ), 2, '.', '' ),
			'subtotal'     => number_format( $subtotal, 2, '.', '' ),
			'issue_date'   => get_post_meta( $invoice->ID, 'issue_date', true ),
			'due_date'     => get_post_meta( $invoice->ID, 'due_date', true ),
			'status'       => $invoice->post_status,
			'created_at'   => $invoice->post_date,
			'updated_at'   => $invoice->post_modified,
		);
	}

	/**
	 * Create an invoice
	 */
	public function create_invoice( $request ) {
		$data   = $request->get_json_params();
		$errors = array();

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

		// Validate outlet_id (optional)
		$outlet_id = intval( $data['outlet_id'] ?? 0 );
		if ( $outlet_id > 0 ) {
			$outlet = get_post( $outlet_id );
			if ( ! $outlet || $outlet->post_type !== 'smartsales_outlet' ) {
				$errors['outlet_id'] = "The outlet with the ID '{$outlet_id}' does not exist.";
			}
		}

		// Validate line_items
		if ( empty( $data['line_items'] ) || ! is_array( $data['line_items'] ) ) {
			$errors['line_items'] = 'At least one line item is required.';
		} else {
			foreach ( $data['line_items'] as $index => $item ) {
				if ( ! isset( $item['product_id'] ) || ! isset( $item['quantity'] ) ) {
					$errors[ "line_items.{$index}" ] = 'Each line item must have a product_id and quantity.';
					continue;
				}

				$product_id = intval( $item['product_id'] );
				$quantity   = intval( $item['quantity'] );

				if ( ! $product_id || $product_id <= 0 ) {
					$errors[ "line_items.{$index}.product_id" ] = 'Product ID must be a valid, non-zero integer.';
				} else {
					$product = get_post( $product_id );
					if ( ! $product || $product->post_type !== 'product' ) {
						$errors[ "line_items.{$index}.product_id" ] = "The product with the ID '{$product_id}' does not exist.";
					} else {
						// Store original product data with proper number formatting
						$data['line_items'][ $index ]['original_name']        = $product->post_title;
						$data['line_items'][ $index ]['original_price']       = number_format( (float) get_post_meta( $product_id, '_price', true ), 2, '.', '' );
						$data['line_items'][ $index ]['original_description'] = $product->post_content;

						// Validate custom fields if provided
						if ( isset( $item['custom_price'] ) ) {
							if ( ! is_numeric( $item['custom_price'] ) || $item['custom_price'] < 0 ) {
								$errors[ "line_items.{$index}.custom_price" ] = 'Custom price must be a non-negative number.';
							}
							$data['line_items'][ $index ]['custom_price'] = number_format( (float) $item['custom_price'], 2, '.', '' );
						}

						if ( isset( $item['custom_name'] ) ) {
							if ( empty( $item['custom_name'] ) || ! is_string( $item['custom_name'] ) ) {
								$errors[ "line_items.{$index}.custom_name" ] = 'Custom name must be a non-empty string.';
							}
							$data['line_items'][ $index ]['custom_name'] = sanitize_text_field( $item['custom_name'] );
						}

						if ( isset( $item['custom_description'] ) ) {
							if ( ! is_string( $item['custom_description'] ) ) {
								$errors[ "line_items.{$index}.custom_description" ] = 'Custom description must be a string.';
							}
							$data['line_items'][ $index ]['custom_description'] = sanitize_textarea_field( $item['custom_description'] );
						}
					}
				}

				if ( $quantity < 1 ) {
					$errors[ "line_items.{$index}.quantity" ] = 'Quantity must be at least 1.';
				}
			}
		}

		// Validate dates if provided
		if ( ! empty( $data['issue_date'] ) && ! strtotime( $data['issue_date'] ) ) {
			$errors['issue_date'] = 'Invalid issue date format. Use YYYY-MM-DD format.';
		}
		if ( ! empty( $data['due_date'] ) && ! strtotime( $data['due_date'] ) ) {
			$errors['due_date'] = 'Invalid due date format. Use YYYY-MM-DD format.';
		}

		// Validate VAT if provided
		if ( isset( $data['vat'] ) && ! is_null( $data['vat'] ) ) {
			$vat = floatval( $data['vat'] );
			if ( $vat < 0 ) {
				$errors['vat'] = 'VAT must be a non-negative number.';
			}
			$data['vat'] = number_format( $vat, 2, '.', '' );
		}

		if ( ! empty( $errors ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Validation failed.',
					$errors,
					400
				),
				400
			);
		}

		// Create invoice post
		$invoice_data = array(
			'post_type'   => 'smartsales_invoice',
			'post_status' => 'publish',
			'post_author' => get_current_user_id(),
		);

		$invoice_id = wp_insert_post( $invoice_data );

		if ( is_wp_error( $invoice_id ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to create invoice.',
					$invoice_id->get_error_messages(),
					500
				),
				500
			);
		}

		// Add invoice meta data
		update_post_meta( $invoice_id, 'customer_id', $customer_id );
		if ( $outlet_id > 0 ) {
			update_post_meta( $invoice_id, 'outlet_id', $outlet_id );
		}
		update_post_meta( $invoice_id, 'channel', 'invoice' );
		update_post_meta( $invoice_id, 'line_items', wp_json_encode( $data['line_items'] ) );
		update_post_meta( $invoice_id, 'vat', $data['vat'] ?? '0.00' );
		update_post_meta( $invoice_id, 'issue_date', $data['issue_date'] ?? gmdate( 'Y-m-d' ) );
		update_post_meta( $invoice_id, 'due_date', $data['due_date'] ?? '' );

		// Get the created invoice
		$invoice = get_post( $invoice_id );

		return new WP_REST_Response(
			$this->format_success_response(
				'Invoice created successfully.',
				$this->format_invoice_response( $invoice ),
				201
			),
			201
		);
	}

	/**
	 * Get all invoices
	 */
	public function get_invoices( $request ) {
		$current_page = $request->get_param( 'current_page' ) ? intval( $request->get_param( 'current_page' ) ) : 1;
		$per_page     = $request->get_param( 'per_page' ) ? intval( $request->get_param( 'per_page' ) ) : 10;

		$args = array(
			'post_type'      => 'smartsales_invoice',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $current_page,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( $customer_id = $request->get_param( 'customer_id' ) ) {
			$args['meta_query'][] = array(
				'key'     => 'customer_id',
				'value'   => intval( $customer_id ),
				'compare' => '=',
			);
		}

		if ( $outlet_id = $request->get_param( 'outlet_id' ) ) {
			$args['meta_query'][] = array(
				'key'     => 'outlet_id',
				'value'   => intval( $outlet_id ),
				'compare' => '=',
			);
		}

		$query    = new \WP_Query( $args );
		$invoices = array_map( array( $this, 'format_invoice_response' ), $query->posts );

		return new WP_REST_Response(
			array(
				'success'    => true,
				'message'    => 'Invoices retrieved successfully.',
				'data'       => $invoices,
				'pagination' => array(
					'total_items'  => $query->found_posts,
					'total_pages'  => $query->max_num_pages,
					'current_page' => $current_page,
					'per_page'     => $per_page,
				),
			),
			200
		);
	}

	/**
	 * Get single invoice
	 */
	public function get_invoice( $request ) {
		$invoice_id = $request->get_param( 'id' );
		$invoice    = get_post( $invoice_id );

		if ( ! $invoice || $invoice->post_type !== 'smartsales_invoice' ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Invoice not found.',
					array( 'id' => "Invoice with ID {$invoice_id} does not exist." ),
					404
				),
				404
			);
		}

		return new WP_REST_Response(
			$this->format_success_response(
				'Invoice retrieved successfully.',
				$this->format_invoice_response( $invoice ),
				200
			),
			200
		);
	}

	/**
	 * Update invoice
	 */
	public function update_invoice( $request ) {
		$invoice_id = $request->get_param( 'id' );
		$invoice    = get_post( $invoice_id );

		if ( ! $invoice || $invoice->post_type !== 'smartsales_invoice' ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Invoice not found.',
					array( 'id' => "Invoice with ID {$invoice_id} does not exist." ),
					404
				),
				404
			);
		}

		$data   = $request->get_json_params();
		$errors = array();

		// Validate customer_id if provided
		if ( isset( $data['customer_id'] ) ) {
			$customer_id = intval( $data['customer_id'] );
			if ( ! $customer_id || $customer_id <= 0 ) {
				$errors['customer_id'] = 'Customer ID must be a valid, non-zero integer.';
			} else {
				$customer = get_userdata( $customer_id );
				if ( ! $customer ) {
					$errors['customer_id'] = "The customer with the ID '{$customer_id}' does not exist.";
				}
			}
		}

		// Validate outlet_id if provided (optional)
		if ( isset( $data['outlet_id'] ) ) {
			$outlet_id = intval( $data['outlet_id'] );
			if ( $outlet_id > 0 ) {
				$outlet = get_post( $outlet_id );
				if ( ! $outlet || $outlet->post_type !== 'smartsales_outlet' ) {
					$errors['outlet_id'] = "The outlet with the ID '{$outlet_id}' does not exist.";
				}
			}
		}

		// Validate line_items if provided
		if ( isset( $data['line_items'] ) ) {
			if ( ! is_array( $data['line_items'] ) ) {
				$errors['line_items'] = 'Line items must be an array.';
			} else {
				foreach ( $data['line_items'] as $index => $item ) {
					if ( ! isset( $item['product_id'] ) || ! isset( $item['quantity'] ) ) {
						$errors[ "line_items.{$index}" ] = 'Each line item must have a product_id and quantity.';
						continue;
					}

					$product_id = intval( $item['product_id'] );
					$quantity   = intval( $item['quantity'] );

					if ( ! $product_id || $product_id <= 0 ) {
						$errors[ "line_items.{$index}.product_id" ] = 'Product ID must be a valid, non-zero integer.';
					} else {
						$product = get_post( $product_id );
						if ( ! $product || $product->post_type !== 'product' ) {
							$errors[ "line_items.{$index}.product_id" ] = "The product with the ID '{$product_id}' does not exist.";
						} else {
							// Store original product data with proper number formatting
							$data['line_items'][ $index ]['original_name']        = $product->post_title;
							$data['line_items'][ $index ]['original_price']       = number_format( (float) get_post_meta( $product_id, '_price', true ), 2, '.', '' );
							$data['line_items'][ $index ]['original_description'] = $product->post_content;

							// Validate custom fields if provided
							if ( isset( $item['custom_price'] ) ) {
								if ( ! is_numeric( $item['custom_price'] ) || $item['custom_price'] < 0 ) {
									$errors[ "line_items.{$index}.custom_price" ] = 'Custom price must be a non-negative number.';
								}
								$data['line_items'][ $index ]['custom_price'] = number_format( (float) $item['custom_price'], 2, '.', '' );
							}

							if ( isset( $item['custom_name'] ) ) {
								if ( empty( $item['custom_name'] ) || ! is_string( $item['custom_name'] ) ) {
									$errors[ "line_items.{$index}.custom_name" ] = 'Custom name must be a non-empty string.';
								}
								$data['line_items'][ $index ]['custom_name'] = sanitize_text_field( $item['custom_name'] );
							}

							if ( isset( $item['custom_description'] ) ) {
								if ( ! is_string( $item['custom_description'] ) ) {
									$errors[ "line_items.{$index}.custom_description" ] = 'Custom description must be a string.';
								}
								$data['line_items'][ $index ]['custom_description'] = sanitize_textarea_field( $item['custom_description'] );
							}
						}
					}

					if ( $quantity < 1 ) {
						$errors[ "line_items.{$index}.quantity" ] = 'Quantity must be at least 1.';
					}
				}
			}
		}

		// Validate dates if provided
		if ( isset( $data['issue_date'] ) && ! strtotime( $data['issue_date'] ) ) {
			$errors['issue_date'] = 'Invalid issue date format. Use YYYY-MM-DD format.';
		}
		if ( isset( $data['due_date'] ) && ! strtotime( $data['due_date'] ) ) {
			$errors['due_date'] = 'Invalid due date format. Use YYYY-MM-DD format.';
		}

		// Validate VAT if provided
		if ( isset( $data['vat'] ) && ! is_null( $data['vat'] ) ) {
			$vat = floatval( $data['vat'] );
			if ( $vat < 0 ) {
				$errors['vat'] = 'VAT must be a non-negative number.';
			}
			$data['vat'] = number_format( $vat, 2, '.', '' );
		}

		if ( ! empty( $errors ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Validation failed.',
					$errors,
					400
				),
				400
			);
		}

		// Update invoice meta
		if ( isset( $data['customer_id'] ) ) {
			update_post_meta( $invoice_id, 'customer_id', intval( $data['customer_id'] ) );
		}
		if ( isset( $data['outlet_id'] ) ) {
			if ( $data['outlet_id'] > 0 ) {
				update_post_meta( $invoice_id, 'outlet_id', intval( $data['outlet_id'] ) );
			} else {
				delete_post_meta( $invoice_id, 'outlet_id' );
			}
		}
		if ( isset( $data['line_items'] ) ) {
			update_post_meta( $invoice_id, 'line_items', wp_json_encode( $data['line_items'] ) );
		}
		if ( isset( $data['vat'] ) ) {
			update_post_meta( $invoice_id, 'vat', $data['vat'] );
		}
		if ( isset( $data['issue_date'] ) ) {
			update_post_meta( $invoice_id, 'issue_date', $data['issue_date'] );
		}
		if ( isset( $data['due_date'] ) ) {
			update_post_meta( $invoice_id, 'due_date', $data['due_date'] );
		}

		$updated_invoice = get_post( $invoice_id );

		return new WP_REST_Response(
			$this->format_success_response(
				'Invoice updated successfully.',
				$this->format_invoice_response( $updated_invoice ),
				200
			),
			200
		);
	}

	/**
	 * Delete invoice (move to trash)
	 */
	public function delete_invoice( $request ) {
		$invoice_id = $request->get_param( 'id' );
		$result     = wp_trash_post( $invoice_id );

		if ( ! $result ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to delete invoice.',
					array( 'id' => "Could not delete invoice with ID {$invoice_id}." ),
					500
				),
				500
			);
		}

		return new WP_REST_Response(
			$this->format_success_response(
				'Invoice moved to trash successfully.',
				array(),
				200
			),
			200
		);
	}

	/**
	 * Get trash invoices
	 */
	public function get_trash_invoices( $request ) {
		$current_page = $request->get_param( 'current_page' ) ? intval( $request->get_param( 'current_page' ) ) : 1;
		$per_page     = $request->get_param( 'per_page' ) ? intval( $request->get_param( 'per_page' ) ) : 10;

		$args = array(
			'post_type'      => 'smartsales_invoice',
			'post_status'    => 'trash',
			'posts_per_page' => $per_page,
			'paged'          => $current_page,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$query    = new \WP_Query( $args );
		$invoices = array_map( array( $this, 'format_invoice_response' ), $query->posts );

		return new WP_REST_Response(
			array(
				'success'    => true,
				'message'    => 'Trash invoices retrieved successfully.',
				'data'       => $invoices,
				'pagination' => array(
					'total_items'  => $query->found_posts,
					'total_pages'  => $query->max_num_pages,
					'current_page' => $current_page,
					'per_page'     => $per_page,
				),
			),
			200
		);
	}

	/**
	 * Bulk restore invoices from trash
	 */
	public function bulk_restore_invoices( $request ) {
		$data = $request->get_json_params();

		if ( empty( $data['ids'] ) || ! is_array( $data['ids'] ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Invalid request.',
					array( 'ids' => 'Invoice IDs array is required.' ),
					400
				),
				400
			);
		}

		$restored = array();
		$failed   = array();

		foreach ( $data['ids'] as $invoice_id ) {
			$result = wp_untrash_post( $invoice_id );
			if ( $result ) {
				$restored[] = $invoice_id;
			} else {
				$failed[] = $invoice_id;
			}
		}

		return new WP_REST_Response(
			$this->format_success_response(
				'Bulk restore operation completed.',
				array(
					'restored' => $restored,
					'failed'   => $failed,
				),
				200
			),
			200
		);
	}

	/**
	 * Bulk delete invoices (move to trash)
	 */
	public function bulk_delete_invoices( $request ) {
		$data = $request->get_json_params();

		if ( empty( $data['ids'] ) || ! is_array( $data['ids'] ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Invalid request.',
					array( 'ids' => 'Invoice IDs array is required.' ),
					400
				),
				400
			);
		}

		$deleted = array();
		$failed  = array();

		foreach ( $data['ids'] as $invoice_id ) {
			$result = wp_trash_post( $invoice_id );
			if ( $result ) {
				$deleted[] = $invoice_id;
			} else {
				$failed[] = $invoice_id;
			}
		}

		return new WP_REST_Response(
			$this->format_success_response(
				'Bulk delete operation completed.',
				array(
					'deleted' => $deleted,
					'failed'  => $failed,
				),
				200
			),
			200
		);
	}
}
