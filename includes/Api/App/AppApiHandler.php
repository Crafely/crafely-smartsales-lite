<?php
/**
 * Crafely Smart Sales Lite
 *
 * @package Crafely_Smart_Sales_Lite
 */

namespace CSMSL\Includes\Api\App;

use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AppApiHandler
 *
 * Handles REST API endpoints for the AI Smart Sales app.
 */
class AppApiHandler {

	/**
	 * Constructor to initialize the API routes.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers the REST API routes for the app.
	 */
	public function register_routes() {
		register_rest_route(
			'ai-smart-sales/v1',
			'/app',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_app_data' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// PUT: Update business data.
		register_rest_route(
			'ai-smart-sales/v1',
			'/app',
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_app_data' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => $this->get_update_args_schema(),
			)
		);
	}

	/**
	 * Checks if the current user has permission to access the app data.
	 *
	 * @return bool True if user has permission, false otherwise.
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
	 * Checks if the current user has admin permissions.
	 *
	 * @return bool|\WP_Error True if user has permission, WP_Error otherwise.
	 */
	public function check_admin_permission() {
		// Check if user is logged in and is an administrator.
		if ( ! is_user_logged_in() ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You must be logged in to access this resource.', 'crafely-smartsales-lite' ),
				array( 'status' => 401 )
			);
		}
		$user = wp_get_current_user();

		// Only administrators can update app data.
		if ( ! in_array( 'administrator', (array) $user->roles, true ) ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to update app data. Administrator role required.', 'crafely-smartsales-lite' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}
	/**
	 * Retrieves app data including store details, inventory size, and wizard data.
	 *
	 * @return WP_REST_Response The response containing app data.
	 */
	public function get_app_data() {
		// Sample WooCommerce store details (already present).
		$store_address   = get_option( 'woocommerce_store_address', '123 Default St' );
		$store_address_2 = get_option( 'woocommerce_store_address_2', '' );
		$store_city      = get_option( 'woocommerce_store_city', 'Default City' );
		$store_postcode  = get_option( 'woocommerce_store_postcode', '00000' );
		$store_country   = get_option( 'woocommerce_default_country', 'US' );
		$currency        = get_option( 'woocommerce_currency', 'USD' );

		// Get inventory size.
		$inventory_size = 0;
		if ( function_exists( 'wc_get_products' ) ) {
			$args           = array(
				'limit'  => -1,
				'status' => 'publish',
				'return' => 'ids',
			);
			$products       = wc_get_products( $args );
			$inventory_size = count( $products );
		}

		// Get wizard data.
		$wizard_data = get_option(
			'csmsl_wizard_data',
			array(
				'business_type'    => 'retail',
				'inventory_range'  => 'small',
				'has_outlet'       => 'no',
				'additional_notes' => '',
			)
		);

		// Check for actual outlets in the system.
		$outlets              = get_posts(
			array(
				'post_type'      => 'outlet',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		$actual_outlets_exist = ! empty( $outlets );

		// Convert has_outlet to boolean with better logic.
		$has_outlet_value = $wizard_data['has_outlet'] ? $wizard_data['has_outlet'] : 'no';
		$has_outlet       = $actual_outlets_exist ||
		in_array( strtolower( $has_outlet_value ), array( 'yes', 'true', '1', 'on' ), true );

		$data = array(
			'store_address'     => $store_address,
			'store_address_2'   => $store_address_2,
			'store_city'        => $store_city,
			'store_postcode'    => $store_postcode,
			'store_country'     => $store_country,
			'currency'          => $currency,
			'email'             => get_option( 'csmsl_admin_email', get_option( 'admin_email', '' ) ),
			'business_type'     => $wizard_data['business_type'] ? $wizard_data['business_type'] : 'retail',
			'inventory_range'   => $wizard_data['inventory_range'] ? $wizard_data['inventory_range'] : 'small',
			'inventory_size'    => $inventory_size,
			'has_outlet'        => $has_outlet,
			'additional_notes'  => $wizard_data['additional_notes'] ? $wizard_data['additional_notes'] : '',
			'plugin_name'       => defined( 'CSMSL_NAME' ) ? CSMSL_NAME : 'AI Smart Sales',
			'plugin_version'    => defined( 'CSMSL_VERSION' ) ? CSMSL_VERSION : '1.0.0',
			'site_url'          => get_site_url(),
			'site_name'         => get_option( 'csmsl_site_name', get_bloginfo( 'name' ) ),
			'wordpress_version' => get_bloginfo( 'version' ),
			'php_version'       => phpversion(),
			'active_theme'      => wp_get_theme()->get( 'Name' ),
			'site_language'     => get_bloginfo( 'language' ),
		);

		return new WP_REST_Response(
			$this->format_success_response( 'App data retrieved successfully.', $data, 200 ),
			200
		);
	}

	/**
	 * Updates app data including store settings, admin email, and wizard data.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response containing updated fields or errors.
	 */
	public function update_app_data( WP_REST_Request $request ) {
		$params         = $request->get_params();
		$updated_fields = array();
		$errors         = array();

		// Validate and update WooCommerce store settings.
		$wc_options = array(
			'store_address'   => 'woocommerce_store_address',
			'store_address_2' => 'woocommerce_store_address_2',
			'store_city'      => 'woocommerce_store_city',
			'store_postcode'  => 'woocommerce_store_postcode',
			'store_country'   => 'woocommerce_default_country',
			'currency'        => 'woocommerce_currency',
		);

		foreach ( $wc_options as $param_key => $option_key ) {
			if ( isset( $params[ $param_key ] ) ) {
				$value = $params[ $param_key ];

				// Additional validation for specific fields.
				if ( 'currency' === $param_key && ! $this->is_valid_currency( $value ) ) {
					$errors[] = "Invalid currency code: {$value}";
					continue;
				}

				if ( 'store_country' === $param_key && ! $this->is_valid_country( $value ) ) {
					$errors[] = "Invalid country code: {$value}";
					continue;
				}

				update_option( $option_key, $value );
				$updated_fields[ $param_key ] = $value;
			}
		}

		// Update admin email (store in plugin-specific option instead of WordPress core).
		if ( isset( $params['email'] ) ) {
			if ( is_email( $params['email'] ) ) {
				update_option( 'csmsl_admin_email', $params['email'] );
				$updated_fields['email'] = $params['email'];
			} else {
				$errors[] = "Invalid email address: {$params['email']}";
			}
		}

		// Update site name (store in plugin-specific option instead of WordPress core).
		if ( isset( $params['site_name'] ) ) {
			update_option( 'csmsl_site_name', $params['site_name'] );
			$updated_fields['site_name'] = $params['site_name'];
		}

		// Update wizard data.
		$wizard_data   = get_option( 'csmsl_wizard_data', array() );
		$wizard_fields = array( 'business_type', 'inventory_range', 'has_outlet', 'additional_notes' );

		$wizard_updated = false;
		foreach ( $wizard_fields as $field ) {
			if ( isset( $params[ $field ] ) ) {
				$wizard_data[ $field ]    = $params[ $field ];
				$updated_fields[ $field ] = $params[ $field ];
				$wizard_updated           = true;
			}
		}

		if ( $wizard_updated ) {
			update_option( 'csmsl_wizard_data', $wizard_data );
		}

		// Return response.
		if ( ! empty( $errors ) ) {
			return new WP_REST_Response(
				array(
					'success'        => false,
					'message'        => 'Some fields could not be updated due to validation errors.',
					'errors'         => $errors,
					'updated_fields' => $updated_fields,
				),
				400
			);
		}

		if ( empty( $updated_fields ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'No valid fields provided for update.',
					'data'    => array(),
				),
				400
			);
		}

		return new WP_REST_Response(
			$this->format_success_response(
				'App data updated successfully.',
				array(
					'updated_fields' => $updated_fields,
					'updated_count'  => count( $updated_fields ),
				)
			),
			200
		);
	}

	/**
	 * Validates if the provided currency code is valid.
	 *
	 * @param string $currency The currency code to validate.
	 * @return bool True if valid, false otherwise.
	 */
	private function is_valid_currency( $currency ) {
		// Extended currency validation - you can expand this list.
		$valid_currencies = array(
			'USD',
			'EUR',
			'GBP',
			'JPY',
			'AUD',
			'CAD',
			'CHF',
			'CNY',
			'SEK',
			'NZD',
			'BDT',
			'INR',
			'PKR',
			'SGD',
			'MYR',
			'THB',
			'PHP',
			'IDR',
			'VND',
			'KRW',
			'TWD',
			'HKD',
			'AED',
			'SAR',
			'QAR',
			'KWD',
			'BHD',
			'OMR',
			'JOD',
			'LBP',
			'EGP',
			'ZAR',
			'NGN',
			'KES',
			'GHS',
			'MAD',
			'TND',
			'DZD',
			'ETB',
			'UGX',
			'TZS',
			'RWF',
			'XOF',
			'XAF',
			'MXN',
			'BRL',
			'ARS',
			'CLP',
			'COP',
			'PEN',
			'UYU',
			'BOB',
			'PYG',
			'VES',
			'DOP',
			'GTQ',
			'HNL',
			'NIO',
			'CRC',
			'PAB',
			'CUP',
			'JMD',
			'BBD',
			'XCD',
			'TTD',
			'BSD',
			'BZD',
			'GYD',
			'SRD',
			'AWG',
			'RUB',
			'PLN',
			'CZK',
			'HUF',
			'RON',
			'BGN',
			'HRK',
			'RSD',
			'BAM',
			'MKD',
			'ALL',
			'MDL',
			'UAH',
			'BYN',
			'GEL',
			'AMD',
			'AZN',
			'KZT',
			'KGS',
			'UZS',
			'TJS',
			'TMT',
			'AFN',
			'IRR',
			'IQD',
			'SYP',
			'YER',
			'LYD',
			'SDG',
			'SOS',
			'DJF',
			'ERN',
			'MRU',
			'CDF',
			'AOA',
			'ZMW',
			'BWP',
			'SZL',
			'LSL',
			'NAD',
			'MWK',
			'MZN',
			'MGA',
			'KMF',
			'SCR',
			'MUR',
			'MVR',
			'LKR',
			'NPR',
			'BTN',
			'MMK',
			'LAK',
			'KHR',
			'BND',
			'FJD',
			'PGK',
			'SBD',
			'VUV',
			'TOP',
			'WST',
			'TVD',
			'NRU',
			'KID',
			'AUD',
		);
		return in_array( strtoupper( $currency ), $valid_currencies, true );
	}

	/**
	 * Validates if the provided country code is valid.
	 *
	 * @param string $country The country code to validate.
	 * @return bool True if valid, false otherwise.
	 */
	private function is_valid_country( $country ) {
		// Extended country validation - WordPress/WooCommerce country codes.
		$valid_countries = array(
			'US',
			'CA',
			'GB',
			'AU',
			'DE',
			'FR',
			'IT',
			'ES',
			'NL',
			'BE',
			'BD',
			'IN',
			'PK',
			'SG',
			'MY',
			'TH',
			'PH',
			'ID',
			'VN',
			'KR',
			'TW',
			'HK',
			'AE',
			'SA',
			'QA',
			'KW',
			'BH',
			'OM',
			'JO',
			'LB',
			'EG',
			'ZA',
			'NG',
			'KE',
			'GH',
			'MA',
			'TN',
			'DZ',
			'ET',
			'UG',
			'TZ',
			'RW',
			'BF',
			'CM',
			'MX',
			'BR',
			'AR',
			'CL',
			'CO',
			'PE',
			'UY',
			'BO',
			'PY',
			'VE',
			'DO',
			'GT',
			'HN',
			'NI',
			'CR',
			'PA',
			'CU',
			'JM',
			'BB',
			'AG',
			'TT',
			'BS',
			'BZ',
			'GY',
			'SR',
			'AW',
			'RU',
			'PL',
			'CZ',
			'HU',
			'RO',
			'BG',
			'HR',
			'RS',
			'BA',
			'MK',
			'AL',
			'MD',
			'UA',
			'BY',
			'GE',
			'AM',
			'AZ',
			'KZ',
			'KG',
			'UZ',
			'TJ',
			'TM',
			'AF',
			'IR',
			'IQ',
			'SY',
			'YE',
			'LY',
			'SD',
			'SO',
			'DJ',
			'ER',
			'MR',
			'CD',
			'AO',
			'ZM',
			'BW',
			'SZ',
			'LS',
			'NA',
			'MW',
			'MZ',
			'MG',
			'KM',
			'SC',
			'MU',
			'MV',
			'LK',
			'NP',
			'BT',
			'MM',
			'LA',
			'KH',
			'BN',
			'FJ',
			'PG',
			'SB',
			'VU',
			'TO',
			'WS',
			'TV',
			'NR',
			'KI',
			'PW',
		);
		return in_array( strtoupper( $country ), $valid_countries, true );
	}
	/**
	 * Formats a successful response for the API.
	 *
	 * @param string $message The success message.
	 * @param array  $data Additional data to include in the response.
	 * @param int    $statusCode HTTP status code for the response.
	 * @return array The formatted response.
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
	 * Get the schema for the update app data endpoint.
	 *
	 * @return array The schema for the update app data endpoint.
	 */
	private function get_update_args_schema() {
		return array(
			'store_address'    => array(
				'type'              => 'string',
				'description'       => 'Store address line 1',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'store_address_2'  => array(
				'type'              => 'string',
				'description'       => 'Store address line 2',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'store_city'       => array(
				'type'              => 'string',
				'description'       => 'Store city',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'store_postcode'   => array(
				'type'              => 'string',
				'description'       => 'Store postal code',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'store_country'    => array(
				'type'              => 'string',
				'description'       => 'Store country code',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'currency'         => array(
				'type'              => 'string',
				'description'       => 'Store currency',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'email'            => array(
				'type'              => 'string',
				'format'            => 'email',
				'description'       => 'Admin email address',
				'sanitize_callback' => 'sanitize_email',
			),
			'business_type'    => array(
				'type'              => 'string',
				'description'       => 'Type of business',
				'enum'              => array( 'retail', 'wholesale', 'restaurant', 'service', 'other' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'inventory_range'  => array(
				'type'              => 'string',
				'description'       => 'Inventory size range',
				'enum'              => array( 'small', 'medium', 'large' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'has_outlet'       => array(
				'type'              => 'string',
				'description'       => 'Whether business has outlets',
				'enum'              => array( 'yes', 'no' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'additional_notes' => array(
				'type'              => 'string',
				'description'       => 'Additional business notes',
				'sanitize_callback' => 'sanitize_textarea_field',
			),
			'site_name'        => array(
				'type'              => 'string',
				'description'       => 'Site name',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}
}
