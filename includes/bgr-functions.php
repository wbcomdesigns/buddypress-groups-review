<?php
/**
 * This code removes extra tab ('Add Review') from group navigation
 * due to BP_Group_Extension.
 *
 * @return void
 */
function bp_group_review_remove_add_review_tab() {
	$bgr_admin_general_settings = get_option( 'bgr_admin_general_settings' );
	$exclude_groups             = isset( $bgr_admin_general_settings['exclude_groups'] ) ? array_map( 'absint', (array) $bgr_admin_general_settings['exclude_groups'] ) : array();
	$current_group_id           = absint( bp_get_current_group_id() );

	if ( ! empty( $exclude_groups ) && in_array( $current_group_id, $exclude_groups, true ) ) {
		?>
		<style>
			/* Hide review tabs for excluded groups - CSS fallback for excluded groups */
			#nav-add-review-groups-li,
			li.add-review,
			li#add-review,
			li.reviews,
			li#reviews,
			#subnav li.add-review,
			#subnav li.reviews,
			.group-button#add-review-groupbutton {
				display: none !important;
			}
		</style>
		<?php
	}
}
add_action( 'wp_head', 'bp_group_review_remove_add_review_tab' );