<?php
/**
 * Group Criteria Helper Functions
 *
 * Helper functions for the BGR_Group_Criteria class.
 *
 * @link       https://wbcomdesigns.com/
 * @since      3.7.0
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Get the BGR_Group_Criteria instance.
 *
 * @since 3.7.0
 * @return BGR_Group_Criteria
 */
function bgr_group_criteria() {
	return BGR_Group_Criteria::get_instance();
}

/**
 * Helper function to get effective criteria for a group.
 *
 * @since 3.7.0
 * @param int $group_id The group ID.
 * @return array List of active criteria names.
 */
function bgr_get_effective_criteria( $group_id ) {
	return bgr_group_criteria()->get_effective_criteria( $group_id );
}

/**
 * Helper function to check if a group is using custom criteria.
 *
 * @since 3.7.0
 * @param int $group_id The group ID.
 * @return bool True if using custom criteria.
 */
function bgr_is_using_custom_criteria( $group_id ) {
	return bgr_group_criteria()->is_using_custom_criteria( $group_id );
}

/**
 * Helper function to get group criteria mode.
 *
 * @since 3.7.0
 * @param int $group_id The group ID.
 * @return string The mode ('inherit' or 'override').
 */
function bgr_get_criteria_mode( $group_id ) {
	return bgr_group_criteria()->get_criteria_mode( $group_id );
}
