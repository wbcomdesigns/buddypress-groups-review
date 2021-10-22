<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.
if ( isset( $_GET ) ) {
	if ( isset( $_GET['tab'] ) ) {
		$bgr_setting_tab = sanitize_text_field( $_GET['tab'] );
		bgr_include_admin_setting_tabs( $bgr_setting_tab );
	} else {
		bgr_include_admin_setting_tabs( 'welcome' );
	}
}

/** Actions performed on Display review admin settings tabs
 *
 * @since    1.0.0
 * @author   Wbcom Designs
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
	<div class="bgr-row">
		<div class="bgr-col-3">
			<label><?php esc_html_e( 'Reviews Criteria(s)', 'bp-group-reviews' ); ?></label>
		</span>
	</div>
	<div class="bgr-col-9">
		<div id="bgr-textbox-container">
			<?php
			if ( ! empty( $review_rating_fields ) ) {
				foreach ( $review_rating_fields as $review_rating_field ) :
					?>
			<div class="rating-review-div"><span>&equiv;</span><input name = "BGRDynamicTextBox" class="draggable" type="text" value = "<?php echo esc_attr( $review_rating_field ); ?>" />
				<input type="button" value="<?php esc_html_e( 'Remove', 'bp-group-reviews' ); ?>" class="remove button button-secondary" />
				<label class="bgr-switch">
				<input type="checkbox" class="bgr-criteria-state" name="bgr-criteria-state" data-attr="<?php echo esc_attr( $review_rating_field ); ?>"
																													<?php
																													if ( in_array( $review_rating_field, $active_rating_fields ) ) {
																														echo 'checked="checked"'; }
																													?>
>
				<div class="bgr-slider round"></div>
			</label>
		</div>
					<?php
		endforeach;
			}
			?>
</div>
<input id="bgr-field-add" type="button" value="<?php esc_html_e( 'Add Review Criteria', 'bp-group-reviews' ); ?>" class="button button-secondary"/>
<p class="bgr-admin-des"><?php esc_html_e( 'This option provide you to add multiple rating criteria. By default, no criteria will be shown until you enable it.', 'bp-group-reviews' ); ?></p>
</div>
</div>
<input type="button" class="button button-primary bgr-submit-button" id="bgr-save-admin-criteria-settings" value="<?php esc_html_e( 'Save Settings', 'bp-group-reviews' ); ?>">
<img src="<?php echo esc_url( $spinner_src ); ?>" class="bgr-admin-criteria-settings-spinner" />
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
<div class="bgr-row">
	<div class="bgr-col-3"><label><?php echo '[add_group_review_form]'; ?></label></div>
	<div class="bgr-col-9"><?php esc_html_e( 'This shortcode will display Group Review Form.', 'bp-group-reviews' ); ?></div>
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
	$spinner_src          = includes_url() . 'images/spinner.gif';
	$bgrReviewLabel       = $bgr['review_label'];
	$bgrManageReviewLabel = $bgr['manage_review_label'];
	$bgr_rating_color     = $bgr['rating_color'];
	?>
	<div class="bgr-col-12"><h3><?php esc_html_e( 'Labels', 'bp-group-reviews' ); ?></h3></div>
	<div class="bgr-row">
		<div class="bgr-col-3"><label for="bgrReviewLabel"><?php esc_html_e( 'Review', 'bp-group-reviews' ); ?></label></div>
		<div class="bgr-col-9">
		<input name = "bgrReviewLabel" id="bgrReviewLabel" type="text" value = "<?php echo esc_attr( $bgrReviewLabel ); ?>" />
		<p class="bgr-admin-des"><?php esc_html_e( 'This option provides flexibility to change review label. By default it shows "Review".', 'bp-group-reviews' ); ?></p>
	</div>
	</div>
	<div class="bgr-row">
		<div class="bgr-col-3"><label for="bgrManageReviewLabel"><?php esc_html_e( 'Reviews ( Plural )', 'bp-group-reviews' ); ?></label></div>
		<div class="bgr-col-9">
		<input name = "bgrManageReviewLabel" id="bgrManageReviewLabel" type="text" value = "<?php echo esc_attr( $bgrManageReviewLabel ); ?>" />
		<p class="bgr-admin-des"><?php esc_html_e( 'This option provides flexibility to change plural of Review.', 'bp-group-reviews' ); ?></p>
	</div>
	</div>
	<div class="bgr-col-12"><h3><?php esc_html_e( 'Colors', 'bp-group-reviews' ); ?></h3></div>
	<div class="bgr-row">
		<div class="bgr-col-3"><label for="bgr-rating-color"><?php esc_html_e( 'Rating Color', 'bp-group-reviews' ); ?></label></div>
		<div class="bgr-col-9">
			<input id="bgr-rating-color" class="bgr-review-color" type="text" data-default-color="#effeff" value="<?php echo esc_attr( $bgr_rating_color ); ?>" />
			<p class="bgr-admin-des"><?php esc_html_e( 'This option lets you to change star rating color.', 'bp-group-reviews' ); ?></p>
		</div>
	</div>
	<input type="button" class="button button-primary bgr-submit-button" id="bgr-save-admin-display-settings" value="<?php esc_html_e( 'Save Settings', 'bp-group-reviews' ); ?>">
	<img src="<?php echo esc_url( $spinner_src ); ?>" class="bgr-admin-display-settings-spinner" />
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
		$base_link = '<a href="' . esc_url( $base_url ) . '">' . esc_html__( 'here' , 'bp-group-reviews') . '</a>';
		echo sprintf( __( '<h2>This plugin is work with BuddyPress Groups Component. Please activet the BuddyPress Groups component. To activate groups component click %s. </h2>', 'bp-group-reviews' ), $base_link );

		return;
	}

	$spinner_src          = includes_url() . 'images/spinner.gif';
	$auto_approve_reviews = $bgr['auto_approve_reviews'];
	$reviews_per_page     = $bgr['reviews_per_page'];
	$review_email_subject = (isset($bgr['review_email_subject'])) ? $bgr['review_email_subject'] : 'A new review posted.' ;
	$review_email_message = (isset($bgr['review_email_message'])) ? $bgr['review_email_message'] : 'Hello

A new review for [group-name] added by [user-name]. Link: [review-link]

Thanks';
	$allow_email          = $bgr['allow_email'];
	$allow_notification   = $bgr['allow_notification'];
	$exclude_groups       = $bgr['exclude_groups'];
	$multi_reviews        = $bgr['multi_reviews'];
	$group_args           = array(
		'order'    => 'DESC',
		'orderby'  => 'date_created',
		'per_page' => -1,
	);
	$allgroups            = groups_get_groups( $group_args );
	?>

	<div class="bgr-row">
	<div class="bgr-col-3">
		<label><?php esc_html_e( 'Enable Multiple Reviews', 'bp-group-reviews' ); ?></label>
	</div>
	<div class="bgr-col-9">
		<label class="bgr-switch" for="bgr-multi-reviews">
			<input type="checkbox" id="bgr-multi-reviews"
			<?php
			if ( 'yes' == $multi_reviews ) {
				echo 'checked="checked"'; }
			?>
>
			<div class="bgr-slider round"></div>
		</label>
		<p class="bgr-admin-des"><?php esc_html_e( 'Enable this option, if you want to add functionality for user to send multiple review to same group.', 'bp-group-reviews' ); ?></p>
	</div>
	</div>

	<div class="bgr-row">
	<div class="bgr-col-3">
		<label><?php esc_html_e( 'Enable auto approval of Reviews', 'bp-group-reviews' ); ?></label>
	</div>
	<div class="bgr-col-9">
		<label class="bgr-switch" for="bgr-auto-approve-reviews">
			<input type="checkbox" id="bgr-auto-approve-reviews"
			<?php
			if ( 'yes' == $auto_approve_reviews ) {
				echo 'checked="checked"'; }
			?>
>
			<div class="bgr-slider round"></div>
		</label>
		<p class="bgr-admin-des"><?php esc_html_e( 'Enable this option, if you want to have the reviews automatically approved, else manual approval will be required.', 'bp-group-reviews' ); ?></p>
	</div>
	</div>

	<div class="bgr-row">
	<div class="bgr-col-3">
		<label for="reviews_per_page"><?php esc_html_e( 'Reviews show at most', 'bp-group-reviews' ); ?></label>
	</div>
	<div class="bgr-col-9">
		<input id="reviews_per_page" class="small-text" name="reviews_per_page" step="1" min="1" value="<?php echo esc_attr( $reviews_per_page ); ?>" type="number">
		<?php esc_html_e( 'Reviews', 'bp-group-reviews' ); ?>
		<p class="bgr-admin-des"><?php esc_html_e( 'This option lets you limit number of reviews in "Group Reviews" page & "Manage Reviews" Page.', 'bp-group-reviews' ); ?></p>
	</div>
	</div>
	<div class="bgr-row">
	<div class="bgr-col-3"><label><?php esc_html_e( 'Enable BuddyPress notifications', 'bp-group-reviews' ); ?></label></div>
	<div class="bgr-col-9">
		<?php if ( bp_is_active( 'notifications' ) ) { ?>
		<label class="bgr-switch" for="bgr-notification">
			<input type="checkbox" id="bgr-notification"
				<?php
				if ( $allow_notification == 'yes' ) {
					echo 'checked="checked"'; }
				?>
>
			<div class="bgr-slider round"></div>
		</label>
		<p class="bgr-admin-des"><?php esc_html_e( 'Enable this option, if you want group admin & reviewer to receive a notification when add, accept & deny review.', 'bp-group-reviews' ); ?></p>
			<?php } else { ?>
		<p class="bgr-admin-des"><?php esc_html_e( 'This setting requires BuddyPress Notifications Component to be active.', 'bp-group-reviews' ); ?></p>
			<?php } ?>
</div>
</div>
<div class="bgr-row">
	<div class="bgr-col-3"><label><?php esc_html_e( 'Emails', 'bp-group-reviews' ); ?></label></div>
	<div class="bgr-col-9">
	<label class="bgr-switch" for="bgr-email">
		<input type="checkbox" id="bgr-email"
		<?php
		if ( $allow_email == 'yes' ) {
			echo 'checked="checked"'; }
		?>
>
		<div class="bgr-slider round"></div>
	</label>
	<p class="bgr-admin-des"><?php esc_html_e( 'Enable this option, if you want group admin & reviewer receive email when someone adds, accepts & denies review.', 'bp-group-reviews' ); ?></p>
	</div>
</div>
<div class="bgr-row review-email-section" <?php if($allow_email != 'yes'):?> style="display:none;" <?php endif; ?>>
	<div class="bgr-col-3"><label><?php esc_html_e( 'Email Subject', 'bp-group-reviews' ); ?></label></div>
	<div class="bgr-col-9">
		<input id="review_email_subject" class="large-text" name="review_email_subject" value="<?php echo esc_attr( $review_email_subject ); ?>" type="text" placeholder="Please enter review email subject.">
		<p class="bgr-admin-des"><?php esc_html_e( 'Please add review email subject.', 'bp-group-reviews' ); ?></p>
	</div>
</div>

<div class="bgr-row review-email-section" <?php if($allow_email != 'yes'):?> style="display:none;" <?php endif; ?>>
	<div class="bgr-col-3"><label><?php esc_html_e( 'Email Message', 'bp-group-reviews' ); ?></label></div>
	<div class="bgr-col-9">
		<textarea id="review_email_message" class="large-text" name="review_email_message" ><?php echo esc_html($review_email_message);?></textarea>
		<p class="bgr-admin-des"><?php esc_html_e( 'Please add review email message.', 'bp-group-reviews' ); ?></p>
	</div>
</div>
<div class="bgr-row">
	<div class="bgr-col-3"><label><?php esc_html_e( 'Exclude Groups from Reviews', 'bp-group-reviews' ); ?></label></div>
	<div class="bgr-col-9">
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
			<p class="bgr-admin-des"><?php esc_html_e( "This option lets you choose those groups that you don't want to provide review functionality.", 'bp-group-reviews' ); ?></p>
		</div>
	</div>
	<input type="button" class="button button-primary bgr-submit-button" id="bgr-save-admin-general-settings" value="<?php esc_html_e( 'Save Settings', 'bp-group-reviews' ); ?>">
	<img src="<?php echo esc_url( $spinner_src ); ?>" class="bgr-admin-general-settings-spinner" />
	<?php
}
