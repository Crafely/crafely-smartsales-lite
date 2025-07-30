<?php
/**
 * AI Smart Sales POS Template
 *
 * This template is used for the AI Smart Sales POS system.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Prevent WordPress from adding any automatic paragraphs or line breaks
remove_filter('the_content', 'wpautop');
remove_filter('the_excerpt', 'wpautop');

// Create a specific nonce for this template
$template_nonce = wp_create_nonce('aipos_template_nonce');
$rest_nonce = wp_create_nonce('wp_rest');

$api_data = array(
    'root' => esc_url_raw(rest_url()),
    'nonce' => $rest_nonce,
    'baseUrl' => esc_url_raw(SMARTSALES_URL),
    'assetsUrl' => esc_url_raw(SMARTSALES_URL . 'dist/'),
    'isLoggedIn' => is_user_logged_in(),
    'templateNonce' => $template_nonce,
    'currentUser' => is_user_logged_in() ? wp_get_current_user()->ID : 0,
    'userRoles' => is_user_logged_in() ? wp_get_current_user()->roles : [],
    'apiNamespace' => 'ai-smart-sales/v1',
);

// Validate and sanitize CSS file path
$css_files = glob(SMARTSALES_DIR . 'dist/css/main.*.css');
$css_file = !empty($css_files) ? esc_url(SMARTSALES_URL . 'dist/css/' . basename($css_files[0])) : '';

// Validate and sanitize JS file path
$js_files = glob(SMARTSALES_DIR . 'dist/js/main.*.js');
$js_file = !empty($js_files) ? esc_url(SMARTSALES_URL . 'dist/js/' . basename($js_files[0])) : '';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy"
        content="default-src 'self' 'unsafe-inline' 'unsafe-eval' data: *.wp.com *.wordpress.com *.cloudflareinsights.com; script-src 'self' 'unsafe-inline' 'unsafe-eval' *.cloudflareinsights.com; img-src * data:;">
    <title><?php echo esc_html(wp_get_document_title()); ?></title>
    <?php
    // Enqueue the CSS file if it exists
    if ($css_file) {
        // Use file modification time as version to prevent caching issues
        $css_version = !empty($css_files) ? filemtime($css_files[0]) : null;
        wp_enqueue_style('aipos-main-style', $css_file, [], $css_version);
        // Print enqueued styles in the head
        wp_print_styles('aipos-main-style');
    }
    ?>
    <!-- Add WordPress API data for Vue.js -->
    <script>
        window.wpApiSettings = <?php echo json_encode($api_data); ?>;
    </script>
</head>

<body class="aipos-app">
    <div id="app" data-nonce="<?php echo esc_attr($template_nonce); ?>"></div>
    <?php
    if ($js_file) {
        // Use file modification time as version to prevent caching issues
        $js_version = !empty($js_files) ? filemtime($js_files[0]) : null;
        
        // Register and enqueue the script first
        wp_register_script('aipos-main-script', $js_file, [], $js_version, true);
        
        // Add the module type via script_loader_tag filter
        add_filter('script_loader_tag', function($tag, $handle) {
            if ('aipos-main-script' === $handle) {
                return str_replace('<script ', '<script type="module" ', $tag);
            }
            return $tag;
        }, 10, 2);
        
        wp_enqueue_script('aipos-main-script');
        wp_print_scripts('aipos-main-script');
        
        // Remove the filter after use
        remove_all_filters('script_loader_tag');
    }
    ?>
</body>

</html>