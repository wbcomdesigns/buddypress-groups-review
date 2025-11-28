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
			add_filter( 'bulk_actions-edit-review', array( $this, 'bp_group_review_remove_edit_bulk_actions' ), 10, 1 );

			// Add group average rating before group header meta
			add_action( 'bp_before_group_header_meta', array( $this, 'bp_group_review_group_average_rating' ) );

			// Check for BuddyBoss or BuddyPress and add the review button accordingly
			$active_plugins = get_option( 'active_plugins' );
			if ( in_array( 'buddyboss-platform/bp-loader.php', $active_plugins ) ) {
				add_action( 'bb_group_single_top_header_action', array( $this, 'bp_group_review_group_header_review_btn' ) );
			} else {
				if ( in_array( 'buddypress/bp-loader.php', $active_plugins ) && ! defined( 'YOUZIFY_VERSION' ) ) {
					 add_action( 'bp_group_header_actions', array( $this, 'bp_group_review_group_header_review_btn' ) );
				}
			}

			// Add rating to group directory
			add_action( 'bp_directory_groups_item', array( $this, 'bp_group_review_group_directory_rating' ) );

			// Add Youzify integration (check using YOUZIFY_VERSION constant)
			if ( defined( 'YOUZIFY_VERSION' ) ) {
				add_action( 'youzify_before_group_header_meta', array( $this, 'bp_group_review_group_average_rating' ) );
				add_action( 'youzify_after_group_header_meta', array( $this, 'bp_group_review_group_header_review_btn' ) );
			}

			/**
			 * GamiPress integration
			 */
			if ( in_array( 'gamipress/gamipress.php', $active_plugins ) ) {
				add_filter( 'gamipress_activity_triggers', array( $this, 'bp_group_review_bp_activity_triggers' ) );
				add_filter( 'gamipress_trigger_get_user_id', array( $this, 'bp_group_review_trigger_get_user_id' ), 10, 3 );
			}

			add_filter( 'bp_nouveau_nav_has_count', array( $this, 'bp_group_review_nav_has_count' ), 10, 3 );
			add_filter( 'bp_nouveau_get_nav_count', array( $this, 'bp_group_review_get_nav_count' ), 10, 3 );
		}

		/** Review Tab has a count attribute.
		 *
		 * @since  3.2.3
		 *
		 * @param bool   $count     True if the nav has a count attribute. False otherwise
		 * @param object $nav_item  The current nav item object.
		 * @param string $value     The current nav in use (eg: 'directory', 'groups', 'personal', etc..).
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

		/** Retrieve the count attribute for the review nav item.
		 *
		 * @since  3.2.3
		 *
		 * @param int $count    The count attribute for the nav item.
		 * @param object $nav_item The current nav item object.
		 * @param string $value    The current nav in use (eg: 'directory', 'groups', 'personal', etc..).
		 * @return int The count attribute for the nav item.
		 */
		public function bp_group_review_get_nav_count( $count, $nav_item, $component ) {
			if ( 'groups' !== $component ) {
				return $count;
			}

			global $bgr;

			if ( sanitize_title( $bgr['manage_review_label'] ) === $nav_item->slug ) {
				$reviews = get_posts(
					array(
						'numberposts' => -1,
						'post_type'   => 'review',
						'post_status' => 'publish',
						'meta_query'  => array(
							array(
								'key'     => 'linked_group',
								'value'   => bp_get_group_id(),
								'compare' => '=',
							),
						),
					)
				);
				$count   = count( $reviews );
			}

			return apply_filters( 'buddypress_groups_review_nav_count', $count );
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
			$review_div          = 'form';
			$exclude_groups      = $bgr['exclude_groups'];
			$current_group_id    = bp_get_current_group_id();
			// Build group URL safely for both BuddyPress and BuddyBoss.
			$current_group = groups_get_group( array( 'group_id' => $current_group_id ) );
			if ( function_exists( 'bp_get_group_url' ) ) {
				$current_group_link = bp_get_group_url( $current_group );
			} else {
				// Fallback for older versions - build URL manually.
				$current_group_link = bp_get_groups_directory_permalink() . $current_group->slug . '/';
			}
			$current_group_link .= 'add-' . bp_group_review_tab_slug();
			if ( ! empty( $exclude_groups ) ) {
				if ( ! in_array( bp_get_current_group_id(), $exclude_groups ) ) {
					if ( ! groups_is_user_admin( bp_loggedin_user_id(), bp_get_current_group_id() ) || ! is_user_logged_in() ) { ?>
					<div class="group-button group-actions-absolute public generic-button" id="add-review-groupbutton">
						<a href='<?php echo esc_url( $current_group_link ); ?>' class="group-button" show ="<?php echo esc_attr( $review_div ); ?>">
							<?php
							/* translators: %1$s is replaced with bp_group_review_add_review_tab_name() */
							echo sprintf( esc_html__( 'Add %1$s', 'bp-group-reviews' ), esc_html( bp_group_review_add_review_tab_name() ) );
							?>
						</a>
					</div>
						<?php
					}
				}
			} else {				
					?>
				<div class="group-button group-actions-absolute public generic-button" id="add-review-groupbutton">
					<a href='<?php echo esc_url( $current_group_link ); ?>' class="group-button button" show ="<?php echo esc_attr( $review_div ); ?>">
						<?php
						/* translators: %1$s is replaced with bp_group_review_add_review_tab_name() */
						echo sprintf( esc_html__( 'Add %1$s', 'bp-group-reviews' ), esc_html( bp_group_review_add_review_tab_name() ) );
						?>
					</a>
				</div>
					<?php
			}
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
			$exclude_groups       = $bgr['exclude_groups'];
			if ( ! empty( $exclude_groups ) ) {
				if ( ! in_array( bp_get_group_id(), $exclude_groups ) ) {
					$this->bp_group_review_single_group_average_rating_data( $reviews, $review_rating_fields, $review_label );
				}
			} else {
				$this->bp_group_review_single_group_average_rating_data( $reviews, $review_rating_fields, $review_label );
			}
		}


		/**
		 *  Actions performed to show rating on groups directing page
		 *
		 *  @since   1.0.0
		 *  @access public
		 *  @author  Wbcom Designs
		 */
		public function bp_group_review_single_group_average_rating_data( $reviews, $review_rating_fields, $review_label ) {
			$group_id = bp_get_current_group_id();

			if ( ! empty( $review_rating_fields ) ) {
				if ( ! empty( $reviews ) ) {
					$ttl_rating         = 0;
					$reviews_count      = 0;
					$rating_count       = 0;
					$total_review_count = count( $reviews );
					foreach ( $reviews as $review ) {
						$rate           = 0;
						$review_ratings = get_post_meta( $review->ID, 'review_star_rating', false );

						$reviews_field_count = 0;
						if ( ! empty( $review_rating_fields ) && ! empty( $review_ratings[0] ) ) :
							foreach ( $review_ratings[0] as $field => $value ) {
								if ( in_array( $field, $review_rating_fields ) ) {
									$rate += $value;
									$reviews_field_count++;
								} else {
									$rate += $value;
									$reviews_field_count++;
								}
							}
							if ( $reviews_field_count > 0 ) {
								$reviews_count++;
								$reviews_field_count = ( $reviews_field_count == 0 ) ? 1 : $reviews_field_count;
								$ttl_rating         += (int) $rate / $reviews_field_count;
								$rating_count++;
							}
						endif;
					}

					$reviews_count = ( $reviews_count == 0 ) ? 1 : $reviews_count;
					$avg_rating    = $ttl_rating / $reviews_count;
					$avg_rating    = round( $avg_rating, 2 );
					if ( $avg_rating > 0 ) {
						// Build group URL safely for both BuddyPress and BuddyBoss.
						if ( function_exists( 'bp_get_group_url' ) ) {
							$group_link = bp_get_group_url( groups_get_current_group() );
						} else {
							// Fallback for older versions - build URL manually.
							$current_group = groups_get_current_group();
							// Ensure we have a group object, not just an ID.
							if ( is_numeric( $current_group ) ) {
								$current_group = groups_get_group( $current_group );
							}
							$group_link = bp_get_groups_directory_permalink() . $current_group->slug . '/';
						}
						}
					?>
					<div class="bgr-header-row"><div class="bgr-group-header-ratings"><div class="rating-bgr">
					<?php
					do_action( 'bgr_display_group_average_ratings', $avg_rating );
					echo '</div></div></div>';
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
			$exclude_groups       = $bgr['exclude_groups'];
			if ( ! empty( $exclude_groups ) ) {
				if ( ! in_array( bp_get_group_id(), $exclude_groups ) ) {
					$this->bp_group_review_group_average_rating_data( $reviews, $review_rating_fields, $review_label );
				}
			} else {
				$this->bp_group_review_group_average_rating_data( $reviews, $review_rating_fields, $review_label );
			}
		}

		/**
		 *  Actions performed to show average rating
		 *
		 *  @since   1.0.0
		 *  @access public
		 *  @author  Wbcom Designs
		 */
		public function bp_group_review_group_average_rating_data( $reviews, $review_rating_fields, $review_label ) {
			if ( ! empty( $review_rating_fields ) ) {
				if ( ! empty( $reviews ) ) {
					$ttl_rating         = 0;
					$reviews_count      = $rating_count = 0;
					$total_review_count = count( $reviews );
					foreach ( $reviews as $review ) {
						$rate           = 0;
						$review_ratings = get_post_meta( $review->ID, 'review_star_rating', false );

						$reviews_field_count = 0;
						if ( ! empty( $review_rating_fields ) && ! empty( $review_ratings[0] ) ) :
							foreach ( $review_ratings[0] as $field => $value ) {
								if ( in_array( $field, $review_rating_fields ) ) {
									$rate += $value;
									$reviews_field_count++;
								} else {
									$rate += $value;
									$reviews_field_count++;
								}
							}
							if ( $reviews_field_count > 0 ) {
								$reviews_count++;
								$reviews_field_count = ( $reviews_field_count == 0 ) ? 1 : $reviews_field_count;
								$ttl_rating         += (int) $rate / $reviews_field_count;
								$rating_count++;
							}
						endif;
					}

					$reviews_count = ( $reviews_count == 0 ) ? 1 : $reviews_count;
					$avg_rating    = $ttl_rating / $reviews_count;
					$avg_rating    = round( $avg_rating, 2 );
					if ( $avg_rating > 0 ) {
						// Build group URL safely for both BuddyPress and BuddyBoss.
						if ( function_exists( 'bp_get_group_url' ) ) {
							$group_link = bp_get_group_url( groups_get_current_group() );
						} else {
							// Fallback for older versions - build URL manually.
							$current_group = groups_get_current_group();
							// Ensure we have a group object, not just an ID.
							if ( is_numeric( $current_group ) ) {
								$current_group = groups_get_group( $current_group );
							}
							$group_link = bp_get_groups_directory_permalink() . $current_group->slug . '/';
						}
						}
					?>
					<div class="bgr-header-row"><div class=" bgr-group-header-ratings"><div class="rating-bgr">
					<?php
					do_action( 'bgr_display_group_average_ratings', $avg_rating );

					if( $total_review_count <= 1 ) {
						
						$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
						$review_label               = isset( $bgr_admin_display_settings['review_label'] ) ? $bgr_admin_display_settings['review_label'] : esc_html__( 'Review', 'bp-group-reviews' );
					}
					$content = "<div class='rating-text'><span>" . esc_html__( 'Rating ', 'bp-group-reviews' ) . ' : ' . $avg_rating . '/5 - ' . $total_review_count . ' ' . $review_label . '</span></div>';

					echo wp_kses_post( apply_filters( 'bgr_filter_group_header_rating_details', $content, $avg_rating, $review_label, $total_review_count, bp_get_group_id() ) );
					echo '</div></div></div>';
				}
			}
		}

		/**
		 *  Actions performed to remove edit from bulk options
		 *
		 *  @since   1.0.0
		 *  @access public
		 *  @author  Wbcom Designs
		 */
		public function bp_group_review_remove_edit_bulk_actions( $actions ) {
			unset( $actions['edit'] );
			return $actions;
		}

		/**
		 *  Actions performed to hide row actions
		 *
		 *  @since   1.0.0
		 *  @access   public
		 *  @author  Wbcom Designs
		 */
		public function bp_group_review_row_actions( $actions, $post ) {
			global $bp;
			if ( $post->post_type == 'review' ) {
				unset( $actions['edit'] );
				unset( $actions['view'] );
				unset( $actions['inline hide-if-no-js'] );

				if ( wp_get_object_terms( $post->ID, 'review_category' )[0]->name == 'Group' ) {
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
					$review_url   = $group_url . sanitize_title( bp_group_review_tab_name() ) . '/view/' . $post->ID;

					$actions['view_review'] = '<a href="' . $review_url . '" title="' . $review_title . '">' . esc_html__( 'View', 'bp-group-reviews' ) . '</a>';

					// Add Approve Link for draft reviews.
					if ( $post->post_status == 'draft' ) {
						$actions['approve_review'] = '<a href="javascript:void(0);" title="' . $review_title . '" class="bgr-approve-review" data-rid="' . $post->ID . '">' . esc_html__( 'Approve', 'bp-group-reviews' ) . '</a>';
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
			$termExists = term_exists( 'Group', 'review_category' );
			if ( $termExists === 0 || $termExists === null ) {
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
				$bgr_admin_display_settings  = get_option( 'bgr_admin_display_settings' );
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
						$post_count++;
					}
				endif;
				wp_reset_postdata(); // Use wp_reset_postdata() instead of deprecated wp_reset_query()
				$user               = bp_get_loggedin_user_username();
				$count_notification = '<span>' . $post_count . '</span>';
				// Build group URL safely for both BuddyPress and BuddyBoss.
			if ( function_exists( 'bp_get_group_url' ) ) {
				$gp_parent_url = bp_get_group_url( $bp->groups->current_group );
			} else {
				// Fallback for older versions - build URL manually.
				$gp_parent_url = bp_get_groups_directory_permalink() . $bp->groups->current_group->slug . '/';
			}
				if ( ! empty( $bgr_admin_general_settings ) ) {
					$exclude_groups = $bgr['exclude_groups'];
					if ( ! empty( $exclude_groups ) ) {
						if ( ! in_array( $bp->groups->current_group->id, $exclude_groups ) ) {
							$tab_args[] = array(
								'name'            => $review_label . ' ' . $count_notification,
								'slug'            => sanitize_title( $bgr['manage_review_label'] ),
								'parent_slug'     => $bp->groups->current_group->slug,
								'parent_url'      => $gp_parent_url,
								'screen_function' => array( $this, 'bp_group_review_tab' ),
								'position'        => 198,
								'item_css_id'     => 'reviews',
							);

							$tab_args[] = array(
								/* translators: %s: review label (e.g., "Review") */
								'name'            => sprintf( __( 'Adds %s', 'bp-group-reviews' ), $add_review_label ),
								'slug'            => 'add-' . bp_group_review_tab_slug(),
								'parent_slug'     => $bp->groups->current_group->slug,
								'parent_url'      => $gp_parent_url,
								'screen_function' => array( $this, 'bp_group_review_add_review_tab' ),
								'position'        => 199,
								'item_css_id'     => 'add-review',
							);

							foreach ( $tab_args as $tab ) {
								bp_core_new_subnav_item( $tab );
							}
						}
					} else {
						$tab_args[] = array(
							'name'            => $review_label . ' ' . $count_notification,
							'slug'            => sanitize_title( $bgr['manage_review_label'] ),
							'parent_slug'     => $bp->groups->current_group->slug,
							'parent_url'      => $gp_parent_url,
							'screen_function' => array( $this, 'bp_group_review_tab' ),
							'position'        => 198,
							'item_css_id'     => 'reviews',
						);

						$tab_args[] = array(
							/* translators: %s: review label (e.g., "Review") */
							'name'            => sprintf( __( 'Adds %s', 'bp-group-reviews' ), $add_review_label ),
							'slug'            => 'add-' . bp_group_review_tab_slug(),
							'parent_slug'     => $bp->groups->current_group->slug,
							'parent_url'      => $gp_parent_url,
							'screen_function' => array( $this, 'bp_group_review_add_review_tab' ),
							'position'        => 199,
							'item_css_id'     => 'add-review',
						);

						foreach ( $tab_args as $tab ) {
							bp_core_new_subnav_item( $tab );
						}
					}
				} else {
					$tab_args[] = array(
						'name'            => $review_label . ' ' . $count_notification,
						'slug'            => sanitize_title( $bgr['manage_review_label'] ),
						'parent_slug'     => $bp->groups->current_group->slug,
						'parent_url'      => $gp_parent_url,
						'screen_function' => array( $this, 'bp_group_review_tab' ),
						'position'        => 198,
						'item_css_id'     => 'reviews',
					);

					$tab_args[] = array(
						/* translators: %s: review label (e.g., "Review") */
						'name'            => sprintf( __( 'Adds %s', 'bp-group-reviews' ), $add_review_label ),
						'slug'            => 'add-' . bp_group_review_tab_slug(),
						'parent_slug'     => $bp->groups->current_group->slug,
						'parent_url'      => $gp_parent_url,
						'screen_function' => array( $this, 'bp_group_review_add_review_tab' ),
						'position'        => 199,
						'item_css_id'     => 'add-review',
					);

					foreach ( $tab_args as $tab ) {
						bp_core_new_subnav_item( $tab );
					}
				}
			}
		}

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
		 * Action performed to show screen of reviews listing tab
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
				if ( 'nouveau' == $bp_template_option ) {
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
		 * Register BuddyPress Groups Review triggers
		 *
		 * @param array $triggers
		 * @return mixed
		 */
		public function bp_group_review_bp_activity_triggers( $triggers ) {
			$triggers[ __( 'BuddyPress Group Review', 'bp-group-reviews' ) ] = array(
				'gamipress_bp_group_review' => __( 'Give Group Review', 'bp-group-reviews' ),
			);

			return $triggers;
		}

		public function bp_group_review_trigger_get_user_id( $user_id, $trigger, $args ) {
			if ( 'gamipress_bp_group_review' === $trigger ) {
				$user_id = $args[0];
			}
			return $user_id;
		}


	}
	new BGR_Custom_Hooks();
}
