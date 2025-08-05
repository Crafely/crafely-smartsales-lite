<?php

namespace AISMARTSALES\Includes\Api\AI;

use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIAssistancesApiHandler {


	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route(
			'ai-smart-sales/v1',
			'/assistances',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_assistances' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/assistances/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_assistance' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/assistances',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_assistance' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/assistances/(?P<id>\d+)',
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_assistance' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'ai-smart-sales/v1',
			'/assistances/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_assistance' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	public function check_permission( $request ) {
		// Check if user is logged in and has appropriate capabilities
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Get current user
		$user = wp_get_current_user();

		// Check if user has any of our POS roles or is an administrator
		$allowed_roles = array( 'administrator', 'aipos_outlet_manager', 'aipos_cashier', 'aipos_shop_manager' );
		$user_roles    = (array) $user->roles;

		if ( ! array_intersect( $allowed_roles, $user_roles ) ) {
			return false;
		}

		return true;
	}

	private function format_error_response( $message, $errors = array(), $statusCode = 400, $path = '' ) {
		$error = array();

		// If $errors is an associative array, use it as-is
		if ( is_array( $errors ) && ! empty( $errors ) && array_keys( $errors ) !== range( 0, count( $errors ) - 1 ) ) {
			$error = $errors; // Use the associative array directly
		} else {
			// Otherwise, use a generic error structure
			$error = array(
				'error' => $message, // Fallback for non-associative errors
			);
		}

		return array(
			'success' => false,
			'message' => $message,
			'data'    => null,
			'error'   => $error,
		);
	}

	private function format_assistance_response( $assistance, $original_ai_config = null ) {
		// Use the original ai_config if provided, otherwise decode from the database
		$ai_config = $original_ai_config ?? json_decode( $assistance['ai_config'], true );

		return array(
			'id'        => (int) $assistance['id'],
			'user_id'   => (int) $assistance['user_id'],
			'thread_id' => $assistance['thread_id'],
			'title'     => $assistance['title'],
			'page'      => $assistance['page'],
			'ai_config' => $ai_config,
		);
	}

	public function get_assistances( $request ) {
		global $wpdb;

		// Escaping the table name (good)
		$table_name = esc_sql( $wpdb->prefix . 'ai_smart_sales_assistances' );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results( "SELECT * FROM {$table_name}", ARRAY_A );

		if ( empty( $results ) ) {
			return new WP_REST_Response(
				array(
					'success' => true,
					'message' => 'No assistances found.',
					'data'    => array(),
				),
				200
			);
		}

		$formatted_results = array_map( array( $this, 'format_assistance_response' ), $results );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Assistances retrieved successfully.',
				'data'    => $formatted_results,
			),
			200
		);
	}

	public function get_assistance( $request ) {
		global $wpdb;

		// Sanitize the table name
		$table_name = esc_sql( $wpdb->prefix . 'ai_smart_sales_assistances' );

		// Sanitize and cast the ID
		$id = isset( $request['id'] ) ? absint( $request['id'] ) : 0;

		// Manually build query string with table name, then prepare the rest
		$sql = "SELECT * FROM {$table_name} WHERE id = %d";

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_row(
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared,
			$wpdb->prepare( $sql, $id ),
			ARRAY_A
		);

		if ( ! $result ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Assistance not found.',
					array(
						'id' => "The assistance with the ID '{$id}' does not exist.",
					),
					404,
					$request->get_route()
				),
				404
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Assistance retrieved successfully.',
				'data'    => $this->format_assistance_response( $result ),
			),
			200
		);
	}

	public function create_assistance( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ai_smart_sales_assistances';
		$data       = $request->get_json_params();

		// Check if the user is logged in
		$user_id = get_current_user_id();
		if ( $user_id === 0 ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'User not logged in.',
					array(
						'user' => 'You must be logged in to create an assistance.',
					),
					401,
					$request->get_route()
				),
				401
			);
		}

		// Define required fields and their error messages
		$required_fields = array(
			'thread_id' => 'thread_id is required.',
			'title'     => 'title is required.',
			'page'      => 'page is required.',
		);

		$errors = array();

		// Check for missing required fields
		foreach ( $required_fields as $field => $error_message ) {
			if ( ! isset( $data[ $field ] ) || empty( $data[ $field ] ) ) {
				$errors[ $field ] = $error_message;
			}
		}

		// If there are missing fields, return a comprehensive error response
		if ( ! empty( $errors ) ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Missing required fields: ' . implode( ', ', array_keys( $errors ) ),
					$errors,
					400,
					$request->get_route()
				),
				400
			);
		}

		// Insert the assistance
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$inserted = $wpdb->insert(
			$table_name,
			array(
				'user_id'   => $user_id,
				'thread_id' => $data['thread_id'],
				'title'     => $data['title'],
				'page'      => $data['page'],
				'ai_config' => isset( $data['ai_config'] ) ? wp_json_encode( $data['ai_config'] ) : null,
			)
		);

		if ( ! $inserted ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to create assistance.',
					array(
						'server' => 'The assistance could not be created.',
					),
					500,
					$request->get_route()
				),
				500
			);
		}

		$assistance_id = $wpdb->insert_id;
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$assistance = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $assistance_id ), ARRAY_A );

		// Pass the original ai_config to preserve the exact value
		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Assistance created successfully.',
				'data'    => $this->format_assistance_response( $assistance, $data['ai_config'] ?? null ),
			),
			201
		);
	}

	public function update_assistance( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ai_smart_sales_assistances';
		$id         = $request['id'];
		$data       = $request->get_json_params();

		// Check if the assistance exists
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$existing_assistance = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ), ARRAY_A );

		if ( ! $existing_assistance ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Assistance not found.',
					array(
						'id' => "The assistance with the ID '{$id}' does not exist.",
					),
					404,
					$request->get_route()
				),
				404
			);
		}

		// Update the assistance
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$updated = $wpdb->update(
			$table_name,
			array(
				'user_id'   => get_current_user_id(),
				'thread_id' => $data['thread_id'] ?? $existing_assistance['thread_id'],
				'title'     => $data['title'] ?? $existing_assistance['title'],
				'page'      => $data['page'] ?? $existing_assistance['page'],
				'ai_config' => isset( $data['ai_config'] ) ? wp_json_encode( $data['ai_config'] ) : $existing_assistance['ai_config'],
			),
			array( 'id' => $id )
		);

		if ( $updated === false ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to update assistance.',
					array(
						'server' => 'The assistance could not be updated.',
					),
					500,
					$request->get_route()
				),
				500
			);
		}
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$updated_assistance = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ), ARRAY_A );

		// Pass the original ai_config to preserve the exact value
		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Assistance updated successfully.',
				'data'    => $this->format_assistance_response( $updated_assistance, $data['ai_config'] ?? null ),
			),
			200
		);
	}

	public function delete_assistance( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ai_smart_sales_assistances';
		$id         = $request['id'];

		// Check if the assistance exists
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$existing_assistance = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ), ARRAY_A );

		if ( ! $existing_assistance ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Assistance not found.',
					array(
						'id' => "The assistance with the ID '{$id}' does not exist.",
					),
					404,
					$request->get_route()
				),
				404
			);
		}
		// Delete the assistance
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->delete( $table_name, array( 'id' => $id ) );

		if ( ! $deleted ) {
			return new WP_REST_Response(
				$this->format_error_response(
					'Failed to delete assistance.',
					array(
						'server' => 'The assistance could not be deleted.',
					),
					500,
					$request->get_route()
				),
				500
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Assistance deleted successfully.',
				'data'    => array( 'id' => $id ),
			),
			200
		);
	}
}

new AIAssistancesApiHandler();
