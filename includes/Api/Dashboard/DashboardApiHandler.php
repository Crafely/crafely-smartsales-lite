<?php

namespace CSMSL\Includes\Api\Dashboard;

use WP_REST_Response;
use WP_Error;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class DashboardApiHandler {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		$routes = array(
			'/dashboard/summary'           => array(
				'methods'  => 'GET',
				'callback' => array( $this, 'get_dashboard_summary' ),
			),
			'/dashboard/sales'             => array(
				'methods'  => 'GET',
				'callback' => array( $this, 'get_sales_analytics' ),
			),
			'/dashboard/customers'         => array(
				'methods'  => 'GET',
				'callback' => array( $this, 'get_customer_analytics' ),
			),
			'/dashboard/products'          => array(
				'methods'  => 'GET',
				'callback' => array( $this, 'get_product_analytics' ),
			),
			'/dashboard/outlets'           => array(
				'methods'  => 'GET',
				'callback' => array( $this, 'get_outlet_analytics' ),
			),
			'/dashboard/recent-activities' => array(
				'methods'  => 'GET',
				'callback' => array( $this, 'get_recent_activities' ),
			),
		);

		foreach ( $routes as $route => $config ) {
			register_rest_route(
				'ai-smart-sales/v1',
				$route,
				array(
					'methods'             => $config['methods'],
					'callback'            => $config['callback'],
					'permission_callback' => array( $this, 'check_permission' ),
				)
			);
		}
	}

	/**
	 * Check if the request has valid permission
	 */
	public function check_permission( $request ) {
		// Check if user is logged in and has appropriate capabilities
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Get current user
		$user = wp_get_current_user();

		// Check if user has any of our POS roles or is an administrator
		$allowed_roles = array( 'administrator', 'aipos_outlet_manager', 'aipos_cashier', 'aipos_shop_manager' );
		$user_roles    = (array) $user->roles;

		if ( ! array_intersect( $allowed_roles, $user_roles ) ) {
			return false;
		}

		return true;
	}

	private function format_success_response( $message, $data = array(), $statusCode = 200 ) {
		return array(
			'success' => true,
			'message' => $message,
			'data'    => $data,
		);
	}

	private function format_error_response( $message, $code = 'error', $status = 400 ) {
		return new WP_Error(
			$code,
			$message,
			array( 'status' => $status )
		);
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

	private function get_date_range( $request ) {
		try {
			$range        = $request->get_param( 'range' ) ?: 'last_30_days';
			$custom_start = $request->get_param( 'start_date' );
			$custom_end   = $request->get_param( 'end_date' );

			// Validate custom date range
			if ( $range === 'custom' && ( ! $custom_start || ! $custom_end ) ) {
				return $this->format_error_response(
					__( 'Start date and end date are required for custom range.', 'crafely-smartsales-lite' ),
					'invalid_date_range'
				);
			}

			$end_date   = date_i18n( 'd-m-Y', current_time( 'timestamp' ) );
			$start_date = '';

			switch ( $range ) {
				case 'today':
					$start_date = $end_date;
					break;
				case 'yesterday':
					$start_date = date_i18n( 'd-m-Y', strtotime( '-1 day', current_time( 'timestamp' ) ) );
					$end_date   = $start_date;
					break;
				case 'this_week':
					$start_date = date_i18n( 'd-m-Y', strtotime( 'monday this week', current_time( 'timestamp' ) ) );
					break;
				case 'last_week':
					$start_date = date_i18n( 'd-m-Y', strtotime( 'monday last week', current_time( 'timestamp' ) ) );
					$end_date   = date_i18n( 'd-m-Y', strtotime( 'sunday last week', current_time( 'timestamp' ) ) );
					break;
				case 'this_month':
					$start_date = date_i18n( 'd-m-Y', strtotime( date_i18n( 'Y-m-01', current_time( 'timestamp' ) ) ) );

					break;
				case 'last_month':
					$start_date = date_i18n( 'd-m-Y', strtotime( 'first day of last month', current_time( 'timestamp' ) ) );
					$end_date   = date_i18n( 'd-m-Y', strtotime( 'last day of last month', current_time( 'timestamp' ) ) );

					break;
				case 'this_year':
					$start_date = date_i18n( 'd-m-Y', strtotime( 'first day of january this year', current_time( 'timestamp' ) ) );
					break;
				case 'last_year':
					$start_date = date_i18n( 'd-m-Y', strtotime( 'first day of january last year', current_time( 'timestamp' ) ) );
					$end_date   = date_i18n( 'd-m-Y', strtotime( 'last day of december last year', current_time( 'timestamp' ) ) );

					break;
				case 'custom':
					if ( $custom_start && $custom_end ) {
						$start_date = date_i18n( 'd-m-Y', strtotime( $custom_start ) );
						$end_date   = date_i18n( 'd-m-Y', strtotime( $custom_end ) );
					}
					break;
				default: // last_30_days
					$start_date = date_i18n( 'd-m-Y', strtotime( '-30 days', current_time( 'timestamp' ) ) );
			}

			return array(
				'start' => $start_date,
				'end'   => $end_date,
			);
		} catch ( Exception $e ) {
			return $this->format_error_response(
				__( 'Invalid date range provided.', 'crafely-smartsales-lite' ),
				'invalid_date_range'
			);
		}
	}

	private function get_orders_by_outlet( $orders, $outlet_id ) {
		if ( ! $outlet_id ) {
			return $orders;
		}

		return array_filter(
			$orders,
			function ( $order ) use ( $outlet_id ) {
				if ( $this->is_refund( $order ) ) {
					return false; // Skip refunds
				}
				$order_outlet_id = $order->get_meta( '_created_by_outlet_id' );
				return $order_outlet_id == $outlet_id;
			}
		);
	}

	public function get_dashboard_summary( $request ) {
		try {
			$date_range = $this->get_date_range( $request );
			if ( is_wp_error( $date_range ) ) {
				return $date_range;
			}

			// Get outlet filter
			$outlet_id = $request->get_param( 'outlet_id' );

			// Validate outlet if specified
			if ( $outlet_id ) {
				$outlet = get_post( $outlet_id );
				if ( ! $outlet || $outlet->post_type !== 'outlet' ) {
					return $this->format_error_response(
						__( 'Invalid outlet ID specified.', 'crafely-smartsales-lite' ),
						'invalid_outlet',
						400
					);
				}
			}

			// Get orders within date range
			$orders = wc_get_orders(
				array(
					'date_created' => $date_range['start'] . '...' . $date_range['end'],
					'status'       => array( 'completed', 'processing' ),
					'limit'        => -1,
					'type'         => 'shop_order', // Ensure we only get orders, not refunds
				)
			);

			// Filter orders by outlet if specified
			if ( $outlet_id ) {
				$orders = $this->get_orders_by_outlet( $orders, $outlet_id );
			}

			// Calculate basic metrics
			$total_sales     = 0;
			$total_orders    = count( $orders );
			$total_items     = 0;
			$channels_data   = array();
			$payment_methods = array();

			foreach ( $orders as $order ) {
				// Skip refunds
				if ( $this->is_refund( $order ) ) {
					continue;
				}

				$total_sales += $order->get_total();
				$total_items += $order->get_item_count();

				// Track sales by channel - with proper error handling
				if ( taxonomy_exists( 'crafsmli_channel' ) ) {
					$channels = wp_get_post_terms( $order->get_id(), 'crafsmli_channel' );
					if ( ! is_wp_error( $channels ) && ! empty( $channels ) ) {
						foreach ( $channels as $channel ) {
							if ( ! isset( $channels_data[ $channel->name ] ) ) {
								$channels_data[ $channel->name ] = 0;
							}
							$channels_data[ $channel->name ] += $order->get_total();
						}
					} else {
						// If no channel is assigned, track as "Uncategorized"
						if ( ! isset( $channels_data['Uncategorized'] ) ) {
							$channels_data['Uncategorized'] = 0;
						}
						$channels_data['Uncategorized'] += $order->get_total();
					}
				}

				// Track payment methods
				$method = $order->get_payment_method_title() ?: __( 'Unknown', 'crafely-smartsales-lite' );
				if ( ! isset( $payment_methods[ $method ] ) ) {
					$payment_methods[ $method ] = 0;
				}
				++$payment_methods[ $method ];
			}

			// For product stats, filter by outlet if specified
			// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$products_query = array(
				'limit'  => -1,
				'status' => 'publish',
			);

			if ( $outlet_id ) {
				$products_query['meta_query'] = array(
					array(
						'key'   => '_outlet_id',
						'value' => $outlet_id,
					),
				);
			}
			$products = wc_get_products( $products_query );

			$low_stock_threshold   = get_option( 'woocommerce_notify_low_stock_amount', 2 );
			$low_stock_count       = 0;
			$out_of_stock_count    = 0;
			$total_inventory_value = 0;

			foreach ( $products as $product ) {
				if ( $product->managing_stock() ) {
					$stock = $product->get_stock_quantity();
					if ( $stock <= 0 ) {
						++$out_of_stock_count;
					} elseif ( $stock <= $low_stock_threshold ) {
						++$low_stock_count;
					}
					// Convert price to float before multiplication
					$regular_price          = (float) $product->get_regular_price();
					$total_inventory_value += $stock * $regular_price;
				}
			}

			// Get customer stats (registered + guest)
			$customer_ids    = array();
			$guest_customers = array();
			$order_query     = array(
				'limit'  => -1,
				'return' => 'ids',
			);
			$orders          = class_exists( 'WC_Order_Query' ) ? wc_get_orders( $order_query ) : array();
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
							$email = $order->get_billing_email();
							if ( $email ) {
								$guest_id = 'guest_' . md5( $email );
								if ( ! isset( $guest_customers[ $guest_id ] ) ) {
									$guest_customers[ $guest_id ] = true;
								}
							}
						}
					}
				}
			}
			// Also include users with 'customer' role
			$role_customers = get_users(
				array(
					'role'   => 'customer',
					'fields' => 'ID',
				)
			);
			foreach ( $role_customers as $id ) {
				if ( ! in_array( $id, $customer_ids ) ) {
					$customer_ids[] = $id;
				}
			}
			$total_customers = count( $customer_ids ) + count( $guest_customers );

			// Get outlet stats
			$outlets       = get_posts(
				array(
					'post_type'      => 'outlet',
					'posts_per_page' => -1,
				)
			);
			$total_outlets = count( $outlets );

			// Calculate average order value
			$average_order_value = $total_orders > 0 ? $total_sales / $total_orders : 0;

			return new WP_REST_Response(
				$this->format_success_response(
					'Dashboard summary retrieved successfully.',
					array(
						'date_range' => $date_range,
						'sales'      => array(
							'total'               => round( $total_sales, 2 ),
							'average_order_value' => round( $average_order_value, 2 ),
							'total_orders'        => $total_orders,
							'total_items_sold'    => $total_items,
							'by_channel'          => $channels_data,
							'payment_methods'     => $payment_methods,
						),
						'inventory'  => array(
							'total_products' => count( $products ),
							'low_stock'      => $low_stock_count,
							'out_of_stock'   => $out_of_stock_count,
							'total_value'    => round( $total_inventory_value, 2 ),
						),
						'customers'  => array(
							'total' => $total_customers,
						),
						'outlets'    => array(
							'total' => $total_outlets,
						),
					)
				),
				200
			);
		} catch ( Exception $e ) {
			return $this->format_error_response(
				__( 'An error occurred while fetching dashboard summary.', 'crafely-smartsales-lite' ) . ' ' . $e->getMessage(),
				'dashboard_summary_error',
				500
			);
		}
	}

	public function get_sales_analytics( $request ) {
		$date_range = $this->get_date_range( $request );
		$outlet_id  = $request->get_param( 'outlet_id' );

		// Get orders within date range
		$orders = wc_get_orders(
			array(
				'date_created' => $date_range['start'] . '...' . $date_range['end'],
				'status'       => array( 'completed', 'processing' ),
				'limit'        => -1,
				'type'         => 'shop_order', // Only get orders, not refunds
			)
		);

		// Filter orders by outlet if specified
		if ( $outlet_id ) {
			$orders = $this->get_orders_by_outlet( $orders, $outlet_id );
		}

		$sales_by_date         = array();
		$sales_by_outlet       = array();
		$sales_by_payment      = array();
		$sales_by_hour         = array_fill( 0, 24, 0 );
		$best_selling_products = array();

		foreach ( $orders as $order ) {
			// Skip refunds
			if ( $this->is_refund( $order ) ) {
				continue;
			}

			$date  = $order->get_date_created()->format( 'd-m-Y' );
			$hour  = $order->get_date_created()->format( 'G' ); // 24-hour format without leading zeros
			$total = $order->get_total();

			// Sales by date
			if ( ! isset( $sales_by_date[ $date ] ) ) {
				$sales_by_date[ $date ] = 0;
			}
			$sales_by_date[ $date ] += $total;

			// Sales by hour
			$sales_by_hour[ $hour ] += $total;

			// Sales by outlet
			$outlet_id = $order->get_meta( '_created_by_outlet_id' );
			if ( $outlet_id ) {
				$outlet_name = get_the_title( $outlet_id ) ?: 'Unknown Outlet';
				if ( ! isset( $sales_by_outlet[ $outlet_name ] ) ) {
					$sales_by_outlet[ $outlet_name ] = 0;
				}
				$sales_by_outlet[ $outlet_name ] += $total;
			}

			// Sales by payment method
			$payment_method = $order->get_payment_method_title();
			if ( ! isset( $sales_by_payment[ $payment_method ] ) ) {
				$sales_by_payment[ $payment_method ] = 0;
			}
			$sales_by_payment[ $payment_method ] += $total;

			// Track product sales
			foreach ( $order->get_items() as $item ) {
				$product_id = $item->get_product_id();
				if ( ! isset( $best_selling_products[ $product_id ] ) ) {
					$best_selling_products[ $product_id ] = array(
						'id'       => $product_id,
						'name'     => $item->get_name(),
						'quantity' => 0,
						'total'    => 0,
					);
				}
				$best_selling_products[ $product_id ]['quantity'] += $item->get_quantity();
				$best_selling_products[ $product_id ]['total']    += $item->get_total();
			}
		}

		// Sort best selling products by quantity
		uasort(
			$best_selling_products,
			function ( $a, $b ) {
				return $b['quantity'] <=> $a['quantity'];
			}
		);

		// Take top 10 products
		$best_selling_products = array_slice( $best_selling_products, 0, 10 );

		return new WP_REST_Response(
			$this->format_success_response(
				'Sales analytics retrieved successfully.',
				array(
					'date_range'            => $date_range,
					'sales_by_date'         => $sales_by_date,
					'sales_by_hour'         => $sales_by_hour,
					'sales_by_outlet'       => $sales_by_outlet,
					'sales_by_payment'      => $sales_by_payment,
					'best_selling_products' => array_values( $best_selling_products ),
				)
			),
			200
		);
	}

	public function get_customer_analytics( $request ) {
		$date_range = $this->get_date_range( $request );
		$outlet_id  = $request->get_param( 'outlet_id' );

		// Get all customers
		$customers       = get_users( array( 'role' => 'customer' ) );
		$total_customers = count( $customers );

		// Get orders within date range
		$orders = wc_get_orders(
			array(
				'date_created' => $date_range['start'] . '...' . $date_range['end'],
				'status'       => array( 'completed', 'processing' ),
				'limit'        => -1,
				'type'         => 'shop_order', // Only get orders, not refunds
			)
		);

		// Filter orders by outlet if specified
		if ( $outlet_id ) {
			$orders = $this->get_orders_by_outlet( $orders, $outlet_id );
		}

		$customer_stats  = array();
		$new_customers   = 0;
		$total_spent     = 0;
		$order_frequency = array();

		foreach ( $orders as $order ) {
			// Skip refunds
			if ( $this->is_refund( $order ) ) {
				continue;
			}

			$customer_id  = $order->get_customer_id();
			$total        = $order->get_total();
			$total_spent += $total;

			if ( ! isset( $customer_stats[ $customer_id ] ) ) {
				$customer_stats[ $customer_id ] = array(
					'orders'      => 0,
					'total_spent' => 0,
				);
			}

			++$customer_stats[ $customer_id ]['orders'];
			$customer_stats[ $customer_id ]['total_spent'] += $total;

			// Track order frequency
			if ( ! isset( $order_frequency[ $customer_id ] ) ) {
				$order_frequency[ $customer_id ] = array();
			}
			$order_frequency[ $customer_id ][] = $order->get_date_created()->getTimestamp();
		}

		// Calculate average order value and frequency
		$average_order_value = count( $orders ) > 0 ? $total_spent / count( $orders ) : 0;

		// Calculate customer segments based on RFM
		$segments = $this->calculate_customer_segments( $customer_stats, $order_frequency );

		// Get top customers
		uasort(
			$customer_stats,
			function ( $a, $b ) {
				return $b['total_spent'] <=> $a['total_spent'];
			}
		);

		$top_customers = array();
		$i             = 0;
		foreach ( $customer_stats as $customer_id => $stats ) {
			if ( $i >= 10 ) {
				break;
			}

			$customer = get_userdata( $customer_id );
			if ( $customer ) {
				$top_customers[] = array(
					'id'          => $customer_id,
					'name'        => $customer->display_name,
					'email'       => $customer->user_email,
					'orders'      => $stats['orders'],
					'total_spent' => round( $stats['total_spent'], 2 ),
				);
			}
			++$i;
		}

		return new WP_REST_Response(
			$this->format_success_response(
				'Customer analytics retrieved successfully.',
				array(
					'date_range'          => $date_range,
					'total_customers'     => $total_customers,
					'new_customers'       => $new_customers,
					'average_order_value' => round( $average_order_value, 2 ),
					'top_customers'       => $top_customers,
					'customer_segments'   => $segments,
				)
			),
			200
		);
	}

	private function calculate_customer_segments( $customer_stats, $order_frequency ) {
		$segments = array(
			'vip'       => 0,
			'loyal'     => 0,
			'potential' => 0,
			'new'       => 0,
			'dormant'   => 0,
		);

		foreach ( $customer_stats as $customer_id => $stats ) {
			// Calculate recency
			$last_order   = max( $order_frequency[ $customer_id ] );
			$recency_days = ( time() - $last_order ) / ( 60 * 60 * 24 );

			// Calculate frequency
			$frequency = $stats['orders'];

			// Calculate monetary value
			$monetary = $stats['total_spent'];

			// Simple segmentation logic
			if ( $frequency > 10 && $monetary > 1000 && $recency_days < 30 ) {
				++$segments['vip'];
			} elseif ( $frequency > 5 && $monetary > 500 && $recency_days < 60 ) {
				++$segments['loyal'];
			} elseif ( $frequency > 2 && $monetary > 200 && $recency_days < 90 ) {
				++$segments['potential'];
			} elseif ( $recency_days < 30 ) {
				++$segments['new'];
			} else {
				++$segments['dormant'];
			}
		}

		return $segments;
	}

	public function get_product_analytics( $request ) {
		$date_range = $this->get_date_range( $request );
		$outlet_id  = $request->get_param( 'outlet_id' );

		// Get products query
		$products_query = array(
			'limit'  => -1,
			'status' => 'publish',
		);

		// Add outlet filter if specified
		if ( $outlet_id ) {
			$products_query['meta_query'] = array(
				array(
					'key'   => '_outlet_id',
					'value' => $outlet_id,
				),
			);
		}

		$products = wc_get_products( $products_query );

		$total_products      = count( $products );
		$low_stock_threshold = get_option( 'woocommerce_notify_low_stock_amount', 2 );

		$inventory_stats = array(
			'total_products' => $total_products,
			'low_stock'      => 0,
			'out_of_stock'   => 0,
			'in_stock'       => 0,
			'total_value'    => 0,
		);

		$category_stats = array();
		$price_ranges   = array(
			'0-10'    => 0,
			'11-50'   => 0,
			'51-100'  => 0,
			'101-500' => 0,
			'501+'    => 0,
		);

		foreach ( $products as $product ) {
			// Inventory stats
			if ( $product->managing_stock() ) {
				$stock         = (int) $product->get_stock_quantity();
				$regular_price = (float) $product->get_regular_price();

				if ( $stock <= 0 ) {
					++$inventory_stats['out_of_stock'];
				} elseif ( $stock <= $low_stock_threshold ) {
					++$inventory_stats['low_stock'];
				} else {
					++$inventory_stats['in_stock'];
				}

				// Only add to total value if both stock and price are valid numbers
				if ( $regular_price > 0 ) {
					$inventory_stats['total_value'] += $stock * $regular_price;
				}
			}

			// Category stats
			$categories = get_the_terms( $product->get_id(), 'product_cat' );
			if ( $categories ) {
				foreach ( $categories as $category ) {
					if ( ! isset( $category_stats[ $category->name ] ) ) {
						$category_stats[ $category->name ] = 0;
					}
					++$category_stats[ $category->name ];
				}
			}

			// Price range stats
			$price = $product->get_regular_price();
			if ( $price <= 10 ) {
				++$price_ranges['0-10'];
			} elseif ( $price <= 50 ) {
				++$price_ranges['11-50'];
			} elseif ( $price <= 100 ) {
				++$price_ranges['51-100'];
			} elseif ( $price <= 500 ) {
				++$price_ranges['101-500'];
			} else {
				++$price_ranges['501+'];
			}
		}

		// Get orders
		$orders = wc_get_orders(
			array(
				'date_created' => $date_range['start'] . '...' . $date_range['end'],
				'status'       => array( 'completed', 'processing' ),
				'limit'        => -1,
			)
		);

		// Filter orders by outlet if specified
		if ( $outlet_id ) {
			$orders = $this->get_orders_by_outlet( $orders, $outlet_id );
		}

		$sales_by_product = array();
		foreach ( $orders as $order ) {
			foreach ( $order->get_items() as $item ) {
				$product_id = $item->get_product_id();
				if ( ! isset( $sales_by_product[ $product_id ] ) ) {
					$sales_by_product[ $product_id ] = array(
						'id'       => $product_id,
						'name'     => $item->get_name(),
						'quantity' => 0,
						'revenue'  => 0,
					);
				}
				$sales_by_product[ $product_id ]['quantity'] += $item->get_quantity();
				$sales_by_product[ $product_id ]['revenue']  += $item->get_total();
			}
		}

		// Sort products by revenue
		uasort(
			$sales_by_product,
			function ( $a, $b ) {
				return $b['revenue'] <=> $a['revenue'];
			}
		);

		// Get top 10 products
		$top_products = array_slice( $sales_by_product, 0, 10 );

		return new WP_REST_Response(
			$this->format_success_response(
				'Product analytics retrieved successfully.',
				array(
					'date_range'      => $date_range,
					'inventory_stats' => $inventory_stats,
					'category_stats'  => $category_stats,
					'price_ranges'    => $price_ranges,
					'top_products'    => array_values( $top_products ),
				)
			),
			200
		);
	}

	public function get_outlet_analytics( $request ) {
		$date_range = $this->get_date_range( $request );

		// Get all outlets
		$outlets = get_posts(
			array(
				'post_type'      => 'outlet',
				'posts_per_page' => -1,
			)
		);

		$outlet_stats = array();
		foreach ( $outlets as $outlet ) {
			$outlet_stats[ $outlet->ID ] = array(
				'id'                  => $outlet->ID,
				'name'                => $outlet->post_title,
				'total_sales'         => 0,
				'total_orders'        => 0,
				'average_order_value' => 0,
				'counters'            => 0,
				'staff'               => 0,
			);

			// Get counters for this outlet
			$counters                                = get_posts(
				array(
					'post_type'      => 'counter',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'   => 'counter_outlet_id',
							'value' => $outlet->ID,
						),
					),
				)
			);
			$outlet_stats[ $outlet->ID ]['counters'] = count( $counters );

			// Get staff count
			// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			$staff = get_users(
				array(
					'meta_key'   => 'assigned_outlet_id',
					'meta_value' => $outlet->ID,
				)
			);
			// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			$outlet_stats[ $outlet->ID ]['staff'] = count( $staff );
		}

		// Get orders within date range
		$orders = wc_get_orders(
			array(
				'date_created' => $date_range['start'] . '...' . $date_range['end'],
				'status'       => array( 'completed', 'processing' ),
				'limit'        => -1,
			)
		);

		foreach ( $orders as $order ) {
			$outlet_id = $order->get_meta( '_created_by_outlet_id' );
			if ( $outlet_id && isset( $outlet_stats[ $outlet_id ] ) ) {
				$outlet_stats[ $outlet_id ]['total_sales'] += $order->get_total();
				++$outlet_stats[ $outlet_id ]['total_orders'];
			}
		}

		// Calculate average order value for each outlet
		foreach ( $outlet_stats as &$stats ) {
			$stats['average_order_value'] = $stats['total_orders'] > 0
				? $stats['total_sales'] / $stats['total_orders']
				: 0;
		}

		// Sort outlets by total sales
		uasort(
			$outlet_stats,
			function ( $a, $b ) {
				return $b['total_sales'] <=> $a['total_sales'];
			}
		);

		return new WP_REST_Response(
			$this->format_success_response(
				'Outlet analytics retrieved successfully.',
				array(
					'date_range'    => $date_range,
					'total_outlets' => count( $outlets ),
					'outlet_stats'  => array_values( $outlet_stats ),
				)
			),
			200
		);
	}

	public function get_recent_activities( $request ) {
		$limit     = $request->get_param( 'limit' ) ?: 20;
		$outlet_id = $request->get_param( 'outlet_id' );

		// Get recent orders with outlet filter if specified
		$orders_args = array(
			'limit'   => $limit,
			'orderby' => 'date',
			'order'   => 'DESC',
			'type'    => 'shop_order', // Only get orders, not refunds
		);

		$recent_orders = wc_get_orders( $orders_args );

		if ( $outlet_id ) {
			$recent_orders = $this->get_orders_by_outlet( $recent_orders, $outlet_id );
		}

		// Get recent products with outlet filter if specified
		$products_args = array(
			'post_type'      => 'product',
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( $outlet_id ) {
			$products_args['meta_query'] = array(
				array(
					'key'   => '_outlet_id',
					'value' => $outlet_id,
				),
			);
		}

		$recent_products = get_posts( $products_args );

		// Get recent customers
		$recent_customers = get_users(
			array(
				'role'    => 'customer',
				'number'  => $limit,
				'orderby' => 'registered',
				'order'   => 'DESC',
			)
		);

		$activities = array();

		// Format orders
		foreach ( $recent_orders as $order ) {
			// Skip refunds
			if ( $this->is_refund( $order ) ) {
				continue;
			}

			$activities[] = array(
				'type'      => 'order',
				'id'        => $order->get_id(),
				'title'     => sprintf( 'New order #%s', $order->get_order_number() ),
				'amount'    => $order->get_total(),
				'status'    => $order->get_status(),
				'timestamp' => $order->get_date_created()->getTimestamp(),
				'date'      => $order->get_date_created()->format( 'd-m-Y H:i:s' ),
			);
		}

		// Format products
		foreach ( $recent_products as $product ) {
			$activities[] = array(
				'type'      => 'product',
				'id'        => $product->ID,
				'title'     => $product->post_title,
				'status'    => $product->post_status,
				'timestamp' => strtotime( $product->post_date ),
				'date'      => date_i18n( 'd-m-Y H:i:s', strtotime( get_date_from_gmt( $product->post_date ) ) ),

			);
		}

		// Format customers
		foreach ( $recent_customers as $customer ) {
			$activities[] = array(
				'type'      => 'customer',
				'id'        => $customer->ID,
				'title'     => $customer->display_name,
				'email'     => $customer->user_email,
				'timestamp' => strtotime( $customer->user_registered ),
				'date'      => date_i18n( 'd-m-Y H:i:s', strtotime( get_date_from_gmt( $customer->user_registered ) ) ),

			);
		}

		// Sort activities by timestamp
		usort(
			$activities,
			function ( $a, $b ) {
				return $b['timestamp'] - $a['timestamp'];
			}
		);

		// Limit to requested number
		$activities = array_slice( $activities, 0, $limit );

		return new WP_REST_Response(
			$this->format_success_response(
				'Recent activities retrieved successfully.',
				$activities
			),
			200
		);
	}
}
