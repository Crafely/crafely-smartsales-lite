<?php

namespace CSMSL\Includes\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {


	public function __construct() {
		if ( ! defined( 'CSMSL_DIR' ) || ! defined( 'CSMSL_URL' ) ) {
			wp_die( esc_html__( 'CSMSL_DIR or CSMSL_URL is not defined.', 'crafely-smartsales-lite' ) );
		}

		// Add admin menu and settings.
		add_action( 'admin_menu', array( $this, 'csmsl_add_admin_menu' ) );

		// Enqueue admin scripts and styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'csmsl_enqueue_admin_assets' ) );

		// Add script loader tag filter for module type.
		add_filter( 'script_loader_tag', array( $this, 'csmsl_add_module_type_to_script' ), 10, 3 );
	}

	/**
	 * Enqueue admin scripts and styles.
	 */
	public function csmsl_enqueue_admin_assets( $hook ) {
		// Keep existing styles for app page.
		if ( 'toplevel_page_smartsales' === $hook ) {
			// Enqueue scoped reset CSS.
			wp_enqueue_style(
				'csmsl-reset-css',
				plugin_dir_url( dirname( __DIR__, 1 ) ) . 'assets/css/reset.css',
				array(),
				filemtime( plugin_dir_path( dirname( __DIR__, 1 ) ) . 'assets/css/reset.css' )
			);
			// Enqueue Admin JS.
			wp_enqueue_script(
				'csmsl-admin-js',
				plugin_dir_url( dirname( __DIR__, 1 ) ) . 'assets/js/admin.js',
				array( 'jquery', 'wp-i18n', 'wp-components', 'wp-element', 'wp-api-fetch' ),
				filemtime( plugin_dir_path( dirname( __DIR__, 1 ) ) . 'assets/js/admin.js' ),
				true
			);

			// Enqueue Vue app assets.
			$this->csmsl_enqueue_vue_assets();
		}
	}

	private function csmsl_enqueue_vue_assets() {
		$js_files  = glob( CSMSL_DIR . 'assets/dist/js/main.*.js' );
		$css_files = glob( CSMSL_DIR . 'assets/dist/css/main.*.css' );

		// Enqueue Vue app's main JS.
		if ( ! empty( $js_files ) ) {
			$js_url = CSMSL_URL . 'assets/dist/js/' . basename( $js_files[0] );
			wp_enqueue_script( 'csmsl-main-js', $js_url, array(), filemtime( $js_files[0] ), true );

			// Localize script with WordPress API settings.
			$this->csmsl_localize_admin_scripts();
		}

		// Enqueue Vue app's main CSS.
		if ( ! empty( $css_files ) ) {
			$css_url = CSMSL_URL . 'assets/dist/css/' . basename( $css_files[0] );
			wp_enqueue_style( 'csmsl-css', $css_url, array(), filemtime( $css_files[0] ) );
		}

		// Enqueue Quill Editor CSS.
		wp_enqueue_style(
			'quill-editor-bubble-css',
			CSMSL_URL . 'assets/quill-editor/quill.bubble.css',
			array(),
			filemtime( CSMSL_DIR . 'assets/quill-editor/quill.bubble.css' )
		);

		wp_enqueue_style(
			'quill-editor-snow-css',
			CSMSL_URL . 'assets/quill-editor/quill.snow.css',
			array(),
			filemtime( CSMSL_DIR . 'assets/quill-editor/quill.snow.css' )
		);
	}

	/**
	 * Add the admin menu for the plugin.
	 */
	public function csmsl_add_admin_menu() {
		// Check if user has required role.
		$user          = wp_get_current_user();
		$allowed_roles = array( 'administrator', 'csmsl_pos_shop_manager', 'csmsl_pos_outlet_manager' );

		if ( ! array_intersect( $allowed_roles, (array) $user->roles ) ) {
			return;
		}

		add_menu_page(
			esc_html__( 'Smart sales lite', 'crafely-smartsales-lite' ),
			esc_html__( 'Smart Sales Lite', 'crafely-smartsales-lite' ),
			'read', // Use 'read' instead of 'manage_options' to allow POS managers.
			'smartsales',
			array( $this, 'csmsl_render_dashboard_page' ),
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjQ0OCIgaGVpZ2h0PSI0NDYiPgo8cGF0aCBkPSJNMCAwIEMwLjUxNTQ2Mzg3IDAuNDUzNzUgMS4wMzA5Mjc3MyAwLjkwNzUgMS41NjIwMTE3MiAxLjM3NSBDNDMuODAxMTg5NTYgMzguNjI2OTY1MTggNjguMzg1NzU0NTIgOTIuMDQzMjc3NDMgNzIgMTQ4IEM3NC42NTE5MDAwNiAyMDQuNTQ3NjExNDEgNTUuODIxMzkxNDEgMjU5LjczODkzMTIzIDE4IDMwMiBDMTcuNTA4NTQ0OTIgMzAyLjU1MzE2ODk1IDE3LjAxNzA4OTg0IDMwMy4xMDYzMzc4OSAxNi41MTA3NDIxOSAzMDMuNjc2MjY5NTMgQy0xOC40MzUxNzM1MyAzNDIuODU4Mzk0MzMgLTY2LjE4MDE1MzkgMzY2LjI2NDQyNzIgLTExOCAzNzMgQy0xMTkuMDA1NDY4NzUgMzczLjEzMjc3MzQ0IC0xMjAuMDEwOTM3NSAzNzMuMjY1NTQ2ODcgLTEyMS4wNDY4NzUgMzczLjQwMjM0Mzc1IEMtMTc1LjY5NDAwMDUgMzc5LjE2NzE0MTY3IC0yMzIuMjAxNjc3NTEgMzYyLjM3NzQxNTI3IC0yNzUuMTQwNjI1IDMyOC4zNzUgQy0yNzguNzYzNDExNzMgMzI1LjM2NTkwOTM4IC0yODIuMjgxNDA3NjQgMzIyLjI2NDAyMjUzIC0yODUuNzQyMTg3NSAzMTkuMDcwMzEyNSBDLTI4Ny43NjQ3NTI1NCAzMTcuMjE1NzExMzQgLTI4OS44MzQ4Mzk4NCAzMTUuNDQ5NjQ4MzkgLTI5MS45Mzc1IDMxMy42ODc1IEMtMzAwLjg3NTg2NjI2IDMwNS44Njg2ODY2OSAtMzA4LjI5NTE0MjEgMjk2Ljc1OTUwNjYzIC0zMTUgMjg3IEMtMzEzLjYyNzkyMjQzIDI4My45MjMyMTMxOSAtMzEyLjA2NTA3MzA2IDI4MS44MTcxOTM1OCAtMzA5LjY4NDQxNzcyIDI3OS40NDM5MDg2OSBDLTMwOS4wMDAxMjE5MiAyNzguNzU2ODYzNTYgLTMwOC4zMTU4MjYxMSAyNzguMDY5ODE4NDIgLTMwNy42MTA3OTQwNyAyNzcuMzYxOTUzNzQgQy0zMDYuODYwNTc0OCAyNzYuNjE4OTI1MDIgLTMwNi4xMTAzNTU1MyAyNzUuODc1ODk2MyAtMzA1LjMzNzQwMjM0IDI3NS4xMTAzNTE1NiBDLTMwNC4xNTE5MDU0NCAyNzMuOTI0NDE2NTggLTMwNC4xNTE5MDU0NCAyNzMuOTI0NDE2NTggLTMwMi45NDI0NTkxMSAyNzIuNzE0NTIzMzIgQy0zMDAuMzI4MzMxMDkgMjcwLjEwMTc4ODkxIC0yOTcuNzA3MTg5NTYgMjY3LjQ5NjIxMDQ2IC0yOTUuMDg1OTM3NSAyNjQuODkwNjI1IEMtMjkzLjI3MjMyMDU3IDI2My4wODA3MzgwOCAtMjkxLjQ1OTE0NDYzIDI2MS4yNzA0MDkxNyAtMjg5LjY0NjM5MjgyIDI1OS40NTk2NTU3NiBDLTI4NS4zNjEyNTk2NiAyNTUuMTgxMzc5MjkgLTI4MS4wNzE3MDM0OSAyNTAuOTA3NTg2OTkgLTI3Ni43Nzk5NzE3MiAyNDYuNjM1OTMzNjQgQy0yNzEuODkzMzYyMjEgMjQxLjc3MTY4OTgyIC0yNjcuMDEyNDQwMSAyMzYuOTAxNzU2MTQgLTI2Mi4xMzE5ODU0MyAyMzIuMDMxMzM4MjEgQy0yNTIuMDk0MzAyMTEgMjIyLjAxNDU1MjAxIC0yNDIuMDQ5NTA0MDcgMjEyLjAwNDkyNTc5IC0yMzIgMjAyIEMtMjI3LjYzNjYzNzg5IDIwNS42NjE2OTcxNCAtMjIzLjQ1ODA1NjgyIDIwOS40MTg1NzM4NCAtMjE5LjQzMTg4NDc3IDIxMy40NTQ1ODk4NCBDLTIxOC45MjM2NzY5MSAyMTMuOTYyNjUxNjcgLTIxOC40MTU0NjkwNiAyMTQuNDcwNzEzNSAtMjE3Ljg5MTg2MDk2IDIxNC45OTQxNzExNCBDLTIxNi4yMzkzMjMyIDIxNi42NDczOTY2OCAtMjE0LjU5MDM0MDEgMjE4LjMwNDExODA4IC0yMTIuOTQxNDA2MjUgMjE5Ljk2MDkzNzUgQy0yMTEuNzg1Nzc3NzEgMjIxLjExODQzNDI2IC0yMTAuNjI5OTI1ODYgMjIyLjI3NTcwODExIC0yMDkuNDczODYxNjkgMjIzLjQzMjc2OTc4IEMtMjA2LjQ1MTcyMjYyIDIyNi40NTg3MDIxOCAtMjAzLjQzMjYwMDQ3IDIyOS40ODc2MjY5NyAtMjAwLjQxNDEyMzU0IDIzMi41MTcyMTE5MSBDLTE5Ny4zMjU0NDU1MyAyMzUuNjE2MTcyOTUgLTE5NC4yMzM4ODY2NSAyMzguNzEyMjU3MTMgLTE5MS4xNDI1NzgxMiAyNDEuODA4NTkzNzUgQy0xODUuMDkyMjQ5MDIgMjQ3Ljg2OTYwMzk0IC0xNzkuMDQ1MTA4MDcgMjUzLjkzMzc4MjQ3IC0xNzMgMjYwIEMtMTY5LjY4MTQ4MTE0IDI1OC41NTI1ODg4MyAtMTY3LjQ5OTY1MTM4IDI1Ni43NDY5NzY2NiAtMTY0Ljk1MDM3ODQyIDI1NC4xOTA4ODc0NSBDLTE2NC4xNTM5NDkyOCAyNTMuMzk3MjA3NjQgLTE2My4zNTc1MjAxNCAyNTIuNjAzNTI3ODMgLTE2Mi41MzY5NTY3OSAyNTEuNzg1Nzk3MTIgQy0xNjEuNjcyMzY4NDcgMjUwLjkxNDAxODI1IC0xNjAuODA3NzgwMTUgMjUwLjA0MjIzOTM4IC0xNTkuOTE2OTkyMTkgMjQ5LjE0NDA0Mjk3IEMtMTU4Ljk5OTU3MjQ1IDI0OC4yMjY5MTUyOCAtMTU4LjA4MjE1MjcxIDI0Ny4zMDk3ODc2IC0xNTcuMTM2OTMyMzcgMjQ2LjM2NDg2ODE2IEMtMTU0LjYyNDQ4NzAzIDI0My44NTI5OTY0IC0xNTIuMTE4MjUzOTQgMjQxLjMzNTAwNzk3IC0xNDkuNjEzMDg5NTYgMjM4LjgxNTg3NzQ0IEMtMTQ2Ljk5MjgxMjIzIDIzNi4xODMwOTM4IC0xNDQuMzY2ODY2MzggMjMzLjU1NTk3NjQ3IC0xNDEuNzQxNTE2MTEgMjMwLjkyODI1MzE3IEMtMTM2Ljc3MjMxMTE4IDIyNS45NTI4MzMxOSAtMTMxLjgwODIyNjMxIDIyMC45NzIzMzcxMyAtMTI2Ljg0NTk1OTkgMjE1Ljk4OTk5Nzk4IEMtMTIxLjE5NTc1MzM3IDIxMC4zMTc0MzMyNyAtMTE1LjUzOTg2MzcyIDIwNC42NTA1NDk1NyAtMTA5Ljg4MzQ4OTEzIDE5OC45ODQxMzYzNCBDLTk4LjI0OTMyMzMgMTg3LjMyOTA2NzkzIC04Ni42MjIzMTY1NSAxNzUuNjY2ODg0NjggLTc1IDE2NCBDLTU5LjM5MzczMzQ1IDE3MS44MDMxMzMyNyAtNDkuMzM3ODM3MDIgMTg3LjY2MjE2Mjk4IC0zNyAyMDAgQy0zNi42NyAxNjUuNjggLTM2LjM0IDEzMS4zNiAtMzYgOTYgQy04Ny40OCA5Ni40OTUgLTg3LjQ4IDk2LjQ5NSAtMTQwIDk3IEMtMTI3Ljc5IDEwOS4yMSAtMTE1LjU4IDEyMS40MiAtMTAzIDEzNCBDLTEwNy4wMTkzNjQ4OCAxMzguNjg5MjU5MDMgLTExMS4wMDkzNzczMiAxNDMuMTUxMDA2NTUgLTExNS4zNzI1NTg1OSAxNDcuNDg2MDgzOTggQy0xMTUuOTc4MTU2MTMgMTQ4LjA5MTgyNzU1IC0xMTYuNTgzNzUzNjYgMTQ4LjY5NzU3MTExIC0xMTcuMjA3NzAyNjQgMTQ5LjMyMTY3MDUzIEMtMTE5LjE4NTU4MzQxIDE1MS4yOTg4NjE0NiAtMTIxLjE2Njk2OTE0IDE1My4yNzI0OTg3NiAtMTIzLjE0ODQzNzUgMTU1LjI0NjA5Mzc1IEMtMTI0LjUzMDEzNjM5IDE1Ni42MjU5MjUxNiAtMTI1LjkxMTYxMjg4IDE1OC4wMDU5NzkzIC0xMjcuMjkyODc3MiAxNTkuMzg2MjQ1NzMgQy0xMzAuOTEwNjQ5MTMgMTYzLjAwMDIxODUgLTEzNC41MzE0MTUxOSAxNjYuNjExMTc2NzMgLTEzOC4xNTI4MzIwMyAxNzAuMjIxNDk2NTggQy0xNDEuODU1MDE3NzcgMTczLjkxMzQxMjAzIC0xNDUuNTU0MzM3NzEgMTc3LjYwODE5NjYzIC0xNDkuMjUzOTA2MjUgMTgxLjMwMjczNDM4IEMtMTU2LjQ5OTgxNjMxIDE4OC41Mzc5NTMwMyAtMTYzLjc0ODg5NTU1IDE5NS43Njk5ODc0MSAtMTcxIDIwMyBDLTE3NC45NzUyNjIyNCAyMDEuMzk5MzI0NCAtMTc3LjY0MDIwMzA0IDE5OC42Mjg1NjQ0NCAtMTgwLjU5NDcyNjU2IDE5NS42MzIzMjQyMiBDLTE4MS4xNDE1MTA2MiAxOTUuMDg1ODMyMjEgLTE4MS42ODgyOTQ2OCAxOTQuNTM5MzQwMjEgLTE4Mi4yNTE2NDc5NSAxOTMuOTc2Mjg3ODQgQy0xODQuMDUyNzU2NDkgMTkyLjE3Mzc4OTA3IC0xODUuODQ2NjgyNDcgMTkwLjM2NDMxNjA0IC0xODcuNjQwNjI1IDE4OC41NTQ2ODc1IEMtMTg4Ljg5MDAwNDk1IDE4Ny4zMDE1NzYzMyAtMTkwLjEzOTgyODAxIDE4Ni4wNDg5MDY4MSAtMTkxLjM5MDA3NTY4IDE4NC43OTY2NjEzOCBDLTE5NC42NzU4Njg3MiAxODEuNTAzMjQwNDUgLTE5Ny45NTU2MTA3IDE3OC4yMDM4NTcwOSAtMjAxLjIzNDEzMDg2IDE3NC45MDMxOTgyNCBDLTIwNC41ODIxNDI1MyAxNzEuNTM0NzEwMDYgLTIwNy45MzU4MzkwNyAxNjguMTcxODkyNCAtMjExLjI4OTA2MjUgMTY0LjgwODU5Mzc1IEMtMjE3Ljg2NDk1Mjk2IDE1OC4yMTEyNjgyNCAtMjI0LjQzNDQ2NTE2IDE1MS42MDc2MzAxNyAtMjMxIDE0NSBDLTIzNi44OTAxNTg4OCAxNDkuODgzNTM3OSAtMjQyLjI3OTc5NDY3IDE1NS4xNTY3MDAzNSAtMjQ3LjY2NzIzNjMzIDE2MC41ODU0NDkyMiBDLTI0OC42MjA0NDM1NSAxNjEuNTQyMjE5NDIgLTI0OS41NzM4OTA2OCAxNjIuNDk4NzUwNjUgLTI1MC41Mjc1NTczNyAxNjMuNDU1MDYyODcgQy0yNTIuNTY3NDMyODIgMTY1LjUwMTQ0ODYyIC0yNTQuNjA1Nzg2MzEgMTY3LjU0OTMzMzc5IC0yNTYuNjQyOTg0MzkgMTY5LjU5ODM4NDg2IEMtMjU5Ljg2Mjc1ODI3IDE3Mi44MzY1NDU4IC0yNjMuMDg2OTU5MDggMTc2LjA3MDI3NDU3IC0yNjYuMzExOTM1NDIgMTc5LjMwMzI1MzE3IEMtMjc1LjQ3Njk1NDQ0IDE4OC40OTIzNTg1NyAtMjg0LjYzNTk0NjIzIDE5Ny42ODc0NTgwNCAtMjkzLjc5MDc3MTQ4IDIwNi44ODY3MTg3NSBDLTI5OC44NTY5NTU3MSAyMTEuOTc3NDE0OTIgLTMwMy45MjY2OTkzNyAyMTcuMDY0NTM5MDkgLTMwOS4wMDAzMTg3MSAyMjIuMTQ3ODI1MzYgQy0zMTIuMjA5Mzk1NzIgMjI1LjM2MzQ1NDQyIC0zMTUuNDEzOTA5NCAyMjguNTgzNTQyOCAtMzE4LjYxNTE0OTE0IDIzMS44MDY5NzMyMiBDLTMyMC42MDA1NzEzNSAyMzMuODA0MDg2MzkgLTMyMi41OTAxMjQyIDIzNS43OTcwNDMyOCAtMzI0LjU4MDg5ODI4IDIzNy43ODg4MjAyNyBDLTMyNS45NDgzNzI2NSAyMzkuMTU5NDgxMiAtMzI3LjMxMDgzNDQ0IDI0MC41MzUxMzcyNiAtMzI4LjY3MzE3MiAyNDEuOTEwOTAzOTMgQy0zMjkuODg2MzgxMjMgMjQzLjEyMjE1NjkxIC0zMjkuODg2MzgxMjMgMjQzLjEyMjE1NjkxIC0zMzEuMTI0MDk5NzMgMjQ0LjM1Nzg3OTY0IEMtMzMxLjgyODQ3NTc3IDI0NS4wNjUzOTgwOCAtMzMyLjUzMjg1MTgxIDI0NS43NzI5MTY1MyAtMzMzLjI1ODU3MjU4IDI0Ni41MDE4NzQ5MiBDLTMzNSAyNDggLTMzNSAyNDggLTMzNyAyNDggQy0zNDcuMTg3MTUxODEgMjIzLjkyODMyOTEgLTM1NS4yNjM0MzE1MiAxOTguMzYzMDEzNjQgLTM1NiAxNzIgQy0zNTYuMDI1NzgxMjUgMTcxLjIwNjc0MzE2IC0zNTYuMDUxNTYyNSAxNzAuNDEzNDg2MzMgLTM1Ni4wNzgxMjUgMTY5LjU5NjE5MTQxIEMtMzU3LjQwMjEyODU3IDEyMy4yODA4OTE2NiAtMzQ1Ljg4Nzc3ODIzIDc1LjY5ODEwMzM4IC0zMTggMzggQy0zMTcuMjQ0NjA5MzcgMzYuOTczOTA2MjUgLTMxNi40ODkyMTg3NSAzNS45NDc4MTI1IC0zMTUuNzEwOTM3NSAzNC44OTA2MjUgQy0zMDQuMjgxMzA1MTggMTkuNjEzNTk4MzYgLTI5MS45MjM0NDc3NyA1LjkzMDMyNjg5IC0yNzcgLTYgQy0yNzUuODQ3NTc4MTIgLTYuOTcwNjY0MDYgLTI3NS44NDc1NzgxMiAtNi45NzA2NjQwNiAtMjc0LjY3MTg3NSAtNy45NjA5Mzc1IEMtMjQzLjk3Mjg2MjY4IC0zMy41NDM0NDc3NiAtMjA1LjM0Nzc1MDQyIC00OC4wNzE3MTM4OSAtMTY2IC01MyBDLTE2NS4wMSAtNTMuMTMxNDg0MzcgLTE2NC4wMiAtNTMuMjYyOTY4NzUgLTE2MyAtNTMuMzk4NDM3NSBDLTEwMy45OTQ2Nzk3OCAtNTkuNzQ0OTM5OTcgLTQ0LjA0MTMzNDU3IC0zOS4yNTkzODUyOSAwIDAgWiAiIGZpbGw9IiNGMEYwRjEiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDM2OCw2NCkiLz4KPC9zdmc+Cg==',
			30
		);
	}

	/**
	 * Render the Vue Dashboard page.
	 */
	public function csmsl_render_dashboard_page() {
		// The API settings are already localized in csmsl_localize_admin_scripts().
		// which is called when enqueuing the main script.
		echo '<div id="app"></div>';
	}

	/**
	 * Localize admin scripts with WordPress API settings.
	 */
	private function csmsl_localize_admin_scripts() {
		// Create the same API data structure as in the POS template.
		$api_data = array(
			'root'         => esc_url_raw( rest_url() ),
			'nonce'        => wp_create_nonce( 'wp_rest' ),
			'baseUrl'      => esc_url_raw( CSMSL_URL ),
			'assetsUrl'    => esc_url_raw( CSMSL_URL . 'assets/dist/free/' ),
			'isLoggedIn'   => is_user_logged_in(),
			'currentUser'  => is_user_logged_in() ? wp_get_current_user()->ID : 0,
			'userRoles'    => is_user_logged_in() ? wp_get_current_user()->roles : array(),
			'apiNamespace' => 'ai-smart-sales/v1',
		);

		// Properly localize the script with WordPress API settings.
		wp_localize_script(
			'csmsl-main-js',
			'wpApiSettings',
			$api_data
		);
	}

	/**
	 * Add module type to specific scripts using WordPress-compliant method
	 */
	public function csmsl_add_module_type_to_script( $tag, $handle, $src ) {
		// Only modify our specific script.
		if ( 'csmsl-main-js' === $handle ) {
			// Replace the script tag to include type="module".
			$tag = str_replace( '<script ', '<script type="module" ', $tag );
		}
		return $tag;
	}
}
