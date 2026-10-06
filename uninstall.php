<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * @link       https://wbcomdesigns.com/
 * @since      3.5.0
 *
 * @package    BuddyPress_Group_Review
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Only remove data if the constant is defined in wp-config.php
 * define( 'BGR_REMOVE_DATA_ON_UNINSTALL', true );
 */
if ( ! defined( 'BGR_REMOVE_DATA_ON_UNINSTALL' ) || ! BGR_REMOVE_DATA_ON_UNINSTALL ) {
	return;
}

global $wpdb;

// Check if multisite.
if ( is_multisite() ) {
	// Get all blog IDs.
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

	foreach ( $blog_ids as $blog_id ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Iterating blog IDs for multisite cleanup.
		switch_to_blog( $blog_id );
		bgr_delete_plugin_data();
		restore_current_blog();
	}
} else {
	bgr_delete_plugin_data();
}

// Network-wide data, removed once: per-group criteria live in BuddyPress's
// shared groupmeta table, and the review-prompt flags are site options.
$bgr_bp_prefix = function_exists( 'bp_core_get_table_prefix' ) ? bp_core_get_table_prefix() : $wpdb->base_prefix;
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- One-shot uninstall cleanup.
if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $bgr_bp_prefix . 'bp_groups_groupmeta' ) ) ) ) {
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$bgr_bp_prefix}bp_groups_groupmeta WHERE meta_key = %s", 'bgr_group_criteria_settings' ) );
}
// phpcs:enable
delete_site_option( 'bp-group-reviews_activation_date' );
delete_site_option( 'bp-group-reviews_no_bug' );

/**
 * Delete all plugin data.
 *
 * @since 3.5.0
 */
function bgr_delete_plugin_data() {
	global $wpdb;

	// Delete group reviews only. The review CPT is shared with BuddyPress Member
	// Reviews; its posts carry no linked_group and must survive this uninstall.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- One-shot uninstall cleanup.
	$review_posts = $wpdb->get_col(
		"SELECT DISTINCT p.ID FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'linked_group'
		WHERE p.post_type = 'review'"
	);

	foreach ( $review_posts as $post_id ) {
		// This will also delete post meta, comments, and terms.
		wp_delete_post( (int) $post_id, true );
	}

	// Delete plugin options.
	delete_option( 'bgr_admin_general_settings' );
	delete_option( 'bgr_admin_criteria_settings' );
	delete_option( 'bgr_admin_display_settings' );
	delete_option( 'bp_group_review_email_settings' );
	delete_option( 'bgr_display_labels_migrated' );

	// Per-group stats caches and submit locks (bgr_review_count_*, bgr_review_rating_*, bgr_review_lock_*).
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- One-shot uninstall cleanup.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_bgr_review_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_bgr_review_' ) . '%'
		)
	);

	// Remove every review capability this plugin grants, from whichever roles
	// the bgr_add_roles_capability filter gave them to.
	$caps = array( 'edit_review', 'read_review', 'delete_review', 'edit_reviews', 'edit_others_reviews', 'publish_reviews', 'read_private_reviews', 'delete_reviews', 'delete_others_reviews', 'delete_published_reviews' );
	foreach ( wp_roles()->role_objects as $role ) {
		foreach ( $caps as $cap ) {
			$role->remove_cap( $cap );
		}
	}

	// Clear any cached data.
	wp_cache_flush();
}
