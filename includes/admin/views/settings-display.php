<?php
/**
 * Display settings tab.
 *
 * Saved via the existing custom AJAX handler
 * BGR_AJAX::bp_group_review_save_admin_display_settings (option
 * bgr_admin_display_settings). The admin JS (bgr-admin.js) reads
 * #bgrReviewLabel, #bgrManageReviewLabel, #bgr-rating-color and
 * #bgr-save-admin-display-settings; the color picker attaches to
 * .bgr-review-color. All preserved verbatim.
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/admin
 * @since      3.9.0
 */

defined( 'ABSPATH' ) || exit;

global $bgr;

$bgr_spinner_src = includes_url() . 'images/spinner.gif';

/*
 * These two inputs must show the RAW stored value, not the resolved label. An
 * empty box means "not renamed - follow the site language", and the translated
 * default is shown as the placeholder instead. Prefilling with the resolved
 * label would mean any admin who opened this tab and hit Save silently froze
 * their own locale's wording into the database - the exact trap this release
 * removes.
 */
$bgr_display_raw    = get_option( 'bgr_admin_display_settings' );
$bgr_review_label   = isset( $bgr_display_raw['review_label'] ) ? (string) $bgr_display_raw['review_label'] : '';
$bgr_manage_label   = isset( $bgr_display_raw['manage_review_label'] ) ? (string) $bgr_display_raw['manage_review_label'] : '';
$bgr_review_default = esc_html__( 'Review', 'bp-group-reviews' );
$bgr_manage_default = esc_html__( 'Reviews', 'bp-group-reviews' );
$bgr_rating_color   = isset( $bgr['rating_color'] ) ? $bgr['rating_color'] : '#FFC400';
?>
<div class="bgr-card">
	<div class="bgr-card__head">
		<p class="bgr-card__title"><?php esc_html_e( 'Labels', 'bp-group-reviews' ); ?></p>
		<p class="bgr-card__desc"><?php esc_html_e( 'Rename the review tabs members see on group pages.', 'bp-group-reviews' ); ?></p>
	</div>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="bgrReviewLabel"><?php esc_html_e( 'Review', 'bp-group-reviews' ); ?></label></th>
			<td>
				<input name="bgrReviewLabel" id="bgrReviewLabel" type="text" class="regular-text" value="<?php echo esc_attr( $bgr_review_label ); ?>" placeholder="<?php echo esc_attr( $bgr_review_default ); ?>" />
				<p class="description">
					<?php
					printf(
						/* translators: %s: the default singular label in the current language, e.g. "Review". */
						esc_html__( 'Change the singular review label. Leave empty to use the default (%s), which follows the site language.', 'bp-group-reviews' ),
						esc_html( $bgr_review_default )
					);
					?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="bgrManageReviewLabel"><?php esc_html_e( 'Reviews (Plural)', 'bp-group-reviews' ); ?></label></th>
			<td>
				<input name="bgrManageReviewLabel" id="bgrManageReviewLabel" type="text" class="regular-text" value="<?php echo esc_attr( $bgr_manage_label ); ?>" placeholder="<?php echo esc_attr( $bgr_manage_default ); ?>" />
				<p class="description">
					<?php
					printf(
						/* translators: %s: the default plural label in the current language, e.g. "Reviews". */
						esc_html__( 'Change the plural review label. Leave empty to use the default (%s), which follows the site language.', 'bp-group-reviews' ),
						esc_html( $bgr_manage_default )
					);
					?>
				</p>
			</td>
		</tr>
	</table>
</div>

<div class="bgr-card">
	<div class="bgr-card__head">
		<p class="bgr-card__title"><?php esc_html_e( 'Colors', 'bp-group-reviews' ); ?></p>
	</div>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="bgr-rating-color"><?php esc_html_e( 'Rating Color', 'bp-group-reviews' ); ?></label></th>
			<td>
				<input id="bgr-rating-color" class="bgr-review-color" type="text" data-default-color="#FFC400" value="<?php echo esc_attr( $bgr_rating_color ); ?>" />
				<p class="description"><?php esc_html_e( 'Change the color of the star rating.', 'bp-group-reviews' ); ?></p>
			</td>
		</tr>
	</table>
	<div class="bgr-save-bar">
		<input type="button" class="button button-primary bgr-btn bgr-btn-primary bgr-submit-button" id="bgr-save-admin-display-settings" value="<?php esc_attr_e( 'Save Settings', 'bp-group-reviews' ); ?>">
		<img src="<?php echo esc_url( $bgr_spinner_src ); ?>" class="bgr-admin-display-settings-spinner" alt="" />
	</div>
</div>
