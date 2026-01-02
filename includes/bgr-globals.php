<?php
/**
 * Group Review Plugin Global Variables
 *
 * @since   1.0.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

/**
 * Group Review Plugin Global Variables
 *
 *  @since   1.0.0
 *  @author  Wbcom Designs
 */
function bp_group_review_globals() {
	global $bgr;
	$bgr_admin_general_settings     = get_option( 'bgr_admin_general_settings' );
	$bgr_admin_criteria_settings    = get_option( 'bgr_admin_criteria_settings' );
	$bgr_admin_display_settings     = get_option( 'bgr_admin_display_settings' );
	$bp_group_review_email_settings = get_option( 'bp_group_review_email_settings' );

	/**** Global variable values for General settings */

	if ( ! empty( $bgr_admin_general_settings ) ) {
		$auto_approve_reviews  = isset( $bgr_admin_general_settings['auto_approve_reviews'] ) ? $bgr_admin_general_settings['auto_approve_reviews'] : 'no';
		$reviews_per_page      = isset( $bgr_admin_general_settings['reviews_per_page'] ) ? $bgr_admin_general_settings['reviews_per_page'] : 5;
		$allow_notification    = isset( $bgr_admin_general_settings['allow_notification'] ) ? $bgr_admin_general_settings['allow_notification'] : 'yes';
		$allow_activity        = isset( $bgr_admin_general_settings['allow_activity'] ) ? $bgr_admin_general_settings['allow_activity'] : 'yes';
		$exclude_groups        = isset( $bgr_admin_general_settings['exclude_groups'] ) ? $bgr_admin_general_settings['exclude_groups'] : array();
		$multi_reviews         = isset( $bgr_admin_general_settings['multi_reviews'] ) ? $bgr_admin_general_settings['multi_reviews'] : 'no';
		$enable_group_criteria = isset( $bgr_admin_general_settings['enable_group_criteria'] ) ? $bgr_admin_general_settings['enable_group_criteria'] : 'no';
	} else {
		$multi_reviews         = 'no';
		$reviews_per_page      = 5;
		$allow_notification    = 'yes';
		$allow_activity        = 'yes';
		$exclude_groups        = array();
		$auto_approve_reviews  = 'no';
		$enable_group_criteria = 'no';
	}

	/**** Global variable values for Email settings */

	$review_accept_email_subject = ''; // Initialize to avoid warnings.
	$review_accept_email_message = ''; // Initialize to avoid warnings.
	$review_deny_email_subject   = ''; // Initialize to avoid warnings.
	$review_deny_email_message   = ''; // Initialize to avoid warnings.

	if ( ! empty( $bp_group_review_email_settings ) ) {
		$allow_email                 = isset( $bp_group_review_email_settings['bgr_allow_email'] ) ? $bp_group_review_email_settings['bgr_allow_email'] : 'yes';
		$accept_email_enable         = isset( $bp_group_review_email_settings['bgr_accept_enable'] ) ? $bp_group_review_email_settings['bgr_accept_enable'] : 'yes';
		$deny_email_enable           = isset( $bp_group_review_email_settings['bgr_deny_email'] ) ? $bp_group_review_email_settings['bgr_deny_email'] : 'yes';
		$review_email_subject        = isset( $bp_group_review_email_settings['review_email_subject'] ) ? $bp_group_review_email_settings['review_email_subject'] : '';
		$review_email_message        = isset( $bp_group_review_email_settings['review_email_message'] ) ? $bp_group_review_email_settings['review_email_message'] : '';
		$review_accept_email_subject = isset( $bp_group_review_email_settings['review_accept_email_subject'] ) ? $bp_group_review_email_settings['review_accept_email_subject'] : '';
		$review_accept_email_message = isset( $bp_group_review_email_settings['review_accept_email_message'] ) ? $bp_group_review_email_settings['review_accept_email_message'] : '';
		$review_deny_email_subject   = isset( $bp_group_review_email_settings['review_deny_email_subject'] ) ? $bp_group_review_email_settings['review_deny_email_subject'] : '';
		$review_deny_email_message   = isset( $bp_group_review_email_settings['review_deny_email_message'] ) ? $bp_group_review_email_settings['review_deny_email_message'] : '';
	} else {
		$allow_email                 = 'yes';
		$accept_email_enable         = 'yes';
		$deny_email_enable           = 'yes';
		$review_email_subject        = '';
		$review_email_message        = '';
		$review_accept_email_subject = '';
		$review_accept_email_message = '';
		$review_deny_email_subject   = '';
		$review_deny_email_message   = '';
	}

	/**** Global variable values for Criteria settings */

	if ( ! empty( $bgr_admin_criteria_settings ) ) {
		$review_rating_fields = isset( $bgr_admin_criteria_settings['add_review_rating_fields'] ) ? $bgr_admin_criteria_settings['add_review_rating_fields'] : array();
		$active_rating_fields = isset( $bgr_admin_criteria_settings['active_rating_fields'] ) ? $bgr_admin_criteria_settings['active_rating_fields'] : array();
	} else {
		$review_rating_fields = array( 'Quality', 'Relevance', 'Engagement' );
		$active_rating_fields = array( 'Quality', 'Relevance', 'Engagement' );
		$bgr_admin_settings   = array(
			'add_review_rating_fields' => $review_rating_fields,
			'active_rating_fields'     => $active_rating_fields,
		);
		update_option( 'bgr_admin_criteria_settings', $bgr_admin_settings );
	}

	/**** Global variable values for Display settings */

	if ( ! empty( $bgr_admin_display_settings ) ) {
		$review_label        = isset( $bgr_admin_display_settings['review_label'] ) ? $bgr_admin_display_settings['review_label'] : esc_html__( 'Review', 'bp-group-reviews' );
		$manage_review_label = isset( $bgr_admin_display_settings['manage_review_label'] ) ? $bgr_admin_display_settings['manage_review_label'] : esc_html__( 'Reviews', 'bp-group-reviews' );
		$rating_color        = isset( $bgr_admin_display_settings['bgr_rating_color'] ) ? $bgr_admin_display_settings['bgr_rating_color'] : '#FFC400';
	} else {
		$review_label               = esc_html__( 'Review', 'bp-group-reviews' );
		$manage_review_label        = esc_html__( 'Reviews', 'bp-group-reviews' );
		$rating_color               = '#FFC400';
		$bgr_admin_display_settings = array(
			'review_label'        => $review_label,
			'manage_review_label' => $manage_review_label,
			'bgr_rating_color'    => $rating_color,
		);
		update_option( 'bgr_admin_display_settings', $bgr_admin_display_settings );
	}

	$bgr = array(
		'multi_reviews'               => $multi_reviews,
		'auto_approve_reviews'        => $auto_approve_reviews,
		'reviews_per_page'            => $reviews_per_page,
		'allow_email'                 => $allow_email,
		'accept_email_enable'         => $accept_email_enable,
		'deny_email_enable'           => $deny_email_enable,
		'allow_notification'          => $allow_notification,
		'allow_activity'              => $allow_activity,
		'exclude_groups'              => $exclude_groups,
		'enable_group_criteria'       => $enable_group_criteria,
		'review_email_subject'        => $review_email_subject,
		'review_email_message'        => $review_email_message,
		'review_accept_email_subject' => $review_accept_email_subject,
		'review_accept_email_message' => $review_accept_email_message,
		'review_deny_email_subject'   => $review_deny_email_subject,
		'review_deny_email_message'   => $review_deny_email_message,
		'review_rating_fields'        => $review_rating_fields,
		'active_rating_fields'        => $active_rating_fields,
		'review_label'                => $review_label,
		'manage_review_label'         => $manage_review_label,
		'rating_color'                => $rating_color,
	);
}
add_action( 'init', 'bp_group_review_globals' );

/**
 * BGR group review tab name.
 */
function bp_group_review_tab_name() {
	$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
	$group_review_tab_name      = isset( $bgr_admin_display_settings['manage_review_label'] ) ? $bgr_admin_display_settings['manage_review_label'] : esc_html__( 'Reviews', 'bp-group-reviews' );
	return apply_filters( 'bp_group_review_tab_name', $group_review_tab_name );
}
/**
 * BGR add group review tab name.
 */
function bp_group_review_add_review_tab_name() {
	$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
	$group_add_review_tab_name  = isset( $bgr_admin_display_settings['review_label'] ) ? $bgr_admin_display_settings['review_label'] : esc_html__( 'Review', 'bp-group-reviews' );
	return apply_filters( 'bp_group_review_add_review_tab_name', $group_add_review_tab_name );
}
/**
 * BGR group review tab slug.
 */
function bp_group_review_tab_slug() {
	$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
	$group_review_tab_slug      = isset( $bgr_admin_display_settings['review_label'] ) ? sanitize_title( $bgr_admin_display_settings['review_label'] ) : 'review';

	return apply_filters( 'bp_group_review_tab_slug', $group_review_tab_slug );
}
