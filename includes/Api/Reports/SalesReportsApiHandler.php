<?php
namespace CSMSL\Includes\Api\Reports;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SalesReportsApiHandler {


	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route(
			'ai-smart-sales/v1',
			'/reports/sales',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_sales_reports' ),
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

	public function get_sales_reports( $request ) {
		$args = array(
			'post_type'      => 'shop_order',
			'post_status'    => array( 'wc-completed', 'wc-processing' ),
			'posts_per_page' => -1,
		);

		$orders = get_posts( $args );

		if ( ! $orders ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'message' => 'No orders found.',
					'data'    => array(),
				)
			);
		}

		$total_sales     = 0;
		$total_orders    = count( $orders );
		$total_customers = 0;
		$total_items     = 0;
		$total_tax       = 0;
		$total_shipping  = 0;
		$total_refunds   = 0;
		$total_discount  = 0;
		$totals          = array();

		foreach ( $orders as $order ) {
			$order_date = get_the_date( 'Y-m-d', $order->ID );

			if ( ! isset( $totals[ $order_date ] ) ) {
				$totals[ $order_date ] = array(
					'sales'     => 0,
					'orders'    => 0,
					'items'     => 0,
					'tax'       => 0,
					'shipping'  => 0,
					'discount'  => 0,
					'customers' => 0,
				);
			}

			$order_total    = (float) get_post_meta( $order->ID, '_order_total', true );
			$order_items    = (int) get_post_meta( $order->ID, '_order_item_count', true );
			$order_tax      = (float) get_post_meta( $order->ID, '_order_tax', true );
			$order_shipping = (float) get_post_meta( $order->ID, '_order_shipping', true );
			$order_discount = (float) get_post_meta( $order->ID, '_cart_discount', true );
			$order_customer = (int) get_post_meta( $order->ID, '_customer_user', true );

			$total_sales     += $order_total;
			$total_items     += $order_items;
			$total_tax       += $order_tax;
			$total_shipping  += $order_shipping;
			$total_discount  += $order_discount;
			$total_customers += $order_customer;

			$totals[ $order_date ]['sales']     += $order_total;
			$totals[ $order_date ]['orders']    += 1;
			$totals[ $order_date ]['items']     += $order_items;
			$totals[ $order_date ]['tax']       += $order_tax;
			$totals[ $order_date ]['shipping']  += $order_shipping;
			$totals[ $order_date ]['discount']  += $order_discount;
			$totals[ $order_date ]['customers'] += $order_customer;
		}

		$response_data = array(
			'total_sales'       => $total_sales,
			'net_sales'         => $total_sales - $total_discount,
			'average_sales'     => $total_orders > 0 ? $total_sales / $total_orders : 0,
			'total_orders'      => $total_orders,
			'total_items'       => $total_items,
			'total_tax'         => $total_tax,
			'total_shipping'    => $total_shipping,
			'total_refunds'     => $total_refunds,
			'total_discount'    => $total_discount,
			'totals_grouped_by' => 'day',
			'totals'            => $totals,
			'total_customers'   => $total_customers,
		);

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => 'Sales reports fetched successfully.',
				'data'    => $response_data,
			)
		);
	}
}
