<?php
/**
 * POS Class
 * Handles the main POS functionality and routing
 *
 * @package CrafelySmartSalesLite
 */

namespace CSMSL\Includes\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * POS Class
 *
 * Handles the main POS functionality and routing
 */
class POS {

	/**
	 * Handles user-related API requests.
	 *
	 * @var UsersApiHandler $usersApiHandler The users API handler instance.
	 */
	private $usersApiHandler;
	/**
	 * Handles outlet-related API requests.
	 *
	 * @var OutletsApiHandler $outletsApiHandler Handles outlet-related API requests.
	 */
	private $outletsApiHandler;

	/**
	 * Handles counter-related API requests.
	 *
	 * @var CountersApiHandler $countersApiHandler Handles counter-related API requests.
	 */
	private $countersApiHandler;

	/**
	 * Constructor
	 * Initializes the POS class and sets up the necessary hooks and actions.
	 */
	public function __construct() {
		if ( ! defined( 'CSMSL_DIR' ) || ! defined( 'CSMSL_URL' ) ) {
			wp_die( esc_html__( 'CSMSL_DIR or CSMSL_URL is not defined.', 'crafely-smartsales-lite' ) );
		}

		// Add high-priority handlers for POS URLs.
		add_action( 'parse_request', array( $this, 'handle_csmsl_pos_endpoint' ), 1 );
		add_action( 'template_redirect', array( $this, 'intercept_pos_redirects' ), 1 );

		add_action( 'init', array( $this, 'add_pos_rewrite_rules' ) );
		add_action( 'template_include', array( $this, 'load_pos_template' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'login_page_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_unnecessary_assets' ), 999 );

		add_action( 'admin_bar_menu', array( $this, 'add_csmsl_pos_toolbar_menu' ), 100 );
		add_action( 'init', array( $this, 'initialize_api_handlers' ), 5 );

		add_filter( 'login_redirect', array( $this, 'handle_login_redirect' ), 10, 3 );
		add_action( 'wp_loaded', array( $this, 'maybe_flush_rewrite_rules' ) );

		// Schedule rewrite flush.
		update_option( 'csmsl_flush_rewrite_rules', true );
	}

	/**
	 * Handles /smart-pos endpoints
	 * This method checks the request URI and serves the appropriate template based on the path.
	 */
	public function handle_csmsl_pos_endpoint() {
		$path = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$path = rtrim( $path, '/' );

		if ( '/smart-pos' !== $path && 0 !== strpos( $path, '/smart-pos/' ) ) {
			return;
		}

		// Login page.
		if ( '/smart-pos/login' === $path || '/smart-pos/auth/login' === $path ) {
			if ( is_user_logged_in() ) {
				$user = wp_get_current_user();
				if ( in_array( 'csmsl_pos_cashier', (array) $user->roles, true ) ) {
					wp_safe_redirect( home_url( '/smart-pos' ) );
					exit;
				} else {
					wp_safe_redirect( admin_url() );
					exit;
				}
			}

			$this->render_login_template();
			exit;
		}

		// Main POS page.
		if ( '/smart-pos' === $path ) {
			if ( ! is_user_logged_in() ) {
				wp_safe_redirect( home_url( '/smart-pos/auth/login' ) );
				exit;
			}

			$user = wp_get_current_user();
			if ( ! in_array( 'csmsl_pos_cashier', (array) $user->roles, true ) ) {
				wp_safe_redirect( home_url( '/smart-pos/auth/login' ) );
				exit;
			}

			$this->render_pos_template();
			exit;
		}
	}

	/**
	 * Render login template
	 */
	private function render_login_template() {
		$error_message = '';
		$error         = get_transient('csmsl_login_error');

		if ( isset($_GET['login_error']) ) {
			$error_message = urldecode(sanitize_text_field(wp_unslash($_GET['login_error'])));
		} elseif ( ! empty($error) ) {
			$error_message = $error;
			delete_transient('csmsl_login_error');
		}

		set_query_var('login_error', $error_message);

		$template = realpath(CSMSL_DIR . 'templates/smart-pos-login.php');
		if ( $template && strpos($template, realpath(CSMSL_DIR)) === 0 ) {
			require $template;
			exit;
		}
	}

	/**
	 * Render POS template
	 */
	private function render_pos_template() {
		$template = realpath(CSMSL_DIR . 'templates/smart-pos-template.php');
		if ( $template && strpos($template, realpath(CSMSL_DIR)) === 0 ) {
			require $template;
			exit;
		}
	}

	/**
	 * Initializes API handlers for POS functionality
	 * This method sets up the necessary API handlers for user roles and outlets.
	 */
	public function initialize_api_handlers() {
		$this->usersApiHandler    = new \CSMSL\Includes\Api\Roles\UsersApiHandler();
		$this->outletsApiHandler  = new \CSMSL\Includes\Api\Outlets\OutletsApiHandler();
		$this->countersApiHandler = new \CSMSL\Includes\Api\Outlets\CountersApiHandler();
	}

	/**
	 * Adds rewrite rules for POS endpoints
	 * This method adds custom rewrite rules for the POS login and main pages.
	 * It also registers query variables and modifies the main query to handle these endpoints.
	 */
	public function add_pos_rewrite_rules() {
		add_rewrite_rule( '^smart-pos/login/?$', 'index.php?pos_login_page=1', 'top' );
		add_rewrite_rule( '^smart-pos/auth/login/?$', 'index.php?pos_login_page=1', 'top' );
		add_rewrite_rule( '^smart-pos(/.*)?/?$', 'index.php?pos_page=1', 'top' );

		add_filter('query_vars', function ( $query_vars ) {
			$query_vars[] = 'pos_page';
			$query_vars[] = 'pos_login_page';
			return $query_vars;
		});

		add_action('pre_get_posts', function ( $query ) {
			if ( $query->get('pos_page') || $query->get('pos_login_page') ) {
				$query->is_404      = false;
				$query->is_page     = true;
				$query->is_singular = true;
			}
		});
	}
	/**
	 * Handles login redirects for POS users
	 * This method checks if the user is a POS cashier and redirects them to the POS page.
	 * If the user is not a POS cashier, it redirects them to the default login redirect URL.
	 *
	 * @param string  $redirect_to The URL to redirect to.
	 * @param string  $requested_redirect_to The requested redirect URL.
	 * @param WP_User $user The user object.
	 * @return string The URL to redirect to.
	 */
	public function handle_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
		if ( ! $user || is_wp_error( $user ) ) {
			return $redirect_to;
		}

		if ( in_array( 'csmsl_pos_cashier', (array) $user->roles, true ) ) {
			return home_url( '/smart-pos' );
		}

		return $redirect_to;
	}
	/**
	 * Loads the POS template based on the current query
	 * This method checks if the current query is for the POS login or main page and returns the appropriate template file.
	 * If the user is logged in and has the 'csmsl_pos_cashier' role, it loads the POS template.
	 * If the user is not logged in or does not have the required role, it redirects them to the login page.
	 *
	 * @param string $template The current template being used.
	 */
	public function load_pos_template( $template ) {
		$template_dir = realpath(CSMSL_DIR . 'templates');

		if ( get_query_var('pos_login_page') ) {
			if ( is_user_logged_in() ) {
				$user = wp_get_current_user();
				if ( in_array( 'csmsl_pos_cashier', (array) $user->roles, true ) ) {
					wp_safe_redirect( home_url( '/smart-pos' ) );
					exit;
				}
				wp_safe_redirect( admin_url() );
				exit;
			}

			$login_template = realpath(CSMSL_DIR . 'templates/smart-pos-login.php');
			if ( $login_template && strpos($login_template, $template_dir) === 0 ) {
				return $login_template;
			}
		} elseif ( get_query_var('pos_page') ) {
			if ( ! is_user_logged_in() ) {
				global $wp;
				$current_url = home_url(add_query_arg(array(), $wp->request));
				set_transient('csmsl_pos_redirect_after_login', $current_url, HOUR_IN_SECONDS);

				wp_safe_redirect(home_url('/smart-pos/auth/login'));
				exit;
			}

			$user = wp_get_current_user();
			if ( ! in_array( 'csmsl_pos_cashier', (array) $user->roles, true ) ) {
				wp_safe_redirect(home_url('/smart-pos/auth/login'));
				exit;
			}

			$template_path = realpath(CSMSL_DIR . 'templates/smart-pos-template.php');
			if ( $template_path && 0 === strpos($template_path, $template_dir) ) {
				return $template_path;
			}
		}

		return $template;
	}
	/**
	 * Enqueues front-end assets for the POS system
	 * This method checks if the current page is a POS page and enqueues the necessary styles and scripts.
	 * It also localizes the POS scripts with necessary data such as API URLs and user information
	 */
	public function enqueue_front_assets() {
		if ( ! get_query_var('pos_page') ) {
			return;
		}

		$user = wp_get_current_user();
		if ( ! is_user_logged_in() || ! in_array('csmsl_pos_cashier', (array) $user->roles, true) ) {
			wp_die( esc_html__( 'Unauthorized access', 'crafely-smartsales-lite' ) );
		}

		global $wp_scripts, $wp_styles;
		$wp_scripts->queue = array();
		$wp_styles->queue  = array();

		remove_all_actions('wp_head');
		remove_all_actions('wp_footer');

		add_action('send_headers', function () {
			header('X-Content-Type-Options: nosniff');
			header('X-Frame-Options: SAMEORIGIN');
			header('X-XSS-Protection: 1; mode=block');
			header('Referrer-Policy: strict-origin-same-origin');
		});

		if ( wp_script_is('csmsl-pos-app', 'registered') ) {
			$this->localize_pos_scripts();
		}
	}
	/**
	 * Enqueues styles and scripts for the POS login page
	 * This method checks if the current page is the POS login page and enqueues the necessary styles and scripts.
	 * It also handles the loading of Tailwind CSS, frontend CSS, and    login JavaScript files.
	 */
	public function login_page_assets() {
		$is_login_page = get_query_var('pos_login_page') ||
			( isset($_SERVER['REQUEST_URI']) &&
				( strpos(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])), '/smart-pos/login') !== false ||
				strpos(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])), '/smart-pos/auth/login') !== false )
			);

		if ( ! $is_login_page ) {
			return;
		}

		$tailwind_css_path = CSMSL_DIR . 'assets/css/tailwind-output.css';
		$frontend_css_path = CSMSL_DIR . 'assets/css/frontend.css';

		if ( file_exists($tailwind_css_path) ) {
			wp_enqueue_style('csmsl-login-tailwind', CSMSL_URL . 'assets/css/tailwind-output.css', [], filemtime($tailwind_css_path));
		} else {
			wp_enqueue_style('csmsl-login-tailwind', CSMSL_URL . 'assets/css/tailwind-output.css', [], '1.0.0');
		}

		if ( file_exists($frontend_css_path) ) {
			wp_enqueue_style('csmsl-login', CSMSL_URL . 'assets/css/frontend.css', [ 'csmsl-login-tailwind' ], filemtime($frontend_css_path));
		} else {
			wp_enqueue_style('csmsl-login', CSMSL_URL . 'assets/css/frontend.css', [ 'csmsl-login-tailwind' ], '1.0.0');
		}

		$login_js_path = CSMSL_DIR . 'assets/js/login.js';
		if ( file_exists($login_js_path) ) {
			wp_enqueue_script('csmsl-login-js', CSMSL_URL . 'assets/js/login.js', [], filemtime($login_js_path), true);
		}

		$spinner_css_path = CSMSL_DIR . 'assets/css/login-spinner.css';
		if ( file_exists($spinner_css_path) ) {
			wp_enqueue_style('csmsl-login-spinner', CSMSL_URL . 'assets/css/login-spinner.css', [], filemtime($spinner_css_path));
		}
	}

	/**
	 * Dequeue unnecessary assets
	 * This method removes all scripts and styles from the queue if the current page is a POS page.
	 * This is useful to prevent loading unnecessary assets on POS pages, improving performance.
	 */
	public function dequeue_unnecessary_assets() {
		if ( ! get_query_var('pos_page') ) {
			return;
		}

		add_action('wp_print_scripts', function () {
			global $wp_scripts;
			$wp_scripts->queue = [];
		}, 100);

		add_action('wp_print_styles', function () {
			global $wp_styles;
			$wp_styles->queue = [];
		}, 100);

		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('wp_print_styles', 'print_emoji_styles');
		remove_action('wp_head', 'wp_oembed_add_discovery_links');
		remove_action('wp_head', 'wp_oembed_add_host_js');
	}
	/**
	 * Adds a toolbar menu for POS in the admin bar
	 * This method adds a menu item to the WordPress admin bar for accessing the POS page.
	 * It checks if the user is logged in and has the 'manage_options' capability before adding the menu item.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The WordPress admin bar object.
	 */
	public function add_csmsl_pos_toolbar_menu( $wp_admin_bar ) {
		if ( ! is_user_logged_in() || ! current_user_can('manage_options') ) {
			return;
		}

		$wp_admin_bar->add_node([
			'id'     => 'csmsl_pos',
			'title'  => 'View POS',
			'href'   => home_url('/smart-pos'),
			'meta'   => [ 'target' => '_blank' ],
			'parent' => 'top-secondary',
		]);
	}
	/**
	 * Maybe flush rewrite rules
	 * This method checks if the rewrite rules need to be flushed and flushes them if necessary
	 * It uses a transient to determine if the rules need to be flushed, which is set during plugin activation.
	 * This helps to avoid unnecessary flushes on every page load, improving performance
	 */
	public function maybe_flush_rewrite_rules() {
		$flush_rules = get_option('csmsl_flush_rewrite_rules', false);
		if ( $flush_rules ) {
			flush_rewrite_rules();
			update_option('csmsl_flush_rewrite_rules', false);
		}
	}
	/**
	 * Intercepts POS redirects
	 * This method checks the request URI for POS-related paths and redirects users accordingly.
	 */
	public function intercept_pos_redirects() {
		$request_uri = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';

		if ( strpos($request_uri, '/smart-pos') !== 0 ) {
			return;
		}

		if ( strpos($request_uri, '/smart-pos/login') === 0 || strpos($request_uri, '/smart-pos/auth/login') === 0 ) {
			if ( is_user_logged_in() ) {
				$user = wp_get_current_user();
				if ( in_array('csmsl_pos_cashier', (array) $user->roles, true) ) {
					wp_safe_redirect(home_url('/smart-pos'));
					exit;
				} else {
					wp_safe_redirect(admin_url());
					exit;
				}
			}
			return;
		}

		if ( strpos($request_uri, '/smart-pos') === 0 ) {
			if ( ! is_user_logged_in() ) {
				wp_safe_redirect(home_url('/smart-pos/auth/login'));
				exit;
			}

			$user = wp_get_current_user();
			if ( ! in_array('csmsl_pos_cashier', (array) $user->roles, true) ) {
				wp_safe_redirect(home_url('/smart-pos/auth/login'));
				exit;
			}
		}
	}

	/**
	 * Localizes POS scripts with necessary data
	 * This method localizes the POS scripts with necessary data such as API URLs, nonce,
	 * current user ID, and AJAX URL. This data is used by the POS application to
	 * interact with the WordPress REST API and perform AJAX requests.
	 */
	public function localize_pos_scripts() {
		wp_localize_script('csmsl-pos-app', 'csmslPosData', [
			'root'            => esc_url_raw(rest_url()),
			'nonce'           => wp_create_nonce('wp_rest'),
			'current_user_id' => get_current_user_id(),
			'ajaxurl'         => admin_url('admin-ajax.php'),
		]);
	}
}
