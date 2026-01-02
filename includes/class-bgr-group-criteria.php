<?php
/**
 * Group-Level Criteria Management Class
 *
 * Handles custom rating criteria at the group level, allowing group owners
 * to override site-wide criteria settings for their specific groups.
 *
 * @since   3.7.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * BGR_Group_Criteria class.
 *
 * @since 3.7.0
 */
class BGR_Group_Criteria {

	/**
	 * Group meta key for storing criteria settings.
	 *
	 * @var string
	 */
	const META_KEY = 'bgr_group_criteria_settings';

	/**
	 * Mode: inherit from global settings.
	 *
	 * @var string
	 */
	const MODE_INHERIT = 'inherit';

	/**
	 * Mode: override with custom settings.
	 *
	 * @var string
	 */
	const MODE_OVERRIDE = 'override';

	/**
	 * Single instance of the class.
	 *
	 * @var BGR_Group_Criteria|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @since 3.7.0
	 * @return BGR_Group_Criteria
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 3.7.0
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 3.7.0
	 */
	private function init_hooks() {
		// Hook for when global criteria is deleted.
		add_action( 'bgr_global_criteria_deleted', array( $this, 'handle_global_criteria_deleted' ), 10, 1 );

		// Hook for when global criteria is archived.
		add_action( 'bgr_global_criteria_archived', array( $this, 'handle_global_criteria_archived' ), 10, 1 );
	}

	/**
	 * Get the default settings structure for a group.
	 *
	 * @since 3.7.0
	 * @return array Default settings.
	 */
	public function get_default_settings() {
		return array(
			'mode'                    => self::MODE_INHERIT,
			'enabled_global_criteria' => array(),
			'custom_criteria'         => array(),
			'archived_criteria'       => array(),
		);
	}

	/**
	 * Get group criteria settings.
	 *
	 * @since 3.7.0
	 * @param int $group_id The group ID.
	 * @return array The group criteria settings.
	 */
	public function get_group_settings( $group_id ) {
		if ( ! $group_id ) {
			return $this->get_default_settings();
		}

		$settings = groups_get_groupmeta( $group_id, self::META_KEY, true );

		if ( empty( $settings ) || ! is_array( $settings ) ) {
			return $this->get_default_settings();
		}

		// Ensure all keys exist.
		return wp_parse_args( $settings, $this->get_default_settings() );
	}

	/**
	 * Save group criteria settings.
	 *
	 * @since 3.7.0
	 * @param int   $group_id The group ID.
	 * @param array $settings The settings to save.
	 * @return bool|int Meta ID if the key didn't exist, true on success, false on failure.
	 */
	public function save_group_settings( $group_id, $settings ) {
		if ( ! $group_id ) {
			return false;
		}

		// Sanitize and validate settings.
		$sanitized = $this->sanitize_settings( $settings );

		return groups_update_groupmeta( $group_id, self::META_KEY, $sanitized );
	}

	/**
	 * Sanitize criteria settings.
	 *
	 * @since 3.7.0
	 * @param array $settings Raw settings.
	 * @return array Sanitized settings.
	 */
	private function sanitize_settings( $settings ) {
		$sanitized = $this->get_default_settings();

		// Mode.
		if ( isset( $settings['mode'] ) && in_array( $settings['mode'], array( self::MODE_INHERIT, self::MODE_OVERRIDE ), true ) ) {
			$sanitized['mode'] = $settings['mode'];
		}

		// Enabled global criteria.
		if ( isset( $settings['enabled_global_criteria'] ) && is_array( $settings['enabled_global_criteria'] ) ) {
			$sanitized['enabled_global_criteria'] = array_map( 'sanitize_text_field', $settings['enabled_global_criteria'] );
		}

		// Custom criteria.
		if ( isset( $settings['custom_criteria'] ) && is_array( $settings['custom_criteria'] ) ) {
			$sanitized['custom_criteria'] = array();
			foreach ( $settings['custom_criteria'] as $criterion ) {
				if ( isset( $criterion['name'] ) && ! empty( $criterion['name'] ) ) {
					$sanitized['custom_criteria'][] = array(
						'name'    => sanitize_text_field( $criterion['name'] ),
						'created' => isset( $criterion['created'] ) ? absint( $criterion['created'] ) : time(),
						'active'  => isset( $criterion['active'] ) ? (bool) $criterion['active'] : true,
					);
				}
			}
		}

		// Archived criteria.
		if ( isset( $settings['archived_criteria'] ) && is_array( $settings['archived_criteria'] ) ) {
			$sanitized['archived_criteria'] = array();
			foreach ( $settings['archived_criteria'] as $name => $data ) {
				$sanitized['archived_criteria'][ sanitize_text_field( $name ) ] = array(
					'archived_at' => isset( $data['archived_at'] ) ? absint( $data['archived_at'] ) : time(),
					'reason'      => isset( $data['reason'] ) ? sanitize_text_field( $data['reason'] ) : 'unknown',
				);
			}
		}

		return $sanitized;
	}

	/**
	 * Get the criteria mode for a group.
	 *
	 * @since 3.7.0
	 * @param int $group_id The group ID.
	 * @return string The criteria mode ('inherit' or 'override').
	 */
	public function get_criteria_mode( $group_id ) {
		$settings = $this->get_group_settings( $group_id );
		return $settings['mode'];
	}

	/**
	 * Check if a group is using custom criteria.
	 *
	 * @since 3.7.0
	 * @param int $group_id The group ID.
	 * @return bool True if using custom criteria.
	 */
	public function is_using_custom_criteria( $group_id ) {
		return self::MODE_OVERRIDE === $this->get_criteria_mode( $group_id );
	}

	/**
	 * Get effective criteria for a group.
	 *
	 * Returns the list of active criteria names that should be used for this group,
	 * taking into account inheritance and group-level overrides.
	 *
	 * @since 3.7.0
	 * @param int $group_id The group ID.
	 * @return array List of active criteria names.
	 */
	public function get_effective_criteria( $group_id ) {
		$settings = $this->get_group_settings( $group_id );

		// If inheriting, use global criteria.
		if ( self::MODE_INHERIT === $settings['mode'] ) {
			return $this->get_global_active_criteria();
		}

		$criteria = array();

		// Add enabled global criteria.
		$global_all = $this->get_global_all_criteria();
		foreach ( $settings['enabled_global_criteria'] as $name ) {
			// Only include if it still exists globally.
			if ( in_array( $name, $global_all, true ) ) {
				$criteria[] = $name;
			}
		}

		// Add active custom criteria.
		foreach ( $settings['custom_criteria'] as $custom ) {
			if ( ! empty( $custom['active'] ) ) {
				$criteria[] = $custom['name'];
			}
		}

		// Fallback to global if empty.
		if ( empty( $criteria ) ) {
			return $this->get_global_active_criteria();
		}

		return $criteria;
	}

	/**
	 * Check if a specific criterion is active for a group.
	 *
	 * @since 3.7.0
	 * @param int    $group_id       The group ID.
	 * @param string $criteria_name  The criterion name.
	 * @return bool True if the criterion is active.
	 */
	public function is_criteria_active_for_group( $group_id, $criteria_name ) {
		$effective = $this->get_effective_criteria( $group_id );
		return in_array( $criteria_name, $effective, true );
	}

	/**
	 * Get all criteria ever used for a group (including archived).
	 *
	 * Useful for displaying historical data.
	 *
	 * @since 3.7.0
	 * @param int $group_id The group ID.
	 * @return array All criteria names (active + archived).
	 */
	public function get_all_criteria_for_group( $group_id ) {
		$settings  = $this->get_group_settings( $group_id );
		$effective = $this->get_effective_criteria( $group_id );
		$archived  = array_keys( $settings['archived_criteria'] );

		return array_unique( array_merge( $effective, $archived ) );
	}

	/**
	 * Add a custom criterion to a group.
	 *
	 * @since 3.7.0
	 * @param int    $group_id The group ID.
	 * @param string $name     The criterion name.
	 * @return bool True on success.
	 */
	public function add_custom_criteria( $group_id, $name ) {
		if ( ! $group_id || empty( $name ) ) {
			return false;
		}

		$settings = $this->get_group_settings( $group_id );

		// Check for duplicates.
		foreach ( $settings['custom_criteria'] as $existing ) {
			if ( strtolower( $existing['name'] ) === strtolower( $name ) ) {
				return false;
			}
		}

		$settings['custom_criteria'][] = array(
			'name'    => sanitize_text_field( $name ),
			'created' => time(),
			'active'  => true,
		);

		return (bool) $this->save_group_settings( $group_id, $settings );
	}

	/**
	 * Archive a custom criterion (soft delete).
	 *
	 * @since 3.7.0
	 * @param int    $group_id The group ID.
	 * @param string $name     The criterion name.
	 * @param string $reason   The reason for archiving.
	 * @return bool True on success.
	 */
	public function archive_custom_criteria( $group_id, $name, $reason = 'disabled_by_group_admin' ) {
		if ( ! $group_id || empty( $name ) ) {
			return false;
		}

		$settings = $this->get_group_settings( $group_id );

		// Find and deactivate the criterion.
		$found = false;
		foreach ( $settings['custom_criteria'] as $key => $criterion ) {
			if ( $criterion['name'] === $name ) {
				$settings['custom_criteria'][ $key ]['active'] = false;
				$found = true;
				break;
			}
		}

		if ( ! $found ) {
			return false;
		}

		// Add to archived list.
		$settings['archived_criteria'][ $name ] = array(
			'archived_at' => time(),
			'reason'      => sanitize_text_field( $reason ),
		);

		return (bool) $this->save_group_settings( $group_id, $settings );
	}

	/**
	 * Delete a custom criterion (hard delete if no reviews use it).
	 *
	 * @since 3.7.0
	 * @param int    $group_id The group ID.
	 * @param string $name     The criterion name.
	 * @return bool True on success.
	 */
	public function delete_custom_criteria( $group_id, $name ) {
		if ( ! $group_id || empty( $name ) ) {
			return false;
		}

		// Check if any reviews use this criterion.
		if ( $this->criterion_has_reviews( $group_id, $name ) ) {
			// Soft delete instead.
			return $this->archive_custom_criteria( $group_id, $name, 'deleted_with_reviews' );
		}

		$settings = $this->get_group_settings( $group_id );

		// Remove from custom criteria.
		$settings['custom_criteria'] = array_filter(
			$settings['custom_criteria'],
			function ( $criterion ) use ( $name ) {
				return $criterion['name'] !== $name;
			}
		);

		// Re-index array.
		$settings['custom_criteria'] = array_values( $settings['custom_criteria'] );

		return (bool) $this->save_group_settings( $group_id, $settings );
	}

	/**
	 * Enable or disable a global criterion for a group.
	 *
	 * @since 3.7.0
	 * @param int    $group_id The group ID.
	 * @param string $name     The criterion name.
	 * @param bool   $enabled  Whether to enable or disable.
	 * @return bool True on success.
	 */
	public function toggle_global_criteria( $group_id, $name, $enabled ) {
		if ( ! $group_id || empty( $name ) ) {
			return false;
		}

		$settings = $this->get_group_settings( $group_id );

		if ( $enabled ) {
			if ( ! in_array( $name, $settings['enabled_global_criteria'], true ) ) {
				$settings['enabled_global_criteria'][] = $name;
			}
		} else {
			$settings['enabled_global_criteria'] = array_diff( $settings['enabled_global_criteria'], array( $name ) );
			$settings['enabled_global_criteria'] = array_values( $settings['enabled_global_criteria'] );
		}

		return (bool) $this->save_group_settings( $group_id, $settings );
	}

	/**
	 * Set the criteria mode for a group.
	 *
	 * @since 3.7.0
	 * @param int    $group_id The group ID.
	 * @param string $mode     The mode ('inherit' or 'override').
	 * @return bool True on success.
	 */
	public function set_criteria_mode( $group_id, $mode ) {
		if ( ! $group_id || ! in_array( $mode, array( self::MODE_INHERIT, self::MODE_OVERRIDE ), true ) ) {
			return false;
		}

		$settings         = $this->get_group_settings( $group_id );
		$settings['mode'] = $mode;

		// When switching to override mode for the first time, inherit current global criteria.
		if ( self::MODE_OVERRIDE === $mode && empty( $settings['enabled_global_criteria'] ) ) {
			$settings['enabled_global_criteria'] = $this->get_global_active_criteria();
		}

		return (bool) $this->save_group_settings( $group_id, $settings );
	}

	/**
	 * Handle global criteria deletion.
	 *
	 * When a global criterion is deleted, remove it from all groups' enabled lists.
	 *
	 * @since 3.7.0
	 * @param string $criteria_name The deleted criterion name.
	 */
	public function handle_global_criteria_deleted( $criteria_name ) {
		global $wpdb;

		$bp = buddypress();

		// Cache table name for use in query.
		$table_name = $bp->groups->table_name_groupmeta;

		// Find all groups using this criterion.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$groups = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT group_id FROM {$table_name} WHERE meta_key = %s", // Table name from BuddyPress core.
				self::META_KEY
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		foreach ( $groups as $group_id ) {
			$settings = $this->get_group_settings( $group_id );

			// Remove from enabled_global_criteria.
			if ( in_array( $criteria_name, $settings['enabled_global_criteria'], true ) ) {
				$settings['enabled_global_criteria'] = array_diff( $settings['enabled_global_criteria'], array( $criteria_name ) );
				$settings['enabled_global_criteria'] = array_values( $settings['enabled_global_criteria'] );

				// Archive if group has reviews with this criterion.
				if ( $this->criterion_has_reviews( $group_id, $criteria_name ) ) {
					$settings['archived_criteria'][ $criteria_name ] = array(
						'archived_at' => time(),
						'reason'      => 'global_criteria_deleted',
					);
				}

				$this->save_group_settings( $group_id, $settings );
			}
		}
	}

	/**
	 * Handle global criteria archival.
	 *
	 * @since 3.7.0
	 * @param string $criteria_name The archived criterion name.
	 */
	public function handle_global_criteria_archived( $criteria_name ) {
		// Same handling as deletion for now.
		$this->handle_global_criteria_deleted( $criteria_name );
	}

	/**
	 * Check if a criterion has any reviews in a group.
	 *
	 * @since 3.7.0
	 * @param int    $group_id       The group ID.
	 * @param string $criteria_name  The criterion name.
	 * @return bool True if reviews exist with this criterion.
	 */
	public function criterion_has_reviews( $group_id, $criteria_name ) {
		$reviews = $this->get_group_reviews( $group_id );

		foreach ( $reviews as $review ) {
			$ratings = get_post_meta( $review->ID, 'review_star_rating', true );
			if ( is_array( $ratings ) && isset( $ratings[ $criteria_name ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get all reviews for a group.
	 *
	 * @since 3.7.0
	 * @param int $group_id The group ID.
	 * @return array Array of review post objects.
	 */
	public function get_group_reviews( $group_id ) {
		$args = array(
			'post_type'      => 'review',
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'   => 'linked_group',
					'value' => $group_id,
				),
			),
		);

		return get_posts( $args );
	}

	/**
	 * Get criteria that were used in a specific review.
	 *
	 * @since 3.7.0
	 * @param int $review_id The review post ID.
	 * @return array List of criteria names.
	 */
	public function get_review_criteria( $review_id ) {
		$ratings = get_post_meta( $review_id, 'review_star_rating', true );

		if ( ! is_array( $ratings ) ) {
			return array();
		}

		return array_keys( $ratings );
	}

	/**
	 * Calculate average ratings for a group with mixed criteria.
	 *
	 * @since 3.7.0
	 * @param int $group_id The group ID.
	 * @return array Associative array of criteria => [average, count].
	 */
	public function calculate_group_averages( $group_id ) {
		$reviews = $this->get_group_reviews( $group_id );

		// Only count published reviews.
		$reviews = array_filter(
			$reviews,
			function ( $review ) {
				return 'publish' === $review->post_status;
			}
		);

		if ( empty( $reviews ) ) {
			return array();
		}

		$criteria_totals = array();
		$criteria_counts = array();

		foreach ( $reviews as $review ) {
			$ratings = get_post_meta( $review->ID, 'review_star_rating', true );

			if ( ! is_array( $ratings ) ) {
				continue;
			}

			foreach ( $ratings as $name => $value ) {
				if ( ! isset( $criteria_totals[ $name ] ) ) {
					$criteria_totals[ $name ] = 0;
					$criteria_counts[ $name ] = 0;
				}
				$criteria_totals[ $name ] += floatval( $value );
				++$criteria_counts[ $name ];
			}
		}

		$averages = array();
		foreach ( $criteria_totals as $name => $total ) {
			$averages[ $name ] = array(
				'average' => round( $total / $criteria_counts[ $name ], 1 ),
				'count'   => $criteria_counts[ $name ],
			);
		}

		return $averages;
	}

	/**
	 * Calculate overall average rating for a group.
	 *
	 * @since 3.7.0
	 * @param int $group_id The group ID.
	 * @return float The overall average rating.
	 */
	public function calculate_overall_average( $group_id ) {
		$averages = $this->calculate_group_averages( $group_id );

		if ( empty( $averages ) ) {
			return 0;
		}

		$total = 0;
		$count = 0;

		foreach ( $averages as $data ) {
			$total += $data['average'] * $data['count'];
			$count += $data['count'];
		}

		return $count > 0 ? round( $total / $count, 1 ) : 0;
	}

	/**
	 * Get global active criteria.
	 *
	 * @since 3.7.0
	 * @return array List of active global criteria names.
	 */
	public function get_global_active_criteria() {
		global $bgr;

		if ( isset( $bgr['active_rating_fields'] ) && is_array( $bgr['active_rating_fields'] ) ) {
			return $bgr['active_rating_fields'];
		}

		$settings = get_option( 'bgr_admin_criteria_settings' );

		if ( isset( $settings['active_rating_fields'] ) && is_array( $settings['active_rating_fields'] ) ) {
			return $settings['active_rating_fields'];
		}

		return array( 'Quality', 'Relevance', 'Engagement' );
	}

	/**
	 * Get all defined global criteria (active and inactive).
	 *
	 * @since 3.7.0
	 * @return array List of all global criteria names.
	 */
	public function get_global_all_criteria() {
		global $bgr;

		if ( isset( $bgr['review_rating_fields'] ) && is_array( $bgr['review_rating_fields'] ) ) {
			return $bgr['review_rating_fields'];
		}

		$settings = get_option( 'bgr_admin_criteria_settings' );

		if ( isset( $settings['add_review_rating_fields'] ) && is_array( $settings['add_review_rating_fields'] ) ) {
			return $settings['add_review_rating_fields'];
		}

		return array( 'Quality', 'Relevance', 'Engagement' );
	}

	/**
	 * Check if a global criterion exists.
	 *
	 * @since 3.7.0
	 * @param string $name The criterion name.
	 * @return bool True if exists.
	 */
	public function is_global_criteria_exists( $name ) {
		return in_array( $name, $this->get_global_all_criteria(), true );
	}

	/**
	 * Check if a criterion is a custom group criterion.
	 *
	 * @since 3.7.0
	 * @param int    $group_id The group ID.
	 * @param string $name     The criterion name.
	 * @return bool True if it's a custom group criterion.
	 */
	public function is_custom_criteria( $group_id, $name ) {
		$settings = $this->get_group_settings( $group_id );

		foreach ( $settings['custom_criteria'] as $criterion ) {
			if ( $criterion['name'] === $name ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a criterion is archived.
	 *
	 * @since 3.7.0
	 * @param int    $group_id The group ID.
	 * @param string $name     The criterion name.
	 * @return bool True if archived.
	 */
	public function is_criteria_archived( $group_id, $name ) {
		$settings = $this->get_group_settings( $group_id );
		return isset( $settings['archived_criteria'][ $name ] );
	}

	/**
	 * Get archived criteria info.
	 *
	 * @since 3.7.0
	 * @param int    $group_id The group ID.
	 * @param string $name     The criterion name.
	 * @return array|null Archive info or null if not archived.
	 */
	public function get_archived_criteria_info( $group_id, $name ) {
		$settings = $this->get_group_settings( $group_id );

		if ( isset( $settings['archived_criteria'][ $name ] ) ) {
			return $settings['archived_criteria'][ $name ];
		}

		return null;
	}
}
