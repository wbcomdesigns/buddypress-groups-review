<?php
/**
 * Plugin admin settings tab content.
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
bp_group_review_include_admin_settings_tab( $bgr_setting_tab );

/**
 * Display admin settings tabs for BuddyPress Group Reviews.
 *
 * @since    1.0.0
 * @param string $bgr_setting_tab Admin settings tab.
 */
function bp_group_review_include_admin_settings_tab( $bgr_setting_tab = 'welcome' ) {
	switch ( $bgr_setting_tab ) {
		case 'welcome':
			require_once BGR_PLUGIN_PATH . 'admin/bgr-welcome-page.php';
			break;
		case 'general':
			bp_group_review_general_setting();
			break;
		case 'criteria':
			bp_group_review_criteria_setting();
			break;
		case 'display':
			bp_group_review_display_setting();
			break;
		case 'emails':
			bp_group_review_emails_setting();
			break;
		default:
			bp_group_review_general_setting();
	}
}

/**
 * Display content for the "Criteria" tab in BuddyPress Group Reviews settings.
 *
 * @since    1.0.0
 */
function bp_group_review_criteria_setting() {
	global $bgr;
	$spinner_src          = includes_url() . 'images/spinner.gif';
	$review_rating_fields = $bgr['review_rating_fields'];
	$active_rating_fields = $bgr['active_rating_fields'];
	?>
	<div class="wbcom-admin-title-section">
		<h3><?php esc_html_e( 'Review Criteria', 'bp-group-reviews' ); ?></h3>
	</div>
	<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
		<div class="form-table">
			<div class="wbcom-settings-section-wrap">
				<div id="bgr-textbox-container">
					<?php
					if ( ! empty( $review_rating_fields ) ) {
						foreach ( $review_rating_fields as $review_rating_field ) :
							?>
							<div class="rating-review-div">
								<span class="move-icons">&equiv;</span>
								<input name="BGRDynamicTextBox" class="draggable" type="text" value="<?php echo esc_attr( $review_rating_field ); ?>" />
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
				<input id="bgr-field-add" type="button" value="<?php esc_html_e( 'Add Review Criteria', 'bp-group-reviews' ); ?>" class="button button-secondary" />
				<p class="description"><?php esc_html_e( 'Add multiple rating criteria. No criteria will be displayed until it is enabled.', 'bp-group-reviews' ); ?></p>
			</div>
		</div>
		<input type="button" class="button button-primary bgr-submit-button" id="bgr-save-admin-criteria-settings" value="<?php esc_html_e( 'Save Settings', 'bp-group-reviews' ); ?>">
		<img src="<?php echo esc_url( $spinner_src ); ?>" class="bgr-admin-criteria-settings-spinner" />
	</div>
	<?php
}

/**
 * Display content for the "Display" tab in BuddyPress Group Reviews settings.
 *
 * @since    1.0.0
 */
function bp_group_review_display_setting() {
	global $bgr;
	$spinner_src             = includes_url() . 'images/spinner.gif';
	$bgr_review_label        = $bgr['review_label'];
	$bgr_manage_review_label = $bgr['manage_review_label'];
	$bgr_rating_color        = $bgr['rating_color'];
	?>
	<div class="wbcom-admin-title-section">
		<h3><?php esc_html_e( 'Display Settings', 'bp-group-reviews' ); ?></h3>
	</div>
	<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
		<div class="form-table">
			<div class="wbcom-admin-title-section">
				<h3><?php esc_html_e( 'Labels', 'bp-group-reviews' ); ?></h3>
			</div>
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label for="bgrReviewLabel"><?php esc_html_e( 'Review', 'bp-group-reviews' ); ?></label>
					<p class="description"><?php esc_html_e( 'Allows you to change the review label. The default is "Review".', 'bp-group-reviews' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<input name="bgrReviewLabel" id="bgrReviewLabel" type="text" value="<?php echo esc_attr( $bgr_review_label ); ?>" />
				</div>
			</div>
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label for="bgrManageReviewLabel"><?php esc_html_e( 'Reviews (Plural)', 'bp-group-reviews' ); ?></label>
					<p class="description"><?php esc_html_e( 'Allows you to modify the plural form of "Review".', 'bp-group-reviews' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<input name="bgrManageReviewLabel" id="bgrManageReviewLabel" type="text" value="<?php echo esc_attr( $bgr_manage_review_label ); ?>" />
				</div>
			</div>
			<div class="wbcom-admin-title-section">
				<h3><?php esc_html_e( 'Colors', 'bp-group-reviews' ); ?></h3>
			</div>
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label for="bgr-rating-color"><?php esc_html_e( 'Rating Color', 'bp-group-reviews' ); ?></label>
					<p class="description"><?php esc_html_e( 'Change the color of the star rating.', 'bp-group-reviews' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<input id="bgr-rating-color" class="bgr-review-color" type="text" data-default-color="#FFC400" value="<?php echo esc_attr( $bgr_rating_color ); ?>" />
				</div>
			</div>
		</div>
		<input type="button" class="button button-primary bgr-submit-button" id="bgr-save-admin-display-settings" value="<?php esc_html_e( 'Save Settings', 'bp-group-reviews' ); ?>">
		<img src="<?php echo esc_url( $spinner_src ); ?>" class="bgr-admin-display-settings-spinner" />
	</div>
	<?php
}

/**
 * Display content for the "General" tab in BuddyPress Group Reviews settings.
 *
 * @since    1.0.0
 */
function bp_group_review_general_setting() {
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
		$group_mgs = esc_html__( 'This plugin works with the BuddyPress Groups component. Please activate the BuddyPress Groups component. Click ', 'bp-group-reviews' );
		printf( wp_kses_post( '<h2>%1s %2s. </h2>' ), esc_html( $group_mgs ), wp_kses_post( $base_link ) );

		return;
	}

	$spinner_src          = includes_url() . 'images/spinner.gif';
	$auto_approve_reviews = $bgr['auto_approve_reviews'];
	$reviews_per_page     = $bgr['reviews_per_page'];
	$allow_notification   = $bgr['allow_notification'];
	$allow_activity       = $bgr['allow_activity'];
	$exclude_groups       = isset( $bgr['exclude_groups'] ) ? array_map( 'absint', (array) $bgr['exclude_groups'] ) : array();
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
					<p class="description"><?php esc_html_e( 'Allow users to submit multiple reviews for the same group.', 'bp-group-reviews' ); ?></p>
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
					<label><?php esc_html_e( 'Enable Auto Approval of Reviews', 'bp-group-reviews' ); ?></label>
					<p class="description"><?php esc_html_e( 'Automatically publish reviews without requiring group admin approval.', 'bp-group-reviews' ); ?></p>
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
					<label for="reviews_per_page"><?php esc_html_e( 'Reviews Per Page', 'bp-group-reviews' ); ?></label>
					<p class="description"><?php esc_html_e( 'Number of reviews to display per page.', 'bp-group-reviews' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<input id="reviews_per_page" class="small-text" name="reviews_per_page" step="1" min="1" value="<?php echo esc_attr( $reviews_per_page ); ?>" type="number">
					<?php esc_html_e( 'Reviews', 'bp-group-reviews' ); ?>
				</div>
			</div>
			<div class="bgr-row wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label><?php esc_html_e( 'Enable BuddyPress Notifications', 'bp-group-reviews' ); ?></label>
					<?php if ( bp_is_active( 'notifications' ) ) { ?>
						<p class="description"><?php esc_html_e( 'Notify group admins and reviewers about review activities.', 'bp-group-reviews' ); ?></p>
					<?php } else { ?>                        
						<p class="description">
						<?php
						printf(
							esc_html__( 'This setting requires the %s to be active.', 'bp-group-reviews' ),
							'<strong><a href="' . esc_url( admin_url( 'admin.php?page=bp-components' ) ) . '">' . esc_html( 'BuddyPress Notifications Component' ) . '</a></strong>'
						);
						?>
					</p>
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
					<label><?php esc_html_e( 'Enable Review Activity', 'bp-group-reviews' ); ?></label>
					<?php if ( bp_is_active( 'activity' ) ) { ?>
						<p class="description"><?php esc_html_e( 'Post group reviews to the activity stream.', 'bp-group-reviews' ); ?></p>
					<?php } else { ?>
						<p class="description">
						<?php
						printf(
							esc_html__( 'This setting requires the %s to be active.', 'bp-group-reviews' ),
							'<strong><a href="' . esc_url( admin_url( 'admin.php?page=bp-components' ) ) . '">' . esc_html( 'BuddyPress Activity Component' ) . '</a></strong>'
						);
						?>
					</p>
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
					<label><?php esc_html_e( 'Enable Group-Level Criteria', 'bp-group-reviews' ); ?></label>
					<p class="description"><?php esc_html_e( 'Allow group admins to customize review criteria for their specific groups.', 'bp-group-reviews' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<label class="wb-switch" for="bgr-enable-group-criteria">
						<input type="checkbox" id="bgr-enable-group-criteria"
						<?php
						$enable_group_criteria = isset( $bgr['enable_group_criteria'] ) ? $bgr['enable_group_criteria'] : 'no';
						if ( 'yes' === $enable_group_criteria ) {
							echo 'checked="checked"'; }
						?>
						>
						<div class="wb-slider wb-round"></div>
					</label>
				</div>
			</div>
			<div class="bgr-row wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label><?php esc_html_e( 'Exclude Groups from Reviews', 'bp-group-reviews' ); ?></label>
					<p class="description"><?php esc_html_e( 'Select the groups that should not have review functionality.', 'bp-group-reviews' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<select id="bgr-exclude-group-review" name="bgr-exclude-group[]" multiple>
						<?php
						if ( $allgroups ) {
							foreach ( $allgroups['groups'] as $group ) :
								$group_id_int = absint( $group->id );
								if ( ! empty( $exclude_groups ) && in_array( $group_id_int, $exclude_groups, true ) ) {
									?>
									<option value="<?php echo esc_attr( $group_id_int ); ?>" selected><?php echo esc_html( $group->name ); ?></option>
									<?php
								} else {
									?>
									<option value="<?php echo esc_attr( $group_id_int ); ?>"><?php echo esc_html( $group->name ); ?></option>
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
 * Display content for the "Emails" tab in BuddyPress Group Reviews settings.
 *
 * @since    1.0.0
 */
function bp_group_review_emails_setting() {
	if ( function_exists( 'wp_enqueue_editor' ) ) {
		wp_enqueue_editor();
	}

	?>

	<?php if ( isset( $_GET['settings-updated'] ) ) { ?> 
		<div id="bgr-email-settings-updated" class="updated settings-error notice is-dismissible">
			<p>
				<strong><?php esc_html_e( 'Settings Saved.', 'bp-group-reviews' ); ?></strong>
			</p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'bp-group-reviews' ); ?></span>
			</button>
		</div>
	<?php } ?> 
	
	<div class="wbcom-admin-title-section">
		<h3><?php esc_html_e( 'Email Settings', 'bp-group-reviews' ); ?></h3>       
	</div>
	<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
	<form method="post" action="options.php">
			<?php
				settings_fields( 'bp_group_review_email_settings' );
				do_settings_sections( 'bp_group_review_email_settings' );

				// Fetch the values from the database
				$bp_group_review_email_settings = get_option( 'bp_group_review_email_settings' );

				// Set defaults if no settings exist
			if ( empty( $bp_group_review_email_settings ) ) {
				$bp_group_review_email_settings = array(
					'bgr_allow_email'             => 'yes',
					'review_email_subject'        => 'New Review Submitted for Your Group [group-name] on [site-name]',
					'review_email_message'        => 'Hello [admin-name],<br><br>
                            A new review has been submitted for your group [group-name] by [user-name].<br><br>
                            You can view and respond to the review here: [review-link]<br><br>
                            Thank you for creating a space where members can share their experiences!<br><br>
                            Best regards,<br>
                            The [site-name] Team',
					'bgr_accept_enable'           => 'yes',
					'review_accept_email_subject' => 'Your Review for [group-name] on [site-name] Has Been Approved',
					'review_accept_email_message' => 'Hello [user-name],<br><br>
                            Your review for [group-name] on [site-name] has been approved and is now published.<br><br>
                            Thank you for sharing your thoughts with the community. View your review here: [review-link]<br><br>
                            Best regards,<br>
                            The [site-name] Team',
					'bgr_deny_email'              => 'yes',
					'review_deny_email_subject'   => 'Your Review for [group-name] on [site-name] Was Not Approved',
					'review_deny_email_message'   => 'Hello [user-name],<br><br>
                            Your review for [group-name] on [site-name] was not approved by the group administrator.<br><br>
                            If you have questions about our community guidelines, please contact the group admin.<br><br>
                            Thank you for your understanding.<br><br>
                            Best regards,<br>
                            The [site-name] Team',
				);
				update_option( 'bp_group_review_email_settings', $bp_group_review_email_settings );
			}

				$bgr_allow_email             = isset( $bp_group_review_email_settings['bgr_allow_email'] ) ? $bp_group_review_email_settings['bgr_allow_email'] : 'no';
				$bgr_accept_enable           = isset( $bp_group_review_email_settings['bgr_accept_enable'] ) ? $bp_group_review_email_settings['bgr_accept_enable'] : 'no';
				$bgr_deny_email              = isset( $bp_group_review_email_settings['bgr_deny_email'] ) ? $bp_group_review_email_settings['bgr_deny_email'] : 'no';
				$review_email_subject        = isset( $bp_group_review_email_settings['review_email_subject'] ) ? $bp_group_review_email_settings['review_email_subject'] : '';
				$review_email_message        = isset( $bp_group_review_email_settings['review_email_message'] ) ? $bp_group_review_email_settings['review_email_message'] : '';
				$review_accept_email_subject = isset( $bp_group_review_email_settings['review_accept_email_subject'] ) ? $bp_group_review_email_settings['review_accept_email_subject'] : '';
				$review_accept_email_message = isset( $bp_group_review_email_settings['review_accept_email_message'] ) ? $bp_group_review_email_settings['review_accept_email_message'] : '';
				$review_deny_email_subject   = isset( $bp_group_review_email_settings['review_deny_email_subject'] ) ? $bp_group_review_email_settings['review_deny_email_subject'] : '';
				$review_deny_email_message   = isset( $bp_group_review_email_settings['review_deny_email_message'] ) ? $bp_group_review_email_settings['review_deny_email_message'] : '';
			?>
			<div class="form-table">
				<label>
					<h3><?php esc_html_e( 'Review Submission', 'bp-group-reviews' ); ?></h3>
				</label>
				<div class="bgr-row wbcom-settings-section-wrap">
					<label></label>
					<div class="wbcom-settings-section-options-heading">
						<label><?php esc_html_e( 'Emails', 'bp-group-reviews' ); ?></label>
						<p class="description"><?php esc_html_e( 'Enable this option to notify the group admin and reviewer via email when a review is added, accepted, or denied.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<label class="wb-switch" for="bgr-allow-email">
							<input type="checkbox" id="bgr-allow-email" name="bp_group_review_email_settings[bgr_allow_email]" value="yes" <?php checked( $bgr_allow_email, 'yes' ); ?>>
							<div class="wb-slider wb-round"></div>
						</label>
					</div>
				</div>
				
				<div class="bgr-row wbcom-settings-section-wrap review-email-section">
					<div class="wbcom-settings-section-options-heading">
						<label><?php esc_html_e( 'Email Subject', 'bp-group-reviews' ); ?></label>
						<p class="description"><?php esc_html_e( 'Enter the email subject for review notifications.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<input id="review_email_subject" class="large-text" name="bp_group_review_email_settings[review_email_subject]" value="<?php echo esc_attr( $review_email_subject ); ?>" type="text" placeholder="Enter review email subject">
					</div>
				</div>

				<div class="bgr-row wbcom-settings-section-wrap review-email-section">
					<div class="wbcom-settings-section-options-heading">
						<label><?php esc_html_e( 'Email Message', 'bp-group-reviews' ); ?></label>
						<p class="description"><?php esc_html_e( 'Enter the email message for review notifications.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<?php
						wp_editor(
							$review_email_message,
							'bgr-email-message',
							array(
								'media_buttons' => false,
								'textarea_name' => 'bp_group_review_email_settings[review_email_message]',
							)
						);
						?>
					</div>
				</div>

				<!-- Review Acceptance Section -->
				<label><h3><?php esc_html_e( 'Review Acceptance', 'bp-group-reviews' ); ?></h3></label>
				<div class="bgr-row wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label><?php esc_html_e( 'Emails', 'bp-group-reviews' ); ?></label>
						<p class="description"><?php esc_html_e( 'Enable this option to notify the reviewer via email when their review is accepted.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<label class="wb-switch" for="bgr-accept-enable">
							<input type="checkbox" id="bgr-accept-enable" name="bp_group_review_email_settings[bgr_accept_enable]" value="yes" <?php checked( $bgr_accept_enable, 'yes' ); ?>>
							<div class="wb-slider wb-round"></div>
						</label>
					</div>
				</div>
				<div class="bgr-row wbcom-settings-section-wrap review-accept-email-section">
					<div class="wbcom-settings-section-options-heading">
						<label><?php esc_html_e( 'Email Subject', 'bp-group-reviews' ); ?></label>
						<p class="description"><?php esc_html_e( 'Enter the email subject for review acceptance notifications.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<input id="review_accept_email_subject" class="large-text" name="bp_group_review_email_settings[review_accept_email_subject]" value="<?php echo esc_attr( $review_accept_email_subject ); ?>" type="text" placeholder="Enter review acceptance email subject">
					</div>
				</div>

				<div class="bgr-row wbcom-settings-section-wrap review-accept-email-section">
					<div class="wbcom-settings-section-options-heading">
						<label><?php esc_html_e( 'Email Message', 'bp-group-reviews' ); ?></label>
						<p class="description"><?php esc_html_e( 'Enter the email message for review acceptance notifications.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<?php
						wp_editor(
							$review_accept_email_message,
							'bgr-accept-email-message',
							array(
								'media_buttons' => false,
								'textarea_name' => 'bp_group_review_email_settings[review_accept_email_message]',
							)
						);
						?>
					</div>
				</div>

				<!-- Review Denial Section -->
				<label><h3><?php esc_html_e( 'Review Denial', 'bp-group-reviews' ); ?></h3></label>
				<div class="bgr-row wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label><?php esc_html_e( 'Emails', 'bp-group-reviews' ); ?></label>
						<p class="description"><?php esc_html_e( 'Enable this option to notify the reviewer via email when their review is denied.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<label class="wb-switch" for="bgr-deny-email">
							<input type="checkbox" id="bgr-deny-email" name="bp_group_review_email_settings[bgr_deny_email]" value="yes" <?php checked( $bgr_deny_email, 'yes' ); ?>>
							<div class="wb-slider wb-round"></div>
						</label>
					</div>
				</div>

				<div class="bgr-row wbcom-settings-section-wrap review-deny-email-section">
					<div class="wbcom-settings-section-options-heading">
						<label><?php esc_html_e( 'Email Subject', 'bp-group-reviews' ); ?></label>
						<p class="description"><?php esc_html_e( 'Enter the email subject for review denial notifications.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<input id="review_deny_email_subject" class="large-text" name="bp_group_review_email_settings[review_deny_email_subject]" value="<?php echo esc_attr( $review_deny_email_subject ); ?>" type="text" placeholder="Enter review denial email subject">
					</div>
				</div>

				<div class="bgr-row wbcom-settings-section-wrap review-deny-email-section">
					<div class="wbcom-settings-section-options-heading">
						<label><?php esc_html_e( 'Email Message', 'bp-group-reviews' ); ?></label>
						<p class="description"><?php esc_html_e( 'Enter the email message for review denial notifications.', 'bp-group-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<?php
						wp_editor(
							$review_deny_email_message,
							'bgr-deny-email-message',
							array(
								'media_buttons' => false,
								'textarea_name' => 'bp_group_review_email_settings[review_deny_email_message]',
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
