<?php

/**
 * Configuration manager for AI Smart Sales
 * 
 * @package AI Smart Sales
 */

namespace CSMSL\Includes\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Config
{

    /**
     * Plugin configuration
     * 
     * @var array
     */
    private static $config = null;

    /**
     * Get configuration value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        if (self::$config === null) {
            self::load();
        }

        return self::get_nested_value(self::$config, $key, $default);
    }

    /**
     * Set configuration value
     * 
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        if (self::$config === null) {
            self::load();
        }

        self::set_nested_value(self::$config, $key, $value);
    }

    /**
     * Load configuration
     */
    private static function load()
    {
        self::$config = [
            'plugin' => [
                'name' => 'crafely-smartsales-lite',
                'version' => CSMSL_VERSION,
                'text_domain' => 'crafely-smartsales-lite',
                'namespace' => 'CSMSL',
                'min_wp_version' => '5.0',
                'min_wc_version' => '5.0',
                'min_php_version' => '7.4'
            ],
            'api' => [
                'namespace' => 'ai-smart-sales/v1',
                'version' => 'v1',
                'rate_limit' => 100, // requests per minute
                'cache_ttl' => 300   // 5 minutes
            ],
            'pos' => [
                'roles' => [
                    'aipos_cashier',
                    'aipos_outlet_manager',
                    'aipos_shop_manager'
                ],
                'urls' => [
                    'base' => '/aipos',
                    'login' => '/aipos/auth/login',
                    'logout' => '/aipos/auth/logout'
                ],
                'session_timeout' => 3600, // 1 hour
                'auto_logout' => true
            ],
            'security' => [
                'nonce_action' => 'csmsl_nonce',
                'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
                'max_file_size' => 5242880, // 5MB
                'enable_logging' => defined('CSMSL_DEV_MODE') && CSMSL_DEV_MODE
            ],
            'database' => [
                'tables' => [
                    'assistances',
                    'activity_logs',
                    'settings'
                ],
                'charset' => 'utf8mb4',
                'collate' => 'utf8mb4_unicode_ci'
            ],
            'cache' => [
                'enabled' => true,
                'default_ttl' => 300,
                'groups' => [
                    'products' => 600,
                    'orders' => 300,
                    'reports' => 900
                ]
            ],
            'features' => [
                'ai_assistance' => true,
                'analytics' => true,
                'inventory_management' => true,
                'multi_outlet' => true,
                'offline_mode' => false
            ]
        ];

        // Apply filters to allow customization
        self::$config = apply_filters('aismartsales_config', self::$config);
    }

    /**
     * Get nested configuration value using dot notation
     * 
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private static function get_nested_value($array, $key, $default = null)
    {
        if (strpos($key, '.') === false) {
            return isset($array[$key]) ? $array[$key] : $default;
        }

        $keys = explode('.', $key);
        $value = $array;

        foreach ($keys as $k) {
            if (!is_array($value) || !isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set nested configuration value using dot notation
     * 
     * @param array &$array
     * @param string $key
     * @param mixed $value
     */
    private static function set_nested_value(&$array, $key, $value)
    {
        if (strpos($key, '.') === false) {
            $array[$key] = $value;
            return;
        }

        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $value;
    }

    /**
     * Get all configuration
     * 
     * @return array
     */
    public static function all()
    {
        if (self::$config === null) {
            self::load();
        }

        return self::$config;
    }

    /**
     * Check if configuration key exists
     * 
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return self::get($key) !== null;
    }

    /**
     * Get API configuration
     * 
     * @return array
     */
    public static function api()
    {
        return self::get('api', []);
    }

    /**
     * Get POS configuration
     * 
     * @return array
     */
    public static function pos()
    {
        return self::get('pos', []);
    }

    /**
     * Get security configuration
     * 
     * @return array
     */
    public static function security()
    {
        return self::get('security', []);
    }

    /**
     * Check if feature is enabled
     * 
     * @param string $feature
     * @return bool
     */
    public static function is_feature_enabled($feature)
    {
        return (bool) self::get("features.{$feature}", false);
    }
}
