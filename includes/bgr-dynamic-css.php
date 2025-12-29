<?php
/**
 * Actions performed to add dynamic css
 *
 * @since   1.0.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


/**
 * Actions performed to add dynamic css
 *
 * This function outputs the rating color CSS for star ratings.
 * It runs at priority 20 to ensure bgr-ratings-css is already enqueued.
 *
 * @return void
 */
function bp_group_review_dynamic_rating_method() {
	global $bgr;

	// Bail early if $bgr is not set or rating_color is missing.
	if ( empty( $bgr ) || ! isset( $bgr['rating_color'] ) ) {
		return;
	}

	$rating_color = $bgr['rating_color'];

	// Sanitize the color value.
	$rating_color = sanitize_hex_color( $rating_color );
	if ( empty( $rating_color ) ) {
		return;
	}

	$custom_css = ".bgr-star-rate { color: {$rating_color}; }";

	// Only add inline style if the stylesheet is enqueued.
	if ( wp_style_is( 'bgr-ratings-css', 'enqueued' ) ) {
		wp_add_inline_style( 'bgr-ratings-css', $custom_css );
	}
}
add_action( 'wp_enqueue_scripts', 'bp_group_review_dynamic_rating_method', 20 );

/**
 * Fallback: Output rating color CSS directly in wp_head.
 *
 * This ensures the rating color is always applied even when
 * bgr-ratings-css is not enqueued (e.g., on groups directory).
 *
 * @return void
 */
function bp_group_review_dynamic_rating_fallback() {
	global $bgr;

	// Bail early if $bgr is not set or rating_color is missing.
	if ( empty( $bgr ) || ! isset( $bgr['rating_color'] ) ) {
		return;
	}

	// Skip if bgr-ratings-css was enqueued (inline style already added).
	if ( wp_style_is( 'bgr-ratings-css', 'enqueued' ) ) {
		return;
	}

	// Check if we're on a page that might display ratings.
	$should_output = false;

	// BuddyPress group pages.
	if ( function_exists( 'bp_is_group' ) && bp_is_group() ) {
		$should_output = true;
	}

	// BuddyPress groups directory.
	if ( function_exists( 'bp_is_groups_directory' ) && bp_is_groups_directory() ) {
		$should_output = true;
	}

	// BuddyPress groups component (covers all group-related pages).
	if ( function_exists( 'bp_is_groups_component' ) && bp_is_groups_component() ) {
		$should_output = true;
	}

	// Activity component (reviews might appear in activity stream).
	if ( function_exists( 'bp_is_activity_component' ) && bp_is_activity_component() ) {
		$should_output = true;
	}

	if ( ! $should_output ) {
		return;
	}

	$rating_color = $bgr['rating_color'];

	// Sanitize the color value.
	$rating_color = sanitize_hex_color( $rating_color );
	if ( empty( $rating_color ) ) {
		return;
	}

	// Output the CSS directly.
	echo '<style id="bgr-rating-color-css">.bgr-star-rate { color: ' . esc_attr( $rating_color ) . '; }</style>' . "\n";
}
add_action( 'wp_head', 'bp_group_review_dynamic_rating_fallback', 99 );
