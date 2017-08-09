<?php 
/**
 * Group Review Plugin Global Variables
 *  @since   1.0.0
 *  @author  Wbcom Designs
 */
function bgr_review_globals() {
	global $bgr;
	$bgr_admin_general_settings  = get_option( 'bgr_admin_general_settings' );
	$bgr_admin_criteria_settings = get_option( 'bgr_admin_criteria_settings' );
	$bgr_admin_display_settings  = get_option( 'bgr_admin_display_settings' );
	
	/**** Global variable values for General settings ****/
	
	if( !empty( $bgr_admin_general_settings ) ) {
		if( array_key_exists( 'add_review_allow_popup', $bgr_admin_general_settings ) ) {
			$add_review_allow_popup = $bgr_admin_general_settings['add_review_allow_popup'];
		}
		if( array_key_exists( 'auto_approve_reviews', $bgr_admin_general_settings ) ) {
			$auto_approve_reviews = $bgr_admin_general_settings['auto_approve_reviews'];
		}
		if( array_key_exists( 'reviews_per_page', $bgr_admin_general_settings ) ) {
			$reviews_per_page       = $bgr_admin_general_settings['reviews_per_page'];
		}
		if( array_key_exists( 'allow_email', $bgr_admin_general_settings ) ) {	
			$allow_email            = $bgr_admin_general_settings['allow_email'];
		}
		if( array_key_exists( 'allow_notification', $bgr_admin_general_settings ) ) {	
			$allow_notification     = $bgr_admin_general_settings['allow_notification'];
		}
		if( array_key_exists( 'exclude_groups', $bgr_admin_general_settings ) ) {	
			$exclude_groups     	= $bgr_admin_general_settings['exclude_groups'];
		}	
		
		if( empty( $add_review_allow_popup )) {
			$add_review_allow_popup = 'no';
		}
		if( empty( $auto_approve_reviews )) {
			$auto_approve_reviews = 'no';
		}
		if( empty( $reviews_per_page ) ) {
			$reviews_per_page = 3;
		}
		if( empty( $allow_email ) ) {
		   $allow_email = 'no';
		}
		if( empty( $allow_notification ) ) {
			$allow_notification = 'no';
		}
		if( empty( $exclude_groups ) ) {
			$exclude_groups = array();
		}
	} else {        
		$add_review_allow_popup = 'no';
		$reviews_per_page       = 3;
		$allow_email            = 'no';
		$allow_notification     = 'no';
		$exclude_groups         = array();
		$auto_approve_reviews   = 'no';
	}
	
	/**** Global variable values for Criteria settings ****/
	
	if( !empty( $bgr_admin_criteria_settings ) ) { 
		if( array_key_exists( 'add_review_rating_fields', $bgr_admin_criteria_settings ) ) {
			$review_rating_fields = $bgr_admin_criteria_settings['add_review_rating_fields'];
		}
		if( array_key_exists( 'active_rating_fields', $bgr_admin_criteria_settings ) ) {	
			$active_rating_fields = $bgr_admin_criteria_settings['active_rating_fields'];
		}	
		if( empty( $review_rating_fields ) ){
			$review_rating_fields = array();
		}
		if( empty( $active_rating_fields ) ){
			$active_rating_fields = array();
		}
	} else {
		$review_rating_fields = array();
		$active_rating_fields = array();
	}
	
	/**** Global variable values for Display settings ****/
	
	if( !empty( $bgr_admin_display_settings ) ) {  
		if( array_key_exists( 'review_label', $bgr_admin_display_settings ) ) {
			$review_label        = $bgr_admin_display_settings['review_label'];
		}
		if( array_key_exists( 'manage_review_label', $bgr_admin_display_settings ) ) {
			$manage_review_label = $bgr_admin_display_settings['manage_review_label'];
		}
		if( array_key_exists( 'bgr_rating_color', $bgr_admin_display_settings ) ) {
			$rating_color        = $bgr_admin_display_settings['bgr_rating_color'];
		}
		if( array_key_exists( 'bgr_rating_type', $bgr_admin_display_settings ) ) {
			$rating_type         = $bgr_admin_display_settings['bgr_rating_type'];                  
		}	
		if( empty( $review_label )) {
			$review_label = 'Review';
		}
		if( empty( $manage_review_label ) ) {
		   $manage_review_label = 'Review';
		}
		if( empty( $rating_color ) ) {
		   $rating_color = '#FFC400';
		}
		if( empty( $rating_type ) ) {
			$rating_type = 'star';
		}
	} else {
		$review_label           = 'Review';
		$manage_review_label    = 'Review';
		$rating_color           = '#FFC400';
		$rating_type            = 'star';
	}
	
	
	$bgr = array(
		'add_review_allow_popup' => $add_review_allow_popup,
		'auto_approve_reviews'	 => $auto_approve_reviews,
		'reviews_per_page'       => $reviews_per_page,
		'allow_email'            => $allow_email,
		'allow_notification'     => $allow_notification,
		'exclude_groups'         => $exclude_groups,
		'review_rating_fields'   => $review_rating_fields,  
		'active_rating_fields'   => $active_rating_fields,
		'review_label'           => $review_label,
		'manage_review_label'    => $manage_review_label,
		'rating_color'           => $rating_color,
		'rating_type'            => $rating_type
	);
}
add_action( 'init', 'bgr_review_globals' );