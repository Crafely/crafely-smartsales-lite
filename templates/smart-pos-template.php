<?php
/**
 * AI Smart Sales POS Template
 *
 * This template is used for the AI Smart Sales POS system.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Set security headers
header( 'X-Content-Type-Options: nosniff' );
header( 'X-Frame-Options: SAMEORIGIN' );
header( 'X-XSS-Protection: 1; mode=block' );
header( 'Referrer-Policy: strict-origin-when-cross-origin' );

// Prevent WordPress from adding any automatic paragraphs or line breaks
remove_filter( 'the_content', 'wpautop' );
remove_filter( 'the_excerpt', 'wpautop' );

// Create a specific nonce for this template
$template_nonce = wp_create_nonce( 'csmsl_template_nonce' );
$rest_nonce     = wp_create_nonce( 'wp_rest' );

$api_data = array(
	'root'          => esc_url_raw( rest_url() ),
	'nonce'         => $rest_nonce,
	'baseUrl'       => esc_url_raw( CSMSL_URL ),
	'assetsUrl'     => esc_url_raw( CSMSL_URL . 'dist/' ),
	'isLoggedIn'    => is_user_logged_in(),
	'templateNonce' => $template_nonce,
	'currentUser'   => is_user_logged_in() ? wp_get_current_user()->ID : 0,
	'userRoles'     => is_user_logged_in() ? wp_get_current_user()->roles : array(),
	'apiNamespace'  => 'csmsl/v1',
);

// Validate and sanitize CSS file path
$css_files = glob( CSMSL_DIR . 'dist/css/main.*.css' );
$css_file  = ! empty( $css_files ) ? esc_url( CSMSL_URL . 'dist/css/' . basename( $css_files[0] ) ) : '';

// Validate and sanitize JS file path
$js_files = glob( CSMSL_DIR . 'dist/js/main.*.js' );
$js_file  = ! empty( $js_files ) ? esc_url( CSMSL_URL . 'dist/js/' . basename( $js_files[0] ) ) : '';

// Enqueue styles and scripts properly
if ( $css_file ) {
	$css_version = ! empty( $css_files ) ? filemtime( $css_files[0] ) : null;
	wp_enqueue_style( 'csmsl-main-style', $css_file, array(), $css_version );
}

if ( $js_file ) {
	$js_version = ! empty( $js_files ) ? filemtime( $js_files[0] ) : null;
	wp_register_script( 'csmsl-main-script', $js_file, array(), $js_version, true );
	
	// Add async attribute for better performance (WordPress 6.3+)
	wp_script_add_data( 'csmsl-main-script', 'async', true );
	
	// Properly localize the script with WordPress API settings using wp_json_encode
	wp_localize_script(
		'csmsl-main-script',
		'wpApiSettings',
		$api_data
	);
	
	wp_enqueue_script( 'csmsl-main-script' );
}

// Add the module type via script_loader_tag filter
add_filter(
	'script_loader_tag',
	function ( $tag, $handle ) {
		if ( 'csmsl-main-script' === $handle ) {
			return str_replace( '<script ', '<script type="module" ', $tag );
		}
		return $tag;
	},
	10,
	2
);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="Content-Security-Policy"
		content="default-src 'self' 'unsafe-inline' 'unsafe-eval' data: *.wp.com *.wordpress.com *.cloudflareinsights.com; script-src 'self' 'unsafe-inline' 'unsafe-eval' *.cloudflareinsights.com; img-src * data:;">
	<title><?php echo esc_html( wp_get_document_title() ); ?></title>
	<?php wp_head(); ?>
</head>

<body class="csmsl-pos-app">
	<div id="app" data-nonce="<?php echo esc_attr( $template_nonce ); ?>"></div>
	<?php wp_footer(); ?>
</body>

</html>
