<?php
/**
 * Emails settings tab.
 *
 * UNLIKE the other tabs, this one persists via the Settings API:
 * options.php + register_setting( 'bp_group_review_email_settings' )
 * with sanitize callback bp_group_review_sanitize_email_settings.
 * The field names (bp_group_review_email_settings[...]) and the
 * settings_fields() group are preserved verbatim from the legacy
 * form, so saving is byte-identical.
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/admin
 * @since      3.9.0
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'wp_enqueue_editor' ) ) {
	wp_enqueue_editor();
}

// Fetch values; seed defaults if empty (mirrors legacy behaviour).
$bgr_email_settings = get_option( 'bp_group_review_email_settings' );
if ( empty( $bgr_email_settings ) ) {
	$bgr_email_settings = array(
		'bgr_allow_email'             => 'yes',
		// The [token] strings are substituted at send time, not translated.
		'review_email_subject'        => __( 'New Review Submitted for Your Group [group-name] on [site-name]', 'bp-group-reviews' ),
		'review_email_message'        => __( 'Hello [admin-name],<br><br>A new review has been submitted for your group [group-name] by [user-name].<br><br>You can view and respond to the review here: [review-link]<br><br>Thank you for creating a space where members can share their experiences!<br><br>Best regards,<br>The [site-name] Team', 'bp-group-reviews' ),
		'bgr_accept_enable'           => 'yes',
		'review_accept_email_subject' => __( 'Your Review for [group-name] on [site-name] Has Been Approved', 'bp-group-reviews' ),
		'review_accept_email_message' => __( 'Hello [user-name],<br><br>Your review for [group-name] on [site-name] has been approved and is now published.<br><br>Thank you for sharing your thoughts with the community. View your review here: [review-link]<br><br>Best regards,<br>The [site-name] Team', 'bp-group-reviews' ),
		'bgr_deny_email'              => 'yes',
		'review_deny_email_subject'   => __( 'Your Review for [group-name] on [site-name] Was Not Approved', 'bp-group-reviews' ),
		'review_deny_email_message'   => __( 'Hello [user-name],<br><br>Your review for [group-name] on [site-name] was not approved by the group administrator.<br><br>If you have questions about our community guidelines, please contact the group admin.<br><br>Thank you for your understanding.<br><br>Best regards,<br>The [site-name] Team', 'bp-group-reviews' ),
	);
	update_option( 'bp_group_review_email_settings', $bgr_email_settings );
}

$bgr_allow_email          = isset( $bgr_email_settings['bgr_allow_email'] ) ? $bgr_email_settings['bgr_allow_email'] : 'no';
$bgr_accept_enable        = isset( $bgr_email_settings['bgr_accept_enable'] ) ? $bgr_email_settings['bgr_accept_enable'] : 'no';
$bgr_deny_email           = isset( $bgr_email_settings['bgr_deny_email'] ) ? $bgr_email_settings['bgr_deny_email'] : 'no';
$bgr_email_subject        = isset( $bgr_email_settings['review_email_subject'] ) ? $bgr_email_settings['review_email_subject'] : '';
$bgr_email_message        = isset( $bgr_email_settings['review_email_message'] ) ? $bgr_email_settings['review_email_message'] : '';
$bgr_accept_email_subject = isset( $bgr_email_settings['review_accept_email_subject'] ) ? $bgr_email_settings['review_accept_email_subject'] : '';
$bgr_accept_email_message = isset( $bgr_email_settings['review_accept_email_message'] ) ? $bgr_email_settings['review_accept_email_message'] : '';
$bgr_deny_email_subject   = isset( $bgr_email_settings['review_deny_email_subject'] ) ? $bgr_email_settings['review_deny_email_subject'] : '';
$bgr_deny_email_message   = isset( $bgr_email_settings['review_deny_email_message'] ) ? $bgr_email_settings['review_deny_email_message'] : '';
?>
<form method="post" action="options.php" id="bgr-email-settings-form">
	<?php settings_fields( 'bp_group_review_email_settings' ); ?>

	<div class="bgr-card">
		<div class="bgr-card__head">
			<p class="bgr-card__title"><?php esc_html_e( 'Review Submission', 'bp-group-reviews' ); ?></p>
			<p class="bgr-card__desc"><?php esc_html_e( 'Sent to the group admin when a member submits a new review.', 'bp-group-reviews' ); ?></p>
		</div>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable Email', 'bp-group-reviews' ); ?></th>
				<td>
					<label class="wb-switch" for="bgr-allow-email">
						<input type="checkbox" id="bgr-allow-email" name="bp_group_review_email_settings[bgr_allow_email]" value="yes" <?php checked( $bgr_allow_email, 'yes' ); ?>>
						<div class="wb-slider wb-round"></div>
					</label>
					<p class="description"><?php esc_html_e( 'Notify the group admin by email when a review is submitted.', 'bp-group-reviews' ); ?></p>
				</td>
			</tr>
			<tr class="review-email-section">
				<th scope="row"><label for="review_email_subject"><?php esc_html_e( 'Email Subject', 'bp-group-reviews' ); ?></label></th>
				<td>
					<input id="review_email_subject" class="large-text" name="bp_group_review_email_settings[review_email_subject]" value="<?php echo esc_attr( $bgr_email_subject ); ?>" type="text">
				</td>
			</tr>
			<tr class="review-email-section">
				<th scope="row"><label><?php esc_html_e( 'Email Message', 'bp-group-reviews' ); ?></label></th>
				<td>
					<?php
					wp_editor(
						$bgr_email_message,
						'bgr-email-message',
						array(
							'media_buttons' => false,
							'textarea_name' => 'bp_group_review_email_settings[review_email_message]',
						)
					);
					?>
				</td>
			</tr>
		</table>
	</div>

	<div class="bgr-card">
		<div class="bgr-card__head">
			<p class="bgr-card__title"><?php esc_html_e( 'Review Acceptance', 'bp-group-reviews' ); ?></p>
			<p class="bgr-card__desc"><?php esc_html_e( 'Sent to the reviewer when their review is approved.', 'bp-group-reviews' ); ?></p>
		</div>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable Email', 'bp-group-reviews' ); ?></th>
				<td>
					<label class="wb-switch" for="bgr-accept-enable">
						<input type="checkbox" id="bgr-accept-enable" name="bp_group_review_email_settings[bgr_accept_enable]" value="yes" <?php checked( $bgr_accept_enable, 'yes' ); ?>>
						<div class="wb-slider wb-round"></div>
					</label>
				</td>
			</tr>
			<tr class="review-accept-email-section">
				<th scope="row"><label for="review_accept_email_subject"><?php esc_html_e( 'Email Subject', 'bp-group-reviews' ); ?></label></th>
				<td>
					<input id="review_accept_email_subject" class="large-text" name="bp_group_review_email_settings[review_accept_email_subject]" value="<?php echo esc_attr( $bgr_accept_email_subject ); ?>" type="text">
				</td>
			</tr>
			<tr class="review-accept-email-section">
				<th scope="row"><label><?php esc_html_e( 'Email Message', 'bp-group-reviews' ); ?></label></th>
				<td>
					<?php
					wp_editor(
						$bgr_accept_email_message,
						'bgr-accept-email-message',
						array(
							'media_buttons' => false,
							'textarea_name' => 'bp_group_review_email_settings[review_accept_email_message]',
						)
					);
					?>
				</td>
			</tr>
		</table>
	</div>

	<div class="bgr-card">
		<div class="bgr-card__head">
			<p class="bgr-card__title"><?php esc_html_e( 'Review Denial', 'bp-group-reviews' ); ?></p>
			<p class="bgr-card__desc"><?php esc_html_e( 'Sent to the reviewer when their review is denied.', 'bp-group-reviews' ); ?></p>
		</div>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable Email', 'bp-group-reviews' ); ?></th>
				<td>
					<label class="wb-switch" for="bgr-deny-email">
						<input type="checkbox" id="bgr-deny-email" name="bp_group_review_email_settings[bgr_deny_email]" value="yes" <?php checked( $bgr_deny_email, 'yes' ); ?>>
						<div class="wb-slider wb-round"></div>
					</label>
				</td>
			</tr>
			<tr class="review-deny-email-section">
				<th scope="row"><label for="review_deny_email_subject"><?php esc_html_e( 'Email Subject', 'bp-group-reviews' ); ?></label></th>
				<td>
					<input id="review_deny_email_subject" class="large-text" name="bp_group_review_email_settings[review_deny_email_subject]" value="<?php echo esc_attr( $bgr_deny_email_subject ); ?>" type="text">
				</td>
			</tr>
			<tr class="review-deny-email-section">
				<th scope="row"><label><?php esc_html_e( 'Email Message', 'bp-group-reviews' ); ?></label></th>
				<td>
					<?php
					wp_editor(
						$bgr_deny_email_message,
						'bgr-deny-email-message',
						array(
							'media_buttons' => false,
							'textarea_name' => 'bp_group_review_email_settings[review_deny_email_message]',
						)
					);
					?>
				</td>
			</tr>
		</table>
		<div class="bgr-save-bar">
			<?php submit_button( __( 'Save Settings', 'bp-group-reviews' ), 'primary bgr-btn bgr-btn-primary', 'submit', false ); ?>
		</div>
	</div>
</form>
