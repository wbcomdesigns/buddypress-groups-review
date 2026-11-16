<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- Legacy file name.
/**
 * Class to serve AJAX Calls.
 *
 * @since   1.0.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'BGR_AJAX' ) ) {
	/**
	 * Class to serve AJAX Calls.
	 *
	 * @since   1.0.0
	 * @author  Wbcom Designs
	 *
	 * @package    BuddyPress_Group_Review
	 * @subpackage BuddyPress_Group_Review/includes
	 */
	class BGR_AJAX {

		/**
		 * Constructor for Group Reviews ajax
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */
		public function __construct() {

			// Admin-only AJAX handlers - no nopriv hooks needed as these require manage_options capability.
			add_action( 'wp_ajax_bp_group_review_save_admin_criteria_settings', array( $this, 'bp_group_review_save_admin_criteria_settings' ) );
			add_action( 'wp_ajax_bp_group_review_save_admin_display_settings', array( $this, 'bp_group_review_save_admin_display_settings' ) );
			add_action( 'wp_ajax_bp_group_review_save_admin_general_settings', array( $this, 'bp_group_review_save_admin_general_settings' ) );
			// Accept/deny/remove/submit all require a logged-in user (group admin
			// caps for the first three, group membership for submit). No nopriv
			// hooks - logged-out requests must not reach these endpoints.
			add_action( 'wp_ajax_bp_group_review_accept_review', array( $this, 'bp_group_review_accept_review' ) );
			add_action( 'wp_ajax_bp_group_review_deny_review', array( $this, 'bp_group_review_deny_review' ) );
			add_action( 'wp_ajax_bp_group_remove_review', array( $this, 'bp_group_remove_review' ) );
			add_action( 'wp_ajax_bp_group_submit_review', array( $this, 'bp_group_submit_review' ) );

			/* add action for approving reviews - admin-only, no nopriv hook needed */
			add_action( 'wp_ajax_bp_group_review_admin_approve_review', array( $this, 'bp_group_review_admin_approve_review' ) );
			/* add action for denying reviews from admin */
			add_action( 'wp_ajax_bp_group_review_admin_deny_review', array( $this, 'bp_group_review_admin_deny_review' ) );
			// Filter widget ratings.
			add_action( 'wp_ajax_bp_group_review_filter_ratings', array( $this, 'bp_group_review_filter_ratings' ) );
			add_action( 'wp_ajax_nopriv_bp_group_review_filter_ratings', array( $this, 'bp_group_review_filter_ratings' ) );
		}

		/**
		 * Actions performed to filter member ratings.
		 *
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bp_group_review_filter_ratings() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
				wp_send_json_error( new WP_Error( '001', esc_html__( 'Security check failed.', 'bp-group-reviews' ), 'nonce_error' ) );
			}
			if ( filter_input( INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS ) && filter_input( INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS ) === 'bp_group_review_filter_ratings' ) {
				global $bp, $post;
				global $bgr;
				$filter               = sanitize_text_field( filter_input( INPUT_POST, 'filter' ) );
				$limit                = (int) sanitize_text_field( filter_input( INPUT_POST, 'limit' ) );
				$group_id             = absint( filter_input( INPUT_POST, 'group_id' ) );
				// Open to logged-out visitors for public groups; private and hidden groups stay private.
				if ( ! bgr_can_view_group_reviews( $group_id ) ) {
					wp_send_json_error( new WP_Error( '403', esc_html__( 'Sorry, you cannot view this group\'s reviews.', 'bp-group-reviews' ), 'forbidden' ) );
				}
				$html                 = '';
				$review_rating_fields = $bgr['review_rating_fields'];

				/**
				 * This widget renders only the top-N members by computed average
				 * rating (highest/lowest) or the latest reviewers, where N is the
				 * widget's "Number of reviews to show" (typically 5). The legacy
				 * code loaded EVERY published review for the group with
				 * posts_per_page => -1, which does not scale past a few hundred
				 * reviews. We bound the candidate pool to a filterable cap so the
				 * query is always bounded; the in-memory sort + truncate to $limit
				 * below preserves the exact display behaviour. Date-DESC ordering
				 * keeps the "latest" filter and the highest/lowest top-N stable for
				 * the realistic widget sizes this surface is built for.
				 *
				 * @since 3.9.0
				 * @param int $max_scan Maximum reviews to scan. Default 500.
				 * @param int $group_id Group whose ratings are being filtered.
				 */
				$max_scan = (int) apply_filters( 'bgr_filter_ratings_max_scan', 500, $group_id );
				if ( $max_scan < 1 ) {
					$max_scan = 500;
				}

				// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				$custom_args = array(
					'post_type'              => 'review',
					'posts_per_page'         => $max_scan,
					'paged'                  => 1,
					'post_status'            => 'publish',
					'category'               => 'review_category',
					'orderby'                => 'date',
					'order'                  => 'DESC',
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_term_cache' => false,
					'meta_key'               => 'linked_group',
					'meta_value'             => $group_id,
				);
				// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				$review_ids = get_posts( $custom_args );

				// Batch-prime post + meta caches up front so the loop below does
				// not fire a per-review meta query (N+1). Authors are primed for
				// get_avatar()/bp_core_get_userlink() to avoid per-row user reads.
				if ( ! empty( $review_ids ) ) {
					_prime_post_caches( $review_ids, false, true );
					$bgr_author_ids = array();
					foreach ( $review_ids as $bgr_review_id ) {
						$bgr_author_ids[] = (int) get_post_field( 'post_author', $bgr_review_id );
					}
					$bgr_author_ids = array_filter( array_unique( $bgr_author_ids ) );
					if ( ! empty( $bgr_author_ids ) ) {
						cache_users( $bgr_author_ids );
					}
				}

				$reviews = array_map( 'get_post', (array) $review_ids );

				$final_review_obj = array();
				$single_rev_avg   = array();
				if ( ! empty( $reviews ) ) {
					foreach ( $reviews as $review ) {
						if ( ! $review instanceof WP_Post ) {
							continue;
						}
						$review_ratings = get_post_meta( $review->ID, 'review_star_rating', false );
						if ( ! empty( $review_ratings ) && ! empty( $review_rating_fields ) ) {
							$rev_rating_array    = $review_ratings[0];
							$total_review        = 0;
							$single_review_count = 0;
							foreach ( $review_rating_fields as $rating_field ) {
								if ( array_key_exists( $rating_field, $rev_rating_array ) ) {
									$total_review += $rev_rating_array[ $rating_field ];
									++$single_review_count;
								}
							}
							if ( ! empty( $single_review_count ) ) {
								$rev_avg                         = $total_review / $single_review_count;
								$single_rev_avg[ $review->ID ]   = $rev_avg;
								$final_review_obj[ $review->ID ] = $review;
							}
						}
					}
				}
				$bgr_user_count = 0;
				if ( ! empty( $single_rev_avg ) ) {
					if ( 'highest' === $filter ) {
						arsort( $single_rev_avg );
					} elseif ( 'lowest' === $filter ) {
						asort( $single_rev_avg );
					} else {
						$single_rev_avg = $single_rev_avg;
					}
					foreach ( $single_rev_avg as $bgr_key => $bgr_value ) {
						if ( $bgr_user_count === $limit ) {
							break;
						} else {
							$html .= '<li class="vcard"><div class="item-avatar">';
							$html .= get_avatar( $final_review_obj[ $bgr_key ]->post_author, 65 );
							$html .= '</div>';
							$html .= '<div class="item">';

							$members_profile = bp_core_get_userlink( $final_review_obj[ $bgr_key ]->post_author );
							$html           .= '<div class="item-title fn">';
							$html           .= $members_profile;
							$html           .= '</div>';

							$bgr_avg_rating = $bgr_value;
							$stars_on       = '';
							$stars_off      = '';
							$stars_half     = '';
							$remaining      = $bgr_avg_rating - (int) $bgr_avg_rating;
							if ( $remaining > 0 ) {
								$stars_on       = intval( $bgr_avg_rating );
								$stars_half     = 1;
								$bgr_half_squar = 1;
								$stars_off      = 5 - ( $stars_on + $stars_half );
							} else {
								$stars_on   = $bgr_avg_rating;
								$stars_off  = 5 - $bgr_avg_rating;
								$stars_half = 0;
							}
							$html .= '<div class="item-meta">';
							for ( $i = 1; $i <= $stars_on; $i++ ) {
								$html .= '<span class="fas fa-star stars bgr-star-rate"></span>';
							}

							for ( $i = 1; $i <= $stars_half; $i++ ) {
								$html .= '<span class="fas fa-star-half-alt stars bgr-star-rate"></span>';
							}

							for ( $i = 1; $i <= $stars_off; $i++ ) {
								$html .= '<span class="far fa-star stars bgr-star-rate"></span>';
							}

							$html .= '</div>';

							$bgr_avg_rating = round( $bgr_avg_rating, 2 );
							$html          .= '<span class="bgr-meta">';
							/* translators: %1$s is replaced with $bgr_avg_rating  */
							$html .= sprintf( esc_html__( 'Rating : ( %1$s )', 'bp-group-reviews' ), esc_html( $bgr_avg_rating ) );
							$html .= '</span>';
							$html .= '</div></li>';

						}

						++$bgr_user_count;
					}
				} else {
					$html .= '<p>' . esc_html__( 'No ratings have been given by any members yet.', 'bp-group-reviews' ) . '</p>';
				}
				// `html` is the only key the existing JS consumer reads
				// (bgr-front.js: obj.html). `total`/`limit`/`scanned` are additive
				// metadata so a future paged/"load more" UI can drive prev/next
				// without another round-trip; current consumers ignore them.
				$result = array(
					'html'    => $html,
					'total'   => count( $single_rev_avg ),
					'limit'   => $limit,
					'scanned' => count( $reviews ),
				);
				echo wp_json_encode( $result );
			}
			die;
		}

		/**
		 * Actions performed to approve review at admin end
		 */
		public function bp_group_review_admin_approve_review() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'admin-ajax-nonce' ) ) {
				$error = new WP_Error( '001', esc_html__( 'Security check failed.', 'bp-group-reviews' ), 'nonce_error' );
				wp_send_json_error( $error );
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( new WP_Error( '002', esc_html__( 'Permission denied.', 'bp-group-reviews' ), 'permission_denied' ) );
			}
			if ( isset( $_POST['action'] ) && 'bp_group_review_admin_approve_review' === $_POST['action'] ) {
				$rid = isset( $_POST['review_id'] ) ? absint( $_POST['review_id'] ) : 0;
				if ( empty( $rid ) ) {
					wp_send_json_error( new WP_Error( '003', esc_html__( 'Invalid review ID.', 'bp-group-reviews' ), 'invalid_review' ) );
				}

				// Check if review post exists and is of correct type.
				$review_post = get_post( $rid );
				if ( ! $review_post || 'review' !== $review_post->post_type ) {
					wp_send_json_error( new WP_Error( '004', esc_html__( 'Review not found.', 'bp-group-reviews' ), 'review_not_found' ) );
				}

				$args = array(
					'ID'          => $rid,
					'post_status' => 'publish',
				);
				wp_update_post( $args );
				echo 'review-approved-successfully';
				die;
			}
		}

		/**
		 * Actions performed to deny review at admin end.
		 *
		 * @since 3.6.0
		 */
		public function bp_group_review_admin_deny_review() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'admin-ajax-nonce' ) ) {
				$error = new WP_Error( '001', esc_html__( 'Security check failed.', 'bp-group-reviews' ), 'nonce_error' );
				wp_send_json_error( $error );
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( new WP_Error( '002', esc_html__( 'Permission denied.', 'bp-group-reviews' ), 'permission_denied' ) );
			}
			if ( isset( $_POST['action'] ) && 'bp_group_review_admin_deny_review' === $_POST['action'] ) {
				$rid = isset( $_POST['review_id'] ) ? absint( $_POST['review_id'] ) : 0;
				if ( empty( $rid ) ) {
					wp_send_json_error( new WP_Error( '003', esc_html__( 'Invalid review ID.', 'bp-group-reviews' ), 'invalid_review' ) );
				}

				// Check if review post exists and is of correct type.
				$review_post = get_post( $rid );
				if ( ! $review_post || 'review' !== $review_post->post_type ) {
					wp_send_json_error( new WP_Error( '004', esc_html__( 'Review not found.', 'bp-group-reviews' ), 'review_not_found' ) );
				}

				wp_trash_post( $rid );
				echo 'review-denied-successfully';
				die;
			}
		}


		/**
		 *  Actions performed for saving admin criteria settings
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */
		public function bp_group_review_save_admin_criteria_settings() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'admin-ajax-nonce' ) ) {
				$error = new WP_Error( '001', 'Nonce not verified!', 'Some information' );
				wp_send_json_error( $error );
			}
			if ( isset( $_POST['action'] ) && 'bp_group_review_save_admin_criteria_settings' === $_POST['action'] && current_user_can( 'manage_options' ) ) {

				// Ensure the variables are always treated as arrays.
				$rating_fields       = isset( $_POST['field_values'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['field_values'] ) ) : array();
				$rating_field_values = array_unique( $rating_fields );

				$active_rating_fields        = isset( $_POST['active_criterias'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['active_criterias'] ) ) : array();
				$active_rating_fields_values = array_unique( $active_rating_fields );

				// Get old settings to compare for deleted/archived criteria.
				$old_settings = get_option( 'bgr_admin_criteria_settings', array() );
				$old_all      = isset( $old_settings['add_review_rating_fields'] ) ? $old_settings['add_review_rating_fields'] : array();
				$old_active   = isset( $old_settings['active_rating_fields'] ) ? $old_settings['active_rating_fields'] : array();

				$bgr_admin_settings = array(
					'add_review_rating_fields' => $rating_field_values,
					'active_rating_fields'     => $active_rating_fields_values,
				);

				update_option( 'bgr_admin_criteria_settings', $bgr_admin_settings );

				// Fire hooks for deleted/archived criteria so groups can sync.
				$deleted_criteria  = array_diff( $old_all, $rating_field_values );
				$archived_criteria = array_diff( $old_active, $active_rating_fields_values );

				foreach ( $deleted_criteria as $deleted ) {
					/**
					 * Fires when a global criterion is deleted.
					 *
					 * @since 3.7.0
					 * @param string $deleted The deleted criterion name.
					 */
					do_action( 'bgr_global_criteria_deleted', $deleted );
				}

				foreach ( $archived_criteria as $archived ) {
					// Only fire if it wasn't deleted (just deactivated).
					if ( in_array( $archived, $rating_field_values, true ) ) {
						/**
						 * Fires when a global criterion is archived (deactivated but not deleted).
						 *
						 * @since 3.7.0
						 * @param string $archived The archived criterion name.
						 */
						do_action( 'bgr_global_criteria_archived', $archived );
					}
				}

				echo 'admin-criteria-settings-saved';
				die;
			}
		}

		/**
		 *  Actions performed for saving admin general settings
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */
		public function bp_group_review_save_admin_general_settings() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'admin-ajax-nonce' ) ) {
				$error = new WP_Error( '001', 'Nonce not verified!', 'Some information' );
				wp_send_json_error( $error );
			}
			if ( isset( $_POST['action'] ) && 'bp_group_review_save_admin_general_settings' === $_POST['action'] && current_user_can( 'manage_options' ) ) {
				$multi_reviews         = isset( $_POST['multi_reviews'] ) ? sanitize_text_field( wp_unslash( $_POST['multi_reviews'] ) ) : '';
				$auto_approve_reviews  = isset( $_POST['bgr_auto_approve_reviews'] ) ? sanitize_text_field( wp_unslash( $_POST['bgr_auto_approve_reviews'] ) ) : '';
				$reviews_per_page      = isset( $_POST['reviews_per_page'] ) ? sanitize_text_field( wp_unslash( $_POST['reviews_per_page'] ) ) : '';
				$allow_notification    = isset( $_POST['allow_notification'] ) ? sanitize_text_field( wp_unslash( $_POST['allow_notification'] ) ) : '';
				$allow_activity        = isset( $_POST['allow_activity'] ) ? sanitize_text_field( wp_unslash( $_POST['allow_activity'] ) ) : '';
				$enable_group_criteria = isset( $_POST['enable_group_criteria'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_group_criteria'] ) ) : 'no';
				$exclude_groups        = isset( $_POST['exclude_groups'] ) ? array_map( 'absint', wp_unslash( $_POST['exclude_groups'] ) ) : array();
				// Filter out any zero values that may result from invalid input.
				$exclude_groups     = array_filter( $exclude_groups );
				$bgr_admin_settings = array(
					'multi_reviews'         => $multi_reviews,
					'auto_approve_reviews'  => $auto_approve_reviews,
					'reviews_per_page'      => $reviews_per_page,
					'allow_notification'    => $allow_notification,
					'allow_activity'        => $allow_activity,
					'enable_group_criteria' => $enable_group_criteria,
					'exclude_groups'        => $exclude_groups,
				);
				update_option( 'bgr_admin_general_settings', $bgr_admin_settings );
				echo 'admin-general-settings-saved';
				wp_die();
			}
		}

		/**
		 *  Actions performed for saving admin display settings
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */
		public function bp_group_review_save_admin_display_settings() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'admin-ajax-nonce' ) ) {
				$error = new WP_Error( '001', 'Nonce not verified!', 'Some information' );
				wp_send_json_error( $error );
			}
			if ( isset( $_POST['action'] ) && 'bp_group_review_save_admin_display_settings' === $_POST['action'] && current_user_can( 'manage_options' ) ) {

				$manage_review_label = isset( $_POST['manage_review_label'] ) ? sanitize_text_field( wp_unslash( $_POST['manage_review_label'] ) ) : '';
				$review_label        = isset( $_POST['review_label'] ) ? sanitize_text_field( wp_unslash( $_POST['review_label'] ) ) : '';
				$bgr_rating_color    = isset( $_POST['bgr_rating_color'] ) ? sanitize_text_field( wp_unslash( $_POST['bgr_rating_color'] ) ) : '';

				/*
				 * Only a genuine rename is persisted. If the admin left the box empty,
				 * or typed back exactly what the default already says in their own
				 * language, store an empty string so the label keeps following the
				 * site language instead of being frozen to this admin's locale.
				 */
				if ( trim( $review_label ) === trim( esc_html__( 'Review', 'bp-group-reviews' ) ) ) {
					$review_label = '';
				}
				if ( trim( $manage_review_label ) === trim( esc_html__( 'Reviews', 'bp-group-reviews' ) ) ) {
					$manage_review_label = '';
				}

				$bgr_admin_settings = array(
					'review_label'        => $review_label,
					'manage_review_label' => $manage_review_label,
					'bgr_rating_color'    => $bgr_rating_color,
				);
				update_option( 'bgr_admin_display_settings', $bgr_admin_settings );
				echo 'admin-display-settings-saved';
				die;
			}
		}

		/**
		 *  Actions performed when submit review
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */
		public function bp_group_submit_review() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
				$error = new WP_Error( '001', esc_html__( 'Security check failed.', 'bp-group-reviews' ), 'nonce_error' );
				wp_send_json_error( $error );
			}
			if ( ! is_user_logged_in() ) {
				wp_send_json_error( new WP_Error( '002', esc_html__( 'You must be logged in to submit a review.', 'bp-group-reviews' ), 'login_required' ) );
			}
			global $bp;
			global $bgr;

			// Parse form data first to get group_id for validation.
			if ( isset( $_POST['data'] ) ) {
				wp_parse_str( wp_unslash( filter_input( INPUT_POST, 'data', FILTER_UNSAFE_RAW ) ), $formarray );
			} else {
				wp_send_json_error( new WP_Error( '003', esc_html__( 'Invalid form data.', 'bp-group-reviews' ), 'invalid_data' ) );
			}

			$review_subject = isset( $formarray['review-subject'] ) ? sanitize_text_field( $formarray['review-subject'] ) : '';
			$review_desc    = isset( $formarray['review-desc'] ) ? sanitize_text_field( $formarray['review-desc'] ) : '';
			$form_group_id  = isset( $formarray['form-group-id'] ) ? absint( $formarray['form-group-id'] ) : 0;

			// Validate group exists.
			if ( empty( $form_group_id ) ) {
				wp_send_json_error( new WP_Error( '005', esc_html__( 'Invalid group.', 'bp-group-reviews' ), 'invalid_group' ) );
			}

			$group_obj = groups_get_group( $form_group_id );

			// Check if group exists.
			if ( empty( $group_obj ) || empty( $group_obj->id ) ) {
				wp_send_json_error( new WP_Error( '006', esc_html__( 'Group not found.', 'bp-group-reviews' ), 'group_not_found' ) );
			}

			$current_user = wp_get_current_user();
			$member_id    = $current_user->ID;

			// Check if user is group admin (admins cannot review their own groups).
			if ( groups_is_user_admin( $member_id, $form_group_id ) ) {
				wp_send_json_error( new WP_Error( '007', esc_html__( 'Group administrators cannot review their own groups.', 'bp-group-reviews' ), 'admin_cannot_review' ) );
			}

			// Check if user is a member of the group.
			if ( ! groups_is_user_member( $member_id, $form_group_id ) ) {
				wp_send_json_error( new WP_Error( '009', esc_html__( 'You must be a member of this group to submit a review.', 'bp-group-reviews' ), 'not_member' ) );
			}

			// Check if group is excluded from reviews.
			$bgr_admin_settings   = get_option( 'bgr_admin_general_settings', array() );
			$admin_exclude_groups = isset( $bgr_admin_settings['exclude_groups'] ) ? array_map( 'absint', (array) $bgr_admin_settings['exclude_groups'] ) : array();
			if ( ! empty( $admin_exclude_groups ) && in_array( absint( $form_group_id ), $admin_exclude_groups, true ) ) {
				wp_send_json_error( new WP_Error( '008', esc_html__( 'Reviews are not enabled for this group.', 'bp-group-reviews' ), 'group_excluded' ) );
			}

			$user_name                      = $current_user->display_name;
			// Use group-level criteria if available, otherwise fall back to global.
			$active_rating_fields = function_exists( 'bgr_get_effective_criteria' )
				? bgr_get_effective_criteria( $form_group_id )
				: $bgr['active_rating_fields'];
			$review_label         = $bgr['review_label'];
			$auto_approve_reviews = $bgr['auto_approve_reviews'];
			$multi_reviews        = $bgr['multi_reviews'];
			$group_name           = $group_obj->name;
			/* Translators: %1$s: User Name %2$s Plural review label, e.g. "Reviews" %3$s Group Name */
			$review_cpt_subject = sprintf( esc_html__( '%1$s %2$s Group %3$s.', 'bp-group-reviews' ), $user_name, bp_group_review_tab_name(), $group_name );

			// Star inputs are keyed by criterion name, so a rating can never land on
			// another criterion. A form rendered before an admin changed the criteria
			// misses a key: reject it rather than store a partial or shifted set.
			$posted_stars = isset( $formarray['rated_stars'] ) ? (array) wp_unslash( $formarray['rated_stars'] ) : array();
			$rated_stars  = array();
			if ( ! empty( $active_rating_fields ) ) {
				foreach ( $active_rating_fields as $rating_field ) {
					$star = isset( $posted_stars[ $rating_field ] ) ? absint( $posted_stars[ $rating_field ] ) : 0;
					if ( $star < 1 || $star > 5 ) {
						wp_send_json_error( new WP_Error( '012', esc_html__( 'The rating criteria have changed. Please reload the page and submit your review again.', 'bp-group-reviews' ), 'criteria_changed' ) );
					}
					$rated_stars[ $rating_field ] = $star;
				}
			} elseif ( ! empty( $posted_stars ) ) {
				// No criteria configured: the form posts a single overall rating.
				$rated_stars = array( min( 5, max( 1, absint( reset( $posted_stars ) ) ) ) );
			}

			// Check for existing reviews FIRST (before acquiring lock) to prevent race conditions.
			$lock_key        = 'bgr_review_lock_' . $member_id . '_' . $form_group_id;
			$user_post_count = 0;

			if ( 'no' === $multi_reviews ) {
				$group_args      = array(
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
							'value'   => $form_group_id,
							'compare' => '=',
						),
					),
				);
				$reviews_args    = new WP_Query( $group_args );
				$user_post_count = $reviews_args->post_count;

				// Check if user already has a review.
				if ( $user_post_count > 0 ) {
					/* translators: %1$s is replaced with review_label */
					$review_add_msg = sprintf( __( 'You have already submitted a %1$s for this group.', 'bp-group-reviews' ), strtolower( $review_label ) );
					echo esc_html( $review_add_msg );
					die;
				}

				// Check if a lock already exists (another request is processing).
				if ( get_transient( $lock_key ) ) {
					wp_send_json_error( new WP_Error( '010', esc_html__( 'Please wait, your review is being processed.', 'bp-group-reviews' ), 'review_in_progress' ) );
				}

				// Set the lock AFTER checking for duplicates (30 second expiration).
				set_transient( $lock_key, true, 30 );
			}

			if ( $user_post_count <= 0 ) {
				if ( 'yes' === $auto_approve_reviews ) {
					/* translators: %1$s is replaced with review_label */
					$review_add_msg = sprintf( __( 'Thank you for sharing your %1$s!', 'bp-group-reviews' ), strtolower( $review_label ) );
					$review_status  = 'publish';
				} else {
					/* translators: %1$s is replaced with review_label */
					$review_add_msg = sprintf( __( 'Thank you for your %1$s! It will be published after the group admin approves it.', 'bp-group-reviews' ), strtolower( $review_label ) );
					$review_status  = 'draft';
				}

				$add_review_args = array(
					'post_type'    => 'review',
					'post_title'   => $review_cpt_subject,
					'post_content' => $review_desc,
					'post_status'  => $review_status,
				);
				$review_id       = wp_insert_post( $add_review_args );

				// Check if post creation failed.
				if ( is_wp_error( $review_id ) || 0 === $review_id ) {
					delete_transient( $lock_key );
					wp_send_json_error( new WP_Error( '011', esc_html__( 'Failed to create review. Please try again.', 'bp-group-reviews' ), 'insert_failed' ) );
				}

				update_post_meta( $review_id, 'linked_group', $form_group_id );
				if ( ! empty( $rated_stars ) ) {
					update_post_meta( $review_id, 'review_star_rating', $rated_stars );
				}

				// Term, GamiPress, admin notification and email: bgr_handle_review_submitted().
				do_action( 'bgr_group_after_review_submit', $member_id, $form_group_id, $review_id );

				// Delete the lock after review is successfully created.
				delete_transient( $lock_key );
			}
			echo esc_html( $review_add_msg );
			die;
		}

		/**
		 *  Actions performed when accept review
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */
		public function bp_group_review_accept_review() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
				$error = new WP_Error( '001', esc_html__( 'Security check failed.', 'bp-group-reviews' ), 'nonce_error' );
				wp_send_json_error( $error );
			}
			if ( ! is_user_logged_in() ) {
				wp_send_json_error( new WP_Error( '002', esc_html__( 'You must be logged in.', 'bp-group-reviews' ), 'login_required' ) );
			}

			// Validate and sanitize review ID.
			$post_id = isset( $_POST['accept_review_id'] ) ? absint( $_POST['accept_review_id'] ) : 0;
			if ( empty( $post_id ) ) {
				wp_send_json_error( new WP_Error( '003', esc_html__( 'Invalid review ID.', 'bp-group-reviews' ), 'invalid_review' ) );
			}

			// Check if review post exists and is of correct type.
			$review_post = get_post( $post_id );
			if ( ! $review_post || 'review' !== $review_post->post_type ) {
				wp_send_json_error( new WP_Error( '004', esc_html__( 'Review not found.', 'bp-group-reviews' ), 'review_not_found' ) );
			}

			// Get the group ID for this review.
			$group_id = get_post_meta( $post_id, 'linked_group', true );
			if ( empty( $group_id ) ) {
				wp_send_json_error( new WP_Error( '005', esc_html__( 'Invalid group for this review.', 'bp-group-reviews' ), 'invalid_group' ) );
			}

			// Check if current user is a group admin for this group.
			$current_user_id = get_current_user_id();
			if ( ! groups_is_user_admin( $current_user_id, $group_id ) && ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( new WP_Error( '006', esc_html__( 'You do not have permission to manage reviews for this group.', 'bp-group-reviews' ), 'permission_denied' ) );
			}

			// Email, notification and GamiPress run on the status change: bgr_handle_review_decision().
			wp_publish_post( $post_id );
			wp_die();
		}

		/**
		 *  Actions performed when deny review
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */
		public function bp_group_review_deny_review() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
				$error = new WP_Error( '001', esc_html__( 'Security check failed.', 'bp-group-reviews' ), 'nonce_error' );
				wp_send_json_error( $error );
			}
			if ( ! is_user_logged_in() ) {
				wp_send_json_error( new WP_Error( '002', esc_html__( 'You must be logged in.', 'bp-group-reviews' ), 'login_required' ) );
			}

			// Validate and sanitize review ID.
			$post_id = isset( $_POST['deny_review_id'] ) ? absint( $_POST['deny_review_id'] ) : 0;
			if ( empty( $post_id ) ) {
				wp_send_json_error( new WP_Error( '003', esc_html__( 'Invalid review ID.', 'bp-group-reviews' ), 'invalid_review' ) );
			}

			// Check if review post exists and is of correct type.
			$review_post = get_post( $post_id );
			if ( ! $review_post || 'review' !== $review_post->post_type ) {
				wp_send_json_error( new WP_Error( '004', esc_html__( 'Review not found.', 'bp-group-reviews' ), 'review_not_found' ) );
			}

			// Get the group ID for this review.
			$group_id = get_post_meta( $post_id, 'linked_group', true );
			if ( empty( $group_id ) ) {
				wp_send_json_error( new WP_Error( '005', esc_html__( 'Invalid group for this review.', 'bp-group-reviews' ), 'invalid_group' ) );
			}

			// Check if current user is a group admin for this group.
			$current_user_id = get_current_user_id();
			if ( ! groups_is_user_admin( $current_user_id, $group_id ) && ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( new WP_Error( '006', esc_html__( 'You do not have permission to manage reviews for this group.', 'bp-group-reviews' ), 'permission_denied' ) );
			}

			// Email and notification run on the status change: bgr_handle_review_decision().
			wp_trash_post( $post_id );
			die;
		}

		/**
		 *  Actions performed when remove review
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */
		public function bp_group_remove_review() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
				$error = new WP_Error( '001', esc_html__( 'Security check failed.', 'bp-group-reviews' ), 'nonce_error' );
				wp_send_json_error( $error );
			}
			if ( ! is_user_logged_in() ) {
				wp_send_json_error( new WP_Error( '002', esc_html__( 'You must be logged in.', 'bp-group-reviews' ), 'login_required' ) );
			}

			// Validate and sanitize review ID.
			$post_id = isset( $_POST['remove_review_id'] ) ? absint( $_POST['remove_review_id'] ) : 0;
			if ( empty( $post_id ) ) {
				wp_send_json_error( new WP_Error( '003', esc_html__( 'Invalid review ID.', 'bp-group-reviews' ), 'invalid_review' ) );
			}

			// Check if review post exists and is of correct type.
			$review_post = get_post( $post_id );
			if ( ! $review_post || 'review' !== $review_post->post_type ) {
				wp_send_json_error( new WP_Error( '004', esc_html__( 'Review not found.', 'bp-group-reviews' ), 'review_not_found' ) );
			}

			// Get the group ID for this review.
			$group_id = get_post_meta( $post_id, 'linked_group', true );
			if ( empty( $group_id ) ) {
				wp_send_json_error( new WP_Error( '005', esc_html__( 'Invalid group for this review.', 'bp-group-reviews' ), 'invalid_group' ) );
			}

			// Check if current user is a group admin for this group.
			$current_user_id = get_current_user_id();
			if ( ! groups_is_user_admin( $current_user_id, $group_id ) && ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( new WP_Error( '006', esc_html__( 'You do not have permission to remove reviews for this group.', 'bp-group-reviews' ), 'permission_denied' ) );
			}

			wp_trash_post( $post_id );
			echo 'review-removed-successfully';
			die;
		}
	}
	new BGR_AJAX();
}
