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
 * Build the rating-color CSS block.
 *
 * Single source of truth for the star rating color rules, shared by the
 * primary wp_add_inline_style() path and the wp_head fallback so the selector
 * list + !important overrides never drift out of sync (previously duplicated).
 * The !important is required: it overrides the theme/plugin colour set on the
 * star pseudo-elements from the admin "rating color" setting. Every selector
 * carries a bgr- class: the generic .stars class is shared with BuddyPress
 * Member Reviews, and painting it recoloured that plugin's stars.
 *
 * @param string $rating_color Sanitized hex colour (e.g. #ffb900).
 * @return string CSS rules, or empty string when no colour supplied.
 */
function bp_group_review_get_rating_color_css( $rating_color ) {
	$rating_color = sanitize_hex_color( $rating_color );

	if ( empty( $rating_color ) ) {
		return '';
	}

	return "
		.bgr-star-rate { color: {$rating_color} !important; }
		.fas.fa-star.bgr-star-rate::before,
		.far.fa-star.bgr-star-rate::before,
		.fas.fa-star-half-alt.bgr-star-rate::before,
		.fas.fa-star-half-alt.bgr-star-rate::after,
		.far.fa-star.bgr-stars::before { color: {$rating_color} !important; }
	";
}

/**
 * Actions performed to add dynamic css via wp_add_inline_style
 *
 * @return void
 */
function bp_group_review_dynamic_rating_method() {
	global $bgr;

	// Ensure bgr global is available.
	if ( empty( $bgr ) || ! isset( $bgr['rating_color'] ) ) {
		return;
	}

	$custom_css = bp_group_review_get_rating_color_css( $bgr['rating_color'] );

	// Only proceed if we have a valid color.
	if ( empty( $custom_css ) ) {
		return;
	}

	// Check if the stylesheet is enqueued before adding inline style.
	if ( wp_style_is( 'bgr-ratings-css', 'enqueued' ) ) {
		wp_add_inline_style( 'bgr-ratings-css', $custom_css );
	}
}
// Run at priority 20 to ensure stylesheet is already enqueued (enqueued at priority 10).
add_action( 'wp_enqueue_scripts', 'bp_group_review_dynamic_rating_method', 20 );

/**
 * Fallback: Output rating color CSS directly in head if inline style wasn't added.
 * This ensures rating color works even when bgr-ratings-css isn't enqueued.
 *
 * @return void
 */
function bp_group_review_rating_color_fallback() {
	global $bgr;

	// Only on BuddyPress pages where reviews might appear.
	if ( ! function_exists( 'bp_is_groups_component' ) ) {
		return;
	}

	// Check if we're on a relevant page.
	$is_groups_page = ( function_exists( 'bp_is_group' ) && bp_is_group() ) ||
		( function_exists( 'bp_is_groups_directory' ) && bp_is_groups_directory() ) ||
		( function_exists( 'bp_is_groups_component' ) && bp_is_groups_component() );

	if ( ! $is_groups_page ) {
		return;
	}

	// Check if inline style was already added via the primary method.
	if ( wp_style_is( 'bgr-ratings-css', 'enqueued' ) ) {
		return; // Primary method should have handled it.
	}

	// Ensure bgr global is available.
	if ( empty( $bgr ) || ! isset( $bgr['rating_color'] ) ) {
		return;
	}

	$custom_css = bp_group_review_get_rating_color_css( $bgr['rating_color'] );

	if ( ! empty( $custom_css ) ) {
		// Same rules as the primary inline-style path (shared helper) so the
		// admin colour setting overrides theme/plugin styles consistently.
		wp_register_style( 'bgr-rating-color-fallback', false, array(), BGR_PLUGIN_VERSION );
		wp_enqueue_style( 'bgr-rating-color-fallback' );
		wp_add_inline_style( 'bgr-rating-color-fallback', $custom_css );
	}
}
add_action( 'wp_enqueue_scripts', 'bp_group_review_rating_color_fallback', 30 );
