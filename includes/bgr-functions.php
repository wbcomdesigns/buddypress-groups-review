<?php
/**
 * Core functions for BuddyPress Group Reviews.
 *
 * @package Buddypress_Groups_Review
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Get cached aggregate review statistics for a group.
 *
 * Replaces the per-page unbounded `get_posts( -1 )` + N+1 meta scans that
 * previously ran on the groups directory, single-group header, REST
 * responses, JSON-LD schema, and rating widget. All rating meta for the
 * group is read with ONE bounded query, aggregated once, and cached in the
 * `bgr_review_rating_{group_id}` transient. The cache is invalidated by
 * BGR_Custom_Hooks::invalidate_group_review_cache() on every review write
 * (save/approve/deny/delete/meta change).
 *
 * @since 3.9.0
 *
 * @param int $group_id The group ID.
 * @return array {
 *     Aggregate stats for the group's published reviews.
 *
 *     @type float $average       Average of per-review average ratings (0-5, 2 decimals).
 *     @type int   $total_reviews Number of published reviews.
 *     @type int   $ratings_count Number of individual rating values across all reviews.
 *     @type array $ratings       Per-criteria average ratings (criteria name => average).
 *     @type array $criteria      Raw per-criteria sums (criteria name => [total, count]).
 *     @type array $index         review_id => per-review average, ordered newest first.
 * }
 */
function bgr_get_group_review_stats( $group_id ) {
	$group_id = absint( $group_id );

	$defaults = array(
		'average'       => 0,
		'total_reviews' => 0,
		'ratings_count' => 0,
		'ratings'       => array(),
		'criteria'      => array(),
		'index'         => array(),
	);

	if ( empty( $group_id ) ) {
		return $defaults;
	}

	$stats = get_transient( 'bgr_review_rating_' . $group_id );
	if ( is_array( $stats ) && isset( $stats['average'], $stats['index'], $stats['criteria'] ) ) {
		return $stats;
	}

	global $wpdb;

	/**
	 * Filter the maximum number of reviews aggregated per group.
	 *
	 * Safety bound so a pathological review count cannot exhaust memory.
	 * Result is cached, so this query only runs after invalidation.
	 *
	 * @since 3.9.0
	 *
	 * @param int $limit    Maximum rows to aggregate. Default 10000.
	 * @param int $group_id The group ID.
	 */
	$limit = (int) apply_filters( 'bgr_review_stats_query_limit', 10000, $group_id );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Single bounded aggregate query, cached via transient above.
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT p.ID, rating_meta.meta_value AS ratings
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm
				ON p.ID = pm.post_id
				AND pm.meta_key = 'linked_group'
				AND pm.meta_value = %s
			LEFT JOIN {$wpdb->postmeta} rating_meta
				ON p.ID = rating_meta.post_id
				AND rating_meta.meta_key = 'review_star_rating'
			WHERE p.post_type = 'review'
			AND p.post_status = 'publish'
			ORDER BY p.post_date DESC, p.ID DESC
			LIMIT %d",
			(string) $group_id,
			$limit
		)
	);

	$index           = array();
	$criteria_totals = array();
	$sum_of_averages = 0;
	$rated_reviews   = 0;
	$ratings_count   = 0;

	foreach ( (array) $rows as $row ) {
		$review_id = (int) $row->ID;
		if ( isset( $index[ $review_id ] ) ) {
			continue; // Guard against duplicate meta rows.
		}

		$review_average = 0;
		$rating_values  = maybe_unserialize( $row->ratings );

		if ( is_array( $rating_values ) && ! empty( $rating_values ) ) {
			$sum   = 0;
			$count = 0;
			foreach ( $rating_values as $field => $value ) {
				$value = (float) $value;
				$sum  += $value;
				++$count;
				++$ratings_count;

				if ( ! isset( $criteria_totals[ $field ] ) ) {
					$criteria_totals[ $field ] = array(
						'total' => 0,
						'count' => 0,
					);
				}
				$criteria_totals[ $field ]['total'] += $value;
				++$criteria_totals[ $field ]['count'];
			}
			if ( $count > 0 ) {
				$review_average   = $sum / $count;
				$sum_of_averages += $review_average;
				++$rated_reviews;
			}
		}

		$index[ $review_id ] = round( $review_average, 2 );
	}

	$criteria_averages = array();
	foreach ( $criteria_totals as $field => $totals ) {
		$criteria_averages[ $field ] = $totals['count'] > 0 ? round( $totals['total'] / $totals['count'], 2 ) : 0;
	}

	$stats = array(
		'average'       => $rated_reviews > 0 ? round( $sum_of_averages / $rated_reviews, 2 ) : 0,
		'total_reviews' => count( $index ),
		'ratings_count' => $ratings_count,
		'ratings'       => $criteria_averages,
		'criteria'      => $criteria_totals,
		'index'         => $index,
	);

	set_transient( 'bgr_review_rating_' . $group_id, $stats, HOUR_IN_SECONDS );

	return $stats;
}

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
/**
 * Whether the current user may see a group's reviews, including its ratings.
 *
 * Public groups: everyone. Private and hidden groups: members and site
 * moderators only - the same rule BuddyPress applies to the group's member
 * list and member count. Every surface that shows reviews or ratings (REST,
 * group header, directory, schema, widgets) must check this.
 *
 * @since 3.9.1
 *
 * @param BP_Groups_Group|int $group Group object or ID.
 * @return bool
 */
function bgr_can_view_group_reviews( $group ) {
	if ( ! $group instanceof BP_Groups_Group ) {
		$group = groups_get_group( absint( $group ) );
	}
	if ( empty( $group->id ) ) {
		return false;
	}

	return 'public' === $group->status
		|| bp_current_user_can( 'bp_moderate' )
		|| groups_is_user_member( get_current_user_id(), $group->id );
}

/**
 * Apply the side effects of a moderation decision on a group review.
 *
 * Every path that approves or denies a pending review - the group Manage tab,
 * the wp-admin row and bulk actions, the post editor, REST - ends in a status
 * change, so the decision is handled once, here: pending -> publish is an
 * approval, pending -> trash a denial. Removing an already published review is
 * not a denial and sends nothing.
 *
 * @since 3.9.1
 *
 * @param string  $new_status New post status.
 * @param string  $old_status Previous post status.
 * @param WP_Post $post       Post object.
 */
function bgr_handle_review_decision( $new_status, $old_status, $post ) {
	if ( 'review' !== $post->post_type || ! in_array( $old_status, array( 'draft', 'pending' ), true ) || ! in_array( $new_status, array( 'publish', 'trash' ), true ) ) {
		return;
	}

	// The review CPT is shared with Member Reviews; only group reviews carry linked_group.
	$group = groups_get_group( absint( get_post_meta( $post->ID, 'linked_group', true ) ) );
	if ( empty( $group->id ) ) {
		return;
	}

	global $bgr;
	$approved  = 'publish' === $new_status;
	$author_id = (int) $post->post_author;

	if ( $approved ) {
		do_action( 'gamipress_bp_group_review', $author_id );
		do_action( 'bgr_group_accept_review', $post->ID );
	} else {
		do_action( 'bgr_group_deny_review', $post->ID );
	}

	$author = get_userdata( $author_id );
	if ( ! $author || 'yes' !== $bgr[ $approved ? 'accept_email_enable' : 'deny_email_enable' ] ) {
		return;
	}

	$settings  = (array) get_option( 'bp_group_review_email_settings', array() );
	$key       = $approved ? 'review_accept_email' : 'review_deny_email';
	$site_name = get_bloginfo( 'name' );

	if ( ! empty( $settings[ $key . '_subject' ] ) ) {
		$subject = str_replace( array( '[group-name]', '[site-name]' ), array( $group->name, $site_name ), $settings[ $key . '_subject' ] );
	} elseif ( $approved ) {
		/* translators: %1$s: review label, %2$s: group name */
		$subject = sprintf( __( 'Your %1$s for %2$s has been approved', 'bp-group-reviews' ), $bgr['review_label'], $group->name );
	} else {
		/* translators: %1$s: review label, %2$s: group name */
		$subject = sprintf( __( 'Your %1$s for %2$s was not approved', 'bp-group-reviews' ), $bgr['review_label'], $group->name );
	}

	if ( ! empty( $settings[ $key . '_message' ] ) ) {
		$message = $settings[ $key . '_message' ];
	} elseif ( $approved ) {
		$message = 'Hello [user-name],<br><br>
		Your review for [group-name] on [site-name] has been approved and is now published.<br><br>
		Thank you for sharing your thoughts with the community. View your review here: [review-link]<br><br>
		Best regards,<br>
		The [site-name] Team';
	} else {
		$message = 'Hello [user-name],<br><br>
		Your review for [group-name] on [site-name] was not approved by the group administrator.<br><br>
		If you have questions about our community guidelines, please contact the group admin.<br><br>
		Thank you for your understanding.<br><br>
		Best regards,<br>
		The [site-name] Team';
	}

	// The reviewer is pointed at the group's reviews, never at wp-admin.
	$group_url   = function_exists( 'bp_get_group_url' ) ? bp_get_group_url( $group ) : bp_get_group_permalink( $group );
	$review_link = $group_url . bp_group_review_manage_tab_slug() . '/';
	$message     = str_replace(
		array( '[group-name]', '[user-name]', '[review-link]', '[site-name]' ),
		array( esc_html( $group->name ), esc_html( $author->display_name ), esc_url( $review_link ), esc_html( $site_name ) ),
		$message
	);

	wp_mail( $author->user_email, sanitize_text_field( str_replace( array( "\r", "\n" ), '', $subject ) ), nl2br( $message ), array( 'Content-Type: text/html; charset=UTF-8' ) );
}
add_action( 'transition_post_status', 'bgr_handle_review_decision', 10, 3 );

/**
 * Apply the side effects of a newly submitted group review.
 *
 * Runs for the group form (AJAX) and the REST create route alike, so both
 * entry points tag the review, notify and email the group admins, and fire
 * the public after-insert action the same way.
 *
 * @since 3.9.1
 *
 * @param int $author_id Reviewer user ID.
 * @param int $group_id  Reviewed group ID.
 * @param int $review_id Review post ID.
 */
function bgr_handle_review_submitted( $author_id, $group_id, $review_id ) {
	global $bgr;

	$group  = groups_get_group( absint( $group_id ) );
	$author = get_userdata( $author_id );
	if ( empty( $group->id ) || ! $author ) {
		return;
	}

	wp_set_object_terms( $review_id, 'Group', 'review_category' );

	if ( 'publish' === get_post_status( $review_id ) ) {
		do_action( 'gamipress_bp_group_review', $author_id );
	}

	/**
	 * Fires after a group review is created, from the group form or REST.
	 *
	 * @since 3.9.1 Passes the review, group and reviewer IDs.
	 *
	 * @param int $review_id Review post ID.
	 * @param int $group_id  Reviewed group ID.
	 * @param int $author_id Reviewer user ID.
	 */
	do_action( 'bgr_group_review_after_review_insert', $review_id, $group->id, $author_id );

	if ( 'yes' === $bgr['allow_notification'] ) {
		foreach ( groups_get_group_admins( $group->id ) as $group_admin ) {
			do_action( 'bgr_group_add_review', $group->id, $group_admin->user_id );
		}
	}

	if ( 'yes' !== $bgr['allow_email'] ) {
		return;
	}

	$settings     = (array) get_option( 'bp_group_review_email_settings', array() );
	$creator      = get_userdata( $group->creator_id );
	$creator_name = $creator ? $creator->display_name : '';
	$site_name    = get_bloginfo( 'name' );

	if ( 'yes' === $bgr['auto_approve_reviews'] ) {
		$group_url = function_exists( 'bp_get_group_url' ) ? bp_get_group_url( $group ) : bp_get_group_permalink( $group );
		$mail_link = esc_url( $group_url . bp_group_review_manage_tab_slug() . '/' );
	} else {
		$mail_link = '<a href="' . esc_url( admin_url( 'edit.php?post_type=review' ) ) . '">' . esc_html( admin_url( 'edit.php?post_type=review' ) ) . '</a>';
	}

	if ( ! empty( $settings['review_email_subject'] ) ) {
		$subject = str_replace( array( '[group-name]', '[site-name]' ), array( $group->name, $site_name ), $settings['review_email_subject'] );
	} else {
		/* translators: %1$s: review label, %2$s: group name */
		$subject = sprintf( __( 'New %1$s submitted for %2$s', 'bp-group-reviews' ), $bgr['review_label'], $group->name );
	}

	if ( ! empty( $settings['review_email_message'] ) ) {
		$message = $settings['review_email_message'];
	} else {
		$message = 'Hello [admin-name],<br><br>
		A new review has been submitted for your group [group-name] by [user-name].<br><br>
		You can view and respond to the review here: [review-link]<br><br>
		Thank you for creating a space where members can share their experiences!<br><br>
		Best regards,<br>
		The [site-name] Team';
	}

	$message = str_replace(
		array( '[admin-name]', '[group-name]', '[user-name]', '[review-link]', '[site-name]' ),
		array( esc_html( $creator_name ), esc_html( $group->name ), esc_html( $author->user_login ), $mail_link, esc_html( $site_name ) ),
		$message
	);
	$subject = sanitize_text_field( str_replace( array( "\r", "\n" ), '', $subject ) );

	foreach ( groups_get_group_admins( $group->id ) as $group_admin ) {
		$admin = get_userdata( $group_admin->user_id );
		if ( $admin ) {
			wp_mail( $admin->user_email, $subject, nl2br( $message ), array( 'Content-Type: text/html; charset=UTF-8' ) );
		}
	}
}
add_action( 'bgr_group_after_review_submit', 'bgr_handle_review_submitted', 10, 3 );
