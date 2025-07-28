<?php

/**
 * Global helper functions for AI Smart Sales
 * 
 * @package AI Smart Sales
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if WooCommerce is active
 * 
 * @return bool
 */
if (!function_exists('aismartsales_is_woocommerce_active')) {
    function aismartsales_is_woocommerce_active()
    {
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }
}

/**
 * Check if AI Smart Sales is active
 * 
 * @return bool
 */
if (!function_exists('aismartsales_is_active')) {
    function aismartsales_is_active()
    {
        return is_plugin_active(plugin_basename(SMARTSALES_DIR . 'smartsales-lite.php'));
    }
}

/**
 * Get plugin instance
 * 
 * @return AISMARTSALES\Includes\Core\Plugin
 */
if (!function_exists('aismartsales')) {
    function aismartsales()
    {
        return AISMARTSALES\Includes\Core\Plugin::instance();
    }
}

/**
 * Get plugin version
 * 
 * @return string
 */
if (!function_exists('aismartsales_get_version')) {
    function aismartsales_get_version()
    {
        return defined('SMARTSALES_VERSION') ? SMARTSALES_VERSION : '1.0.0';
    }
}

/**
 * Get plugin directory path
 * 
 * @param string $path Optional path to append
 * @return string
 */
if (!function_exists('aismartsales_get_plugin_path')) {
    function aismartsales_get_plugin_path($path = '')
    {
        return SMARTSALES_DIR . ltrim($path, '/');
    }
}

/**
 * Get plugin directory URL
 * 
 * @param string $path Optional path to append
 * @return string
 */
if (!function_exists('aismartsales_get_plugin_url')) {
    function aismartsales_get_plugin_url($path = '')
    {
        return SMARTSALES_URL . ltrim($path, '/');
    }
}

/**
 * Log messages for debugging
 * 
 * @param mixed $message
 * @param string $level
 */
if (!function_exists('aismartsales_log')) {
    function aismartsales_log($message, $level = 'info')
    {
        if (!defined('SMARTSALES_DEV_MODE') || !SMARTSALES_DEV_MODE) {
            return;
        }
    }
}

/**
 * Check if current user has POS access
 * 
 * @return bool
 */
if (!function_exists('aismartsales_user_has_pos_access')) {
    function aismartsales_user_has_pos_access()
    {
        if (!is_user_logged_in()) {
            return false;
        }

        $user = wp_get_current_user();
        $pos_roles = ['aipos_cashier', 'aipos_outlet_manager', 'aipos_shop_manager'];

        return !empty(array_intersect($pos_roles, (array)$user->roles));
    }
}

/**
 * Get formatted currency amount
 * 
 * @param float $amount
 * @return string
 */
if (!function_exists('aismartsales_format_currency')) {
    function aismartsales_format_currency($amount)
    {
        if (function_exists('wc_price')) {
            return wc_price($amount);
        }

        return '$' . number_format($amount, 2);
    }
}

/**
 * Sanitize and validate data
 * 
 * @param mixed $data
 * @param string $type
 * @return mixed
 */
if (!function_exists('aismartsales_sanitize_data')) {
    function aismartsales_sanitize_data($data, $type = 'text')
    {
        switch ($type) {
            case 'email':
                return sanitize_email($data);
            case 'url':
                return esc_url_raw($data);
            case 'int':
                return absint($data);
            case 'float':
                return floatval($data);
            case 'html':
                return wp_kses_post($data);
            case 'text':
            default:
                return sanitize_text_field($data);
        }
    }
}

/**
 * Handle AJAX responses consistently
 * 
 * @param mixed $data
 * @param string $message
 * @param bool $success
 */
if (!function_exists('aismartsales_ajax_response')) {
    function aismartsales_ajax_response($data = null, $message = '', $success = true)
    {
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ];

        wp_send_json($response);
    }
}

/**
 * Get template part
 * 
 * @param string $template_name
 * @param array $args
 * @param string $template_path
 */
if (!function_exists('aismartsales_get_template')) {
    function aismartsales_get_template($template_name, $args = [], $template_path = '')
    {
        if (!empty($args) && is_array($args)) {
            extract($args);
        }

        $located = aismartsales_locate_template($template_name, $template_path);

        if (!file_exists($located)) {
            aismartsales_log("Template not found: {$template_name}", 'error');
            return;
        }

        include $located;
    }
}

/**
 * Locate template file
 * 
 * @param string $template_name
 * @param string $template_path
 * @return string
 */
if (!function_exists('aismartsales_locate_template')) {
    function aismartsales_locate_template($template_name, $template_path = '')
    {
        if (!$template_path) {
            $template_path = 'smartsales-lite/';
        }

        // Look in theme first
        $template = locate_template([
            trailingslashit($template_path) . $template_name,
            $template_name
        ]);

        // Get default template
        if (!$template) {
            $template = aismartsales_get_plugin_path('templates/' . $template_name);
        }

        return apply_filters('aismartsales_locate_template', $template, $template_name, $template_path);
    }
}