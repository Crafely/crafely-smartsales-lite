<?php
/**
 * Crafely SmartSales Lite AuthManager
 *
 * This class handles authentication and authorization for the Crafely SmartSales Lite POS system.
 *
 * @package CrafelySmartSalesLite
 */

namespace CSMSL\Includes\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AuthManager
 *
 * Handles authentication and authorization for the POS system.
 */
class AuthManager {

	/**
	 * POS-specific roles.
	 *
	 * @var array
	 */
	private const POS_ROLES = array( 'csmsl_pos_cashier', 'csmsl_pos_outlet_manager', 'csmsl_pos_shop_manager' );

	/**
	 * AuthManager constructor.
	 *
	 * Initializes the class by adding necessary hooks and filters.
	 */
	public function __construct() {
		add_filter( 'woocommerce_prevent_admin_access', array( $this, 'allow_pos_admin_access' ), 20 );
		add_filter( 'woocommerce_login_redirect', array( $this, 'handle_login_redirect' ), 20, 2 );
		add_filter( 'login_redirect', array( $this, 'handle_wp_login_redirect' ), 20, 3 );
		add_action( 'init', array( $this, 'handle_login' ) );
		add_action( 'admin_init', array( $this, 'enforce_access_restrictions' ), 5 );
	}

	/**
	 * Handles the login process for POS users.
	 *
	 * Validates the nonce, checks user credentials, and redirects based on role and assignments.
	 */
	public function handle_login() {
		if ( ! isset( $_POST['pos_login_nonce'] ) || ! wp_verify_nonce( esc_url_raw( wp_unslash( $_POST['pos_login_nonce'] ) ), 'pos_login' ) ) {
			return;
		}

		$user_login = isset( $_POST['login'] ) ? sanitize_text_field( wp_unslash( $_POST['login'] ) ) : '';
		$user       = get_user_by( 'email', $user_login ) ? get_user_by( 'email', $user_login ) : get_user_by( 'login', $user_login );

		if ( ! $user ) {
			$this->set_login_error( 'Invalid username or email.' );
			return;
		}

		$user = wp_signon(
			array(
				'user_login'    => $user->user_login,
				'user_password' => isset( $_POST['password'] ) ? sanitize_textarea_field( wp_unslash( $_POST['password'] ) ) : '',
				'remember'      => isset( $_POST['remember_me'] ),
			),
			false
		);

		if ( is_wp_error( $user ) ) {
			$this->set_login_error( $user->get_error_message() );
			return;
		}

		if ( $this->verify_pos_access( $user ) ) {
			// Check if the user has required outlet and counter assignments.
			if ( $this->verify_outlet_counter_assignment( $user ) ) {
				$this->handle_successful_login( $user );
			} else {
				$this->handle_failed_login( $this->get_missing_assignment_message( $user ) );
			}
		} else {
			$this->handle_failed_login( 'Access denied. Role verification failed.' );
		}
	}

	/**
	 * Verify that the user has the necessary outlet and counter assignments
	 *
	 *  @param \WP_User $user The user object.
	 * @return bool
	 */
	public function verify_outlet_counter_assignment( $user ) {
		if ( ! $user || ! $user->exists() ) {
			return false;
		}

		// Administrators always have access.
		if ( user_can( $user, 'administrator' ) ) {
			return true;
		}

		// Shop managers always have access (they manage all outlets).
		if ( in_array( 'csmsl_pos_shop_manager', $user->roles, true ) ) {
			return true;
		}

		// Outlet managers need an assigned outlet.
		if ( in_array( 'csmsl_pos_outlet_manager', $user->roles, true ) ) {
			$outlet_id = get_user_meta( $user->ID, 'assigned_outlet_id', true );
			return ! empty( $outlet_id );
		}

		// Cashiers need both an outlet and a counter.
		if ( in_array( 'csmsl_pos_cashier', $user->roles, true ) ) {
			$outlet_id  = get_user_meta( $user->ID, 'assigned_outlet_id', true );
			$counter_id = get_user_meta( $user->ID, 'assigned_counter_id', true );
			return ( ! empty( $outlet_id ) && ! empty( $counter_id ) );
		}

		return false;
	}

	/**
	 * Get appropriate error message when assignments are missing
	 *
	 * @param \WP_User $user The user object.
	 * @return string
	 */
	private function get_missing_assignment_message( $user ) {
		if ( in_array( 'csmsl_pos_outlet_manager', $user->roles, true ) ) {
			return 'You need to be assigned to an outlet before accessing the POS system.';
		} elseif ( in_array( 'csmsl_pos_cashier', $user->roles, true ) ) {
			$outlet_id  = get_user_meta( $user->ID, 'assigned_outlet_id', true );
			$counter_id = get_user_meta( $user->ID, 'assigned_counter_id', true );

			if ( empty( $outlet_id ) && empty( $counter_id ) ) {
				return 'You need to be assigned to an outlet and counter before accessing the POS system.';
			} elseif ( empty( $outlet_id ) ) {
				return 'You need to be assigned to an outlet before accessing the POS system.';
			} elseif ( empty( $counter_id ) ) {
				return 'You need to be assigned to a counter before accessing the POS system.';
			}
		}

		return 'You do not have the required assignments to access the POS system.';
	}

	/**
	 * Verify if the user has access to POS based on their roles.
	 *
	 *  @param \WP_User $user The user object.
	 * @return bool
	 */
	public function verify_pos_access( $user ) {
		if ( ! $user || ! $user->exists() ) {
			return false;
		}

		// Check if user has any POS-specific role.
		return ! empty( array_intersect( self::POS_ROLES, $user->roles ) );
	}

	/**
	 * Check if the user should be redirected to the POS system.
	 *
	 *  @param \WP_User $user The user object.
	 * @return bool
	 */
	public function should_redirect_to_pos( $user ) {
		if ( ! $user || ! $user->exists() ) {
			return false;
		}

		// Only redirect if user ONLY has POS roles and no other administrative roles.
		$user_roles  = $user->roles;
		$admin_roles = array( 'administrator', 'editor', 'author', 'shop_manager' );

		// If user has any admin role, don't redirect to POS.
		if ( ! empty( array_intersect( $admin_roles, $user_roles ) ) ) {
			return false;
		}

		// If user has any POS role, redirect to POS.
		return ! empty( array_intersect( self::POS_ROLES, $user_roles ) );
	}

	/**
	 * Enforce access restrictions for POS users.
	 *
	 * Redirects cashiers to the POS system and allows shop/outlet managers to access admin.
	 */
	public function enforce_access_restrictions() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user  = wp_get_current_user();
		$roles = (array) $user->roles;

		if ( in_array( 'csmsl_pos_cashier', $roles, true ) ) {
			if ( ! wp_doing_ajax() ) {
				wp_safe_redirect( home_url( '/smart-pos' ) );
				exit;
			}
			return;
		}

		if ( in_array( 'csmsl_pos_shop_manager', $roles, true ) || in_array( 'csmsl_pos_outlet_manager', $roles, true ) ) {
			remove_filter( 'woocommerce_prevent_admin_access', '__return_true' );
			return;
		}
	}

	/**
	 * Allow POS admin access for shop and outlet managers.
	 *
	 * Determines whether access to the POS admin should be prevented,
	 * allowing POS managers to bypass restrictions.
	 *
	 * @param bool $prevent_access Whether access should be prevented by default.
	 *
	 * @return bool False if the current user is a POS manager (access allowed), otherwise the original $prevent_access value.
	 */
	public function allow_pos_admin_access( $prevent_access ) {
		if ( ! is_user_logged_in() ) {
			return $prevent_access;
		}

		$user = wp_get_current_user();
		if ( $this->is_pos_manager( $user ) ) {
			return false;
		}

		return $prevent_access;
	}
	/**
	 * Handle login redirect for POS users.
	 *
	 * @param string   $redirect The URL to redirect to.
	 * @param \WP_User $user The user object.
	 * @return string The URL to redirect to.
	 */
	public function handle_login_redirect( $redirect, $user ) {
		if ( ! $user || ! isset( $user->roles ) ) {
			return $redirect;
		}

		if ( $this->is_pos_manager( $user ) ) {
			return admin_url();
		} elseif ( in_array( 'csmsl_pos_cashier', (array) $user->roles, true ) ) {
			return home_url( '/smart-pos' );
		}

		return $redirect;
	}
	/**
	 * Handle login redirect .
	 *
	 * @param string   $redirect_to The URL to redirect to.
	 * @param string   $requested_redirect_to The requested redirect URL.
	 * @param \WP_User $user The user object.
	 * @return string The URL to redirect to.
	 */
	public function handle_wp_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
		if ( ! $user || is_wp_error( $user ) ) {
			return $redirect_to;
		}

		// Only handle POS manager roles for wp-admin login.
		if ( $this->is_pos_manager( $user ) ) {
			return admin_url();
		}

		// Don't interfere with cashier logins - let POS class handle them.
		return $redirect_to;
	}
	/**
	 * Check if the user is a POS manager (either shop or outlet).
	 *
	 * @param \WP_User $user The logged-in user object.
	 */
	private function is_pos_manager( $user ) {
		$roles = (array) $user->roles;
		return in_array( 'csmsl_pos_shop_manager', $roles, true ) ||
		in_array( 'csmsl_pos_outlet_manager', $roles, true );
	}
	/**
	 * Set a transient for login error messages and redirect to the login page.
	 *
	 * @param string $message The error message to set.
	 */
	private function set_login_error( $message ) {
		set_transient( 'csmsl_login_error', $message, 30 );
		wp_safe_redirect( home_url( '/smart-pos/login' ) );
		exit;
	}
	/**
	 * Handle successful login by updating user meta and redirecting.
	 *
	 * Updates the user's last login timestamp and redirects them based on their POS access or role.
	 *
	 * @param \WP_User $user The logged-in user object.
	 *
	 * @return void
	 */
	private function handle_successful_login( $user ) {
		update_user_meta( $user->ID, 'last_login', current_time( 'mysql' ) );

		// Always redirect POS users to the POS system, regardless of role.
		if ( $this->verify_pos_access( $user ) ) {
			wp_safe_redirect( home_url( '/smart-pos' ) );
			exit;
		} elseif ( $this->is_pos_manager( $user ) ) {
			wp_safe_redirect( admin_url() );
			exit;
		} else {
			wp_safe_redirect( home_url() );
			exit;
		}
	}

	/**
	 * Handle failed login attempts by logging out the user and setting an error message.
	 *
	 * @param string $message The error message to display.
	 */
	private function handle_failed_login( $message ) {
		wp_logout();
		$this->set_login_error( $message );
	}
}
