<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class to serve AJAX Calls
 */

if ( ! class_exists( 'BGR_AJAX' ) ) {
	class BGR_AJAX {

		/**
		 * Constructor for Group Reviews ajax
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */

		function __construct() {

			add_action( 'wp_ajax_bgr_save_admin_criteria_settings', array( $this, 'bgr_save_admin_criteria_settings' ) );
			add_action( 'wp_ajax_nopriv_bgr_save_admin_criteria_settings', array( $this, 'bgr_save_admin_criteria_settings' ) );
			add_action( 'wp_ajax_bgr_save_admin_display_settings', array( $this, 'bgr_save_admin_display_settings' ) );
			add_action( 'wp_ajax_nopriv_bgr_save_admin_display_settings', array( $this, 'bgr_save_admin_display_settings' ) );
			add_action( 'wp_ajax_bgr_save_admin_general_settings', array( $this, 'bgr_save_admin_general_settings' ) );
			add_action( 'wp_ajax_nopriv_bgr_save_admin_general_settings', array( $this, 'bgr_save_admin_general_settings' ) );
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
		}

		/**
		 * Actions performed to filter member ratings.
		 *
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bgr_filter_ratings() {
			if ( filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) && filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) === 'bgr_filter_ratings' ) {
				global $bp, $post;
				global $bgr;
				$filter = sanitize_text_field( filter_input( INPUT_POST, 'filter' ) );
				$limit  = sanitize_text_field( filter_input( INPUT_POST, 'limit' ) );
				$html   = '';
				$review_rating_fields = $bgr['review_rating_fields'];
				
				$custom_args         = array(
					'post_type'      => 'review',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
					'category'       => 'review_category',
					'meta_key'       => 'linked_group',
					'meta_value'     => bp_get_current_group_id()
				);
				$reviews             = get_posts( $custom_args );

				$final_review_obj    = array();
				$single_rev_avg      = array();
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
							$html .= get_avatar( $final_review_obj[$bgrKey]->ID, 65 );
							$html .= '</div>';
							$html .= '<div class="item">';
							
							$members_profile = bp_core_get_userlink( $final_review_obj[$bgrKey]->post_author );
							$html .= '<div class="item-title fn">';
							$html .= $members_profile;
							$html .= '</div>';

							$bgr_avg_rating     = $bgrValue;
							$stars_on           = $stars_off = $stars_half = '';
							$remaining          = $bgr_avg_rating - (int) $bgr_avg_rating;
							if ( $remaining > 0 ) {
								$stars_on        = intval( $bgr_avg_rating );
								$stars_half      = 1;
								$bgr_half_squar  = 1;
								$stars_off       = 5 - ( $stars_on + $stars_half );
							} else {
								$stars_on   = $bgr_avg_rating;
								$stars_off  = 5 - $bgr_avg_rating;
								$stars_half = 0;
							}
							$html .= '<div class="item-meta">';
							for ( $i = 1; $i <= $stars_on; $i++ ) {
								$html .='<span class="fas fa-star stars bgr-star-rate"></span>';
							}

							for ( $i = 1; $i <= $stars_half; $i++ ) {
								$html .='<span class="fas fa-star-half-alt stars bgr-star-rate"></span>';
							}

							for ( $i = 1; $i <= $stars_off; $i++ ) {
								$html .= '<span class="far fa-star stars bgr-star-rate"></span>';
							}

							$html .= '</div>';

							$bgr_avg_rating = round( $bgr_avg_rating, 2 );
							$html .= '<span class="bgr-meta">';
							$html .= sprintf( esc_html__( 'Rating : ( %1$s )', 'bp-group-reviews' ), esc_html( $bgr_avg_rating ) );
							$html .= '</span>';
							$html .= '</div></li>';
							
						}

						$bgr_user_count++;
					}
				} else {
					$html .= '<p>' . esc_html__( 'No Rating has been given by any member yet!.', 'bp-group-reviews' ) . '</p>';
				}
				$result = array(
					'html'    => $html,
				);
				echo json_encode( $result );
			}
			die;
		}

		/**
		 * Actions performed to approve review at admin end
		 */
		public function bgr_admin_approve_review() {
			if ( isset( $_POST['action'] ) && $_POST['action'] == 'bgr_admin_approve_review' ) {
				$rid  = sanitize_text_field( $_POST['review_id'] );
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
		 *  Actions performed for saving admin criteria settings
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */

		function bgr_save_admin_criteria_settings() {
			if ( isset( $_POST['action'] ) && $_POST['action'] === 'bgr_save_admin_criteria_settings' ) {

				$rating_fields               = array_map( 'sanitize_text_field', wp_unslash( $_POST['field_values'] ) );
				$rating_field_values         = array_unique( $rating_fields );
				$active_rating_fields        = array_map( 'sanitize_text_field', wp_unslash( $_POST['active_criterias'] ) );
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

		function bgr_save_admin_general_settings() {
			if ( isset( $_POST['action'] ) && $_POST['action'] === 'bgr_save_admin_general_settings' ) {

				$multi_reviews        = sanitize_text_field( $_POST['multi_reviews'] );
				$auto_approve_reviews = sanitize_text_field( $_POST['bgr_auto_approve_reviews'] );
				$reviews_per_page     = sanitize_text_field( $_POST['reviews_per_page'] );
				$allow_email          = sanitize_text_field( $_POST['allow_email'] );
				$allow_notification   = sanitize_text_field( $_POST['allow_notification'] );
				$exclude_groups       = array_map( 'sanitize_text_field', wp_unslash( $_POST['exclude_groups'] ) );
				if ( empty( $exclude_groups ) ) {
					$exclude_groups = array();
				}
				$bgr_admin_settings = array(
					'multi_reviews'        => $multi_reviews,
					'auto_approve_reviews' => $auto_approve_reviews,
					'reviews_per_page'     => $reviews_per_page,
					'allow_email'          => $allow_email,
					'allow_notification'   => $allow_notification,
					'exclude_groups'       => $exclude_groups,
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

		function bgr_save_admin_display_settings() {
			if ( isset( $_POST['action'] ) && $_POST['action'] === 'bgr_save_admin_display_settings' ) {

				$manage_review_label = sanitize_text_field( $_POST['manage_review_label'] );
				$review_label        = sanitize_text_field( $_POST['review_label'] );
				$bgr_rating_color    = sanitize_text_field( $_POST['bgr_rating_color'] );

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

		function bgr_submit_review() {
			global $bp;
			global $bgr;
			$current_user         = wp_get_current_user();
			$member_id            = $current_user->ID;
			$active_rating_fields = $bgr['active_rating_fields'];
			$allow_notification   = $bgr['allow_notification'];
			$allow_email          = $bgr['allow_email'];
			$review_label         = $bgr['review_label'];
			$auto_approve_reviews = $bgr['auto_approve_reviews'];
			$multi_reviews        = $bgr['multi_reviews'];

			parse_str( $_POST['data'], $formarray );
			$review_subject = sanitize_text_field( $formarray['review-subject'] );
			$review_desc    = sanitize_text_field( $formarray['review-desc'] );
			$form_group_id  = sanitize_text_field( $formarray['form-group-id'] );

			$group_args   = array(
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
			$reviews_args = new WP_Query( $group_args );
			if ( $multi_reviews == 'no' ) {
				$user_post_count = $reviews_args->post_count;
			} else {
				$user_post_count = 0;
			}
			if ( $user_post_count > 0 ) {
				$review_add_msg = sprintf( __( 'You already posted a %1$s for this group.', 'bp-group-reviews' ), $review_label  );
			} else {
				if ( $auto_approve_reviews == 'yes' ) {
					$review_add_msg = sprintf( esc_html__( 'Thank you for taking the time to write this wonderful %1$s.', 'bp-group-reviews' ), $review_label );
					$review_status  = 'publish';
				} else {
					$review_add_msg = sprintf( esc_html__( 'Thank you for taking the time to write this wonderful %1$s. Your %1$s will display after moderator approval.', 'bp-group-reviews' ), $review_label );
					$review_status  = 'draft';
				}

				if ( ! empty( $formarray['rated_stars'] ) ) {
					$rated_field_values = array_map( 'sanitize_text_field', wp_unslash( $formarray['rated_stars'] ) );
				}

				if ( ! empty( $active_rating_fields ) && ! empty( $rated_field_values ) ) {
					$rated_stars = array_combine( $active_rating_fields, $rated_field_values );
				}

				$add_review_args = array(
					'post_type'    => 'review',
					'post_title'   => $review_subject,
					'post_content' => $review_desc,
					'post_status'  => $review_status,
				);
				$review_id       = wp_insert_post( $add_review_args );
				$post_author_id  = get_post_field( 'post_author', $review_id );
				wp_set_object_terms( $review_id, 'Group', 'review_category' );
				update_post_meta( $review_id, 'linked_group', $form_group_id );
				$group      = groups_get_group( array( 'group_id' => $form_group_id ) );
				$group_name = $group->name;
				$user_info  = get_userdata( $post_author_id );
				$user_name  = $user_info->user_login;

				if ( $auto_approve_reviews == 'yes' ) {
					$mail_link = bp_get_groups_directory_permalink() . $group->slug . '/reviews/';
				} else {
					$mail_link = bp_get_groups_directory_permalink() . $group->slug . '/admin/manage-reviews/';
				}
				$mail_title = sprintf( esc_html__( 'A new %1$s posted.', 'bp-group-reviews' ), $review_label );
				$mail_content = sprintf( esc_html__( 'A new %1$s for %2$s added by %3$s. Link: %4$s', 'bp-group-reviews' ), $review_label, $group_name, $user_name, esc_url( $mail_link ) );

				if ( ! empty( $rated_stars ) ) {
					update_post_meta( $review_id, 'review_star_rating', $rated_stars );
				}

				$group_admins = groups_get_group( $form_group_id );

				if ( 'yes' == $allow_notification ) {
					foreach ( $group_admins->admins as $group_admin ) {
						$admin_id = $group_admin->user_id;
						do_action( 'bgr_group_add_review', $form_group_id, $admin_id );
					}
				}

				if ( 'yes' == $allow_email ) {
					foreach ( $group_admins->admins as $group_admin ) {
						$author_email = get_the_author_meta( 'user_email', $group_admin->user_id );
						wp_mail( $author_email, $mail_title, $mail_content );
					}
				}
			}

			echo $review_add_msg;
			die;
		}

		/**
		 *  Actions performed when accept review
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */

		function bgr_accept_review() {
			global $bgr;
			global $bp;
			$post_id        = sanitize_text_field( $_POST['accept_review_id'] );
			$post_author_id = get_post_field( 'post_author', $post_id );
			wp_publish_post( $post_id );
			$allow_notification = $bgr['allow_notification'];
			$allow_email        = $bgr['allow_email'];
			$review_label       = $bgr['review_label'];
			$group_id           = get_post_meta( $post_id, 'linked_group', true );
			$group              = groups_get_group( array( 'group_id' => $group_id ) );
			$group_name         = $group->name;
			$review_link        = bp_get_groups_directory_permalink() . $group->slug . "/reviews/view/$post_id/";
			$mail_title         = sprintf( esc_html__( '%1$s accepted.', 'bp-group-reviews' ), $review_label );
			$mail_content       = sprintf( esc_html__( 'Your %1$s for %2$s accepted by group admin. Link: %3$s.', 'bp-group-reviews' ), $review_label, $group_name, esc_url( $review_link ) );

			if ( 'yes' == $allow_notification ) {
				do_action( 'bgr_group_accept_review', $post_id );
			}

			if ( 'yes' == $allow_email ) {
				$author_email = get_the_author_meta( 'user_email', $post_author_id );
				wp_mail( $author_email, $mail_title, $mail_content );
			}

			die;
		}

		/**
		 *  Actions performed when deny review
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */

		function bgr_deny_review() {
			global $bgr;
			global $bp;
			$post_id        = sanitize_text_field( $_POST['deny_review_id'] );
			$group_id       = sanitize_text_field( $_POST['group_id'] );
			$post_author_id = get_post_field( 'post_author', $post_id );
			wp_trash_post( $post_id );
			$allow_notification = $bgr['allow_notification'];
			$allow_email        = $bgr['allow_email'];
			$review_label       = $bgr['review_label'];
			$group_id           = get_post_meta( $post_id, 'linked_group', true );
			$group              = groups_get_group( array( 'group_id' => $group_id ) );
			$group_name         = $group->name;
			$mail_title         = sprintf( esc_html__( '%1$s denied.', 'bp-group-reviews' ), $review_label );
			$mail_content       = sprintf( esc_html__( 'Your %1$s for %2$s denied by group admin.', 'bp-group-reviews' ), $review_label, $group_name );

			if ( 'yes' == $allow_notification ) {
				do_action( 'bgr_group_deny_review', $post_id );
			}

			if ( 'yes' == $allow_email ) {
				$author_email = get_the_author_meta( 'user_email', $post_author_id );
				wp_mail( $author_email, $mail_title, $mail_content );
			}

			die;
		}

		/**
		 *  Actions performed when remove review
		 *
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */

		function bgr_remove_review() {
			$post_id = sanitize_text_field( $_POST['remove_review_id'] );
			wp_trash_post( $post_id );
			die;
		}

	}
	new BGR_AJAX();
}
