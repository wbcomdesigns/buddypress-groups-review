<?php

/**
 * This code remove extra tab ('Add Review') from group navigation
 * due to BP_Group_Extension 
 *
 * @return void
 */
function bp_group_review_remove_add_review_tab() {
	$bgr_admin_general_settings = get_option( 'bgr_admin_general_settings' );
	$exclude_groups             = isset( $bgr_admin_general_settings['exclude_groups'] ) ? $bgr_admin_general_settings['exclude_groups'] : array();
	if ( ! empty( $exclude_groups ) ) {
		// Ensure consistent integer comparison for group IDs.
		$current_group_id_int = absint( bp_get_current_group_id() );
		$exclude_groups_int   = array_map( 'absint', $exclude_groups );
		if ( in_array( $current_group_id_int, $exclude_groups_int, true ) ) {
	?>
	<style>
		#nav-add-review-groups-li{
			display: none!important;
		}
	</style>
	<?php 
		} 
	} 
}
add_action( 'wp_head','bp_group_review_remove_add_review_tab' );