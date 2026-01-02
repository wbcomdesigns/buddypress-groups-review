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

	$rating_color = sanitize_hex_color( $bgr['rating_color'] );

	// Only proceed if we have a valid color.
	if ( empty( $rating_color ) ) {
		return;
	}

	// Check if the stylesheet is enqueued before adding inline style.
	if ( wp_style_is( 'bgr-ratings-css', 'enqueued' ) ) {
		// Target ::before and ::after pseudo-elements directly with !important
		// to ensure the admin color setting overrides any theme or plugin styles.
		$custom_css = "
			.bgr-star-rate { color: {$rating_color} !important; }
			.fas.fa-star.bgr-star-rate::before,
			.far.fa-star.bgr-star-rate::before,
			.fas.fa-star-half-alt.bgr-star-rate::before,
			.fas.fa-star-half-alt.bgr-star-rate::after,
			.fas.fa-star.stars::before,
			.far.fa-star.stars::before,
			.fas.fa-star-half-alt.stars::before,
			.fas.fa-star-half-alt.stars::after,
			.far.fa-star.bgr-stars::before { color: {$rating_color} !important; }
		";
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

	$rating_color = sanitize_hex_color( $bgr['rating_color'] );

	if ( ! empty( $rating_color ) ) {
		// Target ::before and ::after pseudo-elements directly with !important
		// to ensure the admin color setting overrides any theme or plugin styles.
		echo '<style id="bgr-rating-color-fallback">
			.bgr-star-rate { color: ' . esc_attr( $rating_color ) . ' !important; }
			.fas.fa-star.bgr-star-rate::before,
			.far.fa-star.bgr-star-rate::before,
			.fas.fa-star-half-alt.bgr-star-rate::before,
			.fas.fa-star-half-alt.bgr-star-rate::after,
			.fas.fa-star.stars::before,
			.far.fa-star.stars::before,
			.fas.fa-star-half-alt.stars::before,
			.fas.fa-star-half-alt.stars::after,
			.far.fa-star.bgr-stars::before { color: ' . esc_attr( $rating_color ) . ' !important; }
		</style>' . "\n";
	}
}
add_action( 'wp_head', 'bp_group_review_rating_color_fallback', 999 );
