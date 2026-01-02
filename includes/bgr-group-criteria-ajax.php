<?php
/**
 * AJAX Handlers for Group-Level Criteria
 *
 * @since   3.7.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * AJAX: Save group criteria settings.
 *
 * @since 3.7.0
 */
function bgr_ajax_save_group_criteria_settings() {
	// Verify nonce.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'bgr_group_criteria_nonce' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'bp-group-reviews' ) ) );
	}

	// Get group ID.
	$group_id = isset( $_POST['group_id'] ) ? absint( $_POST['group_id'] ) : 0;

	if ( ! $group_id ) {
		wp_send_json_error( array( 'message' => __( 'Invalid group ID.', 'bp-group-reviews' ) ) );
	}

	// Check user permissions.
	if ( ! bgr_user_can_manage_group_criteria( $group_id ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to manage this group.', 'bp-group-reviews' ) ) );
	}

	// Get criteria instance.
	$criteria = bgr_group_criteria();

	// Parse settings.
	$mode = isset( $_POST['mode'] ) ? sanitize_text_field( wp_unslash( $_POST['mode'] ) ) : 'inherit';

	// Set mode.
	$criteria->set_criteria_mode( $group_id, $mode );

	// If override mode, update enabled global criteria.
	if ( 'override' === $mode ) {
		$enabled_global = isset( $_POST['enabled_global_criteria'] ) && is_array( $_POST['enabled_global_criteria'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['enabled_global_criteria'] ) )
			: array();

		$settings                            = $criteria->get_group_settings( $group_id );
		$settings['enabled_global_criteria'] = $enabled_global;
		$criteria->save_group_settings( $group_id, $settings );
	}

	wp_send_json_success(
		array(
			'message'  => __( 'Criteria settings saved successfully.', 'bp-group-reviews' ),
			'criteria' => $criteria->get_effective_criteria( $group_id ),
		)
	);
}
add_action( 'wp_ajax_bgr_save_group_criteria_settings', 'bgr_ajax_save_group_criteria_settings' );

/**
 * AJAX: Add custom criterion to group.
 *
 * @since 3.7.0
 */
function bgr_ajax_add_custom_criteria() {
	// Verify nonce.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'bgr_group_criteria_nonce' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'bp-group-reviews' ) ) );
	}

	$group_id = isset( $_POST['group_id'] ) ? absint( $_POST['group_id'] ) : 0;
	$name     = isset( $_POST['criteria_name'] ) ? sanitize_text_field( wp_unslash( $_POST['criteria_name'] ) ) : '';

	if ( ! $group_id || empty( $name ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid group ID or criteria name.', 'bp-group-reviews' ) ) );
	}

	if ( ! bgr_user_can_manage_group_criteria( $group_id ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to manage this group.', 'bp-group-reviews' ) ) );
	}

	$criteria = bgr_group_criteria();

	// Validate name length.
	if ( strlen( $name ) > 50 ) {
		wp_send_json_error( array( 'message' => __( 'Criteria name must be 50 characters or less.', 'bp-group-reviews' ) ) );
	}

	// Check for duplicate with global criteria.
	if ( $criteria->is_global_criteria_exists( $name ) ) {
		wp_send_json_error( array( 'message' => __( 'A global criterion with this name already exists.', 'bp-group-reviews' ) ) );
	}

	$result = $criteria->add_custom_criteria( $group_id, $name );

	if ( $result ) {
		wp_send_json_success(
			array(
				'message'  => __( 'Custom criterion added successfully.', 'bp-group-reviews' ),
				'criteria' => $criteria->get_effective_criteria( $group_id ),
			)
		);
	} else {
		wp_send_json_error( array( 'message' => __( 'A criterion with this name already exists.', 'bp-group-reviews' ) ) );
	}
}
add_action( 'wp_ajax_bgr_add_custom_criteria', 'bgr_ajax_add_custom_criteria' );

/**
 * AJAX: Archive custom criterion.
 *
 * @since 3.7.0
 */
function bgr_ajax_archive_custom_criteria() {
	// Verify nonce.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'bgr_group_criteria_nonce' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'bp-group-reviews' ) ) );
	}

	$group_id = isset( $_POST['group_id'] ) ? absint( $_POST['group_id'] ) : 0;
	$name     = isset( $_POST['criteria_name'] ) ? sanitize_text_field( wp_unslash( $_POST['criteria_name'] ) ) : '';

	if ( ! $group_id || empty( $name ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid group ID or criteria name.', 'bp-group-reviews' ) ) );
	}

	if ( ! bgr_user_can_manage_group_criteria( $group_id ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to manage this group.', 'bp-group-reviews' ) ) );
	}

	$criteria = bgr_group_criteria();
	$result   = $criteria->archive_custom_criteria( $group_id, $name );

	if ( $result ) {
		wp_send_json_success(
			array(
				'message'  => __( 'Criterion archived successfully.', 'bp-group-reviews' ),
				'criteria' => $criteria->get_effective_criteria( $group_id ),
			)
		);
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to archive criterion.', 'bp-group-reviews' ) ) );
	}
}
add_action( 'wp_ajax_bgr_archive_custom_criteria', 'bgr_ajax_archive_custom_criteria' );

/**
 * AJAX: Delete custom criterion.
 *
 * @since 3.7.0
 */
function bgr_ajax_delete_custom_criteria() {
	// Verify nonce.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'bgr_group_criteria_nonce' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'bp-group-reviews' ) ) );
	}

	$group_id = isset( $_POST['group_id'] ) ? absint( $_POST['group_id'] ) : 0;
	$name     = isset( $_POST['criteria_name'] ) ? sanitize_text_field( wp_unslash( $_POST['criteria_name'] ) ) : '';

	if ( ! $group_id || empty( $name ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid group ID or criteria name.', 'bp-group-reviews' ) ) );
	}

	if ( ! bgr_user_can_manage_group_criteria( $group_id ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to manage this group.', 'bp-group-reviews' ) ) );
	}

	$criteria = bgr_group_criteria();
	$result   = $criteria->delete_custom_criteria( $group_id, $name );

	if ( $result ) {
		wp_send_json_success(
			array(
				'message'  => __( 'Criterion deleted successfully.', 'bp-group-reviews' ),
				'criteria' => $criteria->get_effective_criteria( $group_id ),
			)
		);
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to delete criterion.', 'bp-group-reviews' ) ) );
	}
}
add_action( 'wp_ajax_bgr_delete_custom_criteria', 'bgr_ajax_delete_custom_criteria' );

/**
 * AJAX: Toggle global criterion for group.
 *
 * @since 3.7.0
 */
function bgr_ajax_toggle_global_criteria() {
	// Verify nonce.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'bgr_group_criteria_nonce' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'bp-group-reviews' ) ) );
	}

	$group_id = isset( $_POST['group_id'] ) ? absint( $_POST['group_id'] ) : 0;
	$name     = isset( $_POST['criteria_name'] ) ? sanitize_text_field( wp_unslash( $_POST['criteria_name'] ) ) : '';
	$enabled  = isset( $_POST['enabled'] ) && 'true' === $_POST['enabled'];

	if ( ! $group_id || empty( $name ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid group ID or criteria name.', 'bp-group-reviews' ) ) );
	}

	if ( ! bgr_user_can_manage_group_criteria( $group_id ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to manage this group.', 'bp-group-reviews' ) ) );
	}

	$criteria = bgr_group_criteria();
	$result   = $criteria->toggle_global_criteria( $group_id, $name, $enabled );

	if ( $result ) {
		wp_send_json_success(
			array(
				'message'  => $enabled ? __( 'Criterion enabled.', 'bp-group-reviews' ) : __( 'Criterion disabled.', 'bp-group-reviews' ),
				'criteria' => $criteria->get_effective_criteria( $group_id ),
			)
		);
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to update criterion.', 'bp-group-reviews' ) ) );
	}
}
add_action( 'wp_ajax_bgr_toggle_global_criteria', 'bgr_ajax_toggle_global_criteria' );

/**
 * AJAX: Get group criteria settings.
 *
 * @since 3.7.0
 */
function bgr_ajax_get_group_criteria() {
	// Verify nonce.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'bgr_group_criteria_nonce' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'bp-group-reviews' ) ) );
	}

	$group_id = isset( $_POST['group_id'] ) ? absint( $_POST['group_id'] ) : 0;

	if ( ! $group_id ) {
		wp_send_json_error( array( 'message' => __( 'Invalid group ID.', 'bp-group-reviews' ) ) );
	}

	$criteria = bgr_group_criteria();

	wp_send_json_success(
		array(
			'settings'       => $criteria->get_group_settings( $group_id ),
			'effective'      => $criteria->get_effective_criteria( $group_id ),
			'global_all'     => $criteria->get_global_all_criteria(),
			'global_active'  => $criteria->get_global_active_criteria(),
			'mode'           => $criteria->get_criteria_mode( $group_id ),
			'group_averages' => $criteria->calculate_group_averages( $group_id ),
		)
	);
}
add_action( 'wp_ajax_bgr_get_group_criteria', 'bgr_ajax_get_group_criteria' );

/**
 * Check if user can manage group criteria.
 *
 * @since 3.7.0
 * @param int $group_id The group ID.
 * @return bool True if user can manage.
 */
function bgr_user_can_manage_group_criteria( $group_id ) {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	$user_id = get_current_user_id();

	// Site admins can always manage.
	if ( current_user_can( 'manage_options' ) ) {
		return true;
	}

	// Check if user is group admin.
	if ( function_exists( 'groups_is_user_admin' ) && groups_is_user_admin( $user_id, $group_id ) ) {
		return true;
	}

	// Check if user is group creator.
	if ( function_exists( 'bp_get_group' ) ) {
		$group = bp_get_group( $group_id );
		if ( $group && $group->creator_id === $user_id ) {
			return true;
		}
	}

	return false;
}

/**
 * REST API: Register group criteria endpoints.
 *
 * @since 3.7.0
 */
function bgr_register_group_criteria_rest_routes() {
	register_rest_route(
		'bgr/v1',
		'/groups/(?P<id>\d+)/criteria',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'bgr_rest_get_group_criteria',
			'permission_callback' => function ( $request ) {
				$group_id = absint( $request->get_param( 'id' ) );

				// Verify group exists.
				$group = groups_get_group( $group_id );
				if ( empty( $group->id ) ) {
					return false;
				}

				// Public groups: allow logged-in users.
				// Private/hidden groups: require membership or admin.
				if ( 'public' === $group->status ) {
					return is_user_logged_in();
				}

				return groups_is_user_member( get_current_user_id(), $group_id ) || current_user_can( 'manage_options' );
			},
			'args'                => array(
				'id' => array(
					'validate_callback' => function ( $param ) {
						return is_numeric( $param );
					},
				),
			),
		)
	);

	register_rest_route(
		'bgr/v1',
		'/groups/(?P<id>\d+)/criteria',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'bgr_rest_update_group_criteria',
			'permission_callback' => function ( $request ) {
				$group_id = $request->get_param( 'id' );
				return bgr_user_can_manage_group_criteria( $group_id );
			},
			'args'                => array(
				'id' => array(
					'validate_callback' => function ( $param ) {
						return is_numeric( $param );
					},
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'bgr_register_group_criteria_rest_routes' );

/**
 * REST API: Get group criteria.
 *
 * @since 3.7.0
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response
 */
function bgr_rest_get_group_criteria( $request ) {
	$group_id = $request->get_param( 'id' );
	$criteria = bgr_group_criteria();

	return rest_ensure_response(
		array(
			'group_id'  => $group_id,
			'mode'      => $criteria->get_criteria_mode( $group_id ),
			'settings'  => $criteria->get_group_settings( $group_id ),
			'effective' => $criteria->get_effective_criteria( $group_id ),
			'averages'  => $criteria->calculate_group_averages( $group_id ),
		)
	);
}

/**
 * REST API: Update group criteria.
 *
 * @since 3.7.0
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error
 */
function bgr_rest_update_group_criteria( $request ) {
	$group_id = $request->get_param( 'id' );
	$body     = $request->get_json_params();
	$criteria = bgr_group_criteria();

	if ( isset( $body['mode'] ) ) {
		$criteria->set_criteria_mode( $group_id, $body['mode'] );
	}

	if ( isset( $body['settings'] ) && is_array( $body['settings'] ) ) {
		$criteria->save_group_settings( $group_id, $body['settings'] );
	}

	return rest_ensure_response(
		array(
			'success'   => true,
			'group_id'  => $group_id,
			'effective' => $criteria->get_effective_criteria( $group_id ),
		)
	);
}
