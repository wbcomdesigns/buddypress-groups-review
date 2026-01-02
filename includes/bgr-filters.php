<?php
/**
 * Group Review Plugin Global Variables
 *
 * @since   1.0.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class to add custom hooks for this plugin
 */
if ( ! class_exists( 'BGR_Custom_Hooks' ) ) {

	/**
	 * Group Review Plugin Global Variables
	 *
	 * @since   1.0.0
	 * @author  Wbcom Designs
	 *
	 * @package    BuddyPress_Group_Review
	 * @subpackage BuddyPress_Group_Review/includes
	 */
	class BGR_Custom_Hooks {

		/**
		 * Constructor for custom hooks
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'wp', array( $this, 'bp_group_review_add_group_reviews_tab' ) );
			add_action( 'init', array( $this, 'bp_group_review_add_taxonomy_term' ) );
			add_filter( 'post_row_actions', array( $this, 'bp_group_review_row_actions' ), 10, 2 );
			add_filter( 'bulk_actions-edit-review', array( $this, 'bp_group_review_bulk_actions' ), 10, 1 );
			add_filter( 'handle_bulk_actions-edit-review', array( $this, 'bp_group_review_handle_bulk_actions' ), 10, 3 );
			add_action( 'admin_notices', array( $this, 'bp_group_review_bulk_action_notices' ) );

			// CSV Export functionality.
			add_action( 'restrict_manage_posts', array( $this, 'bp_group_review_export_button' ) );
			add_action( 'admin_init', array( $this, 'bp_group_review_handle_csv_export' ) );

			// Add group average rating before group header meta.
			add_action( 'bp_before_group_header_meta', array( $this, 'bp_group_review_group_average_rating' ) );

			// Check for BuddyBoss or BuddyPress and add the review button accordingly.
			$active_plugins = get_option( 'active_plugins' );
			if ( in_array( 'buddyboss-platform/bp-loader.php', $active_plugins, true ) ) {
				add_action( 'bb_group_single_top_header_action', array( $this, 'bp_group_review_group_header_review_btn' ) );
			} elseif ( in_array( 'buddypress/bp-loader.php', $active_plugins, true ) && ! defined( 'YOUZIFY_VERSION' ) ) {
					add_action( 'bp_group_header_actions', array( $this, 'bp_group_review_group_header_review_btn' ) );
			}

			// Add rating to group directory.
			add_action( 'bp_directory_groups_item', array( $this, 'bp_group_review_group_directory_rating' ) );

			// Add Youzify integration (check using YOUZIFY_VERSION constant).
			if ( defined( 'YOUZIFY_VERSION' ) ) {
				add_action( 'youzify_before_group_header_meta', array( $this, 'bp_group_review_group_average_rating' ) );
				add_action( 'youzify_after_group_header_meta', array( $this, 'bp_group_review_group_header_review_btn' ) );
			}

			/**
			 * GamiPress integration
			 */
			if ( in_array( 'gamipress/gamipress.php', $active_plugins, true ) ) {
				add_filter( 'gamipress_activity_triggers', array( $this, 'bp_group_review_bp_activity_triggers' ) );
				add_filter( 'gamipress_trigger_get_user_id', array( $this, 'bp_group_review_trigger_get_user_id' ), 10, 3 );
			}

			add_filter( 'bp_nouveau_nav_has_count', array( $this, 'bp_group_review_nav_has_count' ), 10, 3 );
			add_filter( 'bp_nouveau_get_nav_count', array( $this, 'bp_group_review_get_nav_count' ), 10, 3 );

			// Cache invalidation hooks.
			add_action( 'save_post_review', array( $this, 'invalidate_cache_on_review_save' ), 10, 2 );
			add_action( 'bgr_group_accept_review', array( $this, 'invalidate_cache_on_review_action' ), 10, 1 );
			add_action( 'bgr_group_deny_review', array( $this, 'invalidate_cache_on_review_action' ), 10, 1 );
			add_action( 'before_delete_post', array( $this, 'invalidate_cache_on_review_action' ), 10, 1 );
		}

		/**
		 * Review Tab has a count attribute.
		 *
		 * @since  3.2.3
		 *
		 * @param bool   $count     True if the nav has a count attribute. False otherwise.
		 * @param object $nav_item  The current nav item object.
		 * @param string $component The current nav in use (eg: 'directory', 'groups', 'personal', etc.).
		 * @return boolean
		 */
		public function bp_group_review_nav_has_count( $count, $nav_item, $component ) {

			if ( 'groups' !== $component ) {
				return $count;
			}

			global $bgr;

			if ( sanitize_title( $bgr['manage_review_label'] ) === $nav_item->slug ) {
				$count = true;
			}

			return apply_filters( 'bp_group_review_nav_has_count', $count );
		}

		/**
		 * Retrieve the count attribute for the review nav item.
		 *
		 * @since  3.2.3
		 *
		 * @param int    $count     The count attribute for the nav item.
		 * @param object $nav_item  The current nav item object.
		 * @param string $component The current nav in use (eg: 'directory', 'groups', 'personal', etc.).
		 * @return int The count attribute for the nav item.
		 */
		public function bp_group_review_get_nav_count( $count, $nav_item, $component ) {
			if ( 'groups' !== $component ) {
				return $count;
			}

			global $bgr;

			if ( sanitize_title( $bgr['manage_review_label'] ) === $nav_item->slug ) {
				$count = $this->get_group_review_count( bp_get_group_id() );
			}

			return apply_filters( 'buddypress_groups_review_nav_count', $count );
		}

		/**
		 * Get the published review count for a group with transient caching.
		 *
		 * @since  3.6.0
		 *
		 * @param int $group_id The group ID.
		 * @return int The number of published reviews.
		 */
		public function get_group_review_count( $group_id ) {
			$group_id      = absint( $group_id );
			$transient_key = 'bgr_review_count_' . $group_id;

			// Try to get cached count.
			$count = get_transient( $transient_key );

			if ( false === $count ) {
				// Cache miss - query the database.
				global $wpdb;

				// Use direct query for better performance (avoids loading full post objects).
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Caching via transient.
				$count = (int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(p.ID) FROM {$wpdb->posts} p
						INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
						WHERE p.post_type = 'review'
						AND p.post_status = 'publish'
						AND pm.meta_key = 'linked_group'
						AND pm.meta_value = %d",
						$group_id
					)
				);

				// Cache for 1 hour (will be invalidated when reviews change).
				set_transient( $transient_key, $count, HOUR_IN_SECONDS );
			}

			return (int) $count;
		}

		/**
		 * Invalidate the review count cache for a group.
		 *
		 * @since  3.6.0
		 *
		 * @param int $group_id The group ID.
		 */
		public static function invalidate_group_review_cache( $group_id ) {
			$group_id = absint( $group_id );
			delete_transient( 'bgr_review_count_' . $group_id );
		}

		/**
		 * Invalidate cache when a review is saved.
		 *
		 * @since  3.6.0
		 *
		 * @param int     $post_id Post ID.
		 * @param WP_Post $post    Post object.
		 */
		public function invalidate_cache_on_review_save( $post_id, $post ) {
			if ( 'review' !== $post->post_type ) {
				return;
			}

			$group_id = get_post_meta( $post_id, 'linked_group', true );
			if ( $group_id ) {
				self::invalidate_group_review_cache( $group_id );
			}
		}

		/**
		 * Invalidate cache when a review is approved, denied, or deleted.
		 *
		 * @since  3.6.0
		 *
		 * @param int $post_id Post ID.
		 */
		public function invalidate_cache_on_review_action( $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post || 'review' !== $post->post_type ) {
				return;
			}

			$group_id = get_post_meta( $post_id, 'linked_group', true );
			if ( $group_id ) {
				self::invalidate_group_review_cache( $group_id );
			}
		}

		/**
		 *  Actions performed to show review button on group header
		 *
		 *  @since   1.0.0
		 *  @access public
		 *  @author  Wbcom Designs
		 */
		public function bp_group_review_group_header_review_btn() {
			if ( ! is_user_logged_in() ) {
				return false;
			}
			global $bgr;
			$review_div       = 'form';
			$exclude_groups   = isset( $bgr['exclude_groups'] ) ? array_map( 'absint', (array) $bgr['exclude_groups'] ) : array();
			$current_group_id = absint( bp_get_current_group_id() );
			$current_user_id  = bp_loggedin_user_id();

			// Check if current group is excluded from reviews.
			if ( ! empty( $exclude_groups ) && in_array( $current_group_id, $exclude_groups, true ) ) {
				return;
			}

			// Group admins should not see the "Add Review" button on their own groups.
			if ( groups_is_user_admin( $current_user_id, $current_group_id ) ) {
				return;
			}

			// Non-members should not see the "Add Review" button.
			if ( ! groups_is_user_member( $current_user_id, $current_group_id ) ) {
				return;
			}

			// Build group URL safely for both BuddyPress and BuddyBoss.
			$current_group = groups_get_group( array( 'group_id' => $current_group_id ) );
			if ( function_exists( 'bp_get_group_url' ) ) {
				$current_group_link = bp_get_group_url( $current_group );
			} else {
				// Fallback for older versions - build URL manually.
				$current_group_link = bp_get_groups_directory_permalink() . $current_group->slug . '/';
			}
			$current_group_link .= 'add-' . bp_group_review_tab_slug();
			?>
			<div class="group-button group-actions-absolute public generic-button" id="add-review-groupbutton">
				<a href='<?php echo esc_url( $current_group_link ); ?>' class="group-button button" show ="<?php echo esc_attr( $review_div ); ?>">
					<?php
					/* translators: %1$s is replaced with bp_group_review_add_review_tab_name() */
					printf( esc_html__( 'Add %1$s', 'bp-group-reviews' ), esc_html( bp_group_review_add_review_tab_name() ) );
					?>
				</a>
			</div>
			<?php
		}

		/**
		 *  Actions performed to add ratings in group directory page
		 *
		 *  @since   1.0.0
		 *  @access public
		 *  @author  Wbcom Designs
		 */
		public function bp_group_review_group_directory_rating() {
			global $bgr;
			global $bp;
			$args                 = array(
				'post_type'      => 'review',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'category'       => 'group',
				'meta_query'     => array(
					array(
						'key'     => 'linked_group',
						'value'   => bp_get_group_id(),
						'compare' => '=',
					),
				),
			);
			$reviews              = get_posts( $args );
			$review_rating_fields = $bgr['review_rating_fields'];
			$review_label         = bp_group_review_tab_name();
			$exclude_groups       = isset( $bgr['exclude_groups'] ) ? array_map( 'absint', (array) $bgr['exclude_groups'] ) : array();
			$current_group_id     = absint( bp_get_group_id() );
			if ( ! empty( $exclude_groups ) ) {
				if ( ! in_array( $current_group_id, $exclude_groups, true ) ) {
					$this->bp_group_review_single_group_average_rating_data( $reviews, $review_rating_fields, $review_label );
				}
			} else {
				$this->bp_group_review_single_group_average_rating_data( $reviews, $review_rating_fields, $review_label );
			}
		}


		/**
		 * Actions performed to show rating on groups directing page.
		 *
		 * @since  1.0.0
		 * @access public
		 * @author Wbcom Designs
		 *
		 * @param array  $reviews              Array of review posts.
		 * @param array  $review_rating_fields Array of rating field names.
		 * @param string $review_label         Label for reviews (unused but kept for compatibility).
		 * @return void
		 */
		public function bp_group_review_single_group_average_rating_data( $reviews, $review_rating_fields, $review_label ) {
			if ( ! empty( $review_rating_fields ) ) {
				if ( ! empty( $reviews ) ) {
					$ttl_rating    = 0;
					$reviews_count = 0;
					foreach ( $reviews as $review ) {
						$rate           = 0;
						$review_ratings = get_post_meta( $review->ID, 'review_star_rating', false );

						$reviews_field_count = 0;
						// Ensure review_ratings[0] is an array before iterating.
						if ( ! empty( $review_rating_fields ) && ! empty( $review_ratings[0] ) && is_array( $review_ratings[0] ) ) :
							foreach ( $review_ratings[0] as $field => $value ) {
								// Add all rating values regardless of field name match.
								$rate += (int) $value;
								++$reviews_field_count;
							}
							if ( $reviews_field_count > 0 ) {
								++$reviews_count;
								$ttl_rating += (int) $rate / $reviews_field_count;
							}
						endif;
					}

					$reviews_count = ( 0 === $reviews_count ) ? 1 : $reviews_count;
					$avg_rating    = $ttl_rating / $reviews_count;
					$avg_rating    = round( $avg_rating, 2 );
					if ( $avg_rating > 0 ) {
						?>
					<div class="bgr-header-row"><div class="bgr-group-header-ratings"><div class="rating-bgr">
						<?php
						do_action( 'bgr_display_group_average_ratings', $avg_rating );
						echo '</div></div></div>';
					}
				}
			}
		}

		/**
		 *  Actions performed to show average rating on a group
		 *
		 *  @since   1.0.0
		 *  @access public
		 *  @author  Wbcom Designs
		 */
		public function bp_group_review_group_average_rating() {
			// Gather all the group reviews.
			global $bgr;
			global $bp;
			$args                 = array(
				'post_type'      => 'review',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'category'       => 'group',
				'meta_query'     => array(
					array(
						'key'     => 'linked_group',
						'value'   => bp_get_group_id(),
						'compare' => '=',
					),
				),
			);
			$reviews              = get_posts( $args );
			$review_rating_fields = $bgr['review_rating_fields'];
			$review_label         = bp_group_review_tab_name();
			$exclude_groups       = isset( $bgr['exclude_groups'] ) ? array_map( 'absint', (array) $bgr['exclude_groups'] ) : array();
			$current_group_id     = absint( bp_get_group_id() );
			if ( ! empty( $exclude_groups ) ) {
				if ( ! in_array( $current_group_id, $exclude_groups, true ) ) {
					$this->bp_group_review_group_average_rating_data( $reviews, $review_rating_fields, $review_label );
				}
			} else {
				$this->bp_group_review_group_average_rating_data( $reviews, $review_rating_fields, $review_label );
			}
		}

		/**
		 * Actions performed to show average rating.
		 *
		 * @since  1.0.0
		 * @access public
		 * @author Wbcom Designs
		 *
		 * @param array  $reviews              Array of review posts.
		 * @param array  $review_rating_fields Array of rating field names.
		 * @param string $review_label         Label for reviews.
		 * @return void
		 */
		public function bp_group_review_group_average_rating_data( $reviews, $review_rating_fields, $review_label ) {
			if ( ! empty( $review_rating_fields ) ) {
				if ( ! empty( $reviews ) ) {
					$ttl_rating         = 0;
					$reviews_count      = 0;
					$total_review_count = count( $reviews );
					foreach ( $reviews as $review ) {
						$rate           = 0;
						$review_ratings = get_post_meta( $review->ID, 'review_star_rating', false );

						$reviews_field_count = 0;
						// Ensure review_ratings[0] is an array before iterating.
						if ( ! empty( $review_rating_fields ) && ! empty( $review_ratings[0] ) && is_array( $review_ratings[0] ) ) :
							foreach ( $review_ratings[0] as $field => $value ) {
								// Add all rating values regardless of field name match.
								$rate += (int) $value;
								++$reviews_field_count;
							}
							if ( $reviews_field_count > 0 ) {
								++$reviews_count;
								$ttl_rating += (int) $rate / $reviews_field_count;
							}
						endif;
					}

					$reviews_count = ( 0 === $reviews_count ) ? 1 : $reviews_count;
					$avg_rating    = $ttl_rating / $reviews_count;
					$avg_rating    = round( $avg_rating, 2 );
					if ( $avg_rating > 0 ) {
						?>
					<div class="bgr-header-row"><div class=" bgr-group-header-ratings"><div class="rating-bgr">
						<?php
						do_action( 'bgr_display_group_average_ratings', $avg_rating );

						if ( $total_review_count <= 1 ) {

							$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
							$review_label               = isset( $bgr_admin_display_settings['review_label'] ) ? $bgr_admin_display_settings['review_label'] : esc_html__( 'Review', 'bp-group-reviews' );
						}
						$content = "<div class='rating-text'><span>" . esc_html__( 'Rating ', 'bp-group-reviews' ) . ' : ' . $avg_rating . '/5 - ' . $total_review_count . ' ' . $review_label . '</span></div>';

						echo wp_kses_post( apply_filters( 'bgr_filter_group_header_rating_details', $content, $avg_rating, $review_label, $total_review_count, bp_get_group_id() ) );
						echo '</div></div></div>';
					}
				}
			}
		}

		/**
		 * Modify bulk actions for reviews - remove edit, add approve/deny.
		 *
		 * @since  1.0.0
		 * @access public
		 * @author Wbcom Designs
		 *
		 * @param array $actions Array of bulk actions.
		 * @return array Modified actions array.
		 */
		public function bp_group_review_bulk_actions( $actions ) {
			unset( $actions['edit'] );
			$actions['approve_reviews'] = __( 'Approve', 'bp-group-reviews' );
			$actions['deny_reviews']    = __( 'Deny', 'bp-group-reviews' );
			return $actions;
		}

		/**
		 * Handle bulk approve/deny actions for reviews.
		 *
		 * @since  3.6.0
		 * @access public
		 *
		 * @param string $redirect_to The redirect URL.
		 * @param string $doaction    The action being taken.
		 * @param array  $post_ids    The items to take the action on.
		 * @return string The redirect URL.
		 */
		public function bp_group_review_handle_bulk_actions( $redirect_to, $doaction, $post_ids ) {
			if ( 'approve_reviews' !== $doaction && 'deny_reviews' !== $doaction ) {
				return $redirect_to;
			}

			$processed = 0;

			foreach ( $post_ids as $post_id ) {
				$post = get_post( $post_id );
				if ( ! $post || 'review' !== $post->post_type ) {
					continue;
				}

				if ( 'approve_reviews' === $doaction ) {
					// Only approve draft reviews.
					if ( 'draft' === $post->post_status ) {
						wp_update_post(
							array(
								'ID'          => $post_id,
								'post_status' => 'publish',
							)
						);
						$author_id = $post->post_author;
						do_action( 'gamipress_bp_group_review', $author_id );
						do_action( 'bgr_group_accept_review', $post_id );
						++$processed;
					}
				} elseif ( 'deny_reviews' === $doaction ) {
					// Move to trash.
					wp_trash_post( $post_id );
					do_action( 'bgr_group_deny_review', $post_id );
					++$processed;
				}
			}

			$redirect_to = add_query_arg(
				array(
					'bulk_action' => $doaction,
					'processed'   => $processed,
				),
				$redirect_to
			);

			return $redirect_to;
		}

		/**
		 * Display admin notices after bulk actions.
		 *
		 * @since  3.6.0
		 * @access public
		 */
		public function bp_group_review_bulk_action_notices() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading results from WP bulk action handler.
			if ( ! isset( $_GET['bulk_action'] ) || ! isset( $_GET['processed'] ) ) {
				return;
			}

			$screen = get_current_screen();
			if ( ! $screen || 'edit-review' !== $screen->id ) {
				return;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Displaying result of previous action.
			$action = sanitize_text_field( wp_unslash( $_GET['bulk_action'] ) );
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$processed = intval( $_GET['processed'] );

			if ( 'approve_reviews' === $action ) {
				$message = sprintf(
					/* translators: %d: number of reviews */
					_n(
						'%d review approved.',
						'%d reviews approved.',
						$processed,
						'bp-group-reviews'
					),
					$processed
				);
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
			} elseif ( 'deny_reviews' === $action ) {
				$message = sprintf(
					/* translators: %d: number of reviews */
					_n(
						'%d review denied and moved to trash.',
						'%d reviews denied and moved to trash.',
						$processed,
						'bp-group-reviews'
					),
					$processed
				);
				echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
			}
		}

		/**
		 * Add CSV export button to reviews list.
		 *
		 * @since  3.6.0
		 * @access public
		 *
		 * @param string $post_type The post type slug.
		 */
		public function bp_group_review_export_button( $post_type ) {
			if ( 'review' !== $post_type ) {
				return;
			}

			$export_url = wp_nonce_url(
				add_query_arg(
					array(
						'action'    => 'bgr_export_reviews',
						'post_type' => 'review',
					),
					admin_url( 'admin.php' )
				),
				'bgr_export_reviews'
			);
			?>
			<a href="<?php echo esc_url( $export_url ); ?>" class="button bgr-export-reviews-btn">
				<?php esc_html_e( 'Export to CSV', 'bp-group-reviews' ); ?>
			</a>
			<?php
		}

		/**
		 * Handle CSV export for reviews.
		 *
		 * @since  3.6.0
		 * @access public
		 */
		public function bp_group_review_handle_csv_export() {
			if ( ! isset( $_GET['action'] ) || 'bgr_export_reviews' !== $_GET['action'] ) {
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to export reviews.', 'bp-group-reviews' ) );
			}

			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bgr_export_reviews' ) ) {
				wp_die( esc_html__( 'Security check failed.', 'bp-group-reviews' ) );
			}

			global $bgr;
			$review_rating_fields = isset( $bgr['review_rating_fields'] ) ? $bgr['review_rating_fields'] : array();

			// Get all reviews.
			$args = array(
				'post_type'      => 'review',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			);

			$reviews = get_posts( $args );

			// Set headers for CSV download.
			$filename = 'group-reviews-' . gmdate( 'Y-m-d-His' ) . '.csv';
			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=' . $filename );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			// Create output stream.
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Using php://output for CSV streaming.
			$output = fopen( 'php://output', 'w' );

			// Add UTF-8 BOM for Excel compatibility.
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fputs -- Required for CSV output.
			fputs( $output, "\xEF\xBB\xBF" );

			// CSV header row.
			$headers = array(
				__( 'Review ID', 'bp-group-reviews' ),
				__( 'Date', 'bp-group-reviews' ),
				__( 'Status', 'bp-group-reviews' ),
				__( 'Reviewer', 'bp-group-reviews' ),
				__( 'Reviewer Email', 'bp-group-reviews' ),
				__( 'Group Name', 'bp-group-reviews' ),
				__( 'Group ID', 'bp-group-reviews' ),
				__( 'Review Content', 'bp-group-reviews' ),
				__( 'Average Rating', 'bp-group-reviews' ),
			);

			// Add rating field columns.
			foreach ( $review_rating_fields as $field ) {
				$headers[] = $field . ' ' . __( 'Rating', 'bp-group-reviews' );
			}

			fputcsv( $output, $headers );

			// CSV data rows.
			foreach ( $reviews as $review ) {
				$author_id      = $review->post_author;
				$author_info    = get_userdata( $author_id );
				$linked_group   = get_post_meta( $review->ID, 'linked_group', true );
				$group          = groups_get_group( array( 'group_id' => $linked_group ) );
				$group_name     = $group ? $group->name : __( 'Unknown Group', 'bp-group-reviews' );
				$review_ratings = get_post_meta( $review->ID, 'review_star_rating', true );

				// Calculate average rating.
				$avg_rating = 0;
				if ( ! empty( $review_ratings ) && is_array( $review_ratings ) ) {
					$avg_rating = round( array_sum( $review_ratings ) / count( $review_ratings ), 2 );
				}

				$row = array(
					$review->ID,
					get_the_date( 'Y-m-d H:i:s', $review->ID ),
					$review->post_status,
					$author_info ? $author_info->display_name : __( 'Unknown', 'bp-group-reviews' ),
					$author_info ? $author_info->user_email : '',
					$group_name,
					$linked_group,
					wp_strip_all_tags( $review->post_content ),
					$avg_rating,
				);

				// Add individual rating values.
				foreach ( $review_rating_fields as $field ) {
					$row[] = isset( $review_ratings[ $field ] ) ? $review_ratings[ $field ] : '';
				}

				fputcsv( $output, $row );
			}

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing php://output for CSV streaming.
			fclose( $output );
			exit;
		}

		/**
		 * Actions performed to hide row actions.
		 *
		 * @since  1.0.0
		 * @access public
		 * @author Wbcom Designs
		 *
		 * @param array   $actions Array of row actions.
		 * @param WP_Post $post    The post object.
		 * @return array Modified actions array.
		 */
		public function bp_group_review_row_actions( $actions, $post ) {
			global $bp;
			if ( 'review' === $post->post_type ) {
				unset( $actions['edit'] );
				unset( $actions['view'] );
				unset( $actions['inline hide-if-no-js'] );

				$terms = wp_get_object_terms( $post->ID, 'review_category' );
				if ( ! empty( $terms ) && 'Group' === $terms[0]->name ) {
					// Add a link to view the review.
					$review_title = $post->post_title;
					$linked_group = get_post_meta( $post->ID, 'linked_group', true );
					$group        = groups_get_group( array( 'group_id' => $linked_group ) );
					// Build group URL safely for both BuddyPress and BuddyBoss.
					if ( function_exists( 'bp_get_group_url' ) ) {
						$group_url = bp_get_group_url( $group );
					} else {
						// Fallback for older versions - build URL manually.
						$group_url = bp_get_groups_directory_permalink() . $group->slug . '/';
					}
					$review_url = $group_url . sanitize_title( bp_group_review_tab_name() ) . '/view/' . $post->ID;

					$actions['view_review'] = '<a href="' . $review_url . '" title="' . $review_title . '">' . esc_html__( 'View', 'bp-group-reviews' ) . '</a>';

					// Add Approve Link for draft reviews.
					if ( 'draft' === $post->post_status ) {
						$actions['approve_review'] = '<a href="javascript:void(0);" title="' . $review_title . '" class="bgr-approve-review" data-rid="' . $post->ID . '">' . esc_html__( 'Approve', 'bp-group-reviews' ) . '</a>';
					}

					// Add Deny Link for draft and published reviews.
					if ( 'draft' === $post->post_status || 'publish' === $post->post_status ) {
						$actions['deny_review'] = '<a href="javascript:void(0);" title="' . $review_title . '" class="bgr-deny-review" data-rid="' . $post->ID . '">' . esc_html__( 'Deny', 'bp-group-reviews' ) . '</a>';
					}
				}
			}
			return $actions;
		}

		/**
		 *  Action performed to add taxonomy term for group reviews
		 *
		 *  @since   1.0.0
		 *  @access   public
		 *  @author  Wbcom Designs
		 */
		public function bp_group_review_add_taxonomy_term() {
			$term_exists = term_exists( 'Group', 'review_category' );
			if ( 0 === $term_exists || null === $term_exists ) {
				wp_insert_term( 'Group', 'review_category' );
			}
		}

		/**
		 *  Action performed to add a tab for group reviews
		 *
		 *  @since   1.0.0
		 *  @access   public
		 *  @author  Wbcom Designs
		 */
		public function bp_group_review_add_group_reviews_tab() {
			if ( bp_is_group_single() ) {
				global $bp;
				global $bgr;
				global $post, $wpdb;
				$bgr_admin_general_settings = get_option( 'bgr_admin_general_settings' );
				$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
				$review_label               = isset( $bgr['manage_review_label'] ) ? $bgr['manage_review_label'] : '';
				$add_review_label           = ! empty( $bgr_admin_display_settings ) ? bp_group_review_add_review_tab_name() : esc_html__( 'Review', 'bp-group-reviews' );

				$args = array(
					'category'    => 'group',
					'orderby'     => 'post_date',
					'order'       => 'DESC',
					'meta_key'    => 'linked_group',
					'meta_value'  => $bp->groups->current_group->id,
					'post_type'   => 'review',
					'post_status' => 'publish',
				);

				$post_count   = 0;
				$recent_posts = wp_get_recent_posts( $args );
				if ( ! empty( $recent_posts ) ) :
					foreach ( $recent_posts as $recent ) {
						++$post_count;
					}
				endif;
				wp_reset_postdata(); // Reset post data after custom query.
				$user               = bp_get_loggedin_user_username();
				$count_notification = '<span>' . $post_count . '</span>';
				// Build group URL safely for both BuddyPress and BuddyBoss.
				if ( function_exists( 'bp_get_group_url' ) ) {
					$gp_parent_url = bp_get_group_url( $bp->groups->current_group );
				} else {
					// Fallback for older versions - build URL manually.
					$gp_parent_url = bp_get_groups_directory_permalink() . $bp->groups->current_group->slug . '/';
				}

				// Check if user can add reviews (must be logged in, member, and not admin).
				$current_group_id = absint( $bp->groups->current_group->id );
				$current_user_id  = bp_loggedin_user_id();
				$can_add_review   = is_user_logged_in()
					&& groups_is_user_member( $current_user_id, $current_group_id )
					&& ! groups_is_user_admin( $current_user_id, $current_group_id );

				if ( ! empty( $bgr_admin_general_settings ) ) {
					$exclude_groups = isset( $bgr['exclude_groups'] ) ? array_map( 'absint', (array) $bgr['exclude_groups'] ) : array();
					if ( ! empty( $exclude_groups ) ) {
						if ( ! in_array( $current_group_id, $exclude_groups, true ) ) {
							// Always add the Reviews listing tab.
							bp_core_new_subnav_item(
								array(
									'name'            => $review_label . ' ' . $count_notification,
									'slug'            => sanitize_title( $bgr['manage_review_label'] ),
									'parent_slug'     => $bp->groups->current_group->slug,
									'parent_url'      => $gp_parent_url,
									'screen_function' => array( $this, 'bp_group_review_tab' ),
									'position'        => 198,
									'item_css_id'     => 'reviews',
								)
							);

							// Only add the "Add Review" tab if user can add reviews.
							if ( $can_add_review ) {
								bp_core_new_subnav_item(
									array(
										/* translators: %s: review label (e.g., "Review") */
										'name'            => sprintf( __( 'Adds %s', 'bp-group-reviews' ), $add_review_label ),
										'slug'            => 'add-' . bp_group_review_tab_slug(),
										'parent_slug'     => $bp->groups->current_group->slug,
										'parent_url'      => $gp_parent_url,
										'screen_function' => array( $this, 'bp_group_review_add_review_tab' ),
										'position'        => 199,
										'item_css_id'     => 'add-review',
									)
								);
							}
						}
					} else {
						// Always add the Reviews listing tab.
						bp_core_new_subnav_item(
							array(
								'name'            => $review_label . ' ' . $count_notification,
								'slug'            => sanitize_title( $bgr['manage_review_label'] ),
								'parent_slug'     => $bp->groups->current_group->slug,
								'parent_url'      => $gp_parent_url,
								'screen_function' => array( $this, 'bp_group_review_tab' ),
								'position'        => 198,
								'item_css_id'     => 'reviews',
							)
						);

						// Only add the "Add Review" tab if user can add reviews.
						if ( $can_add_review ) {
							bp_core_new_subnav_item(
								array(
									/* translators: %s: review label (e.g., "Review") */
									'name'            => sprintf( __( 'Adds %s', 'bp-group-reviews' ), $add_review_label ),
									'slug'            => 'add-' . bp_group_review_tab_slug(),
									'parent_slug'     => $bp->groups->current_group->slug,
									'parent_url'      => $gp_parent_url,
									'screen_function' => array( $this, 'bp_group_review_add_review_tab' ),
									'position'        => 199,
									'item_css_id'     => 'add-review',
								)
							);
						}
					}
				} else {
					// Always add the Reviews listing tab.
					bp_core_new_subnav_item(
						array(
							'name'            => $review_label . ' ' . $count_notification,
							'slug'            => sanitize_title( $bgr['manage_review_label'] ),
							'parent_slug'     => $bp->groups->current_group->slug,
							'parent_url'      => $gp_parent_url,
							'screen_function' => array( $this, 'bp_group_review_tab' ),
							'position'        => 198,
							'item_css_id'     => 'reviews',
						)
					);

					// Only add the "Add Review" tab if user can add reviews.
					if ( $can_add_review ) {
						bp_core_new_subnav_item(
							array(
								/* translators: %s: review label (e.g., "Review") */
								'name'            => sprintf( __( 'Adds %s', 'bp-group-reviews' ), $add_review_label ),
								'slug'            => 'add-' . bp_group_review_tab_slug(),
								'parent_slug'     => $bp->groups->current_group->slug,
								'parent_url'      => $gp_parent_url,
								'screen_function' => array( $this, 'bp_group_review_add_review_tab' ),
								'position'        => 199,
								'item_css_id'     => 'add-review',
							)
						);
					}
				}
			}
		}

		/**
		 * Action performed to show screen of add review tab.
		 *
		 * @since  1.0.0
		 * @access public
		 * @author Wbcom Designs
		 *
		 * @return void
		 */
		public function bp_group_review_add_review_tab() {
			add_action( 'bp_template_content', array( $this, 'bp_group_review_add_reviews_tab_template' ) );
			$templates = array( 'groups/single/plugins.php', 'plugin-template.php' );
			if ( strstr( locate_template( $templates ), 'groups/single/plugins.php' ) ) {
				bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'groups/single/plugins' ) );
			} else {
				bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'plugin-template' ) );
			}
		}

		/**
		 * Action performed to show screen of reviews listing tab.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bp_group_review_tab() {
			add_action( 'bp_template_content', array( $this, 'bp_group_reviews_tab_template' ) );
			$templates = array( 'groups/single/plugins.php', 'plugin-template.php' );
			if ( strstr( locate_template( $templates ), 'groups/single/plugins.php' ) ) {
				bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'groups/single/plugins' ) );
			} else {
				bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'plugin-template' ) );
			}
		}

		/**
		 *  Action performed to show the content of reviews list tab
		 *
		 *  @since   1.0.0
		 *  @access   public
		 *  @author  Wbcom Designs
		 */
		public function bp_group_reviews_tab_template() {
			global $bgr;
			if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), sanitize_title( bp_group_review_tab_name() ) . '/view' ) !== false ) {
				include 'templates/bgr-single-review-template.php';
			} elseif ( sanitize_title( $bgr['manage_review_label'] ) === bp_current_action() ) {
				include 'templates/bgr-reviews-tab-template.php';
			}
		}

		/**
		 *  Action performed to show add review form on single group
		 *
		 *  @since   3.2.2
		 *  @access   public
		 *  @author  Wbcom Designs
		 */
		public function bp_group_review_add_reviews_tab_template() {
			?>
			<div class="bgr-bp-success">
				<?php
				$bp_template_option = bp_get_option( '_bp_theme_package_id' );
				if ( 'nouveau' === $bp_template_option ) {
					?>
					<div id="message" class="success bp-feedback bp-messages bp-template-notice">
						<span class="bp-icon" aria-hidden="true"></span>
				<?php } else { ?>
					<div id="message" class="success bgr-bp-success">
				<?php } ?>
					<p><?php esc_html_e( 'Your Response added. This will be published when group admin has approved it.', 'bp-group-reviews' ); ?></p>
				</div>
			</div>
			<div class="bgr-group-review-no-popup-add-block">
				<?php echo do_shortcode( '[add_group_review_form]' ); ?>
			</div>
			<?php
		}

		/**
		 * Register BuddyPress Groups Review triggers.
		 *
		 * @param array $triggers Array of GamiPress activity triggers.
		 * @return array Modified triggers array.
		 */
		public function bp_group_review_bp_activity_triggers( $triggers ) {
			$triggers[ __( 'BuddyPress Group Review', 'bp-group-reviews' ) ] = array(
				'gamipress_bp_group_review' => __( 'Give Group Review', 'bp-group-reviews' ),
			);

			return $triggers;
		}

		/**
		 * Get user ID for GamiPress trigger.
		 *
		 * @param int    $user_id The user ID.
		 * @param string $trigger The trigger name.
		 * @param array  $args    Additional arguments.
		 * @return int The user ID.
		 */
		public function bp_group_review_trigger_get_user_id( $user_id, $trigger, $args ) {
			if ( 'gamipress_bp_group_review' === $trigger ) {
				$user_id = $args[0];
			}
			return $user_id;
		}
	}
	new BGR_Custom_Hooks();
}
