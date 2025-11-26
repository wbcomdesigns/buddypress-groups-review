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

	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );
		bgr_delete_plugin_data();
		restore_current_blog();
	}
} else {
	bgr_delete_plugin_data();
}

/**
 * Delete all plugin data.
 *
 * @since 3.5.0
 */
function bgr_delete_plugin_data() {
	global $wpdb;

	// Delete all review posts.
	$review_posts = get_posts(
		array(
			'post_type'      => 'review',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	foreach ( $review_posts as $post_id ) {
		// This will also delete post meta, comments, and terms.
		wp_delete_post( $post_id, true );
	}

	// Delete plugin options.
	delete_option( 'bgr_admin_general_settings' );
	delete_option( 'bgr_admin_criteria_settings' );
	delete_option( 'bgr_admin_display_settings' );
	delete_option( 'bp_group_review_email_settings' );

	// Delete user meta if any (check for review-related user meta).
	$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE '%bgr_%'" );

	// Delete any custom database tables if they were created.
	// Currently, this plugin uses custom post types, so no custom tables to delete.

	// Delete capabilities from roles.
	$roles = array( 'administrator', 'editor' );
	foreach ( $roles as $role_name ) {
		$role = get_role( $role_name );
		if ( $role ) {
			$role->remove_cap( 'edit_review' );
			$role->remove_cap( 'read_review' );
			$role->remove_cap( 'delete_review' );
			$role->remove_cap( 'edit_reviews' );
			$role->remove_cap( 'edit_others_reviews' );
			$role->remove_cap( 'publish_reviews' );
			$role->remove_cap( 'read_private_reviews' );
		}
	}

	// Clear any cached data.
	wp_cache_flush();
}
