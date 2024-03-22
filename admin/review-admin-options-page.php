<?php
/**
 *  Plugin admin setting tab content.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.
$bgr_setting_tab = filter_input( INPUT_GET, 'tab' ) ? filter_input( INPUT_GET, 'tab' ) : 'welcome';
bgr_include_admin_setting_tabs( $bgr_setting_tab );

/** Actions performed on Display review admin settings tabs
 *
 * @since    1.0.0
 * @author   Wbcom Designs
 *
 * @param string $bgr_setting_tab Admin Setting tab.
 */
function bgr_include_admin_setting_tabs( $bgr_setting_tab = 'welcome' ) {
	switch ( $bgr_setting_tab ) {
		case 'welcome':
			require_once BGR_PLUGIN_PATH . 'admin/bgr-welcome-page.php';
			break;
		case 'general':
			bgr_general_setting();
			break;
		case 'criteria':
			bgr_criteria_setting();
			break;
		case 'shortcode':
			bgr_shortcode_setting();
			break;
		case 'display':
			bgr_display_setting();
			break;
		case 'emails':
			bgr_emails_setting();
			break;
		default:
			bgr_general_setting();
	}
}

/** Actions performed on BuddyPress Group Reviews Settings : Criteria Tab Content
 *
 * @since    1.0.0
 * @author   Wbcom Designs
 */
function bgr_criteria_setting() {
	global $bgr;
	$spinner_src          = includes_url() . 'images/spinner.gif';
	$review_rating_fields = $bgr['review_rating_fields'];
	$active_rating_fields = $bgr['active_rating_fields'];
	?>
<div class="wbcom-admin-title-section">
	<h3><?php esc_html_e( 'Reviews Criteria(s)', 'bp-group-reviews' ); ?></h3>
</div>
<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
	<div class="form-table">
		<div class="wbcom-settings-section-wrap">
			<div id="bgr-textbox-container">
				<?php
				if ( ! empty( $review_rating_fields ) ) {
					foreach ( $review_rating_fields as $review_rating_field ) :
						?>
				<div class="rating-review-div"><span>&equiv;</span><input name = "BGRDynamicTextBox" class="draggable" type="text" value = "<?php echo esc_attr( $review_rating_field ); ?>" />
					<input type="button" value="<?php esc_html_e( 'Remove', 'bp-group-reviews' ); ?>" class="remove button button-secondary" />
					<label class="wb-switch">
					<input type="checkbox" class="bgr-criteria-state" name="bgr-criteria-state" data-attr="<?php echo esc_attr( $review_rating_field ); ?>"
							<?php
							if ( in_array( $review_rating_field, $active_rating_fields ) ) {
								echo 'checked="checked"'; }
							?>
	>
					<div class="wb-slider wb-round"></div>
				</label>
			</div>
						<?php
			endforeach;
				}
				?>
	</div>
	<input id="bgr-field-add" type="button" value="<?php esc_html_e( 'Add Review Criteria', 'bp-group-reviews' ); ?>" class="button button-secondary"/>
	<p class="description"><?php esc_html_e( 'This option provide you to add multiple rating criteria. By default, no criteria will be shown until you enable it.', 'bp-group-reviews' ); ?></p>
	</div>
</div>
<input type="button" class="button button-primary bgr-submit-button" id="bgr-save-admin-criteria-settings" value="<?php esc_html_e( 'Save Settings', 'bp-group-reviews' ); ?>">
<img src="<?php echo esc_url( $spinner_src ); ?>" class="bgr-admin-criteria-settings-spinner" />
</div>
	<?php
}

	/**
	 * Actions performed on BuddyPress Group Reviews Settings : Shortcode Tab Content
	 *
	 * @since    1.0.0
	 * @author   Wbcom Designs
	 */
function bgr_shortcode_setting() {
	?>
<div class="wbcom-admin-title-section">
	<h3><?php esc_html_e( 'Group Review Shortcode', 'bp-group-reviews' ); ?></h3>
</div>
<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
	<div class="form-table">
		<div class="wbcom-settings-section-wrap">
			<div class="wbcom-settings-section-options-heading">
				<label>
					<?php esc_html_e( 'Group Review Form Shortcode', 'bp-group-reviews' ); ?>
				</label>
				<p class="description">
					<?php esc_html_e( 'This shortcode will display Group Review Form.', 'bp-group-reviews' ); ?>
				</p>
			</div>
			<div class="wbcom-settings-section-options">
				<?php echo '[add_group_review_form]'; ?>
			</div>
		</div>
	</div>
</div>
	<?php
}

/**
 * Actions performed on BuddyPress Group Reviews Settings : Display Tab Content
 *
 * @since    1.0.0
 * @author   Wbcom Designs
 */
function bgr_display_setting() {
	global $bgr;
	$spinner_src             = includes_url() . 'images/spinner.gif';
	$bgr_review_label        = $bgr['review_label'];
	$bgr_manage_review_label = $bgr['manage_review_label'];
	$bgr_rating_color        = $bgr['rating_color'];
	?>
<div class="wbcom-admin-title-section">
	<h3><?php esc_html_e( 'Display Setting', 'bp-group-reviews' ); ?></h3>
</div>
<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
	<div class="form-table">
		<div class="wbcom-admin-title-section">
			<h3><?php esc_html_e( 'Labels', 'bp-group-reviews' ); ?></h3>
		</div>
		<div class="wbcom-settings-section-wrap">
			<div class="wbcom-settings-section-options-heading">
				<label for="bgrReviewLabel">
					<?php esc_html_e( 'Review', 'bp-group-reviews' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'This option provides flexibility to change review label. By default it shows "Review".', 'bp-group-reviews' ); ?></p>
			</div>
			<div class="wbcom-settings-section-options">
				<input name = "bgrReviewLabel" id="bgrReviewLabel" type="text" value = "<?php echo esc_attr( $bgr_review_label ); ?>" />
			</div>
		</div>
		<div class="wbcom-settings-section-wrap">
			<div class="wbcom-settings-section-options-heading">
				<label for="bgrManageReviewLabel">
					<?php esc_html_e( 'Reviews ( Plural )', 'bp-group-reviews' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'This option provides flexibility to change plural of Review.', 'bp-group-reviews' ); ?></p>
			</div>
			<div class="wbcom-settings-section-options">
				<input name = "bgrManageReviewLabel" id="bgrManageReviewLabel" type="text" value = "<?php echo esc_attr( $bgr_manage_review_label ); ?>" />
			</div>
		</div>
		<div class="wbcom-admin-title-section">
			<h3><?php esc_html_e( 'Colors', 'bp-group-reviews' ); ?></h3>
		</div>
		<div class="wbcom-settings-section-wrap">
			<div class="wbcom-settings-section-options-heading">
				<label for="bgr-rating-color">
					<?php esc_html_e( 'Rating Color', 'bp-group-reviews' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'This option lets you to change star rating color.', 'bp-group-reviews' ); ?></p>
			</div>
			<div class="wbcom-settings-section-options">
				<input id="bgr-rating-color" class="bgr-review-color" type="text" data-default-color="#effeff" value="<?php echo esc_attr( $bgr_rating_color ); ?>" />
			</div>
		</div>
	</div>
	<input type="button" class="button button-primary bgr-submit-button" id="bgr-save-admin-display-settings" value="<?php esc_html_e( 'Save Settings', 'bp-group-reviews' ); ?>">
	<img src="<?php echo esc_url( $spinner_src ); ?>" class="bgr-admin-display-settings-spinner" />
</div>
	<?php
}


/**
 * Actions performed on BuddyPress Group Reviews Settings : General Tab Content
 *
 * @since    1.0.0
 * @author   Wbcom Designs
 */
function bgr_general_setting() {
	global $bp, $bgr;

	if ( ! bp_is_active( 'groups' ) ) {
		$base_url  = bp_get_admin_url(
			add_query_arg(
				array(
					'page' => 'bp-components',
				),
				'admin.php'
			)
		);
		$base_link = '<a href="' . esc_url( $base_url ) . '">' . esc_html__( 'here', 'bp-group-reviews' ) . '</a>';
		$group_mgs = esc_html__( 'This plugin is work with BuddyPress Groups Component. Please activate the BuddyPress Groups component. To activate groups component click ', 'bp-group-reviews' );
		echo sprintf( wp_kses_post( '<h2>%1s %2s. </h2>' ), esc_html( $group_mgs ), wp_kses_post( $base_link ) );

		return;
	}

	$spinner_src          = includes_url() . 'images/spinner.gif';
	$auto_approve_reviews = $bgr['auto_approve_reviews'];
	$reviews_per_page     = $bgr['reviews_per_page'];
	$review_email_subject = ( isset( $bgr['review_email_subject'] ) ) ? $bgr['review_email_subject'] : 'New Review Submitted for Your Group [group-name] on [site-name]';
	$review_email_message = ( isset( $bgr['review_email_message'] ) ) ? $bgr['review_email_message'] : 'Hello [admin-name],

We are excited to inform you that a new review has been submitted for your group, [group-name], by [user-name]. Your members\'s feedback is invaluable in fostering a vibrant and engaging community.

You can read and respond to the review by following the link below:
[review-link]

Thank you for creating a space where members can share their thoughts and experiences. Keep up the great work!

Best regards,
The [site-name] Team';
	$allow_email          = $bgr['allow_email'];
	$allow_notification   = $bgr['allow_notification'];
	$allow_activity       = $bgr['allow_activity'];
	$exclude_groups       = $bgr['exclude_groups'];
	$multi_reviews        = $bgr['multi_reviews'];
	$group_args           = array(
		'order'    => 'DESC',
		'orderby'  => 'date_created',
		'per_page' => -1,
	);
	$allgroups            = groups_get_groups( $group_args );

	?>
<div class="wbcom-admin-title-section">
	<h3><?php esc_html_e( 'General Settings', 'bp-group-reviews' ); ?></h3>
</div>
<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
	<div class="form-table">
		<div class="bgr-row wbcom-settings-section-wrap">
			<div class="wbcom-settings-section-options-heading">
				<label><?php esc_html_e( 'Enable Multiple Reviews', 'bp-group-reviews' ); ?></label>
				<p class="description"><?php esc_html_e( 'Enable this option, if you want to add functionality for user to send multiple review to same group.', 'bp-group-reviews' ); ?></p>
			</div>
			<div class="wbcom-settings-section-options">
				<label class="wb-switch" for="bgr-multi-reviews">
					<input type="checkbox" id="bgr-multi-reviews"
					<?php
					if ( 'yes' === $multi_reviews ) {
						echo 'checked="checked"'; }
					?>
		>
					<div class="wb-slider wb-round"></div>
				</label>
			</div>
		</div>
		<div class="bgr-row wbcom-settings-section-wrap">
			<div class="wbcom-settings-section-options-heading">
				<label><?php esc_html_e( 'Enable auto approval of Reviews', 'bp-group-reviews' ); ?></label>
				<p class="description"><?php esc_html_e( 'Enable this option, if you want to have the reviews automatically approved, else manual approval will be required.', 'bp-group-reviews' ); ?></p>
			</div>
			<div class="wbcom-settings-section-options">
				<label class="wb-switch" for="bgr-auto-approve-reviews">
					<input type="checkbox" id="bgr-auto-approve-reviews"
					<?php
					if ( 'yes' === $auto_approve_reviews ) {
						echo 'checked="checked"'; }
					?>
					 >
					<div class="wb-slider wb-round"></div>
				</label>
			</div>
		</div>
		<div class="bgr-row wbcom-settings-section-wrap">
			<div class="wbcom-settings-section-options-heading">
				<label for="reviews_per_page"><?php esc_html_e( 'Reviews show at most', 'bp-group-reviews' ); ?></label>
				<p class="description"><?php esc_html_e( 'This option lets you limit number of reviews in "Group Reviews" & "Manage Reviews" tab.', 'bp-group-reviews' ); ?></p>
			</div>
			<div class="wbcom-settings-section-options">
				<input id="reviews_per_page" class="small-text" name="reviews_per_page" step="1" min="1" value="<?php echo esc_attr( $reviews_per_page ); ?>" type="number">
				<?php esc_html_e( 'Reviews', 'bp-group-reviews' ); ?>
			</div>
		</div>
		<div class="bgr-row wbcom-settings-section-wrap">
			<div class="wbcom-settings-section-options-heading">
				<label>
					<?php esc_html_e( 'Enable BuddyPress notifications', 'bp-group-reviews' ); ?>
				</label>
				<?php if ( bp_is_active( 'notifications' ) ) { ?>
					<p class="description"><?php esc_html_e( 'Enable this option, if you want group admin & reviewer to receive a notification when add, accept & deny review.', 'bp-group-reviews' ); ?></p>
				<?php } else { ?>
					<p class="description"><?php esc_html_e( 'This setting requires BuddyPress Notifications Component to be active.', 'bp-group-reviews' ); ?></p>
				<?php } ?>
			</div>
			<div class="wbcom-settings-section-options">
					<?php if ( bp_is_active( 'notifications' ) ) { ?>
				<label class="wb-switch" for="bgr-notification">
					<input type="checkbox" id="bgr-notification"
						<?php
						if ( 'yes' === $allow_notification ) {
							echo 'checked="checked"'; }
						?>
		>
					<div class="wb-slider wb-round"></div>
				</label>
					<?php } ?>
			</div>
		</div>
		<div class="bgr-row wbcom-settings-section-wrap">
			<div class="wbcom-settings-section-options-heading">
				<label>
					<?php esc_html_e( 'Enable Review Activity', 'bp-group-reviews' ); ?>
				</label>
				<?php if ( bp_is_active( 'activity' ) ) { ?>
					<p class="description"><?php esc_html_e( 'Enable this option, if you want to generate group review activities.', 'bp-group-reviews' ); ?></p>
				<?php } else { ?>
					<p class="description"><?php esc_html_e( 'This setting requires BuddyPress Activity Component to be active.', 'bp-group-reviews' ); ?></p>
				<?php } ?>
			</div>
			<div class="wbcom-settings-section-options">
					<?php if ( bp_is_active( 'activity' ) ) { ?>
				<label class="wb-switch" for="bgr-activity">
					<input type="checkbox" id="bgr-activity"
						<?php
						if ( 'yes' === $allow_activity ) {
							echo 'checked="checked"'; }
						?>
				>
					<div class="wb-slider wb-round"></div>
				</label>
				<?php } ?>
			</div>
		</div>
		<div class="bgr-row wbcom-settings-section-wrap">
			<div class="wbcom-settings-section-options-heading">
				<label>
					<?php esc_html_e( 'Exclude Groups from Reviews', 'bp-group-reviews' ); ?>
				</label>
				<p class="description"><?php esc_html_e( "This option lets you choose those groups that you don't want to provide review functionality.", 'bp-group-reviews' ); ?></p>
			</div>
			<div class="wbcom-settings-section-options">
			<select id="bgr-exclude-group-review" name="bgr-exclude-group[]" multiple >
					<?php
					if ( $allgroups ) {
						foreach ( $allgroups['groups'] as $group ) :
							if ( ! empty( $exclude_groups ) ) {
								if ( in_array( $group->id, $exclude_groups ) ) {
									?>
								<option value="<?php echo esc_attr( $group->id ); ?>" <?php echo 'selected = selected'; ?>><?php echo esc_html( $group->name ); ?></option>
								<?php } else { ?>
								<option value="<?php echo esc_attr( $group->id ); ?>"><?php echo esc_html( $group->name ); ?></option>
									<?php
								}
							} else {
								?>
								<option value="<?php echo esc_attr( $group->id ); ?>"><?php echo esc_html( $group->name ); ?></option>
								<?php
							}
							endforeach;
					}
					?>
					</select>
			</div>
		</div>
	</div>
	<input type="button" class="button button-primary bgr-submit-button" id="bgr-save-admin-general-settings" value="<?php esc_html_e( 'Save Settings', 'bp-group-reviews' ); ?>">
	<img src="<?php echo esc_url( $spinner_src ); ?>" class="bgr-admin-general-settings-spinner" />
</div>
	
					<?php
}

/**
 * Email settiong option.
 *
 */
function bgr_emails_setting() {
	$bp_group_review_email_settigs = get_option( 'bp_group_review_email_settigs' );
	$review_email_subject = ( isset( $bp_group_review_email_settigs['review_email_subject'] ) ) ? $bp_group_review_email_settigs['review_email_subject'] : 'New Review Submitted for Your Group [group-name] on [site-name]';
	$review_email_message = ( ! empty( $bp_group_review_email_settigs['review_email_message'] ) ) ? $bp_group_review_email_settigs['review_email_message'] : 'Hello [admin-name],<br><br>
	We are excited to inform you that a new review has been submitted for your group [group-name], by [user-name]. Your members\'s feedback is invaluable in fostering a vibrant and engaging community.<br><br>
	You can read and respond to the review by following the link below:[review-link]<br><br>
	Thank you for creating a space where members can share their thoughts and experiences. Keep up the great work!<br><br>
	Best regards,<br>
	The [site-name] Team';
	$review_accept_email_subject = ( isset( $bp_group_review_email_settigs['review_accept_email_subject'] ) ) ? $bp_group_review_email_settigs['review_accept_email_subject'] : 'Your review for the Group [group-name] on [site-name] is approved';
	$review_accept_email_message = ( ! empty( $bp_group_review_email_settigs['review_accept_email_message'] ) ) ? $bp_group_review_email_settigs['review_accept_email_message'] : 'Hello [admin-name],<br><br>
	We are pleased to inform you that your review for the group, [group-name] on [site-name] has been reviewed and approved by the administrator.<br><br>
	We appreciate the time and effort you took to share your thoughts about the group.[review-link]<br><br>
	Thank you for being an active and valuable member of our community.<br><br>
	Best regards,<br>
	The [site-name] Team';
	$review_deny_email_subject = ( isset( $bp_group_review_email_settigs['review_deny_email_subject'] ) ) ? $bp_group_review_email_settigs['review_deny_email_subject'] : 'Your review for the Group [group-name] on [site-name] is denied';
	$review_deny_email_message = ( ! empty( $bp_group_review_email_settigs['review_deny_email_message'] ) ) ? $bp_group_review_email_settigs['review_deny_email_message'] : 'Hello [admin-name],<br><br>
	We regret to inform you that after careful consideration, your review for the group, [group-name] on [site-name] has been denied by the administrator.<br><br>
	While we appreciate your willingness to share your thoughts and feedback, it seems that certain aspects of the review did not align with our community guidelines or policies.[review-link]<br><br>
	Thank you for your understanding and cooperation.<br><br>
	Best regards,<br>
	The [site-name] Team';
	?>
	<div class="wbcom-admin-title-section">
		<h3><?php esc_html_e( 'Emails Setting', 'bp-group-reviews' ); ?></h3>
	</div>
	<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
		<form method="post" action="options.php">
		<?php
			settings_fields( 'bp_group_review_email_settigs' );
			do_settings_sections( 'bp_group_review_email_settigs' );
		?>
			<div class="form-table">
				<label>
					<h3><?php esc_html_e( 'Submit Review', 'bp-group-reviews' ); ?></h3>
				</label>
				<div class="bgr-row wbcom-settings-section-wrap">
					<label></label>
					<div class="wbcom-settings-section-options-heading">
						<label>
							<?php esc_html_e( 'Emails', 'bp-group-reviews' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Enable this option, if you want group admin & reviewer receive email when someone adds, accepts & denies review.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<label class="wb-switch" for="bgr-allow-email">
							<input type="checkbox" id="bgr-allow-email" name="bp_group_review_email_settigs[bgr_allow_email]" value="yes"<?php ( isset( $bp_group_review_email_settigs['bgr_allow_email'] ) ) ? checked( $bp_group_review_email_settigs['bgr_allow_email'], 'yes' ) : ''; ?>>
							<div class="wb-slider wb-round"></div>
						</label>
					</div>
				</div>
				<div class="bgr-row wbcom-settings-section-wrap review-email-section">
					<div class="wbcom-settings-section-options-heading">
						<label>
							<?php esc_html_e( 'Email Subject', 'bp-group-reviews' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Please add review email subject.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<input id="review_email_subject" class="large-text" name="bp_group_review_email_settigs[review_email_subject]" value="<?php echo esc_attr( $review_email_subject ); ?>" type="text" placeholder="Please enter review email subject.">
					</div>
				</div>

				<div class="bgr-row wbcom-settings-section-wrap review-email-section">
					<div class="wbcom-settings-section-options-heading">
						<label>
							<?php esc_html_e( 'Email Message', 'bp-group-reviews' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Please add review email message.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<!-- <textarea id="review_email_message" class="large-text" name="bp_group_review_email_settigs[review_email_message]" ><?php echo esc_html( $review_email_message ); ?></textarea> -->
						<?php
						wp_editor(
							$review_email_message,
							'bgr-email-message',
							array(
								'media_buttons' => false,
								'textarea_name' => 'bp_group_review_email_settigs[review_email_message]',
							)
						);
						?>
					</div>
				</div>
				
				<label>
					<h3><?php esc_html_e( 'Accept Review', 'bp-group-reviews' ); ?></h3>
				</label>
				<div class="bgr-row wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label>
							<?php esc_html_e( 'Emails', 'bp-group-reviews' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Enable this option, if you want group admin & reviewer receive email when someone adds, accepts & denies review.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<label class="wb-switch" for="bgr-accept-enable">
							<input type="checkbox" id="bgr-accept-enable" name="bp_group_review_email_settigs[bgr_accept_enable]" value="yes"<?php ( isset( $bp_group_review_email_settigs['bgr_accept_enable'] ) ) ? checked( $bp_group_review_email_settigs['bgr_accept_enable'], 'yes' ) : ''; ?>>
							<div class="wb-slider wb-round"></div>
						</label>
					</div>
				</div>
				<div class="bgr-row wbcom-settings-section-wrap review-accept-email-section">
					<div class="wbcom-settings-section-options-heading">
						<label>
							<?php esc_html_e( 'Email Subject', 'bp-group-reviews' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Please add review email subject.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<input id="review_accept_email_subject" class="large-text" name="bp_group_review_email_settigs[review_accept_email_subject]" value="<?php echo esc_attr( $review_accept_email_subject ); ?>" type="text" placeholder="Please enter review email subject.">
					</div>
				</div>

				<div class="bgr-row wbcom-settings-section-wrap review-accept-email-section">
					<div class="wbcom-settings-section-options-heading">
						<label>
							<?php esc_html_e( 'Email Message', 'bp-group-reviews' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Please add review email message.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<!-- <textarea id="review_accept_email_message" class="large-text" name="bp_group_review_email_settigs[review_accept_email_message]" ><?php //echo esc_html( $review_email_message ); ?></textarea>
					-->
					<?php
						wp_editor(
							$review_accept_email_message,
							'bgr-accept-email-message',
							array(
								'media_buttons' => false,
								'textarea_name' => 'bp_group_review_email_settigs[review_accept_email_message]',
							)
						);
					?>
					</div>
				</div>
				<label>
					<h3><?php esc_html_e( 'Deny Review', 'bp-group-reviews' ); ?></h3>
				</label>
				<div class="bgr-row wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label>
							<?php esc_html_e( 'Emails', 'bp-group-reviews' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Enable this option, if you want group admin & reviewer receive email when someone adds, accepts & denies review.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<label class="wb-switch" for="bgr-deny-email">
							<input type="checkbox" id="bgr-deny-email" name="bp_group_review_email_settigs[bgr_deny_email]" value="yes"<?php ( isset( $bp_group_review_email_settigs['bgr_deny_email'] ) ) ? checked( $bp_group_review_email_settigs['bgr_deny_email'], 'yes' ) : ''; ?>>
							<div class="wb-slider wb-round"></div>
						</label>
					</div>
				</div>
				<div class="bgr-row wbcom-settings-section-wrap review-deny-email-section">
					<div class="wbcom-settings-section-options-heading">
						<label>
							<?php esc_html_e( 'Email Subject', 'bp-group-reviews' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Please add review email subject.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<input id="review_deny_email_subject" class="large-text" name="bp_group_review_email_settigs[review_deny_email_subject]" value="<?php echo esc_attr( $review_deny_email_subject ); ?>" type="text" placeholder="Please enter review email subject.">
					</div>
				</div>

				<div class="bgr-row wbcom-settings-section-wrap review-deny-email-section">
					<div class="wbcom-settings-section-options-heading">
						<label>
							<?php esc_html_e( 'Email Message', 'bp-group-reviews' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Please add review email message.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<?php
						wp_editor(
							$review_deny_email_message,
							'bgr-deny-email-message',
							array(
								'media_buttons' => false,
								'textarea_name' => 'bp_group_review_email_settigs[review_deny_email_message]',
							)
						);
						?>
					</div>
				</div>
			</div>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
