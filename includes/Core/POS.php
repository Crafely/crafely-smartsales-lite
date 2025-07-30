<?php

namespace AISMARTSALES\Includes\Core;

if (!defined('ABSPATH')) {
    exit;
}
/**
 * POS Class
 * 
 * Handles the main POS functionality and routing
 * 
 */
class POS
{
    private $usersApiHandler;
    private $outletsApiHandler;
    private $countersApiHandler;
    private $authManager;

    public function __construct()
    {
        if (!defined('SMARTSALES_DIR') || !defined('SMARTSALES_URL')) {
            wp_die(esc_html__('SMARTSALES_DIR or SMARTSALES_URL is not defined.', 'crafely-smartsales-lite'));
        }

        // Add high-priority handlers for POS URLs
        add_action('parse_request', [$this, 'handle_aipos_endpoint'], 1);

        // Add this high priority redirect handling
        add_action('template_redirect', [$this, 'intercept_pos_redirects'], 1);

        add_action('init', [$this, 'add_pos_rewrite_rules']);
        add_action('template_include', [$this, 'load_pos_template']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front_assets']);
        add_action('wp_enqueue_scripts', [$this, 'login_page_assets']);
        add_action('wp_enqueue_scripts', [$this, 'dequeue_unnecessary_assets'], 999);
        add_action('admin_bar_menu', [$this, 'add_aipos_toolbar_menu'], 100);
        add_action('init', [$this, 'initialize_api_handlers'], 5);
        // Remove the force_module_type filter
        // add_filter('script_loader_tag', [$this, 'force_module_type'], 10, 3);

        // Add login redirect filter
        add_filter('login_redirect', [$this, 'handle_login_redirect'], 10, 3);

        // Check if rewrite rules need flushing
        add_action('wp_loaded', [$this, 'maybe_flush_rewrite_rules']);

        // Force the rewrite rules to be flushed on next page load
        update_option('aipos_flush_rewrite_rules', true);
    }

    /**
     * Ultra high-priority handler for /aipos endpoints to bypass WordPress routing
     * This runs at parse_request which is even earlier than template_redirect
     */
    public function handle_aipos_endpoint($wp)
    {
        // Get the request path
        $path = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';

        // Normalize the path
        $path = rtrim($path, '/');

        // Only process /aipos paths
        if ($path !== '/aipos' && strpos($path, '/aipos/') !== 0) {
            return;
        }

        // Handle login endpoints
        if ($path === '/aipos/login' || $path === '/aipos/auth/login') {
            // If already logged in with POS access, go to main POS
            if (is_user_logged_in()) {
                $user = wp_get_current_user();

                // Simple role check for aipos_cashier
                if (in_array('aipos_cashier', (array)$user->roles)) {
                    wp_redirect(home_url('/aipos'));
                    exit;
                } else {
                    // No cashier role, redirect to admin
                    wp_redirect(admin_url());
                    exit;
                }

            }

            // Show login page
            $this->render_login_template();
            exit;
        }

        // Handle main POS endpoint
        if ($path === '/aipos') {
            // Check if logged in
            if (!is_user_logged_in()) {
                wp_redirect(home_url('/aipos/auth/login'));
                exit;
            }

            $user = wp_get_current_user();

            // Simple role check for aipos_cashier
            if (!in_array('aipos_cashier', (array)$user->roles)) {
                wp_redirect(home_url('/aipos/auth/login'));
                exit;
            }

            // Show POS template
            $this->render_pos_template();
            exit;
        }
    }

    /**
     * Render the login template directly
     */
    private function render_login_template()
    {
        // Get login error if any
        $error_message = '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['login_error'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $error_message = urldecode(sanitize_text_field(wp_unslash($_GET['login_error'])));
        } elseif ($error = get_transient('pos_login_error')) {
            $error_message = $error;
            delete_transient('pos_login_error');
        }

        // Set up the query var so template can use it
        set_query_var('login_error', $error_message);

        // Include the template directly
        $template = realpath(SMARTSALES_DIR . 'templates/aipos-login.php');

        // Validate template path for security
        if ($template && strpos($template, realpath(SMARTSALES_DIR)) === 0) {
            // Set up WordPress
            if (!defined('WP_USE_THEMES')) {
                define('WP_USE_THEMES', false);
            }

            // Load the template
            require_once($template);
            exit;
        }
    }

    /**
     * Render the POS template directly
     */
    private function render_pos_template()
    {
        // Include the template directly
        $template = realpath(SMARTSALES_DIR . 'templates/aipos-template.php');

        // Validate template path for security
        if ($template && strpos($template, realpath(SMARTSALES_DIR)) === 0) {
            // Set up WordPress
            if (!defined('WP_USE_THEMES')) {
                define('WP_USE_THEMES', false);
            }

            // Load the template
            require_once($template);
            exit;
        }
    }

    public function initialize_api_handlers()
    {
        $this->usersApiHandler = new \AISMARTSALES\Includes\Api\Roles\UsersApiHandler();
        $this->outletsApiHandler = new \AISMARTSALES\Includes\Api\Outlets\OutletsApiHandler();
        $this->countersApiHandler = new \AISMARTSALES\Includes\Api\Outlets\CountersApiHandler();
    }

    public function add_pos_rewrite_rules()
    {
        // Main POS login page route
        add_rewrite_rule('^aipos/login/?$', 'index.php?pos_login_page=1', 'top');

        // Handle SPA routes for auth flow
        add_rewrite_rule('^aipos/auth/login/?$', 'index.php?pos_login_page=1', 'top');

        // Main POS route - this should catch all other aipos routes for SPA
        add_rewrite_rule('^aipos(/.*)?/?$', 'index.php?pos_page=1', 'top');

        add_filter('query_vars', function ($query_vars) {
            $query_vars[] = 'pos_page';
            $query_vars[] = 'pos_login_page';
            return $query_vars;
        });

        add_action('pre_get_posts', function ($query) {
            if ($query->get('pos_page') || $query->get('pos_login_page')) {
                $query->is_404 = false;
                $query->is_page = true;
                $query->is_singular = true;
            }
        });
    }

    public function handle_login_redirect($redirect_to, $requested_redirect_to, $user)
    {
        if (!$user || is_wp_error($user)) {
            return $redirect_to;
        }

        // Simple role check - if user has cashier role, redirect to POS
        if (in_array('aipos_cashier', (array)$user->roles)) {
            return home_url('/aipos');
        }
        return $redirect_to;
    }

    public function load_pos_template($template)
    {
        // Add file path validation
        $template_dir = realpath(SMARTSALES_DIR . 'templates');


        // Handle login page
        if (get_query_var('pos_login_page')) {
            // If user is already logged in and has POS access, redirect to main POS app
            if (is_user_logged_in()) {
                $user = wp_get_current_user();
                // Simple role check
                if (in_array('aipos_cashier', (array)$user->roles)) {
                    wp_redirect(home_url('/aipos'));
                    exit;
                }
                // Otherwise, redirect to admin
                wp_redirect(admin_url());
                exit;
            }

            // Load login template
            $login_template = realpath(SMARTSALES_DIR . 'templates/aipos-login.php');
            if ($login_template && strpos($login_template, $template_dir) === 0) {
                set_query_var('login_error', get_transient('pos_login_error'));
                delete_transient('pos_login_error');
                return $login_template;
            }
        }
        // Handle main POS page and all POS routes
        elseif (get_query_var('pos_page')) {

            // Handle the root /aipos URL - redirect to login if not authenticated
            if (!is_user_logged_in()) {

                // Store the current URL as the redirect destination after login
                $current_url = home_url(add_query_arg([], $GLOBALS['wp']->request));
                set_transient('aipos_redirect_after_login', $current_url, HOUR_IN_SECONDS);

                // Redirect to login
                wp_safe_redirect(home_url('/aipos/auth/login'));
                exit;
            }

            // Simple role check
            $user = wp_get_current_user();
            if (!in_array('aipos_cashier', (array)$user->roles)) {

                wp_safe_redirect(home_url('/aipos/auth/login'));
                exit;
            }

            // User is authenticated, load the main POS template

            $template_path = realpath(SMARTSALES_DIR . 'templates/aipos-template.php');
            if ($template_path && strpos($template_path, $template_dir) === 0) {
                return $template_path;
            }
        }

        return $template;
    }

    public function enqueue_front_assets()
    {
        if (!get_query_var('pos_page')) return;

        // Simple role check
        $user = wp_get_current_user();
        if (!is_user_logged_in() || !in_array('aipos_cashier', (array)$user->roles)) {
            wp_die(esc_html__('Unauthorized access', 'crafely-smartsales-lite'));
        }

        // Remove all existing scripts and styles
        global $wp_scripts, $wp_styles;
        $wp_scripts->queue = [];
        $wp_styles->queue = [];

        // Remove all default WordPress actions that might interfere
        remove_all_actions('wp_head');
        remove_all_actions('wp_footer');

        // Add security headers
        add_action('send_headers', function () {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-same-origin');
        });

        // Add back only essential head actions with security checks
        add_action('wp_head', 'wp_enqueue_scripts', 1);
        add_action('wp_head', '_wp_render_title_tag', 1);
        add_action('wp_head', 'rest_output_link_wp_head');

        // When you enqueue your main app script, also localize it with authentication data
        if (wp_script_is('aipos-app', 'registered')) {
            $this->localize_pos_scripts();
        }
    }

    public function login_page_assets()
    {
        // Check if we're on the login page using multiple detection methods
        $is_login_page = get_query_var('pos_login_page') ||
            (isset($_SERVER['REQUEST_URI']) &&
                (strpos(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])), '/aipos/login') !== false ||
                    strpos(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])), '/aipos/auth/login') !== false));

        if (!$is_login_page) return;



        // Ensure the file exists before using filemtime to prevent errors
        $tailwind_css_path = SMARTSALES_DIR . 'assets/css/tailwind-output.css';
        $frontend_css_path = SMARTSALES_DIR . 'assets/css/frontend.css';

        if (file_exists($tailwind_css_path)) {
            $css_version = filemtime($tailwind_css_path);
            wp_enqueue_style(
                'ai-smart-sales-login-tailwind',
                SMARTSALES_URL . 'assets/css/tailwind-output.css',
                [],
                $css_version
            );
        } else {

            // Fallback to a version number if file doesn't exist
            wp_enqueue_style(
                'ai-smart-sales-login-tailwind',
                SMARTSALES_URL . 'assets/css/tailwind-output.css',
                [],
                '1.0.0'
            );
        }

        // Frontend CSS
        if (file_exists($frontend_css_path)) {
            $css_version = filemtime($frontend_css_path);
            wp_enqueue_style(
                'ai-smart-sales-login',
                SMARTSALES_URL . 'assets/css/frontend.css',
                ['ai-smart-sales-login-tailwind'],
                $css_version
            );
        } else {

            // Fallback to a version number if file doesn't exist
            wp_enqueue_style(
                'ai-smart-sales-login',
                SMARTSALES_URL . 'assets/css/frontend.css',
                ['ai-smart-sales-login-tailwind'],
                '1.0.0'
            );
        }

        // Enqueue login JS
        $login_js_path = SMARTSALES_DIR . 'assets/js/login.js';
        if (file_exists($login_js_path)) {
            $js_version = filemtime($login_js_path);
            wp_enqueue_script(
                'ai-smart-sales-login-js',
                SMARTSALES_URL . 'assets/js/login.js',
                [],
                $js_version,
                true
            );
        }

        // Enqueue spinner CSS
        $spinner_css_path = SMARTSALES_DIR . 'assets/css/login-spinner.css';
        if (file_exists($spinner_css_path)) {
            $css_version = filemtime($spinner_css_path);
            wp_enqueue_style(
                'ai-smart-sales-login-spinner',
                SMARTSALES_URL . 'assets/css/login-spinner.css',
                [],
                $css_version
            );
        }
    }

    public function dequeue_unnecessary_assets()
    {
        if (!get_query_var('pos_page')) return;

        // Remove all scripts
        add_action('wp_print_scripts', function () {
            global $wp_scripts;
            $wp_scripts->queue = [];
        }, 100);

        // Remove all styles
        add_action('wp_print_styles', function () {
            global $wp_styles;
            $wp_styles->queue = [];
        }, 100);

        // Disable emojis and embeds
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');
    }

    public function add_aipos_toolbar_menu($wp_admin_bar)
    {
        if (!is_user_logged_in() || !current_user_can('manage_options')) return;

        $wp_admin_bar->add_node([
            'id' => 'aipos',
            'title' => 'View aiPOS',
            'href' => home_url('/aipos'),
            'meta' => ['target' => '_blank'],
            'parent' => 'top-secondary',
        ]);
    }

    /**
     * Check and flush rewrite rules if necessary
     * This helps ensure the /aipos URL works properly
     */
    public function maybe_flush_rewrite_rules()
    {
        $flush_rules = get_option('aipos_flush_rewrite_rules', false);

        if ($flush_rules) {
            flush_rewrite_rules();
            update_option('aipos_flush_rewrite_rules', false);
        }
    }

    /**
     * Intercept any redirections for POS URLs to ensure they work correctly
     * This runs at priority 1 during template_redirect to catch issues early
     */
    public function intercept_pos_redirects()
    {
        // Get the current request URI
        $request_uri = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';

        // Check if this is a POS-related URL
        if (strpos($request_uri, '/aipos') === 0) {


            // Handle different POS URL patterns
            if (strpos($request_uri, '/aipos/login') === 0 || strpos($request_uri, '/aipos/auth/login') === 0) {
                // If it's a login URL
                if (is_user_logged_in()) {
                    $user = wp_get_current_user();
                    if (in_array('aipos_cashier', (array)$user->roles)) {
                        // User has cashier role, redirect to POS
                        wp_redirect(home_url('/aipos'));
                        exit;
                    } else {
                        // User is logged in but has no cashier role, redirect to admin
                        wp_redirect(admin_url());
                        exit;
                    }
                }

                // Otherwise, let the template loading handle showing the login page
                return;
            } else if (strpos($request_uri, '/aipos') === 0) {
                // Main POS URL
                if (!is_user_logged_in()) {
                    // User is not logged in, redirect to login
                    wp_redirect(home_url('/aipos/auth/login'));
                    exit;
                }

                // Check for cashier role
                $user = wp_get_current_user();
                if (!in_array('aipos_cashier', (array)$user->roles)) {
                    // User doesn't have cashier role, redirect to login
                    wp_redirect(home_url('/aipos/auth/login'));
                    exit;
                }

                // Otherwise, let the template loading handle showing the POS app
                return;
            }
        }
    }

    // Add this function to localize scripts with auth data
    public function localize_pos_scripts()
    {
        wp_localize_script('aipos-app', 'aiposData', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'current_user_id' => get_current_user_id(),
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);
    }
}