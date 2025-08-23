<?php
/**
 * Crafely SmartSales Lite POS Login Template
 *
 * This template is used for the Crafely SmartSales Lite POS system.
 *
 * @package CrafelySmartSalesLite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Process login form submission.
$error_message = '';
$nonce         = wp_create_nonce(action: 'csmsl_login_error_nonce');







// Check if form was submitted.
if ( isset( $_POST['login'] ) && isset( $_POST['password'] ) ) {
	// Check if nonce is set before verification.
	if ( ! isset( $_POST['pos_login_nonce'] ) ) {
		$error_message = 'Security token missing. Please try again.';
	} elseif ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pos_login_nonce'] ) ), 'pos_login' ) ) {
		$error_message = 'Security verification failed. Please try again.';
	} else {
		$login    = sanitize_text_field( wp_unslash( $_POST['login'] ) );
		$password = sanitize_textarea_field( wp_unslash( $_POST['password'] ) );
		$remember = isset( $_POST['remember_me'] ) ? true : false;

		// Attempt login.
		$user = wp_authenticate( $login, $password );

		if ( ! is_wp_error( $user ) ) {
			// User authenticated successfully.
			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID, $remember );

			// Simple check if user has csmsl_pos_cashier role.
			if ( in_array( 'csmsl_pos_cashier', (array) $user->roles, true ) ) {
				// User has cashier role, redirect to POS.
				wp_safe_redirect( home_url( '/smart-pos' ) );
				exit;
			} else {
				// No cashier role.
				$error_message = 'You must be a cashier to access the POS system.';
				wp_logout();
			}
		} else {
			$error_message = wp_strip_all_tags( $user->get_error_message() );
		}
	}
} elseif ( ! empty($_POST) ) {
	// If the form was submitted but crucial fields are missing, set a generic error.
	$error_message = 'Please enter both username/email and password.';
}

// If an error message exists, redirect with it and the nonce.
if ( ! empty( $error_message ) && ! isset( $_GET['login_error'] ) ) {
	wp_safe_redirect( add_query_arg( array(
		'login_error' => rawurldecode($error_message),
		'_wpnonce'    => $nonce,
	), home_url('/smart-pos/auth/login') ) );
	exit;
}

// Check for error messages from URL or query var.
if ( empty( $error_message ) ) {
	if ( isset( $_GET['login_error'] ) ) {
		$error_message = sanitize_text_field( wp_unslash( $_GET['login_error'] ) );
	} elseif ( get_query_var( 'login_error' ) ) {
		$error_message = wp_strip_all_tags( get_query_var( 'login_error' ) );
	}
}
?>

<!DOCTYPE html>
<html class="h-full bg-white" <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<?php wp_head(); ?>
</head>

<body class="h-full flex items-center justify-center bg-gray-50">
	<div class="max-w-sm w-full bg-white rounded-lg shadow-lg">
	<div class="px-8 py-12">
		<div class="text-center">
		<?php
		$logo_path = CSMSL_DIR . 'assets/images/csmsl-pos-black.png';

		if ( file_exists( $logo_path ) ) {
			$logo_url = CSMSL_URL . 'assets/images/csmsl-pos-black.png';
			do_action( 'csmsl_render_logo', $logo_url );
		}

		if ( ! did_action( 'csmsl_render_logo' ) && ! empty( $logo_url ) ) {
			echo '<img class="h-12 w-auto mx-auto" src="' . esc_url( $logo_url ) . '" alt="' . esc_attr__( 'POS System Logo', 'crafely-smartsales-lite' ) . '">';
		}
		?>
		<h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">Access POS System</h2>
		<?php if ( ! empty( $error_message ) ) : ?>
			<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded relative mt-4" role="alert">
			<strong class="font-bold">Error!</strong>
			<span class="block sm:inline"><?php echo esc_html( $error_message ); ?></span>
			</div>
		<?php endif; ?>
		</div>

		<form class="space-y-6 mt-8" method="post" action="" id="pos-login-form">
		<?php wp_nonce_field( 'pos_login', 'pos_login_nonce' ); ?>
		<div>
			<div class="w-full max-w-sm min-w-[200px]">
			<div class="relative">
				<input
				class="peer w-full bg-transparent placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded-md px-3 py-2 transition duration-300 ease focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm focus:shadow"
				id="login" name="login" type="text" autocomplete="username" required autofocus />
				<label for="login"
				class="absolute cursor-text bg-white px-1 left-2.5 top-3 !text-[16px] leading-none text-[#333333] transition-all transform origin-left peer-focus:-top-2 peer-focus:left-2.5 peer-focus:text-xs peer-focus:text-[#333333] peer-focus:scale-90 peer-[:not(:placeholder-shown)]:-top-2 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:scale-90">
				Username or Email
				</label>
			</div>
			</div>
		</div>

		<div>
			<div class="w-full max-w-sm min-w-[200px]">
			<div class="relative">
				<input
				class="peer w-full bg-transparent placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded-md px-3 py-2 transition duration-300 ease focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm focus:shadow"
				id="password" name="password" type="password" required />
				<label for="password"
				class="absolute cursor-text bg-white px-1 left-2.5 top-3 !text-[16px] leading-none text-[#333333] transition-all transform origin-left peer-focus:-top-2 peer-focus:left-2.5 peer-focus:text-xs peer-focus:text-[#333333] peer-focus:scale-90 peer-[:not(:placeholder-shown)]:-top-2 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:scale-90">
				Password
				</label>
			</div>
			</div>
		</div>

		<div class="flex items-center justify-between">
			<div class="flex items-center">
			<input id="remember_me" name="remember_me" type="checkbox"
				class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
			<label for="remember_me" class="ml-2 block text-sm text-gray-900">Remember me</label>
			</div>
			<div class="text-sm">
			<a href="" class="font-medium text-indigo-600 hover:text-indigo-500">Forgot your password?</a>
			</div>
		</div>
		<p class="mt-2 text-sm text-gray-600">
			Don't have an account? Contact your system administrator to set up your access.
		</p>

		<div>
			<button type="submit" id="login-submit"
			class="w-full inline-flex items-center justify-center border font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 px-4 py-2 text-base bg-black text-white hover:bg-gray-800 border-black focus:ring-black">
			<svg id="login-spinner" class="hidden w-5 h-5 mr-2 animate-spin"
				xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
				</circle>
				<path class="opacity-75" fill="currentColor"
				d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
				</path>
			</svg>
			<span id="button-text" class="whitespace-nowrap">Sign in</span>
			</button>
		</div>
		</form>
	</div>
	</div>

	<!-- JS and spinner CSS moved to enqueued files: assets/js/login.js and assets/css/login-spinner.css -->
</body>

</html>
