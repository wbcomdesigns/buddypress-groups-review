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
 * @return void
 */
function bgr_dynamic_rating_method() {
	global $bgr;
	$rating_color   = $bgr['rating_color'];
		$custom_css = "
        		.bgr-star-rate {
        			color: {$rating_color};
        		}
				";
		wp_add_inline_style( 'bgr-ratings-css', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'bgr_dynamic_rating_method' );
