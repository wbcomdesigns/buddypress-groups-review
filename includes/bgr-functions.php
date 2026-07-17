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
		'index'         => array(),
	);

	if ( empty( $group_id ) ) {
		return $defaults;
	}

	$stats = get_transient( 'bgr_review_rating_' . $group_id );
	if ( is_array( $stats ) && isset( $stats['average'], $stats['index'] ) ) {
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