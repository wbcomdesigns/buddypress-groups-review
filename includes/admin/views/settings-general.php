<?php
/**
 * General settings tab.
 *
 * Saved via the existing custom AJAX handler
 * BGR_AJAX::bp_group_review_save_admin_general_settings (option
 * bgr_admin_general_settings). The admin JS (bgr-admin.js) reads the
 * element IDs below by selector, so those IDs are preserved verbatim
 * from the legacy form — only the surrounding chrome changed.
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/admin
 * @since      3.9.0
 */

defined( 'ABSPATH' ) || exit;

global $bgr;

if ( ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'groups' ) ) {
	$bgr_base_url = function_exists( 'bp_get_admin_url' )
		? bp_get_admin_url( add_query_arg( array( 'page' => 'bp-components' ), 'admin.php' ) )
		: admin_url( 'admin.php?page=bp-components' );
	?>
	<div class="bgr-card">
		<div class="bgr-card__body">
			<p class="bgr-card__body-text">
				<?php
				printf(
					/* translators: %s: link to BuddyPress Components settings. */
					esc_html__( 'This plugin works with the BuddyPress Groups component. Please activate the BuddyPress Groups component %s.', 'bp-group-reviews' ),
					'<a href="' . esc_url( $bgr_base_url ) . '">' . esc_html__( 'here', 'bp-group-reviews' ) . '</a>'
				);
				?>
			</p>
		</div>
	</div>
	<?php
	return;
}

$bgr_spinner_src         = includes_url() . 'images/spinner.gif';
$bgr_auto_approve        = isset( $bgr['auto_approve_reviews'] ) ? $bgr['auto_approve_reviews'] : 'no';
$bgr_reviews_per_page    = isset( $bgr['reviews_per_page'] ) ? $bgr['reviews_per_page'] : 5;
$bgr_allow_notification  = isset( $bgr['allow_notification'] ) ? $bgr['allow_notification'] : 'yes';
$bgr_allow_activity      = isset( $bgr['allow_activity'] ) ? $bgr['allow_activity'] : 'yes';
$bgr_multi_reviews       = isset( $bgr['multi_reviews'] ) ? $bgr['multi_reviews'] : 'no';
$bgr_enable_grp_criteria = isset( $bgr['enable_group_criteria'] ) ? $bgr['enable_group_criteria'] : 'no';
$bgr_exclude_groups      = isset( $bgr['exclude_groups'] ) ? array_map( 'absint', (array) $bgr['exclude_groups'] ) : array();

/**
 * A site can have thousands of groups; a full <select> of every group is both a
 * heavy query and an unusable control. We cap the option list to a filterable
 * limit (default 200) ordered by most-recent. Already-excluded groups are always
 * appended via `include` below so a saved selection beyond the cap is never
 * silently dropped on the next save. Sites with very large group counts should
 * raise this cap deliberately, or filter the list down to the groups they manage.
 *
 * @since 3.9.0
 * @param int $bgr_groups_cap Maximum groups listed in the exclude selector. Default 200.
 */
$bgr_groups_cap = (int) apply_filters( 'bgr_exclude_groups_select_limit', 200 );
if ( $bgr_groups_cap < 1 ) {
	$bgr_groups_cap = 200;
}
$bgr_all_groups = groups_get_groups(
	array(
		'order'           => 'DESC',
		'orderby'         => 'date_created',
		'per_page'        => $bgr_groups_cap,
		'page'            => 1,
		'show_hidden'     => true,
		'populate_extras' => false,
	)
);

// Always include currently-excluded groups even if they fall outside the cap,
// so re-saving the form preserves the full existing selection.
$bgr_listed_group_ids = array();
if ( ! empty( $bgr_all_groups['groups'] ) ) {
	foreach ( $bgr_all_groups['groups'] as $bgr_group_item ) {
		$bgr_listed_group_ids[] = absint( $bgr_group_item->id );
	}
}
$bgr_missing_excluded = array_diff( $bgr_exclude_groups, $bgr_listed_group_ids );
if ( ! empty( $bgr_missing_excluded ) ) {
	$bgr_extra_groups = groups_get_groups(
		array(
			'include'         => array_map( 'absint', $bgr_missing_excluded ),
			'per_page'        => count( $bgr_missing_excluded ),
			'show_hidden'     => true,
			'populate_extras' => false,
		)
	);
	if ( ! empty( $bgr_extra_groups['groups'] ) ) {
		$bgr_all_groups['groups'] = array_merge( $bgr_all_groups['groups'], $bgr_extra_groups['groups'] );
	}
}
$bgr_groups_capped = ( ! empty( $bgr_all_groups['total'] ) && (int) $bgr_all_groups['total'] > $bgr_groups_cap );
?>
<div class="bgr-card">
	<div class="bgr-card__head">
		<p class="bgr-card__title"><?php esc_html_e( 'General Settings', 'bp-group-reviews' ); ?></p>
		<p class="bgr-card__desc"><?php esc_html_e( 'Control how members submit reviews and how those reviews are moderated.', 'bp-group-reviews' ); ?></p>
	</div>
	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Enable Multiple Reviews', 'bp-group-reviews' ); ?></th>
			<td>
				<label class="wb-switch" for="bgr-multi-reviews">
					<input type="checkbox" id="bgr-multi-reviews" <?php checked( $bgr_multi_reviews, 'yes' ); ?>>
					<div class="wb-slider wb-round"></div>
				</label>
				<p class="description"><?php esc_html_e( 'Allow members to submit more than one review for the same group.', 'bp-group-reviews' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Auto Approval of Reviews', 'bp-group-reviews' ); ?></th>
			<td>
				<label class="wb-switch" for="bgr-auto-approve-reviews">
					<input type="checkbox" id="bgr-auto-approve-reviews" <?php checked( $bgr_auto_approve, 'yes' ); ?>>
					<div class="wb-slider wb-round"></div>
				</label>
				<p class="description"><?php esc_html_e( 'Publish reviews automatically without requiring group-admin approval.', 'bp-group-reviews' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="reviews_per_page"><?php esc_html_e( 'Reviews Per Page', 'bp-group-reviews' ); ?></label></th>
			<td>
				<input id="reviews_per_page" class="small-text" name="reviews_per_page" step="1" min="1" value="<?php echo esc_attr( $bgr_reviews_per_page ); ?>" type="number">
				<span class="bgr-inline-hint"><?php esc_html_e( 'Reviews', 'bp-group-reviews' ); ?></span>
				<p class="description"><?php esc_html_e( 'Number of reviews to display per page.', 'bp-group-reviews' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'BuddyPress Notifications', 'bp-group-reviews' ); ?></th>
			<td>
				<?php if ( bp_is_active( 'notifications' ) ) : ?>
					<label class="wb-switch" for="bgr-notification">
						<input type="checkbox" id="bgr-notification" <?php checked( $bgr_allow_notification, 'yes' ); ?>>
						<div class="wb-slider wb-round"></div>
					</label>
					<p class="description"><?php esc_html_e( 'Notify group admins and reviewers about review activity.', 'bp-group-reviews' ); ?></p>
				<?php else : ?>
					<p class="description">
						<?php
						printf(
							/* translators: %s: BuddyPress Notifications component link. */
							esc_html__( 'This setting requires the %s to be active.', 'bp-group-reviews' ),
							'<strong><a href="' . esc_url( admin_url( 'admin.php?page=bp-components' ) ) . '">' . esc_html__( 'BuddyPress Notifications Component', 'bp-group-reviews' ) . '</a></strong>'
						);
						?>
					</p>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Review Activity', 'bp-group-reviews' ); ?></th>
			<td>
				<?php if ( bp_is_active( 'activity' ) ) : ?>
					<label class="wb-switch" for="bgr-activity">
						<input type="checkbox" id="bgr-activity" <?php checked( $bgr_allow_activity, 'yes' ); ?>>
						<div class="wb-slider wb-round"></div>
					</label>
					<p class="description"><?php esc_html_e( 'Post group reviews to the activity stream.', 'bp-group-reviews' ); ?></p>
				<?php else : ?>
					<p class="description">
						<?php
						printf(
							/* translators: %s: BuddyPress Activity component link. */
							esc_html__( 'This setting requires the %s to be active.', 'bp-group-reviews' ),
							'<strong><a href="' . esc_url( admin_url( 'admin.php?page=bp-components' ) ) . '">' . esc_html__( 'BuddyPress Activity Component', 'bp-group-reviews' ) . '</a></strong>'
						);
						?>
					</p>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Group-Level Criteria', 'bp-group-reviews' ); ?></th>
			<td>
				<label class="wb-switch" for="bgr-enable-group-criteria">
					<input type="checkbox" id="bgr-enable-group-criteria" <?php checked( $bgr_enable_grp_criteria, 'yes' ); ?>>
					<div class="wb-slider wb-round"></div>
				</label>
				<p class="description"><?php esc_html_e( 'Allow group admins to customize review criteria for their own groups.', 'bp-group-reviews' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="bgr-exclude-group-review"><?php esc_html_e( 'Exclude Groups', 'bp-group-reviews' ); ?></label></th>
			<td>
				<select id="bgr-exclude-group-review" name="bgr-exclude-group[]" multiple>
					<?php
					if ( ! empty( $bgr_all_groups['groups'] ) ) {
						foreach ( $bgr_all_groups['groups'] as $bgr_group_item ) {
							$bgr_group_id = absint( $bgr_group_item->id );
							printf(
								'<option value="%1$s"%2$s>%3$s</option>',
								esc_attr( $bgr_group_id ),
								selected( in_array( $bgr_group_id, $bgr_exclude_groups, true ), true, false ),
								esc_html( $bgr_group_item->name )
							);
						}
					}
					?>
				</select>
				<p class="description"><?php esc_html_e( 'Selected groups will not have review functionality.', 'bp-group-reviews' ); ?></p>
				<?php if ( $bgr_groups_capped ) : ?>
					<p class="description">
						<?php
						printf(
							/* translators: %s: number of groups shown. */
							esc_html__( 'Showing the %s most recent groups. Groups already excluded remain selected. Use the bgr_exclude_groups_select_limit filter to raise this cap.', 'bp-group-reviews' ),
							esc_html( number_format_i18n( $bgr_groups_cap ) )
						);
						?>
					</p>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	<div class="bgr-save-bar">
		<input type="button" class="button button-primary bgr-btn bgr-btn-primary bgr-submit-button" id="bgr-save-admin-general-settings" value="<?php esc_attr_e( 'Save Settings', 'bp-group-reviews' ); ?>">
		<img src="<?php echo esc_url( $bgr_spinner_src ); ?>" class="bgr-admin-general-settings-spinner" alt="" />
	</div>
</div>
