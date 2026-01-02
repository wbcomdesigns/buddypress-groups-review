<?php
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
			add_shortcode( 'add_group_review_form', array( $this, 'bp_group_review_add_new_review' ), 999 );
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
					<?php /* translators: %s: search term */ ?>
							<textarea class="review_desc" name="review-desc" placeholder="<?php printf( esc_attr__( '%1$s Description (optional)', 'bp-group-reviews' ), esc_attr( $review_label ) ); ?>" rows="3" cols="50"></textarea>
							<br/>
						<p>
							<input type="hidden" name="bgr-flag" value="1">
						</p>
						<p>
							<?php wp_nonce_field( 'save-group-review', 'security-nonce' ); ?>
							<?php /* translators: %1$s is used for review label */ ?>
							<button class="btn btn-default bgr-submit-review" name="bgr-submit-review"><?php printf( esc_html__( 'Submit %1$s', 'bp-group-reviews' ), esc_html( $review_label ) ); ?></button>
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
							<div class="multi-review">
								<div class="bgr-col-4 "><?php echo esc_html( $review_rating_field ); ?> <span class="required"> * </span>  </div>
								<div id="review<?php echo esc_html( $field_counter ); ?>" class="bgr-col-4">
										<input type="hidden" id="<?php echo 'clicked' . esc_html( $field_counter ); ?>" value="not_clicked">
										<input type="hidden" name="rated_stars[]" class="rated_stars bgr_mrating" id="<?php echo 'rated_stars' . esc_html( $field_counter ); ?>" value="0">
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
				<div class="multi-review">
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
	}
	new BGR_Shortcodes();
}
