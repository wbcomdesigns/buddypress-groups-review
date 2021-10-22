<?php
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
			add_shortcode( 'add_group_review_form', array( $this, 'add_new_review' ) );
		}

			/**
			 *  Display Review form when member logged in
			 *
			 *  @since   1.0.0
			 *  @author  Wbcom Designs
			 */
		public function add_new_review() {
			global $bp;
			global $bgr;
			$review_rating_fields = $bgr['review_rating_fields'];
			$active_rating_fields = $bgr['active_rating_fields'];
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
						'value'   => $current_group_id,
						'compare' => '=',
					),
				),
			);
			$reviews_args = new WP_Query( $group_args );
			$output      .= '<div id="bgr-message" class="bgr-success"></div>';

			if ( ! is_user_logged_in() ) {
					$output .= '<div id="message" class="info">';
					$output .= '<p>';
					$output .= sprintf(
							/* translators: %1$s is used for review lable*/
						esc_html__( 'You should log in for post %1$s.', 'bp-group-reviews' ),
						esc_html( $review_label )
					);
					$output .= '</p>';
					$output .= '</div>';
			} elseif ( ! groups_is_user_admin( bp_loggedin_user_id(), bp_get_current_group_id() ) && is_user_logged_in() ) {
				// Get All groups of user in which user is admin.
				if ( bp_is_group_single() ) {
					if ( 'no' === $multi_reviews ) {
						$user_post_count = $reviews_args->post_count;
					} else {
						$user_post_count = 0;
					}

					if ( 0 === $user_post_count ) {
						$output .= $this->bgr_review_form( $review_rating_fields, $active_rating_fields, $review_label, $admin_exclude_groups, $auto_approve_reviews, $multi_reviews, $current_group_id, $member_id );
					} else {

						$bp_template_option = bp_get_option( '_bp_theme_package_id' );
						if ( 'nouveau' == $bp_template_option ) {
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
					$output .= $this->bgr_review_form( $review_rating_fields, $active_rating_fields, $review_label, $admin_exclude_groups, $auto_approve_reviews, $multi_reviews, $current_group_id, $member_id );
				}
			} else {
				$output .= '<div id="message" class="error">';
				$output .= '<p>' . sprintf(
					/* translators: %s is used for bp-group-review */
					esc_html__( "You can't post %s on your own group.", 'bp-group-reviews' ),
					esc_html( $review_label )
				) . '</p>';
				$output .= '</div>';
			}
			return $output;
		}

		public function bgr_review_form( $review_rating_fields, $active_rating_fields, $review_label, $admin_exclude_groups, $auto_approve_reviews, $multi_reviews, $current_group_id, $member_id ) {
			global $bp;
			$user_groups   = BP_Groups_Member::get_is_admin_of( $member_id );
			$exclude_group = array();

			if ( ! empty( $user_groups ) ) {
				foreach ( $user_groups['groups'] as $user_group ) {
					array_push( $exclude_group, $user_group->id );
				}
			}

			if ( ! empty( $admin_exclude_groups ) ) {
				foreach ( $admin_exclude_groups as $admin_exclude_group ) {
					array_push( $exclude_group, $admin_exclude_group );
				}
			}
			// Get All groups.
			$group_args = array(
				'order'   => 'DESC',
				'orderby' => 'date_created',
				'exclude' => $exclude_group,
			);
			$allgroups  = groups_get_groups( $group_args );
			?>
			<form id="bgr-add-review-form" method="POST">
				<input type="hidden" id="reviews_pluginurl"  name="reviews_pluginurl" value="<?php echo esc_attr( BGR_PLUGIN_URL ); ?>">
				<div class="group-add-form">
					<p>
						<?php
						/* translators: %1$s is used for review label */
						echo sprintf( esc_html__( 'Write a %1$s', 'bp-group-reviews' ), esc_html( $review_label ) );
						?>
					</p>

					<?php if ( 0 === $current_group_id ) { ?>
						<p class="bgr-form-group-id">
							<select id="form-group-id" name="form-group-id">
								<option value=""><?php esc_html_e( '--Select Group--', 'bp-group-reviews' ); ?></option>
								<?php
								if ( ! empty( $allgroups ) ) {
									foreach ( $allgroups['groups'] as $formGroup ) {
										?>
										<option value="<?php echo esc_attr( $formGroup->id ); ?>"
											<?php
											if ( bp_is_group() ) {
												if ( bp_get_group_id() == $formGroup->id ) {
													echo 'selected="selected"';
												}
											}
											?>
											><?php echo esc_html( $formGroup->name ); ?>
										</option>
										<?php
									}
								}
								?>
							</select>
							<br/>
							<span class="bgr-error-fields">*<?php esc_html_e( 'This field is required.', 'bp-group-reviews' ); ?></span>
						</p>
						<?php } else { ?>
							<input type="hidden" name="form-group-id" value="<?php echo esc_attr( $current_group_id ); ?>" />
						<?php } ?>
						<p class="bupr-hide-subject">
							<?php $review_subject = bgr_group_add_review_tab_name().' ' . time(); ?>
							<input name="review-subject" type="hidden" value="<?php echo esc_attr( $review_subject ); ?>">
						</p>
					<?php /* translators: %s: search term */ ?>
							<textarea class="review_desc" name="review-desc" placeholder="<?php echo sprintf( esc_attr__( '%1$s Description', 'bp-group-reviews' ), esc_attr( $review_label ) ); ?>" rows="3" cols="50"></textarea>
							<br/>
							<span class="bgr-error-fields">*<?php esc_html_e( 'This field is required.', 'bp-group-reviews' ); ?></span>

						<p>
							<input type="hidden" name="bgr-flag" value="1">
						</p>
						<?php $this->bgr_display_form_rating(); ?>
						<p>
							<?php wp_nonce_field( 'save-group-review', 'security-nonce' ); ?>
							<?php /* translators: %1$s is used for review label */ ?>
							<button class="btn btn-default bgr-submit-review" name="bgr-submit-review"><?php echo sprintf( esc_html__( 'Submit %1$s', 'bp-group-reviews' ), esc_html( $review_label ) ); ?></button>
						</p>
					</div>
				</form>
				<?php
		}

		public function bgr_display_form_rating() {
			global $bgr;
			$this->bgr_display_form_star_rating();
		}

			/**
			 *  Display Ratings when rating type = star
			 *
			 *  @since   1.0.0
			 *  @author  Wbcom Designs
			 */
		public function bgr_display_form_star_rating() {
			global $bgr;
			$review_rating_fields = $bgr['review_rating_fields'];
			$active_rating_fields = $bgr['active_rating_fields'];
			if ( ! empty( $review_rating_fields ) ) {
				$field_counter = 1;
				foreach ( $review_rating_fields as $review_rating_field ) :
					if ( in_array( $review_rating_field, $active_rating_fields ) ) {
						?>
							<div class="multi-review">
								<div class="bgr-col-4"><?php echo esc_html( $review_rating_field ); ?></div>
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
							$field_counter++; }
						endforeach;
				?>
					<input type="hidden" id="rating_field_counter" value="<?php echo esc_html( --$field_counter ); ?>">
				<?php
			}
		}
	}
	new BGR_Shortcodes();
}
