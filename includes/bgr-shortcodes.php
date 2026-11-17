<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- Legacy file name.
/**
 * Plugin shortcode functionality.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'BGR_Shortcodes' ) ) {
	/**
	 * Class to serve shortcodes
	 *
	 *  @since   1.0.0
	 *  @author   Wbcom Designs
	 */
	class BGR_Shortcodes {

			/**
			 * Constructor for shortcodes
			 *
			 *  @since   1.0.0
			 *  @author  Wbcom Designs
			 */
		public function __construct() {
			add_shortcode( 'add_group_review_form', array( $this, 'bp_group_review_add_new_review' ) );
			add_shortcode( 'bgr_display_top_groups', array( $this, 'bp_group_review_leaderboard' ) );
		}

			/**
			 *  Display Review form when member logged in
			 *
			 *  @since   1.0.0
			 *  @author  Wbcom Designs
			 */
		public function bp_group_review_add_new_review() {
			global $bgr;
			$review_rating_fields = $bgr['review_rating_fields'];
			$review_label         = $bgr['review_label'];
			$admin_exclude_groups = $bgr['exclude_groups'];
			$auto_approve_reviews = $bgr['auto_approve_reviews'];
			$multi_reviews        = $bgr['multi_reviews'];
			$current_user         = wp_get_current_user();
			$member_id            = $current_user->ID;
			$output               = '';

			$current_group_id = 0;
			if ( ! empty( bp_get_current_group_id() ) ) {
				$current_group_id = bp_get_current_group_id();
			}

			// Use group-level criteria if available, otherwise fall back to global.
			$active_rating_fields = function_exists( 'bgr_get_effective_criteria' ) && $current_group_id
				? bgr_get_effective_criteria( $current_group_id )
				: $bgr['active_rating_fields'];
			$group_args           = array(
				'post_type'   => 'review',
				'category'    => 'group',
				'post_status' => array(
					'draft',
					'publish',
				),
				'author'      => $member_id,
				'meta_query'  => array(
					array(
						'key'     => 'linked_group',
						'value'   => $current_group_id,
						'compare' => '=',
					),
				),
			);
			$reviews_args         = new WP_Query( $group_args );
			if ( ! is_user_logged_in() ) {
				$output .= '<div id="message" class="bp-messages bp-feedback error">';
				$output .= '<span class="bp-icon" aria-hidden="true"></span>';
				$output .= '<p>';
				$output .= sprintf(
					/* translators: %1$s is used for review lable*/
					esc_html__( 'You should %1$s for post %2$s.', 'bp-group-reviews' ),
					'<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '"> ' . esc_html__( 'login', 'bp-group-reviews' ) . '</a>',
					esc_html( $review_label )
				);
				$output .= '</p>';
				$output .= '</div>';
			} elseif ( bp_is_group_single() ) {
				// User is logged in and on single group page.
				if ( 'no' === $multi_reviews ) {
					$user_post_count = $reviews_args->post_count;
				} else {
					$user_post_count = 0;
				}

				if ( 0 === $user_post_count ) {
					$output .= $this->bp_group_review_form( $review_rating_fields, $active_rating_fields, $review_label, $admin_exclude_groups, $auto_approve_reviews, $multi_reviews, $current_group_id, $member_id );
				} else {

					$bp_template_option = bp_get_option( '_bp_theme_package_id' );
					if ( 'nouveau' === $bp_template_option ) {
						$output .= '<div id="message" class="info bp-feedback bp-messages bp-template-notice">';
						$output .= '<span class="bp-icon" aria-hidden="true"></span>';
					} else {
						$output .= '<div id="message" class="info">';
					}
					/* translators: %1$s is used for review label */
					$output .= '<p>' . sprintf(
						/* translators: %1$s is used for review label */
						esc_html__( 'You already posted a %1$s for this group.', 'bp-group-reviews' ),
						esc_html( $review_label )
					) . '</p>';
					$output .= '</div>';
				}
			} else {
				// User is logged in but not on single group page.
				ob_start();
				$this->bp_group_review_form( $review_rating_fields, $active_rating_fields, $review_label, $admin_exclude_groups, $auto_approve_reviews, $multi_reviews, $current_group_id, $member_id );
				$output .= ob_get_clean();
			}
			return $output;
		}

		/**
		 * BGR review form
		 *
		 * @param  array   $review_rating_fields Review Rating Fields.
		 * @param  array   $active_rating_fields Active Rating Fields.
		 * @param  string  $review_label Review Label.
		 * @param  array   $admin_exclude_groups Exclude Groups.
		 * @param  boolean $auto_approve_reviews Auto approve review.
		 * @param  boolean $multi_reviews Multiple review.
		 * @param  int     $current_group_id Group ID.
		 * @param  int     $member_id Member ID.
		 * @return void
		 */
		public function bp_group_review_form( $review_rating_fields, $active_rating_fields, $review_label, $admin_exclude_groups, $auto_approve_reviews, $multi_reviews, $current_group_id, $member_id ) {
			$user_groups   = BP_Groups_Member::get_is_admin_of( $member_id );
			$exclude_group = array();

			// Safely check user_groups array structure before iterating.
			if ( ! empty( $user_groups ) && isset( $user_groups['groups'] ) && is_array( $user_groups['groups'] ) ) {
				foreach ( $user_groups['groups'] as $user_group ) {
					if ( ! empty( $user_group->id ) ) {
						$exclude_group[] = absint( $user_group->id );
					}
				}
			}

			// Cast admin exclude groups to integers for consistent comparison.
			if ( ! empty( $admin_exclude_groups ) && is_array( $admin_exclude_groups ) ) {
				foreach ( $admin_exclude_groups as $admin_exclude_group ) {
					$exclude_group[] = absint( $admin_exclude_group );
				}
			}

			// Check if current group is in the exclusion list and return early if so.
			$current_group_id_int = absint( $current_group_id );
			if ( ! empty( $current_group_id_int ) && in_array( $current_group_id_int, $exclude_group, true ) ) {
				return;
			}
			?>
			<form id="bgr-add-review-form" method="POST">
				<input type="hidden" id="reviews_pluginurl"  name="reviews_pluginurl" value="<?php echo esc_attr( BGR_PLUGIN_URL ); ?>">
				<div class="group-add-form">
					<h4>
						<?php
						/* translators: %1$s is used for review label */
						printf( esc_html__( 'Write a %1$s', 'bp-group-reviews' ), esc_html( $review_label ) );
						?>
					</h4>
						<input type="hidden" name="form-group-id" value="<?php echo esc_attr( $current_group_id ); ?>" />
						<p class="bupr-hide-subject">
							<?php $review_subject = bp_group_review_add_review_tab_name() . ' ' . time(); ?>
							<input name="review-subject" type="hidden" value="<?php echo esc_attr( $review_subject ); ?>">
						</p>
						<?php $this->bp_group_review_display_form_rating(); ?>
							<textarea class="review_desc" name="review-desc" placeholder="<?php /* translators: %1$s: review label (e.g. "Review"). */ printf( esc_attr__( '%1$s Description (optional)', 'bp-group-reviews' ), esc_attr( $review_label ) ); ?>" rows="3" cols="50"></textarea>
							<br/>
						<p>
							<input type="hidden" name="bgr-flag" value="1">
						</p>
						<p>
							<?php wp_nonce_field( 'save-group-review', 'security-nonce' ); ?>
							<button class="btn btn-default bgr-submit-review" name="bgr-submit-review"><?php /* translators: %1$s: review label (e.g. "Review"). */ printf( esc_html__( 'Submit %1$s', 'bp-group-reviews' ), esc_html( $review_label ) ); ?></button>
						</p>
					</div>
				</form>
				<div id="bgr-message" class="bgr-success"></div>
				<?php
		}

		/**
		 *  Display Ratings form.
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */
		public function bp_group_review_display_form_rating() {
			global $bgr;
			$group_id = bp_get_current_group_id();
			$this->bp_group_review_display_form_star_rating( $group_id );
		}

		/**
		 * Check active rating fields
		 *
		 * @param int $group_id Optional group ID for group-level criteria.
		 * @return bool True if there are active rating fields, false otherwise.
		 */
		public function bp_group_review_display_star( $group_id = 0 ) {
			global $bgr;
			// Use group-level criteria if available.
			$active_rating_fields = function_exists( 'bgr_get_effective_criteria' ) && $group_id
				? bgr_get_effective_criteria( $group_id )
				: $bgr['active_rating_fields'];
			if ( ! empty( $active_rating_fields ) ) {
				$show_rating = true;
			} else {
				$show_rating = false;
			}
			return $show_rating;
		}

		/**
		 *  Display Ratings when rating type = star
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 *  @param   int $group_id Optional group ID for group-level criteria.
		 */
		public function bp_group_review_display_form_star_rating( $group_id = 0 ) {
			global $bgr;
			// Use group-level criteria if available.
			$active_rating_fields = function_exists( 'bgr_get_effective_criteria' ) && $group_id
				? bgr_get_effective_criteria( $group_id )
				: $bgr['active_rating_fields'];
			// For the form, we only show the active criteria (whether global or group-level).
			$review_rating_fields         = $active_rating_fields;
			$bp_group_review_display_star = $this->bp_group_review_display_star( $group_id );
			if ( true === $bp_group_review_display_star ) {
				$field_counter = 1;
				foreach ( $review_rating_fields as $review_rating_field ) :
					if ( in_array( $review_rating_field, $active_rating_fields, true ) ) {
						?>
							<div class="multi-review bgr-criteria-row">
								<div class="bgr-col-4 "><?php echo esc_html( bp_group_review_get_criteria_label( $review_rating_field ) ); ?> <span class="required"> * </span>  </div>
								<div id="review<?php echo esc_html( $field_counter ); ?>" class="bgr-col-4">
										<input type="hidden" id="<?php echo 'clicked' . esc_html( $field_counter ); ?>" value="not_clicked">
										<input type="hidden" name="rated_stars[<?php echo esc_attr( $review_rating_field ); ?>]" class="rated_stars bgr_mrating" id="<?php echo 'rated_stars' . esc_html( $field_counter ); ?>" value="0">
										<?php for ( $i = 1; $i <= 5; $i++ ) { ?>
												<span class="far fa-star bgr-stars bgr-star-rate <?php echo esc_attr( $i ); ?>" id="<?php echo esc_attr( $field_counter ) . esc_attr( $i ); ?>" data-attr="<?php echo esc_attr( $i ); ?>" ></span>
											<?php } ?>
								</div>
								<div class="bgr-col-12 bgr-error-fields">*<?php esc_html_e( 'This field is required.', 'bp-group-reviews' ); ?></div>
							</div>
							<?php
							++$field_counter; }
						endforeach;
				?>
					<input type="hidden" id="rating_field_counter" value="<?php echo esc_html( --$field_counter ); ?>">
				<?php
			} else {
				$field_counter = 1;
				?>
				<div class="multi-review bgr-criteria-row">
					<div class="bgr-col-4"><?php esc_html_e( 'Your Rating', 'bp-group-reviews' ); ?><span class="required"> * </span> </div>
					<div id="review" class="bgr-col-4">
						<input type="hidden" id="<?php echo 'clicked'; ?>" value="not_clicked">
							<input type="hidden" name="rated_stars[]" class="rated_stars bgr_mrating" id="rated_stars" value="0">
							<?php for ( $i = 1; $i <= 5; $i++ ) { ?>
									<span class="far fa-star bgr-stars bgr-star-rate <?php echo esc_attr( $i ); ?>" id="<?php echo esc_attr( $i ); ?>" data-attr="<?php echo esc_attr( $i ); ?>" ></span>
								<?php } ?>
					</div>	
					<div class="bgr-col-12 bgr-error-fields">*<?php esc_html_e( 'This field is required.', 'bp-group-reviews' ); ?></div>				
				</div>
				<input type="hidden" id="rating_field_counter" value="1">				
				<?php
			}
		}

	/**
	 * Display group leaderboard shortcode [bgr_display_top_groups].
	 *
	 * Only groups that have at least one published review are included.
	 *
	 * Attributes:
	 *   title        - heading text (default '')
	 *   total_groups - number of groups to show (default 10)
	 *   type         - 'top rated'     : highest avg_rating first, review_count as tiebreaker
	 *                  'most reviewed' : highest review_count first, avg_rating as tiebreaker
	 *                  'members'       : highest member_count first
	 *                  (default 'top rated')
	 *   show_avatar  - 'true' | 'false' (default 'true')
	 *   show_rating  - 'true' | 'false' (default 'true')
	 *
	 * Usage example:
	 *   [bgr_display_top_groups title="Most Reviewed" total_groups="10" type="top rated"]
	 *
	 * @since   3.8.0
	 * @author  Wbcom Designs
	 * @param   array $atts Shortcode attributes.
	 * @return  string HTML output.
	 */
	public function bp_group_review_leaderboard( $atts ) {
		global $wpdb;

		$atts = shortcode_atts(
			array(
				'title'        => '',
				'total_groups' => 10,
				'type'         => 'top rated',
				'show_avatar'  => 'true',
				'show_rating'  => 'true',
			),
			$atts
		);

		$title        = sanitize_text_field( $atts['title'] );
		$total_groups = absint( $atts['total_groups'] );
		$type         = sanitize_text_field( $atts['type'] );
		$show_avatar  = filter_var( $atts['show_avatar'], FILTER_VALIDATE_BOOLEAN );
		$show_rating  = filter_var( $atts['show_rating'], FILTER_VALIDATE_BOOLEAN );

		// 'top rated' requires PHP re-ranking by avg_rating after the SQL fetch,
		// so pull a larger pool up-front to ensure the final top-N is accurate.
		// avg_rating is calculated in PHP because review_star_rating is stored as
		// a PHP-serialized array and cannot be averaged directly in SQL.
		if ( 'top rated' === $type ) {
			$fetch_limit  = max( $total_groups * 5, 50 );
			$order_clause = 'review_count DESC';
		} elseif ( 'members' === $type ) {
			$fetch_limit  = $total_groups;
			$order_clause = 'member_count DESC';
		} else {
			// 'most reviewed' — SQL ORDER BY review_count is already correct.
			$fetch_limit  = $total_groups;
			$order_clause = 'review_count DESC';
		}

		// INNER JOIN on posts/postmeta ensures only groups with at least one
		// published review are returned — groups with zero reviews are excluded.
		// ORDER BY clause is hardcoded from the switch above — safe to interpolate.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$groups = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT g.id, g.name, g.slug,
					COUNT(DISTINCT r.ID)      AS review_count,
					COUNT(DISTINCT m.user_id) AS member_count
				FROM {$wpdb->prefix}bp_groups g
				INNER JOIN {$wpdb->postmeta} pm
					ON g.id = CAST(pm.meta_value AS UNSIGNED)
					AND pm.meta_key = 'linked_group'
				INNER JOIN {$wpdb->posts} r
					ON pm.post_id = r.ID
					AND r.post_type = 'review'
					AND r.post_status = 'publish'
				LEFT JOIN {$wpdb->prefix}bp_groups_members m
					ON g.id = m.group_id
					AND m.is_banned = 0
				WHERE g.status = 'public'
				GROUP BY g.id, g.name, g.slug
				ORDER BY {$order_clause}
				LIMIT %d",
				$fetch_limit
			)
		);
		// phpcs:enable

		if ( empty( $groups ) ) {
			return '<p>' . esc_html__( 'No groups found.', 'bp-group-reviews' ) . '</p>';
		}

		// --- Calculate avg_rating in PHP ---
		// Batch-fetch all review IDs for the returned groups in one query,
		// then read post meta to avoid N+1 queries.
		$group_ids    = array_map( 'intval', wp_list_pluck( $groups, 'id' ) );
		$placeholders = implode( ',', array_fill( 0, count( $group_ids ), '%d' ) );

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$review_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.meta_value AS group_id, r.ID AS review_id
				FROM {$wpdb->postmeta} pm
				JOIN {$wpdb->posts} r ON pm.post_id = r.ID
				WHERE pm.meta_key = 'linked_group'
				AND pm.meta_value IN ($placeholders)
				AND r.post_type = 'review'
				AND r.post_status = 'publish'",
				...$group_ids
			)
		);
		// phpcs:enable

		// Index review IDs by group ID.
		$reviews_by_group = array();
		foreach ( $review_rows as $row ) {
			$reviews_by_group[ (int) $row->group_id ][] = (int) $row->review_id;
		}

		// Calculate per-group average using the same logic as bgr-filters.php.
		$avg_by_group = array();
		foreach ( $reviews_by_group as $gid => $review_ids ) {
			$ttl_rating    = 0;
			$reviews_count = 0;
			foreach ( $review_ids as $review_id ) {
				$review_ratings = get_post_meta( $review_id, 'review_star_rating', false );
				if ( ! empty( $review_ratings[0] ) && is_array( $review_ratings[0] ) ) {
					$rate        = 0;
					$field_count = count( $review_ratings[0] );
					foreach ( $review_ratings[0] as $value ) {
						$rate += (int) $value;
					}
					if ( $field_count > 0 ) {
						++$reviews_count;
						$ttl_rating += $rate / $field_count;
					}
				}
			}
			$avg_by_group[ $gid ] = $reviews_count > 0 ? round( $ttl_rating / $reviews_count, 2 ) : 0;
		}

		// Attach avg_rating to each group object.
		foreach ( $groups as $group ) {
			$group->avg_rating = isset( $avg_by_group[ (int) $group->id ] ) ? $avg_by_group[ (int) $group->id ] : 0;
		}

		// Final PHP sort and trim to $total_groups.
		// 'top rated'    : primary avg_rating DESC, secondary review_count DESC.
		// 'most reviewed': primary review_count DESC, secondary avg_rating DESC.
		// 'members'      : SQL already ordered by member_count — no re-sort needed.
		if ( 'top rated' === $type ) {
			usort(
				$groups,
				function ( $a, $b ) {
					if ( $b->avg_rating !== $a->avg_rating ) {
						return $b->avg_rating <=> $a->avg_rating;
					}
					return (int) $b->review_count <=> (int) $a->review_count;
				}
			);
		} elseif ( 'most reviewed' === $type ) {
			usort(
				$groups,
				function ( $a, $b ) {
					if ( (int) $b->review_count !== (int) $a->review_count ) {
						return (int) $b->review_count <=> (int) $a->review_count;
					}
					return $b->avg_rating <=> $a->avg_rating;
				}
			);
		}
		$groups = array_slice( $groups, 0, $total_groups );

		// --- Build HTML output ---
		$output  = '<div class="bgr-group-leaderboard">';
		$output .= '<div class="bgr-leaderboard-header">';
		if ( ! empty( $title ) ) {
			$output .= '<h2>' . esc_html( $title ) . '</h2>';
		}
		$output .= '</div>';
		$output .= '<div class="bgr-leaderboard-list">';

		foreach ( $groups as $group ) {
			$group_url    = bgr_get_group_url( $group );
			$group_avatar = bp_core_fetch_avatar(
				array(
					'item_id'    => $group->id,
					'object'     => 'group',
					'type'       => 'thumb',
					'avatar_dir' => 'group-avatars',
					'width'      => 50,
					'height'     => 50,
					'alt'        => $group->name,
				)
			);

			$output .= '<div class="bgr-leaderboard-item">';

			if ( $show_avatar ) {
				$output .= '<div class="bgr-item-avatar">';
				$output .= '<a href="' . esc_url( $group_url ) . '">' . $group_avatar . '</a>';
				$output .= '</div>';
			}

			$output .= '<div class="bgr-item-info">';
			$output .= '<div class="bgr-item-name">';
			$output .= '<a href="' . esc_url( $group_url ) . '">' . esc_html( $group->name ) . '</a>';
			$output .= '</div>';

			if ( $show_rating ) {
				$rating     = $group->avg_rating;
				$stars_on   = intval( $rating );
				$remaining  = $rating - $stars_on;
				$stars_half = ( $remaining > 0 ) ? 1 : 0;
				$stars_off  = 5 - ( $stars_on + $stars_half );

				$output .= '<div class="bgr-item-stats">';
				$output .= '<div class="bgr-rating"><span class="bgr-stars">';
				for ( $i = 1; $i <= $stars_on; $i++ ) {
					$output .= '<span class="fas fa-star bgr-star-rate"></span>';
				}
				if ( $stars_half ) {
					$output .= '<span class="fas fa-star-half-alt bgr-star-rate"></span>';
				}
				for ( $i = 1; $i <= $stars_off; $i++ ) {
					$output .= '<span class="far fa-star bgr-star-rate"></span>';
				}
				$output .= '</span>';
				$output .= '<span class="bgr-rating-number">' . esc_html( round( $rating, 2 ) ) . '</span>';
				$output .= '</div>';

				$output .= '<div class="bgr-members">';
				$output .= '<span class="bgr-member-label">' . esc_html( bgr_review_count_label( (int) $group->review_count ) ) . '</span>';
				$output .= '</div>';
				$output .= '</div>'; // End bgr-item-stats
			}

			if ( $group->member_count > 0 ) {
				$output .= '<div class="bgr-item-members">';
				$output .= '<span class="bgr-members-label">' . esc_html__( 'Members:', 'bp-group-reviews' ) . '</span> ';
				$output .= '<span class="bgr-members-count">' . esc_html( $group->member_count ) . '</span>';
				$output .= '</div>';
			}

			$output .= '</div>'; // End bgr-item-info
			$output .= '</div>'; // End bgr-leaderboard-item
		}

		$output .= '</div>'; // End bgr-leaderboard-list
		$output .= '</div>'; // End bgr-group-leaderboard

		return $output;
	}
	}

new BGR_Shortcodes();
}
