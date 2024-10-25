<?php

/**
 * Update the activity action with youzify.
 *
 * @param  mixed $action
 * @param  object $activity
 * @return mixed $action
 */
function bgr_youzify_activity_action_wall_posts( $action, $activity ){	
	$action = $activity->action;
	return $action;
}
add_filter( 'youzify_activity_new_post_action', 'bgr_youzify_activity_action_wall_posts',10, 2 );

/**
 * This code remove extra tab ('Add Review') from group navigation
 * due to BP_Group_Extension 
 *
 * @return void
 */
function bgr_remove_add_review_tab_exclude_on_option(){ 	
	$bgr_admin_general_settings = get_option( 'bgr_admin_general_settings' );
	$exclude_groups = isset( $bgr_admin_general_settings['exclude_groups'] ) ? $bgr_admin_general_settings['exclude_groups'] : array();
	if ( ! empty( $exclude_groups ) ) {
		if ( in_array( bp_get_current_group_id(), $exclude_groups ) ) {
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
add_action( 'wp_head','bgr_remove_add_review_tab_exclude_on_option' );