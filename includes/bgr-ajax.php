<?php
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

			add_action( 'wp_ajax_bgr_save_admin_criteria_settings', array( $this, 'bgr_save_admin_criteria_settings' ) );
			add_action( 'wp_ajax_nopriv_bgr_save_admin_criteria_settings', array( $this, 'bgr_save_admin_criteria_settings' ) );
			add_action( 'wp_ajax_bgr_save_admin_display_settings', array( $this, 'bgr_save_admin_display_settings' ) );
			add_action( 'wp_ajax_nopriv_bgr_save_admin_display_settings', array( $this, 'bgr_save_admin_display_settings' ) );
			add_action( 'wp_ajax_bgr_save_admin_general_settings', array( $this, 'bgr_save_admin_general_settings' ) );
			add_action( 'wp_ajax_bgr_accept_review', array( $this, 'bgr_accept_review' ) );
			add_action( 'wp_ajax_nopriv_bgr_accept_review', array( $this, 'bgr_accept_review' ) );
			add_action( 'wp_ajax_bgr_deny_review', array( $this, 'bgr_deny_review' ) );
			add_action( 'wp_ajax_nopriv_bgr_deny_review', array( $this, 'bgr_deny_review' ) );
			add_action( 'wp_ajax_bgr_remove_review', array( $this, 'bgr_remove_review' ) );
			add_action( 'wp_ajax_nopriv_bgr_remove_review', array( $this, 'bgr_remove_review' ) );
			add_action( 'wp_ajax_bgr_submit_review', array( $this, 'bgr_submit_review' ) );
			add_action( 'wp_ajax_nopriv_bgr_submit_review', array( $this, 'bgr_submit_review' ) );

			/* add action for approving reviews */
			add_action( 'wp_ajax_bgr_admin_approve_review', array( $this, 'bgr_admin_approve_review' ) );
			add_action( 'wp_ajax_nopriv_bgr_admin_approve_review', array( $this, 'bgr_admin_approve_review' ) );
			// Filter widget ratings.
			add_action( 'wp_ajax_bgr_filter_ratings', array( $this, 'bgr_filter_ratings' ) );
			add_action( 'wp_ajax_nopriv_bgr_filter_ratings', array( $this, 'bgr_filter_ratings' ) );

			// Filter Reviews listings.
			add_action( 'wp_ajax_bgr_reviews_filter', array( $this, 'bgr_reviews_filter' ) );
			add_action( 'wp_ajax_nopriv_bgr_reviews_filter', array( $this, 'bgr_reviews_filter' ) );
		}

		/**
		 * Actions performed to filter member reviews.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bgr_reviews_filter() {
			if ( filter_input( INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS ) && filter_input( INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS ) === 'bgr_reviews_filter' ) {
				global $bp, $post;
				global $bgr;
				$filter               = sanitize_text_field( filter_input( INPUT_POST, 'filter' ) );
				$limit                = $bgr['reviews_per_page'];
				$review_rating_fields = $bgr['review_rating_fields'];
				$custom_args          = array(
					'post_type'      => 'review',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
					'category'       => 'review_category',
					'meta_key'       => 'linked_group',
					'meta_value'     => bp_get_current_group_id(),
				);
				$reviews_arr          = get_posts( $custom_args );
				$html                 = '';
				$single_review_count = 0;
				$final_review_obj    = array();
				$single_rev_avg = array();
				if ( ! empty( $reviews_arr ) ) {					
					foreach ( $reviews_arr as $review ) {
						$linked_group   = get_post_meta( $review->ID, 'linked_group', false );
						$review_ratings = get_post_meta( $review->ID, 'review_star_rating', false );
						if ( ! empty( $review_ratings ) && ! empty( $review_rating_fields ) ) {
							$rev_rating_array    = $review_ratings[0];
							$total_review        = 0;
							$single_review_count = 0;
							foreach ( $review_rating_fields as $rating_field ) {
								if ( array_key_exists( $rating_field, $rev_rating_array ) ) {
									$total_review += $rev_rating_array[ $rating_field ];
									$single_review_count++;
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
				if ( ! empty( $single_rev_avg ) ) {
					if ( 'highest' === $filter ) {
						arsort( $single_rev_avg );
					} elseif ( 'lowest' === $filter ) {
						asort( $single_rev_avg );
					} else {
						$single_rev_avg = $single_rev_avg;
					}
				}

				$args    = array(
					'post_type'      => 'review',
					'post_status'    => 'publish',
					'category'       => 'group',
					'posts_per_page' => $limit,
					'paged'          => get_query_var( 'page', 1 ),
					'post__in'       => array_keys( $single_rev_avg ),
					'orderby'        => 'post__in',
					'meta_query'     => array(
						array(
							'key'     => 'linked_group',
							'value'   => bp_get_current_group_id(),
							'compare' => '=',
						),
					),
				);
				$reviews = new WP_Query( $args );

				if ( $reviews->have_posts() ) {
					while ( $reviews->have_posts() ) :
						$reviews->the_post();
						$html  .= '<div class="bgr-row item-list group-request-list"><div class="bgr-col-2">';
						$author = $reviews->post->post_author;
						$html  .= bp_get_displayed_user_avatar( array( 'item_id' => $author ) );
						$html  .= '</div><div class="bgr-col-8"><div class="reviewer"><b>' . bp_core_get_userlink( $author ) . '</b></div>';

						$html       .= '<div class="item-description"><div class="review-description">';
						$trimcontent = get_the_content();
						$url         = bp_get_group_url() . sanitize_title( bgr_group_review_tab_name() ) . '\/view/' . get_the_id();
						if ( ! empty( $trimcontent ) ) {
							$len = strlen( $trimcontent );
							if ( $len > 150 ) {
								$shortexcerpt = substr( $trimcontent, 0, 150 );
								$html        .= $shortexcerpt;
								$html        .= '<a href="' . $url . '"><i><b>' . esc_html__( 'read more...', 'bp-group-reviews' ) . '</b></i></a>';
							} else {
								$html .= $trimcontent;
							}
						}
						$html .= '<div class="review-ratings">';
						ob_start();
						do_action( 'bgr_display_ratings', $post->ID );
						$html .= ob_get_clean();
						$html .= '</div></div></div></div>';
						$html .= '<div class="bgr-col-2">';
						if ( groups_is_user_admin( $member_id, bp_get_group_id() ) ) :
							$html .= '<div class="remove-review generic-button">';
							$html .= '<a class="remove-review-button">' . __( 'Delete', 'bp-group-reviews' ) . '</a><input type="hidden" name="remove_review_id" value="' . esc_attr( $post->ID ) . '"></div>';
						endif;
						$html .= '</div><div class="clear"></div></div>';
				endwhile;
					$total_pages = $reviews->max_num_pages;
					if ( $total_pages > 3 ) {
						$html        .= '<div class="review-pagination">';
						$current_page = max( 1, get_query_var( 'paged' ) );
						$html        .= paginate_links(
							array(
								'base'      => get_pagenum_link( 1 ) . '%_%',
								'format'    => 'page/%#%',
								'current'   => $current_page,
								'total'     => $total_pages,
								'prev_text' => esc_html__( 'prev', 'bp-group-reviews' ),
								'next_text' => esc_html__( 'next', 'bp-group-reviews' ),
							)
						);
						$html        .= '</div>';
					}
					wp_reset_postdata();

				} else {

					$bp_template_option = bp_get_option( '_bp_theme_package_id' );
					if ( 'nouveau' == $bp_template_option ) {
						$html .= '<div id="message" class="info bp-feedback bp-messages bp-template-notice">
					<span class="bp-icon" aria-hidden="true"></span>';
					} else {
						$html .= '<div id="message" class="info">';
					}
					$review_label = isset( $bgr['review_label'] ) && ! empty( $bgr['review_label'] ) ? $bgr['review_label'] : 'Review';
					/* translators: %1$s is replaced with review_label */
					$html .= '<p>' . sprintf( esc_html__( 'Sorry, no %1$s were found.', 'bp-group-reviews' ), $review_label ) . '</p>';
					$html .= '</div>';
				}
				$html .= '</div>';
				echo wp_kses_post( stripslashes( $html ) );
				die;

			}
		}

		/**
		 * Actions performed to filter member ratings.
		 *
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bgr_filter_ratings() {
			if ( filter_input( INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS ) && filter_input( INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS ) === 'bgr_filter_ratings' ) {
				global $bp, $post;
				global $bgr;
				$filter               = sanitize_text_field( filter_input( INPUT_POST, 'filter' ) );
				$limit                = sanitize_text_field( filter_input( INPUT_POST, 'limit' ) );
				$html                 = '';
				$review_rating_fields = $bgr['review_rating_fields'];

				$custom_args = array(
					'post_type'      => 'review',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
					'category'       => 'review_category',
					'meta_key'       => 'linked_group',
					'meta_value'     => bp_get_current_group_id(),
				);
				$reviews     = get_posts( $custom_args );

				$final_review_obj = array();
				$single_rev_avg   = array();
				if ( ! empty( $reviews ) ) {
					foreach ( $reviews as $review ) {
						$review_ratings = get_post_meta( $review->ID, 'review_star_rating', false );
						if ( ! empty( $review_ratings ) && ! empty( $review_rating_fields ) ) {
							$rev_rating_array    = $review_ratings[0];
							$total_review        = 0;
							$single_review_count = 0;
							foreach ( $review_rating_fields as $rating_field ) {
								if ( array_key_exists( $rating_field, $rev_rating_array ) ) {
									$total_review += $rev_rating_array[ $rating_field ];
									$single_review_count++;
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
					if ( 'highest' == $filter ) {
						arsort( $single_rev_avg );
					} elseif ( 'lowest' == $filter ) {
						asort( $single_rev_avg );
					} else {
						$single_rev_avg = $single_rev_avg;
					}
					foreach ( $single_rev_avg as $bgrKey => $bgrValue ) {
						if ( $bgr_user_count == $limit ) {
							break;
						} else {
							$html .= '<li class="vcard"><div class="item-avatar">';
							$html .= get_avatar( $final_review_obj[ $bgrKey ]->post_author, 65 );
							$html .= '</div>';
							$html .= '<div class="item">';

							$members_profile = bp_core_get_userlink( $final_review_obj[ $bgrKey ]->post_author );
							$html           .= '<div class="item-title fn">';
							$html           .= $members_profile;
							$html           .= '</div>';

							$bgr_avg_rating = $bgrValue;
							$stars_on       = $stars_off = $stars_half = '';
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

						$bgr_user_count++;
					}
				} else {
					$html .= '<p>' . esc_html__( 'No Rating has been given by any member yet!', 'bp-group-reviews' ) . '</p>';
				}
				$result = array(
					'html' => $html,
				);
				echo wp_json_encode( $result );
			}
			die;
		}

		/**
		 * Actions performed to approve review at admin end
		 */
		public function bgr_admin_approve_review() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'admin-ajax-nonce' ) ) {
				$error = new WP_Error( '001', 'Nonce not verified!', 'Some information' );
				wp_send_json_error( $error );
			}
			if ( isset( $_POST['action'] ) && 'bgr_admin_approve_review' === $_POST['action'] && current_user_can( 'administrator' ) ) {
				$rid  = isset( $_POST['review_id'] ) ? sanitize_text_field( wp_unslash( $_POST['review_id'] ) ) : '';
				$args = array(
					'ID'          => $rid,
					'post_status' => 'publish',
				);
				wp_update_post( $args );
				$author_id = get_post_field( 'post_author', $rid );
				do_action( 'gamipress_bp_group_review', $author_id );
				echo 'review-approved-successfully';
				die;
			}
		}


		/**
		 *  Actions performed for saving admin criteria settings
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */
		public function bgr_save_admin_criteria_settings() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'admin-ajax-nonce' ) ) {
				$error = new WP_Error( '001', 'Nonce not verified!', 'Some information' );
				wp_send_json_error( $error );
			}
			if ( isset( $_POST['action'] ) && 'bgr_save_admin_criteria_settings' === $_POST['action'] && current_user_can( 'administrator' ) ) {
				
				// Ensure the variables are always treated as arrays
				$rating_fields = isset( $_POST['field_values'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['field_values'] ) ) : array();
				$rating_field_values = array_unique( $rating_fields );
				
				$active_rating_fields = isset( $_POST['active_criterias'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['active_criterias'] ) ) : array();
				$active_rating_fields_values = array_unique( $active_rating_fields );
				
				$bgr_admin_settings = array(
					'add_review_rating_fields' => $rating_field_values,
					'active_rating_fields'     => $active_rating_fields_values,
				);

				update_option( 'bgr_admin_criteria_settings', $bgr_admin_settings );
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
		public function bgr_save_admin_general_settings() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'admin-ajax-nonce' ) ) {
				$error = new WP_Error( '001', 'Nonce not verified!', 'Some information' );
				wp_send_json_error( $error );
			}
			if ( isset( $_POST['action'] ) && 'bgr_save_admin_general_settings' === $_POST['action'] && current_user_can( 'administrator' ) ) {
				$multi_reviews        = isset( $_POST['multi_reviews'] ) ? sanitize_text_field( wp_unslash( $_POST['multi_reviews'] ) ) : '';
				$auto_approve_reviews = isset( $_POST['bgr_auto_approve_reviews'] ) ? sanitize_text_field( wp_unslash( $_POST['bgr_auto_approve_reviews'] ) ) : '';
				$reviews_per_page     = isset( $_POST['reviews_per_page'] ) ? sanitize_text_field( wp_unslash( $_POST['reviews_per_page'] ) ) : '';
				$allow_email          = isset( $_POST['allow_email'] ) ? sanitize_text_field( wp_unslash( $_POST['allow_email'] ) ) : '';
				$allow_notification   = isset( $_POST['allow_notification'] ) ? sanitize_text_field( wp_unslash( $_POST['allow_notification'] ) ) : '';
				$allow_activity       = isset( $_POST['allow_activity'] ) ? sanitize_text_field( wp_unslash( $_POST['allow_activity'] ) ) : '';
				$review_email_subject = isset( $_POST['review_email_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['review_email_subject'] ) ) : '';
				$review_email_message = isset( $_POST['review_email_message'] ) ? sanitize_text_field( wp_unslash( $_POST['review_email_message'] ) ) : '';
				$exclude_groups       = isset( $_POST['exclude_groups'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['exclude_groups'] ) ) : '';
				if ( empty( $exclude_groups ) ) {
					$exclude_groups = array();
				}
				$bgr_admin_settings = array(
					'multi_reviews'        => $multi_reviews,
					'auto_approve_reviews' => $auto_approve_reviews,
					'reviews_per_page'     => $reviews_per_page,
					'allow_email'          => $allow_email,
					'allow_notification'   => $allow_notification,
					'allow_activity'       => $allow_activity,
					'exclude_groups'       => $exclude_groups,
					'review_email_subject' => $review_email_subject,
					'review_email_message' => $review_email_message,
				);
				update_option( 'bgr_admin_general_settings', $bgr_admin_settings );
				echo 'admin-general-settings-saved';
				die;
			}
		}

		/**
		 *  Actions performed for saving admin display settings
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */
		public function bgr_save_admin_display_settings() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'admin-ajax-nonce' ) ) {
				$error = new WP_Error( '001', 'Nonce not verified!', 'Some information' );
				wp_send_json_error( $error );
			}
			if ( isset( $_POST['action'] ) && 'bgr_save_admin_display_settings' === $_POST['action'] && current_user_can( 'administrator' ) ) {

				$manage_review_label = isset( $_POST['manage_review_label'] ) ? sanitize_text_field( wp_unslash( $_POST['manage_review_label'] ) ) : '';
				$review_label        = isset( $_POST['review_label'] ) ? sanitize_text_field( wp_unslash( $_POST['review_label'] ) ) : '';
				$bgr_rating_color    = isset( $_POST['bgr_rating_color'] ) ? sanitize_text_field( wp_unslash( $_POST['bgr_rating_color'] ) ) : '';

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
		public function bgr_submit_review() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
				$error = new WP_Error( '001', 'Nonce not verified!', 'Some information' );
				wp_send_json_error( $error );
			}
			if ( ! is_user_logged_in() ) {
				return false;
			}
			global $bp;
			global $bgr;
			$bp_group_review_email_settigs = get_option( 'bp_group_review_email_settigs' );
			$bgr_allow_email      = isset( $bp_group_review_email_settigs['bgr_allow_email'] ) ? $bp_group_review_email_settigs['bgr_allow_email'] : '';
			$current_user         = wp_get_current_user();
			$member_id            = $current_user->ID;
			$user_name            = $current_user->display_name;
			$active_rating_fields = $bgr['active_rating_fields'];
			$allow_notification   = $bgr['allow_notification'];
			$allow_email          = $bgr_allow_email;
			$allow_activity       = $bgr['allow_activity'];
			$review_label         = $bgr['review_label'];
			$auto_approve_reviews = $bgr['auto_approve_reviews'];
			$multi_reviews        = $bgr['multi_reviews'];
			/* Translators: %1$s: Review Label */
			$review_email_subject = ( isset( $bgr['review_email_subject'] ) ) ? $bgr['review_email_subject'] : sprintf( esc_html__( 'A new %1$s posted.', 'bp-group-reviews' ), $review_label );
			/* Translators: %1$s: Review Label %2$s Group Name %3$s User Name %4$s User Link */
			$review_email_message = ( isset( $bgr['review_email_message'] ) ) ? $bgr['review_email_message'] : esc_html__( 'A new %1$s for %2$s added by %3$s. Link: %4$s', 'bp-group-reviews' );
			if ( isset( $_POST['data'] ) ) {
				wp_parse_str( wp_unslash( filter_input( INPUT_POST, 'data', FILTER_UNSAFE_RAW ) ), $formarray );				
			}
			$review_subject = sanitize_text_field( $formarray['review-subject'] );
			$review_desc    = sanitize_text_field( $formarray['review-desc'] );
			$form_group_id  = sanitize_text_field( $formarray['form-group-id'] );
			$group_obj      = groups_get_group( $form_group_id );
			$group_name     = $group_obj->name;
			/* Translators: %1$s: User Name %2$s Review Label %3$s Group Name */
			$review_cpt_subject = sprintf( esc_html__( '%1$s %2$ss Group %3$s.', 'bp-group-reviews' ), $user_name, $review_label, $group_name );			
			$headers[]          = 'Content-Type: text/html; charset=UTF-8';
			$group_args         = array(
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
			$reviews_args       = new WP_Query( $group_args );
			if ( $multi_reviews == 'no' ) {
				$user_post_count = $reviews_args->post_count;
			} else {
				$user_post_count = 0;
			}
			if ( $user_post_count > 0 ) {
				/* translators: %1$s is replaced with review_label */
				$review_add_msg = sprintf( __( 'You already posted a %1$s for this group.', 'bp-group-reviews' ), $review_label );
			} else {
				if ( $auto_approve_reviews == 'yes' ) {
					/* translators: %1$s is replaced with review_label */
					$review_add_msg = sprintf( __( 'Thank you for taking time to write this wonderful %1$s.', 'bp-group-reviews' ), strtolower( $review_label ) );
					$review_status  = 'publish';
				} else {
					/* translators: %1$s is replaced with review_label */
					$review_add_msg = sprintf( __( 'Thank you for taking time to write this wonderful %1$s. Your %1$s will display after moderator\'s approval.', 'bp-group-reviews' ), strtolower( $review_label ) );
					$review_status  = 'draft';
				}

				if ( ! empty( $formarray['rated_stars'] ) ) {
					$rated_field_values = array_map( 'sanitize_text_field', wp_unslash( $formarray['rated_stars'] ) );
				}

				if ( ! empty( $active_rating_fields ) && ! empty( $rated_field_values ) ) {
					$rated_stars = array_combine( $active_rating_fields, $rated_field_values );
				} else {
					$rated_stars = $rated_field_values;
				}

				$add_review_args = array(
					'post_type'    => 'review',
					'post_title'   => $review_cpt_subject,
					'post_content' => $review_desc,
					'post_status'  => $review_status,
				);
				$review_id       = wp_insert_post( $add_review_args );
				
				if ( 'publish' === $review_status ) {
					do_action( 'gamipress_bp_group_review', $member_id );
				}

				do_action( 'bgr_group_review_after_review_insert' );
				$post_author_id = get_post_field( 'post_author', $review_id );
				wp_set_object_terms( $review_id, 'Group', 'review_category' );
				update_post_meta( $review_id, 'linked_group', $form_group_id );
				$group      = groups_get_group( array( 'group_id' => $form_group_id ) );
				$creator_id = $group->creator_id;
				$creator_info = get_userdata( $creator_id );
				$creator_name = $creator_info->display_name;
				$group_name = $group->name;
				$site_name = get_bloginfo( 'name' );
				$user_info  = get_userdata( $post_author_id );
				$user_name  = $user_info->user_login;

				if ( $auto_approve_reviews == 'yes' ) {
					$mail_link = bp_get_groups_directory_permalink() . $group->slug . '/' . sanitize_title( bgr_group_review_tab_name() ) . '/';
				} else {
					$mail_link = '<a href=" '. admin_url( 'edit.php?post_type=review' ). ' ">'. admin_url( 'edit.php?post_type=review' ) .'</a>';
				}
				// Use custom subject if set
				if ( isset( $bp_group_review_email_settigs['review_email_subject'] ) && ! empty( $bp_group_review_email_settigs['review_email_subject'] ) ) {
					$mail_title = str_replace(
						array( '[group-name]', '[site-name]' ),
						array( $group_name, $site_name ),
						$bp_group_review_email_settigs['review_email_subject']
					);
				}

				// Use custom message if set
				if ( isset( $bp_group_review_email_settigs['review_email_message'] ) && ! empty( $bp_group_review_email_settigs['review_email_message'] ) ) {
					$mail_content = str_replace(
						array( '[admin-name]' ,'[group-name]', '[user-name]', '[review-link]', '[site-name]' ),
						array( $creator_name, $group_name, $user_name, $mail_link, $site_name ),
						$bp_group_review_email_settigs['review_email_message']
					);
				}else{
					$submit_message = 'Hello [admin-name],<br><br>
					We are excited to inform you that a new review has been submitted for your group [group-name], by [user-name]. Your members\'s feedback is invaluable in fostering a vibrant and engaging community.<br><br>
					You can read and respond to the review by following the link below:[review-link]<br><br>
					Thank you for creating a space where members can share their thoughts and experiences. Keep up the great work!<br><br>
					Best regards,<br>
					The [site-name] Team';
					$mail_content = str_replace(
						array( '[admin-name]' ,'[group-name]', '[user-name]', '[review-link]', '[site-name]' ),
						array( $creator_name, $group_name, $user_name, $mail_link, $site_name ),
						$submit_message
					);
				}

				if ( ! empty( $rated_stars ) ) {
					update_post_meta( $review_id, 'review_star_rating', $rated_stars );
				}

				$group_admins = groups_get_group( $form_group_id );

				if ( 'yes' == $allow_notification || 'yes' == $allow_activity ) {
					foreach ( $group_admins->admins as $group_admin ) {
						$admin_id = $group_admin->user_id;
						do_action( 'bgr_group_add_review', $form_group_id, $admin_id );
					}
				}
				
				if ( 'yes' == $allow_email ) {
					foreach ( $group_admins->admins as $group_admin ) {
						$author_email = get_the_author_meta( 'user_email', $group_admin->user_id );
						wp_mail( $author_email, $mail_title, $mail_content, $headers );
					}
				}
				do_action( 'bgr_group_after_review_submit', $post_author_id, $form_group_id, $review_id );
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
		public function bgr_accept_review() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
				$error = new WP_Error( '001', 'Nonce not verified!', 'Some information' );
				wp_send_json_error( $error );
			}
			if ( ! is_user_logged_in() ) {
				return false;
			}
			global $bgr;
			global $bp;
			$bp_group_review_email_settigs = get_option( 'bp_group_review_email_settigs' );
			$post_id            = isset( $_POST['accept_review_id'] ) ? sanitize_text_field( wp_unslash( $_POST['accept_review_id'] ) ) : '';
			$post_author_id     = get_post_field( 'post_author', $post_id );
			wp_publish_post( $post_id );
			$allow_notification = $bgr['allow_notification'];
			$allow_email        = $bp_group_review_email_settigs['bgr_accept_enable'];
			$review_label       = $bgr['review_label'];
			$group_id           = get_post_meta( $post_id, 'linked_group', true );
			$group              = groups_get_group( array( 'group_id' => $group_id ) );
			$creator_id         = $group->creator_id;
			$creator_info       = get_userdata( $creator_id );
			$creator_name       = $creator_info->display_name;
			$group_name         = $group->name;
			$site_name          = get_bloginfo( 'name' );
			$current_user       = wp_get_current_user();
			$member_id          = $current_user->ID;
			$user_name          = $current_user->display_name;
			$review_link        = bp_get_groups_directory_permalink() . $group->slug . "/reviews/view/$post_id/";
			$headers[]          = 'Content-Type: text/html; charset=UTF-8';
			if ( $auto_approve_reviews == 'yes' ) {
				$mail_link = bp_get_groups_directory_permalink() . $group->slug . '/' . sanitize_title( bgr_group_review_tab_name() ) . '/';
			} else {
				$mail_link = '<a href=" '. admin_url( 'edit.php?post_type=review' ). ' ">'. admin_url( 'edit.php?post_type=review' ) .'</a>';
			}

			// Use custom subject if set
			if ( isset( $bp_group_review_email_settigs['review_accept_email_subject'] ) && ! empty( $bp_group_review_email_settigs['review_accept_email_subject'] ) ) {
				$mail_title = str_replace(
					array( '[group-name]', '[site-name]' ),
					array( $group_name, $site_name ),
					$bp_group_review_email_settigs['review_accept_email_subject']
				);
			}
			
			// Use custom message if set
			if ( isset( $bp_group_review_email_settigs['review_accept_email_message'] ) && ! empty( $bp_group_review_email_settigs['review_accept_email_message'] ) ) {
				$mail_content = str_replace(
					array( '[group-name]', '[user-name]', '[review-link]', '[admin-name]', '[site-name]' ),
					array( $group_name, $user_name, $mail_link, $creator_name, $site_name ),
					$bp_group_review_email_settigs['review_accept_email_message']
				);
			}else{
				$accept_message = 'Hello [admin-name],<br><br>
				We are pleased to inform you that your review for the group, [group-name] on [site-name] has been reviewed and approved by the administrator.<br><br>
				We appreciate the time and effort you took to share your thoughts about the group.[review-link]<br><br>
				Thank you for being an active and valuable member of our community.<br><br>
				Best regards,<br>
				The [site-name] Team';
				$mail_content = str_replace(
					array( '[group-name]', '[user-name]', '[review-link]', '[admin-name]', '[site-name]' ),
					array( $group_name, $user_name, $mail_link, $creator_name, $site_name ),
					$accept_message
				);
			}

			if ( 'yes' == $allow_notification ) {
				do_action( 'bgr_group_accept_review', $post_id );
			}

			if ( 'yes' == $allow_email ) {
				$author_email = get_the_author_meta( 'user_email', $post_author_id );
				wp_mail( $author_email, $mail_title, $mail_content, $headers );
			}

			die;
		}

		/**
		 *  Actions performed when deny review
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */
		public function bgr_deny_review() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
				$error = new WP_Error( '001', 'Nonce not verified!', 'Some information' );
				wp_send_json_error( $error );
			}
			if ( ! is_user_logged_in() ) {
				return false;
			}
			global $bgr;
			global $bp;
			$post_id              = isset( $_POST['deny_review_id'] ) ? sanitize_text_field( wp_unslash( $_POST['deny_review_id'] ) ) : '';
			$group_id             = isset( $_POST['group_id'] ) ? sanitize_text_field( wp_unslash( $_POST['group_id'] ) ) : '';
			$post_author_id       = get_post_field( 'post_author', $post_id );
			$bp_group_review_email_settigs = get_option( 'bp_group_review_email_settigs' );
			wp_trash_post( $post_id );
			$allow_notification   = $bgr['allow_notification'];
			$allow_email          = $bp_group_review_email_settigs['bgr_deny_email'];
			$review_label         = $bgr['review_label'];
			$group_id             = get_post_meta( $post_id, 'linked_group', true );
			$group                = groups_get_group( array( 'group_id' => $group_id ) );
			$creator_id           = $group->creator_id;
			$creator_info         = get_userdata( $creator_id );
			$creator_name         = $creator_info->display_name;
			$group_name           = $group->name;
			$site_name            = get_bloginfo( 'name' );
			$current_user         = wp_get_current_user();
			$member_id            = $current_user->ID;
			$user_name            = $current_user->display_name;
			$auto_approve_reviews = $bgr['auto_approve_reviews'];			
			$headers[]          = 'Content-Type: text/html; charset=UTF-8';
			if ( $auto_approve_reviews == 'yes' ) {
				$mail_link = bp_get_groups_directory_permalink() . $group->slug . '/' . sanitize_title( bgr_group_review_tab_name() ) . '/';
			} else {
				$mail_link = '<a href=" '. admin_url( 'edit.php?post_type=review' ). ' ">'. admin_url( 'edit.php?post_type=review' ) .'</a>';
			}

			// Use custom subject if set
			if ( isset( $bp_group_review_email_settigs['review_deny_email_subject'] ) && ! empty( $bp_group_review_email_settigs['review_deny_email_subject'] ) ) {
				$mail_title = str_replace(
					array( '[group-name]', '[site-name]' ),
					array( $group_name, $site_name ),
					$bp_group_review_email_settigs['review_deny_email_subject']
				);
			}

			// Use custom message if set
			if ( isset( $bp_group_review_email_settigs['review_deny_email_message'] ) && ! empty( $bp_group_review_email_settigs['review_deny_email_message'] ) ) {
				$mail_content = str_replace(
					array( '[group-name]', '[user-name]', '[review-link]', '[admin-name]', '[site-name]' ),
					array( $group_name, $user_name, $mail_link, $creator_name, $site_name ),
					$bp_group_review_email_settigs['review_deny_email_message']
				);
			} else{
				$deny_message = 'Hello [admin-name],<br><br>
				We regret to inform you that after careful consideration, your review for the group, [group-name] on [site-name] has been denied by the administrator.<br><br>
				While we appreciate your willingness to share your thoughts and feedback, it seems that certain aspects of the review did not align with our community guidelines or policies.[review-link]<br><br>
				Thank you for your understanding and cooperation.<br><br>
				Best regards,<br>
				The [site-name] Team';
				$mail_content = str_replace(
					array( '[group-name]', '[user-name]', '[review-link]', '[admin-name]', '[site-name]' ),
					array( $group_name, $user_name, $mail_link, $creator_name, $site_name ),
					$deny_message
				);
			}

			if ( 'yes' == $allow_notification ) {
				do_action( 'bgr_group_deny_review', $post_id );
			}

			if ( 'yes' == $allow_email ) {
				$author_email = get_the_author_meta( 'user_email', $post_author_id );
				wp_mail( $author_email, $mail_title, $mail_content, $headers );
			}
			die;
		}

		/**
		 *  Actions performed when remove review
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */
		public function bgr_remove_review() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
				$error = new WP_Error( '001', 'Nonce not verified!', 'Some information' );
				wp_send_json_error( $error );
			}
			if ( ! is_user_logged_in() ) {
				return false;
			}
			$post_id = isset( $_POST['remove_review_id'] ) ? sanitize_text_field( wp_unslash( $_POST['remove_review_id'] ) ) : '';
			wp_trash_post( $post_id );
			die;
		}

	}
	new BGR_AJAX();
}
