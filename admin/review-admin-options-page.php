<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly
if ( isset( $_GET ) ) {
	if ( isset( $_GET['tab'] ) ) {
		$bgr_setting_tab = sanitize_text_field( $_GET['tab'] );
		bgr_include_admin_setting_tabs( $bgr_setting_tab );
	} else {
		bgr_include_admin_setting_tabs( 'general' );
	}
}

/** Actions performed on Display review admin settings tabs
 *
 * @since    1.0.0
 * @author   Wbcom Designs
 */

function bgr_include_admin_setting_tabs( $bgr_setting_tab = 'general' ) {
	switch ( $bgr_setting_tab ) {
		case 'criteria':
			bgr_criteria_setting();
			break;
		case 'shortcode':
			bgr_shortcode_setting();
			break;
		case 'display':
			bgr_display_setting();
			break;
		case 'support':
			bgr_support_setting();
			break;
		default:
			bgr_general_setting();
	}
}

/** Actions performed on Group Reviews Settings : Criteria Tab Content
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
	<div class="bgr-col-12"><h2><?php _e( 'Criteria Settings', 'bp-group-reviews' ); ?></h2></div>
	<div class="bgr-row">
		<div class="bgr-col-3">
			<label><?php _e( 'Reviews Criteria(s)', 'bp-group-reviews' ); ?></label>
		</span>
	</div>
	<div class="bgr-col-9">
		<div id="bgr-textbox-container">
			<?php
			if ( ! empty( $review_rating_fields ) ) {
				foreach ( $review_rating_fields as $review_rating_field ) :
				?>
			<div class="rating-review-div"><span>&equiv;</span><input name = "BGRDynamicTextBox" class="draggable" type="text" value = "<?php echo $review_rating_field; ?>" />
				<input type="button" value="<?php _e( 'Remove', 'bp-group-reviews' ); ?>" class="remove button button-secondary" />
				<label class="bgr-switch">
				<input type="checkbox" class="bgr-criteria-state" name="bgr-criteria-state" data-attr="<?php echo $review_rating_field; ?>"
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
<input id="bgr-field-add" type="button" value="<?php _e( 'Add Review Criteria', 'bp-group-reviews' ); ?>" class="button button-secondary"/>
<p class="bgr-admin-des"><?php _e( 'This option provide you to add multiple rating criteria. By default, no criteria will be shown until you active it.', 'bp-group-reviews' ); ?></p>
</div>
</div>
<div class="bgr-col-12">
	<div class="bgr-col-6">
		<input type="button" class="button button-primary" id="bgr-save-admin-criteria-settings" value="<?php _e( 'Save Settings', 'bp-group-reviews' ); ?>">
		<img src="<?php echo $spinner_src; ?>" class="bgr-admin-criteria-settings-spinner" />
	</div>
</div>
<?php
}

	/**
	 * Actions performed on Group Reviews Settings : Shortcode Tab Content
	 *
	 * @since    1.0.0
	 * @author   Wbcom Designs
	 */
function bgr_shortcode_setting() {
?>
<div class="bgr-col-12"><h2><?php _e( 'Shortcode to display Group Review Form', 'bp-group-reviews' ); ?></h2></div>
<div class="bgr-row">
	<div class="bgr-col-3"><label><?php echo '[add_group_review_form]'; ?></label></div>
	<div class="bgr-col-9"><?php _e( 'This shortcode will be display Group Review Form.', 'bp-group-reviews' ); ?></div>
</div>
<?php
}

/**
 * Actions performed on Group Reviews Settings : Display Tab Content
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
	<div class="bgr-col-12">
		<h2><?php _e( 'Display Settings', 'bp-group-reviews' ); ?></h2>
		<p class="description">
			<b><?php _e( 'Note', 'bp-group-reviews' ); ?>:</b>
			<?php _e( 'Rating will be displayed in Review form, if and only if you added criteria for review.', 'bp-group-reviews' ); ?>
		</p>
	</div>
	<div class="bgr-col-12"><h3><?php _e( 'Labels', 'bp-group-reviews' ); ?></h3></div>
	<div class="bgr-row">
		<div class="bgr-col-3"><label for="bgrReviewLabel"><?php _e( 'Review', 'bp-group-reviews' ); ?></label></div>
		<div class="bgr-col-9">
		<input name = "bgrReviewLabel" id="bgrReviewLabel" type="text" value = "<?php echo $bgrReviewLabel; ?>" />
		<p class="bgr-admin-des"><?php _e( 'This option provide flexibility to change review label. By default it shows "Review".', 'bp-group-reviews' ); ?></p>
	</div>
	</div>
<div class="bgr-row">
	<div class="bgr-col-3"><label for="bgrManageReviewLabel"><?php _e( 'Review (In Manage Section)', 'bp-group-reviews' ); ?></label></div>
	<div class="bgr-col-9">
	<input name = "bgrManageReviewLabel" id="bgrManageReviewLabel" type="text" value = "<?php echo $bgrManageReviewLabel; ?>" />
	<p class="bgr-admin-des"><?php _e( 'This option provides flexibility to change review label in "BuddyPress Group Manage" section. By default it shows "Reviews".', 'bp-group-reviews' ); ?></p>
</div>
</div>
<div class="bgr-col-12"><h3><?php _e( 'Colors', 'bp-group-reviews' ); ?></h3></div>
<div class="bgr-row">
	<div class="bgr-col-3"><label for="bgr-rating-color"><?php _e( 'Rating Color', 'bp-group-reviews' ); ?></label></div>
	<div class="bgr-col-9">
		<input id="bgr-rating-color" class="bgr-review-color" type="text" data-default-color="#effeff" value="<?php echo $bgr_rating_color; ?>" />
		<p class="bgr-admin-des"><?php _e( 'This option lets you to change star rating color.', 'bp-group-reviews' ); ?></p>
	</div>
</div>

<div class="bgr-col-12">
	<div class="bgr-col-6">
		<input type="button" class="button button-primary" id="bgr-save-admin-display-settings" value="<?php _e( 'Save Settings', 'bp-group-reviews' ); ?>">
		<img src="<?php echo $spinner_src; ?>" class="bgr-admin-display-settings-spinner" />
	</div>
</div>
<?php
}

	/*
	 Actions performed on Group Reviews Settings : Support Tab Content
 * @since    1.0.0
 * @author   Wbcom Designs
 */

function bgr_support_setting() {
?>
 <div class="bgr-col-12"><h2><?php _e( 'FAQ(s)', 'bp-group-reviews' ); ?></h2></div>
 <div id="bgr-settings-tbl">
	<div class="bgr-admin-row">
		<div>
		   <button class="bgr-accordion">
			<?php _e( 'How can we submit a review to a group using this plugin?', 'bp-group-reviews' ); ?>
		</button>
		<div class="panel">
			<p>
				<?php _e( 'When visiting the "/groups" section in the site, go for single group view page, there you can see a menu namely, "Reviews" that will allow you if you are a site member to add group review.', 'bp-group-reviews' ); ?>
			</p>
		</div>
	</div>
</div>

<div class="bgr-admin-row">
	<div>
	   <button class="bgr-accordion">
		<?php _e( 'How to allow users to give multiple reviews on the same group?', 'bp-group-reviews' ); ?>
	</button>
	<div class="panel">
		<p>
			<?php _e( 'A new functionality "Multiple Review" is added to this version of the plugin. In general settings, you can enable or disable it.', 'bp-group-reviews' ); ?>
		</p>
	</div>
</div>
</div>

<div class="bgr-admin-row">
	<div>
	   <button class="bgr-accordion">
		<?php _e( 'If I don\'t want to provide review functionality to any group, then what will I do?', 'bp-group-reviews' ); ?>
	</button>
	<div class="panel">
		<p>
			<?php _e( 'If you don\'t want to provide review functionality to any group, you can exclude that group for this functionality. For exclude, the plugin has option to exclude groups in the General tab. Here, you exclude one or more groups from reviews.', 'bp-group-reviews' ); ?>
		</p>
	</div>
</div>
</div>

<div class="bgr-admin-row">
	<div>
	<button class="bgr-accordion">
		<?php _e( 'How to hide some rating criteria from Review form?', 'bp-group-reviews' ); ?>
	</button>
	<div class="panel">
		<p>
			<?php _e( 'You can hide criteria by disable criteria in \'Criteria\' tab.', 'bp-group-reviews' ); ?>
		</p>
	</div>
</div>
</div>

<div class="bgr-admin-row">
	<div>
	<button class="bgr-accordion">
		<?php _e( 'How change label of \'Reviews\' tab or label of \'Reviews\' sub-tab under \'Manage\' tab?', 'bp-group-reviews' ); ?>
	</button>
	<div class="panel">
		<p>
			<?php _e( 'This plugin provides settings to change labels, you can see settings in plugin\'s settings \'Display\' tab.', 'bp-group-reviews' ); ?>
		</p>
	</div>
</div>
</div>

<div class="bgr-admin-row">
	<div>
	<button class="bgr-accordion">
		<?php _e( 'Why \'Add Review\' option is not displaying in a group created by me?', 'bp-group-reviews' ); ?>
	</button>
	<div class="panel">
		<p>
			<?php _e( 'This option is not displaying because group admin can\'t review own group, you can add the review to only that groups in which you are not the admin.', 'bp-group-reviews' ); ?>
		</p>
	</div>
</div>
</div>

<div class="bgr-admin-row">
	<div>
	<button class="bgr-accordion">
		<?php _e( 'How to display groups average reviews according to reviews or ratings in sidebar or footer?', 'bp-group-reviews' ); ?>
	</button>
	<div class="panel">
		<p>
			<?php _e( 'This plugin provides a widget to show average ratings or reviews of BuddyPress Groups, you can use it for display average rating & reviews.', 'bp-group-reviews' ); ?>
		</p>
	</div>
</div>
</div>

<div class="bgr-admin-row">
	<div>
	<button class="bgr-accordion">
		<?php _e( 'Can I use the review form on any other page?', 'bp-group-reviews' ); ?>
	</button>
	<div class="panel">
		<p>
			<?php _e( 'Yes, you can use the review form on other page, just copy shortcode from review setting page and paste it on the other page.', 'bp-group-reviews' ); ?>
		</p>
	</div>
</div>
</div>
<div class="bgr-admin-row">
	<div>
	<button class="bgr-accordion">
		<?php _e( 'How to show only star rating in Group header?', 'bp-group-reviews' ); ?>
	</button>
	<div class="panel">
		<p>
			<?php _e( "If you want to remove additional rating details in group header, you can use 'bgr_filter_group_header_rating_details' filter.", 'bp-group-reviews' ); ?>
<pre>
	// You can remove details for specific group id
	add_filter('bgr_filter_group_header_rating_details', 'remove_rating_addittional_details', 15, 5);
	function remove_rating_addittional_details(  $content, $avg_rating, $review_label, $total_review_count, $group_id ) {
		if( $group_id !== 1 ) {
			return $content;
		}
		return false;
	}

	// Remove details from all groups
	add_filter('bgr_filter_group_header_rating_details', '__return_false');
</pre>
		</p>
	</div>
</div>
</div>
<div class="bgr-admin-row">
	<div>
	<button class="bgr-accordion">
		<?php _e( 'Where do I ask for support?', 'bp-group-reviews' ); ?>
	</button>
	<div class="panel">
		<p>
			<?php
			echo sprintf( __( 'Please visit %1$s for any query related to plugin and BuddyPress.', 'bp-group-reviews' ), '<a href="http://wbcomdesigns.com/contact" rel="nofollow" target="_blank"> Wbcom Designs </a>' );
			?>
		</p>
	</div>
</div>
</div>
</div>
<?php
}

		/**
		 * Actions performed on Group Reviews Settings : General Tab Content
		 *
		 * @since    1.0.0
		 * @author   Wbcom Designs
		 */
function bgr_general_setting() {
	global $bp, $bgr;

	$spinner_src          = includes_url() . 'images/spinner.gif';
	$auto_approve_reviews = $bgr['auto_approve_reviews'];
	$reviews_per_page     = $bgr['reviews_per_page'];
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
<div class="bgr-col-12"><h2><?php _e( 'General Settings', 'bp-group-reviews' ); ?></h2></div>

	<div class="bgr-row">
	<div class="bgr-col-3">
		<label><?php _e( 'Enable Multiple Review', 'bp-group-reviews' ); ?></label>
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
		<p class="bgr-admin-des"><?php _e( 'Enable this option, if you want to add functionality to user can send multiple review to same group.', 'bp-group-reviews' ); ?></p>
	</div>
	</div>

	<div class="bgr-row">
	<div class="bgr-col-3">
		<label><?php _e( 'Enable auto approval of Reviews', 'bp-group-reviews' ); ?></label>
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
		<p class="bgr-admin-des"><?php _e( 'Enable this option, if you want to have the reviews automatically approved, else manual approval will be required.', 'bp-group-reviews' ); ?></p>
	</div>
	</div>

	<div class="bgr-row">
	<div class="bgr-col-3">
		<label for="reviews_per_page"><?php _e( 'Reviews show at most', 'bp-group-reviews' ); ?></label>
	</div>
	<div class="bgr-col-9">
		<input id="reviews_per_page" class="small-text" name="reviews_per_page" step="1" min="1" value="<?php echo $reviews_per_page; ?>" type="number">
		<?php _e( 'Reviews', 'bp-group-reviews' ); ?>
		<p class="bgr-admin-des"><?php _e( 'This option lets you limit number of reviews in "Group Reviews" page & "Manage Reviews" Page.', 'bp-group-reviews' ); ?></p>
	</div>
	</div>
	<div class="bgr-row">
	<div class="bgr-col-3"><label><?php _e( 'Enable BuddyPress notifications', 'bp-group-reviews' ); ?></label></div>
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
		<p class="bgr-admin-des"><?php _e( 'Enable this option, if you want to group admin & reviewer receive notification when add, accept & deny review.', 'bp-group-reviews' ); ?></p>
			<?php } else { ?>
		<p class="bgr-admin-des"><?php _e( 'This setting requires BuddyPress Notifications Component to be active.', 'bp-group-reviews' ); ?></p>
			<?php } ?>
</div>
</div>
<div class="bgr-row">
	<div class="bgr-col-3"><label><?php _e( 'Emails', 'bp-group-reviews' ); ?></label></div>
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
	<p class="bgr-admin-des"><?php _e( 'Enable this option, if you want to group admin & reviewer receive email when add, accept & deny review.', 'bp-group-reviews' ); ?></p>
	</div>
</div>
<div class="bgr-row">
	<div class="bgr-col-3"><label><?php _e( 'Exclude Groups from reviews.', 'bp-group-reviews' ); ?></label></div>
	<div class="bgr-col-9">
	<select id="bgr-exclude-group-review" name="bgr-exclude-group[]" multiple >
		<?php
		if ( $allgroups ) {
			foreach ( $allgroups['groups'] as $group ) :
				if ( ! empty( $exclude_groups ) ) {
					if ( in_array( $group->id, $exclude_groups ) ) {
					?>
						<option value="<?php echo $group->id; ?>" <?php echo 'selected = selected'; ?>><?php echo $group->name; ?></option>
						<?php } else { ?>
						<option value="<?php echo $group->id; ?>"><?php echo $group->name; ?></option>
						<?php
}
				} else {
?>
						<option value="<?php echo $group->id; ?>"><?php echo $group->name; ?></option>
						<?php
				}
				endforeach;
		}
			?>
			</select>
			<p class="bgr-admin-des"><?php _e( "This option lets you to choose those groups that you don't want to provide review functionality.", 'bp-group-reviews' ); ?></p>
		</div>
	</div>
	<div class="bgr-col-12">
		<div class="bgr-col-6">
			<input type="button" class="button button-primary" id="bgr-save-admin-general-settings" value="<?php _e( 'Save Settings', 'bp-group-reviews' ); ?>">
			<img src="<?php echo $spinner_src; ?>" class="bgr-admin-general-settings-spinner" />
		</div>
	</div>
	<?php
}
