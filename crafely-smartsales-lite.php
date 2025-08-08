<?php
/**
 * Plugin Name: Crafely SmartSales Lite
 * Plugin URI: https://github.com/Crafely/crafely-smartsales-lite
 * Description: SmartSales Lite is a comprehensive WordPress plugin that transforms your WooCommerce store into a complete Point of Sale (POS) system with advanced sales management, multi-outlet support, AI assistance, and powerful analytics. Perfect for retail stores, restaurants, and service businesses looking to unify their online and offline sales operations.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: MD AL AMIN
 * Author URI: https://profiles.wordpress.org/alaminit/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: crafely-smartsales-lite
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Declare HPOS compatibility
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

// Define constants
// get plugin version from the plugin header
$plugin_data = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
define( 'CSMSL_VERSION', $plugin_data['Version'] );
define( 'CSMSL_NAME', 'Crafely SmartSales Lite' );
define( 'CSMSL_DIR', plugin_dir_path( __FILE__ ) );
define( 'CSMSL_URL', plugin_dir_url( __FILE__ ) );
define( 'CSMSL_PLUGIN_FILE', __FILE__ );
define( 'CSMSL_DEV_MODE', false ); // Set to false in production

// Include essential files
require_once CSMSL_DIR . 'includes/functions.php';

// Autoload dependencies
if ( file_exists( CSMSL_DIR . 'vendor/autoload.php' ) ) {
	require_once CSMSL_DIR . 'vendor/autoload.php';
}

// Include essential core files and API handlers (fallback for when autoloader is not available)
$core_includes = array(
	'includes/Core/Plugin.php',
	'includes/Core/Admin.php',
	'includes/Core/POS.php',
	'includes/Core/Activation.php',
	'includes/Core/AuthManager.php',
	'includes/Api/Roles/RolesManager.php',
	'includes/CPT/PostTypes.php',
	'includes/Api/BaseApiHandler.php',
	'includes/Api/Roles/UsersApiHandler.php',
	'includes/Api/Outlets/OutletsApiHandler.php',
	'includes/Api/Outlets/CountersApiHandler.php',
	'includes/Api/Products/ProductApiHandler.php',
	'includes/Api/Customers/CustomersApiHandler.php',
	'includes/Api/Orders/OrdersApiHandler.php',
	'includes/Api/Categories/CategoriesApiHandler.php',
	'includes/Api/Dashboard/DashboardApiHandler.php',
	'includes/Api/Reports/SalesReportsApiHandler.php',
	'includes/Api/Invoices/InvoiceApiHandler.php',
	'includes/Api/Media/MediaApiHandler.php',
	'includes/Api/Channels/ChannelsApiHandler.php',
	'includes/Api/AI/AIAssistancesApiHandler.php',
	'includes/Api/App/AppApiHandler.php',
	'includes/Api/App/WizardApiHandler.php',
);

foreach ( $core_includes as $file ) {
	if ( file_exists( CSMSL_DIR . $file ) ) {
		require_once CSMSL_DIR . $file;
	}
}

// Initialize the plugin
function csmsl_init() {
	if ( ! csmsl_is_woocommerce_active() ) {
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'WooCommerce is required for AI Smart Sales to function properly.', 'crafely-smartsales-lite' ) . '</p></div>';
			}
		);
		return;
	}

	try {
		// Initialize the main plugin class using singleton
		$plugin = CSMSL\Includes\Core\Plugin::instance();
		$plugin->init();
	} catch ( Exception $e ) {

		if ( is_admin() ) {
			add_action(
				'admin_notices',
				function () use ( $e ) {
					printf(
						'<div class="notice notice-error"><p>%s</p></div>',
						esc_html(
							sprintf(
							// translators: %s is the error message returned during plugin initialization failure.
								__( 'AI Smart Sales failed to initialize: %s', 'crafely-smartsales-lite' ),
								$e->getMessage()
							)
						)
					);
				}
			);
		}
	}
}
add_action( 'plugins_loaded', 'csmsl_init', 15 );

// Very early interception of POS URLs - run before everything else
function csmsl_early_url_handler() {
	// Only run on frontend requests
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}

	// Get request URI
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

	// Only process POS URLs
	if ( strpos( $request_uri, '/aipos' ) === false ) {
		return;
	}

	// Make sure we capture all aipos URL variants (with or without trailing slash)
	$is_aipos_login = ( strpos( $request_uri, '/aipos/login' ) === 0 || strpos( $request_uri, '/aipos/auth/login' ) === 0 );
	$is_aipos_root  = ( $request_uri === '/aipos' || $request_uri === '/aipos/' );

	// Let WordPress handle the rest of the loading process
	// This ensures the URL is properly parsed and template_include will work
}

// Run this very early in WordPress initialization
add_action( 'plugins_loaded', 'csmsl_early_url_handler', 1 );

// Add direct access check to handle direct aipos URLs BEFORE WordPress routing
if ( ! function_exists( 'CSMSL_direct_access_handler' ) ) {
	function CSMSL_direct_access_handler() {
		// Only check non-admin requests with aipos in the URL
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		if ( is_admin() || $request_uri === '' || strpos( $request_uri, '/aipos' ) === false ) {
			return;
		}

		// Process /aipos URLs
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		// Make sure the plugin has initialized
		if ( defined( 'CSMSL_DIR' ) && file_exists( CSMSL_DIR . 'templates/aipos-login.php' ) ) {
			// Check if it's a login URL
			if ( strpos( $request_uri, '/aipos/login' ) === 0 || strpos( $request_uri, '/aipos/auth/login' ) === 0 ) {
				// If already logged in, check permissions
				if ( is_user_logged_in() ) {
					$user      = wp_get_current_user();
					$pos_roles = array( 'aipos_cashier', 'aipos_outlet_manager', 'aipos_shop_manager' );

					// Check if user has any POS roles
					$has_pos_access = ! empty( array_intersect( $pos_roles, (array) $user->roles ) );

					if ( $has_pos_access ) {
						wp_redirect( home_url( '/aipos' ) );
						exit;
					}
				}
				// Don't redirect - let the regular flow show login page
				return;
			} elseif ( $request_uri === '/aipos' || $request_uri === '/aipos/' ) {
				// For main POS URL, check if logged in
				if ( ! is_user_logged_in() ) {
					// Not logged in, redirect to login
					wp_redirect( home_url( '/aipos/auth/login' ) );
					exit;
				}

				// Check permissions for logged in users
				$user           = wp_get_current_user();
				$pos_roles      = array( 'aipos_cashier', 'aipos_outlet_manager', 'aipos_shop_manager' );
				$has_pos_access = ! empty( array_intersect( $pos_roles, (array) $user->roles ) );

				if ( ! $has_pos_access ) {
					// No access, redirect to login
					wp_redirect( home_url( '/aipos/auth/login' ) );
					exit;
				}

				// Has access, let the template system handle it
				return;
			}
		}
	}
}

// Run direct access handler before WordPress processes the request
add_action( 'init', 'CSMSL_direct_access_handler', 5 );


// Register activation and deactivation hooks without role removal
register_activation_hook( __FILE__, array( 'CSMSL\Includes\Core\Activation', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'CSMSL\Includes\Core\Activation', 'deactivate' ) );

// Add activation hook to create default roles and capabilities
function csmsl_activate() {
	// Initialize RolesManager to create roles
	$roles_manager = new CSMSL\Includes\Api\Roles\RolesManager();
	$roles_manager->register_custom_roles();

	// Initialize PostTypes to register post types
	$post_types = new CSMSL\Includes\CPT\PostTypes();
	$post_types->register_post_types();

	// Create default outlet and counter using the Core\Plugin class
	$plugin = CSMSL\Includes\Core\Plugin::instance();
	$plugin->activate();

	// Clear permalinks
	flush_rewrite_rules();

	// Set version
	update_option( 'CSMSL_VERSION', CSMSL_VERSION );

	// Mark that rewrite rules should be flushed
	update_option( 'csmsl_flush_rewrite_rules', true );
}

// Fix for rewrite rules not working properly
function csmsl_fix_rewrite_rules() {
	if ( get_option( 'csmsl_permalinks_flushed' ) !== CSMSL_VERSION ) {
		// Set flag to flush rewrite rules
		update_option( 'csmsl_flush_rewrite_rules', true );
		update_option( 'csmsl_permalinks_flushed', CSMSL_VERSION );
	}
}
add_action( 'init', 'csmsl_fix_rewrite_rules', 20 );

// Remove deactivation hook or modify it to preserve roles
register_activation_hook( __FILE__, 'csmsl_activate' );
// Do not remove roles on deactivation
register_deactivation_hook( __FILE__, array( 'CSMSL\Includes\Core\Activation', 'deactivate' ) );
