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
function bgr_review_globals() {
	global $bgr;
	$bgr_admin_general_settings  = get_option( 'bgr_admin_general_settings' );
	$bgr_admin_criteria_settings = get_option( 'bgr_admin_criteria_settings' );
	$bgr_admin_display_settings  = get_option( 'bgr_admin_display_settings' );

	/**** Global variable values for General settings */

	if ( ! empty( $bgr_admin_general_settings ) ) {
		if ( array_key_exists( 'auto_approve_reviews', $bgr_admin_general_settings ) ) {
			$auto_approve_reviews = $bgr_admin_general_settings['auto_approve_reviews'];
		}
		if ( array_key_exists( 'reviews_per_page', $bgr_admin_general_settings ) ) {
			$reviews_per_page = $bgr_admin_general_settings['reviews_per_page'];
		}
		if ( array_key_exists( 'allow_email', $bgr_admin_general_settings ) ) {
			$allow_email = $bgr_admin_general_settings['allow_email'];
		}
		if ( array_key_exists( 'allow_notification', $bgr_admin_general_settings ) ) {
			$allow_notification = $bgr_admin_general_settings['allow_notification'];
		}
		if ( array_key_exists( 'allow_activity', $bgr_admin_general_settings ) ) {
			$allow_activity = $bgr_admin_general_settings['allow_activity'];
		}
		if ( array_key_exists( 'exclude_groups', $bgr_admin_general_settings ) ) {
			$exclude_groups = $bgr_admin_general_settings['exclude_groups'];
		}
		$review_email_subject = '';
		if ( array_key_exists( 'review_email_subject', $bgr_admin_general_settings ) ) {
			$review_email_subject = $bgr_admin_general_settings['review_email_subject'];
		}
		$review_email_message = '';
		if ( array_key_exists( 'review_email_message', $bgr_admin_general_settings ) ) {
			$review_email_message = $bgr_admin_general_settings['review_email_message'];
		}
		if ( array_key_exists( 'multi_reviews', $bgr_admin_general_settings ) ) {
			$multi_reviews = $bgr_admin_general_settings['multi_reviews'];
		}

		if ( empty( $multi_reviews ) ) {
			$multi_reviews = 'no';
		}
		if ( empty( $auto_approve_reviews ) ) {
			$auto_approve_reviews = 'no';
		}
		if ( empty( $reviews_per_page ) ) {
			$reviews_per_page = 3;
		}
		if ( empty( $allow_email ) ) {
			$allow_email = 'no';
		}
		if ( empty( $allow_notification ) ) {
			$allow_notification = 'no';
		}
		if ( empty( $allow_activity ) ) {
			$allow_activity = 'no';
		}
		if ( empty( $exclude_groups ) ) {
			$exclude_groups = array();
		}
	} else {
		$multi_reviews        = 'no';
		$reviews_per_page     = 3;
		$allow_email          = 'no';
		$review_email_subject = 'New Review Submitted for Your Group [group-name] on [site-name]';
		$review_email_message = 'Hello [admin-name],

We are excited to inform you that a new review has been submitted for your group, [group-name], by [user-name]. Your members\'s feedback is invaluable in fostering a vibrant and engaging community.

You can read and respond to the review by following the link below:
[review-link]

Thank you for creating a space where members can share their thoughts and experiences. Keep up the great work!

Best regards,
The [site-name] Team';
		$allow_notification   = 'no';
		$allow_activity       = 'no';
		$exclude_groups       = array();
		$auto_approve_reviews = 'no';
	}

	/**** Global variable values for Criteria settings */

	if ( ! empty( $bgr_admin_criteria_settings ) ) {
		if ( array_key_exists( 'add_review_rating_fields', $bgr_admin_criteria_settings ) ) {
			$review_rating_fields = $bgr_admin_criteria_settings['add_review_rating_fields'];
		}
		if ( array_key_exists( 'active_rating_fields', $bgr_admin_criteria_settings ) ) {
			$active_rating_fields = $bgr_admin_criteria_settings['active_rating_fields'];
		}
		if ( empty( $review_rating_fields ) ) {
			$review_rating_fields = array();
		}
		if ( empty( $active_rating_fields ) ) {
			$active_rating_fields = array();
		}
	} else {
		$review_rating_fields = array( 'Friendliness', 'Usability', 'Ease of Access' );
		$active_rating_fields = array( 'Friendliness', 'Usability', 'Ease of Access' );
		$bgr_admin_settings   = array(
			'add_review_rating_fields' => $review_rating_fields,
			'active_rating_fields'     => $active_rating_fields,
		);
		update_option( 'bgr_admin_criteria_settings', $bgr_admin_settings );
	}

	/**** Global variable values for Display settings */

	if ( ! empty( $bgr_admin_display_settings ) ) {
		if ( array_key_exists( 'review_label', $bgr_admin_display_settings ) ) {
			$review_label = $bgr_admin_display_settings['review_label'];
		}
		if ( array_key_exists( 'manage_review_label', $bgr_admin_display_settings ) ) {
			$manage_review_label = $bgr_admin_display_settings['manage_review_label'];
		}
		if ( array_key_exists( 'bgr_rating_color', $bgr_admin_display_settings ) ) {
			$rating_color = $bgr_admin_display_settings['bgr_rating_color'];
		}
		if ( empty( $review_label ) ) {
			$review_label = esc_html__( 'Review', 'bp-group-reviews' );
		}
		if ( empty( $manage_review_label ) ) {
			$manage_review_label = esc_html__( 'Reviews', 'bp-group-reviews' );
		}
		if ( empty( $rating_color ) ) {
			$rating_color = '#FFC400';
		}
	} else {
		$review_label        = esc_html__( 'Review', 'bp-group-reviews' );
		$manage_review_label = esc_html__( 'Reviews', 'bp-group-reviews' );
		$rating_color        = '#FFC400';
		$bgr_admin_display_settings   = array(
			'review_label'          => $review_label,
			'manage_review_label'   => $manage_review_label,
			'bgr_rating_color'      => $rating_color,
		);
		update_option( 'bgr_admin_display_settings', $bgr_admin_display_settings );
	}

	$bgr = array(
		'multi_reviews'        => $multi_reviews,
		'auto_approve_reviews' => $auto_approve_reviews,
		'reviews_per_page'     => $reviews_per_page,
		'allow_email'          => $allow_email,
		'allow_notification'   => $allow_notification,
		'allow_activity'       => $allow_activity,
		'exclude_groups'       => $exclude_groups,
		'review_email_subject' => $review_email_subject,
		'review_email_message' => $review_email_message,
		'review_rating_fields' => $review_rating_fields,
		'active_rating_fields' => $active_rating_fields,
		'review_label'         => $review_label,
		'manage_review_label'  => $manage_review_label,
		'rating_color'         => $rating_color,
	);
}
add_action( 'init', 'bgr_review_globals' );

/**
 * BGR group review tab name.
 */
function bgr_group_review_tab_name() {
	$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
	$group_review_tab_name      = isset( $bgr_admin_display_settings['manage_review_label'] ) ? $bgr_admin_display_settings['manage_review_label'] : esc_html__( 'Reviews', 'bp-group-reviews' );
	return apply_filters( 'bgr_group_review_tab_name', $group_review_tab_name );
}
/**
 * BGR add group review tab name.
 */
function bgr_group_add_review_tab_name() {
	$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
	$group_add_review_tab_name  = isset( $bgr_admin_display_settings['review_label'] ) ? $bgr_admin_display_settings['review_label'] : esc_html__( 'Review', 'bp-group-reviews' );
	return apply_filters( 'bgr_group_add_review_tab_name', $group_add_review_tab_name );
}
/**
 * BGR group review tab slug.
 */
function bgr_group_review_tab_slug() {
	$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
	$group_review_tab_slug      = isset( $bgr_admin_display_settings['review_label'] ) ? sanitize_title( $bgr_admin_display_settings['review_label'] ) : 'review';

	return apply_filters( 'bgr_group_review_tab_slug', $group_review_tab_slug );
}
