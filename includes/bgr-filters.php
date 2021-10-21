<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class to add custom hooks for this plugin
 */
if ( ! class_exists( 'BGR_Custom_Hooks' ) ) {
	class BGR_Custom_Hooks {
		/*
		Constructor for custom hooks
		*  @since   1.0.0
		*  @author  Wbcom Designs
		*/

		function __construct() {
			add_action( 'wp', array( $this, 'bgr_add_groups_reviews_tab' ) );
			add_action( 'init', array( $this, 'bgr_add_groups_reviews_taxonomy_term' ) );
			add_filter( 'post_row_actions', array( $this, 'bgr_group_reviews_row_actions' ), 10, 2 );
			add_filter( 'bulk_actions-edit-review', array( $this, 'bgr_remove_edit_bulk_actions' ), 10, 1 );
			add_action( 'bp_before_group_header_meta', array( $this, 'bgr_group_average_rating' ) );
			add_action( 'bp_group_header_actions', array( $this, 'bgr_group_header_review_btn' ) );
			add_action( 'bp_directory_groups_item', array( $this, 'bgr_group_directory_rating' ) );
		}

		/**
		 *  Actions performed to show review button on group header
		 *
		 *  @since   1.0.0
		 *  @access public
		 *  @author  Wbcom Designs
		 */
		public function bgr_group_header_review_btn() {
			global $bgr;
			$review_div          = 'form';
			$exclude_groups      = $bgr['exclude_groups'];
			$current_group_id    = bp_get_current_group_id();
			$current_group_link  = bp_get_group_permalink( groups_get_group( array( 'group_id' => $current_group_id ) ) );
			$current_group_link .= 'add-' . bgr_group_review_tab_slug();
			if ( ! empty( $exclude_groups ) ) {
				if ( ! in_array( bp_get_current_group_id(), $exclude_groups ) ) {
					if ( ! groups_is_user_admin( bp_loggedin_user_id(), bp_get_current_group_id() ) || ! is_user_logged_in() ) { ?>
					<div class="group-button public generic-button" id="groupbutton-85">
						<a href='<?php echo esc_url( $current_group_link ); ?>' class="group-button" show ="<?php echo esc_attr( $review_div ); ?>">
							<?php
							/* translators: %1$s is replaced with bgr_group_add_review_tab_name() */
							echo sprintf( esc_html__( 'Add %1$s', 'bp-group-reviews' ), esc_html( bgr_group_add_review_tab_name() ) );
							?>
						</a>
					</div>
						<?php
					}
				}
			} else {
				if ( ! groups_is_user_admin( bp_loggedin_user_id(), bp_get_current_group_id() ) || ! is_user_logged_in() ) {
					?>
				<div class="group-button public generic-button" id="groupbutton-85">
					<a href='<?php echo esc_url( $current_group_link ); ?>' class="group-button" show ="<?php echo esc_attr( $review_div ); ?>">
						<?php
						/* translators: %1$s is replaced with bgr_group_add_review_tab_name() */
						echo sprintf( esc_html__( 'Add %1$s', 'bp-group-reviews' ), esc_html( bgr_group_add_review_tab_name() ) );
						?>
					</a>
				</div>
					<?php
				}
			}
		}

		/**
		 *  Actions performed to add ratings in group directory page
		 *
		 *  @since   1.0.0
		 *  @access public
		 *  @author  Wbcom Designs
		 */
		public function bgr_group_directory_rating() {
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
			$review_label         = bgr_group_review_tab_name();
			$exclude_groups       = $bgr['exclude_groups'];
			if ( ! empty( $exclude_groups ) ) {
				if ( ! in_array( bp_get_group_id(), $exclude_groups ) ) {
					$this->bgr_single_group_average_rating_data( $reviews, $review_rating_fields, $review_label );
				}
			} else {
				$this->bgr_single_group_average_rating_data( $reviews, $review_rating_fields, $review_label );
			}
		}


		/**
		 *  Actions performed to show rating on groups directing page
		 *
		 *  @since   1.0.0
		 *  @access public
		 *  @author  Wbcom Designs
		 */
		public function bgr_single_group_average_rating_data( $reviews, $review_rating_fields, $review_label ) {
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
						?>
					<div class="buddypress-single-group-reviews-stars" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
						<span itemprop="ratingValue"  content="<?php echo esc_attr( $avg_rating ); ?>"></span>
						<span itemprop="bestRating"   content="5"></span>
						<span itemprop="reviewCount"  content="<?php echo esc_attr( $total_review_count ); ?>"></span>
						<span itemprop="ratingCount"  content="<?php echo esc_attr( $rating_count ); ?>"></span>
						<span itemprop="itemReviewed" content="Person"></span>
						<span itemprop="name" content="<?php echo esc_attr( bp_get_current_group_name() ); ?>"></span>
						<span itemprop="url" content="<?php echo esc_attr( bp_get_group_permalink( groups_get_current_group() ) ); ?>"></span>
					</div>
					<?php } ?>
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

		public function bgr_group_average_rating() {
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
			$review_label         = bgr_group_review_tab_name();
			$exclude_groups       = $bgr['exclude_groups'];
			if ( ! empty( $exclude_groups ) ) {
				if ( ! in_array( bp_get_group_id(), $exclude_groups ) ) {
					$this->bgr_group_average_rating_data( $reviews, $review_rating_fields, $review_label );
				}
			} else {
				$this->bgr_group_average_rating_data( $reviews, $review_rating_fields, $review_label );
			}
		}

		/**
		 *  Actions performed to show average rating
		 *
		 *  @since   1.0.0
		 *  @access public
		 *  @author  Wbcom Designs
		 */
		public function bgr_group_average_rating_data( $reviews, $review_rating_fields, $review_label ) {
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
						?>
					<div class="buddypress-single-group-reviews-stars" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
						<span itemprop="ratingValue"  content="<?php echo esc_attr( $avg_rating ); ?>"></span>
						<span itemprop="bestRating"   content="5"></span>
						<span itemprop="reviewCount"  content="<?php echo esc_attr( $total_review_count ); ?>"></span>
						<span itemprop="ratingCount"  content="<?php echo esc_attr( $rating_count ); ?>"></span>
						<span itemprop="itemReviewed" content="Person"></span>
						<span itemprop="name" content="<?php echo esc_attr( bp_get_current_group_name() ); ?>"></span>
						<span itemprop="url" content="<?php echo esc_attr( bp_get_group_permalink( groups_get_current_group() ) ); ?>"></span>
					</div>
					<?php } ?>
					<div class="bgr-header-row"><div class=" bgr-group-header-ratings"><div class="rating-bgr">
					<?php
					do_action( 'bgr_display_group_average_ratings', $avg_rating );

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
		public function bgr_remove_edit_bulk_actions( $actions ) {
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
		public function bgr_group_reviews_row_actions( $actions, $post ) {
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
					$group_url    = bp_get_group_permalink( $group );
					$review_url   = $group_url . sanitize_title(bgr_group_review_tab_name()) . '/view/' . $post->ID;					

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
		public function bgr_add_groups_reviews_taxonomy_term() {
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
		public function bgr_add_groups_reviews_tab() {
			if ( bp_is_group_single() ) {
				global $bp;
				global $bgr;
				global $post, $wpdb;
				$bgr_admin_general_settings = get_option( 'bgr_admin_general_settings' );
				$review_label               = isset( $bgr['manage_review_label'] ) ? $bgr['manage_review_label'] : '';

				$args = array(
					'category'         => 'group',
					'orderby'          => 'post_date',
					'order'            => 'DESC',
					'meta_key'         => 'linked_group',
					'meta_value'       => $bp->groups->current_group->id,
					'post_type'        => 'review',
					'post_status'      => 'publish',
					'suppress_filters' => true,
				);

				$post_count   = 0;
				$recent_posts = wp_get_recent_posts( $args );
				if ( ! empty( $recent_posts ) ) :
					foreach ( $recent_posts as $recent ) {
						$post_count++;
					}
				endif;
				wp_reset_query();
				$user               = bp_get_loggedin_user_username();
				$count_notification = '<span>' . $post_count . '</span>';
				if ( ! empty( $bgr_admin_general_settings ) ) {
					$exclude_groups = $bgr['exclude_groups'];
					if ( ! empty( $exclude_groups ) ) {
						if ( ! in_array( $bp->groups->current_group->id, $exclude_groups ) ) {
							$tab_args = array(
								'name'            => $review_label . ' ' . $count_notification,
								'slug'            => sanitize_title( $bgr['manage_review_label'] ),
								'parent_slug'     => $bp->groups->current_group->slug,
								'parent_url'      => bp_get_group_permalink( $bp->groups->current_group ),
								'screen_function' => array( $this, 'bgr_group_reviews_tab' ),
								'position'        => 199,
							);
							bp_core_new_subnav_item( $tab_args );
						}
					} else {
						$tab_args = array(
							'name'            => $review_label . ' ' . $count_notification,
							'slug'            => sanitize_title( $bgr['manage_review_label'] ),
							'parent_slug'     => $bp->groups->current_group->slug,
							'parent_url'      => bp_get_group_permalink( $bp->groups->current_group ),
							'screen_function' => array( $this, 'bgr_group_reviews_tab' ),
							'position'        => 199,
						);
						bp_core_new_subnav_item( $tab_args );
					}
				} else {
					$tab_args = array(
						'name'            => $review_label . ' ' . $count_notification,
						'slug'            => sanitize_title( $bgr['manage_review_label'] ),
						'parent_slug'     => $bp->groups->current_group->slug,
						'parent_url'      => bp_get_group_permalink( $bp->groups->current_group ),
						'screen_function' => array( $this, 'bgr_group_reviews_tab' ),
						'position'        => 199,
					);
					bp_core_new_subnav_item( $tab_args );
				}
			}
		}

		public function bgr_group_add_review_tab() {
			add_action( 'bp_template_content', array( $this, 'bgr_group_reviews_tab_template' ) );
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
		public function bgr_group_reviews_tab() {
			add_action( 'bp_template_content', array( $this, 'bgr_group_reviews_tab_template' ) );
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
		public function bgr_group_reviews_tab_template() {
			global $bgr;			 
			if ( strpos( $_SERVER['REQUEST_URI'], sanitize_title(bgr_group_review_tab_name()).'/view' ) !== false ) {
				include 'templates/bgr-single-review-template.php';
			} elseif ( sanitize_title( $bgr['manage_review_label'] ) === bp_current_action() ) {
				include 'templates/bgr-reviews-tab-template.php';
			}
		}


	}
	new BGR_Custom_Hooks();
}
