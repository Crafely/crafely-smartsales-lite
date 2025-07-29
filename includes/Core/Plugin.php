<?php

namespace AISMARTSALES\Includes\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Plugin Class
 *
 * @package AI Smart Sales
 */
class Plugin
{
    /**
     * Plugin instance
     *
     * @var Plugin
     */
    private static $instance = null;

    /**
     * Initialization flag
     *
     * @var bool
     */
    private $initialized = false;

    /**
     * Get plugin instance (Singleton)
     *
     * @return Plugin
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        // Basic setup only in constructor

    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }

    /**
     * Initialize the plugin
     */
    public function init()
    {
        if ($this->initialized) {
            return;
        }

        // Load activation hook
        register_activation_hook(SMARTSALES_DIR . 'crafely-smartsales-lite.php', [Activation::class, 'run']);

        // Load admin scripts
        add_action('admin_enqueue_scripts', [$this, 'common_admin_scripts']);

        // Initialize core components
        $this->init_core_components();

        // Initialize API handlers
        $this->init_api_handlers();

        // Allow addons to register their functionality
        do_action('aismartsales_register_addons');

        $this->initialized = true;
    }



    /**
     * Initialize core components
     */
    private function init_core_components()
    {
        // Initialize RolesManager first to ensure roles exist
        new \AISMARTSALES\Includes\Api\Roles\RolesManager();

        // Initialize core functionality
        new Admin();
        new AuthManager();
        new Activation();
        new POS();
        new \AISMARTSALES\Includes\CPT\PostTypes();
    }

    /**
     * Initialize API handlers
     */
    private function init_api_handlers()
    {
        $api_handlers = [
            'AISMARTSALES\Includes\Api\Products\ProductApiHandler',
            'AISMARTSALES\Includes\Api\Categories\CategoriesApiHandler',
            'AISMARTSALES\Includes\Api\Orders\OrdersApiHandler',
            'AISMARTSALES\Includes\Api\Media\MediaApiHandler',
            'AISMARTSALES\Includes\Api\Customers\CustomersApiHandler',
            'AISMARTSALES\Includes\Api\Invoices\InvoiceApiHandler',
            'AISMARTSALES\Includes\Api\Reports\SalesReportsApiHandler',
            'AISMARTSALES\Includes\Api\Channels\ChannelsApiHandler',
            'AISMARTSALES\Includes\Api\Outlets\OutletsApiHandler',
            'AISMARTSALES\Includes\Api\Roles\UsersApiHandler',
            'AISMARTSALES\Includes\Api\AI\AIAssistancesApiHandler',
            'AISMARTSALES\Includes\Api\App\AppApiHandler',
            'AISMARTSALES\Includes\Api\Outlets\CountersApiHandler',
            'AISMARTSALES\Includes\Api\Dashboard\DashboardApiHandler',
            'AISMARTSALES\Includes\Api\App\WizardApiHandler',
        ];

        $api_handlers = apply_filters('aismartsales_api_handlers', $api_handlers);

        foreach ($api_handlers as $handler) {
            if (class_exists($handler)) {
                new $handler();
            }
        }
    }


    public function common_admin_scripts()
    {
        wp_enqueue_style('aismartsales-admin-css', SMARTSALES_URL . 'assets/css/admin.css', [], SMARTSALES_VERSION);
    }

    /**
     * Create default outlet and counter on plugin activation
     */
    public function activate()
    {
        // Create default outlet if it doesn't exist
        $default_outlet = get_posts([
            'post_type' => 'outlet',
            'posts_per_page' => 1,
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
            'meta_query' => [
                [
                    'key' => 'is_default',
                    'value' => true
                ]
            ]
        ]);

        if (empty($default_outlet)) {
            $outlet_id = wp_insert_post([
                'post_type' => 'outlet',
                'post_title' => 'Main Outlet',
                'post_status' => 'publish',
            ]);

            update_post_meta($outlet_id, 'outlet_status', 'active');
            update_post_meta($outlet_id, 'is_default', true);
            update_post_meta($outlet_id, 'outlet_address', 'Default Address');
            update_post_meta($outlet_id, 'outlet_phone', '');
            update_post_meta($outlet_id, 'outlet_email', '');
            update_post_meta($outlet_id, 'outlet_operating_hours', '9:00 AM - 5:00 PM');

            // Create default counter for the outlet
            $counter_id = wp_insert_post([
                'post_type' => 'counter',
                'post_title' => 'Main Counter',
                'post_status' => 'publish',
            ]);

            update_post_meta($counter_id, 'counter_outlet_id', $outlet_id);
            update_post_meta($counter_id, 'counter_status', 'active');
            update_post_meta($counter_id, 'is_default', true);
            update_post_meta($counter_id, 'counter_description', 'Default counter');
            update_post_meta($counter_id, 'counter_position', '1');
        }
    }
}
