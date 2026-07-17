<?php
/**
 * Criteria settings tab.
 *
 * Saved via the existing custom AJAX handler
 * BGR_AJAX::bp_group_review_save_admin_criteria_settings (option
 * bgr_admin_criteria_settings). The admin JS (bgr-admin.js) reads the
 * #bgr-textbox-container, BGRDynamicTextBox inputs, .bgr-criteria-state
 * toggles, #bgr-field-add and #bgr-save-admin-criteria-settings
 * selectors, so those are preserved verbatim. jQuery-UI sortable
 * also attaches to #bgr-textbox-container.
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/admin
 * @since      3.9.0
 */

defined( 'ABSPATH' ) || exit;

global $bgr;

$bgr_spinner_src   = includes_url() . 'images/spinner.gif';
$bgr_review_rating = isset( $bgr['review_rating_fields'] ) ? (array) $bgr['review_rating_fields'] : array();
$bgr_active_rating = isset( $bgr['active_rating_fields'] ) ? (array) $bgr['active_rating_fields'] : array();
?>
<div class="bgr-card">
	<div class="bgr-card__head">
		<p class="bgr-card__title"><?php esc_html_e( 'Review Criteria', 'bp-group-reviews' ); ?></p>
		<p class="bgr-card__desc"><?php esc_html_e( 'Add multiple rating criteria. No criterion is displayed to members until it is enabled. Drag to reorder.', 'bp-group-reviews' ); ?></p>
	</div>
	<div class="bgr-card__body">
		<div id="bgr-textbox-container" class="bgr-criteria-list">
			<?php
			/*
			 * These inputs are an IDENTIFIER editor, not display copy. bgr-admin.js
			 * reads their values straight back and posts them as the criterion keys
			 * that get stored in the `review_star_rating` post meta. Do NOT run these
			 * through bp_group_review_get_criteria_label(): a non-English admin who
			 * opened this tab and hit Save would rewrite "Quality" to its translation
			 * and orphan every rating ever stored under the old key.
			 */
			if ( ! empty( $bgr_review_rating ) ) {
				foreach ( $bgr_review_rating as $bgr_rating_field ) :
					?>
					<div class="rating-review-div">
						<span class="move-icons" aria-hidden="true">&equiv;</span>
						<input name="BGRDynamicTextBox" class="draggable" type="text" value="<?php echo esc_attr( $bgr_rating_field ); ?>" />
						<input type="button" value="<?php esc_attr_e( 'Remove', 'bp-group-reviews' ); ?>" class="remove button button-secondary" />
						<label class="wb-switch">
							<input type="checkbox" class="bgr-criteria-state" name="bgr-criteria-state" data-attr="<?php echo esc_attr( $bgr_rating_field ); ?>" <?php checked( in_array( $bgr_rating_field, $bgr_active_rating, true ) ); ?>>
							<div class="wb-slider wb-round"></div>
						</label>
					</div>
					<?php
				endforeach;
			}
			?>
		</div>
		<p>
			<input id="bgr-field-add" type="button" value="<?php esc_attr_e( 'Add Review Criteria', 'bp-group-reviews' ); ?>" class="button button-secondary" />
		</p>
	</div>
	<div class="bgr-save-bar">
		<input type="button" class="button button-primary bgr-btn bgr-btn-primary bgr-submit-button" id="bgr-save-admin-criteria-settings" value="<?php esc_attr_e( 'Save Settings', 'bp-group-reviews' ); ?>">
		<img src="<?php echo esc_url( $bgr_spinner_src ); ?>" class="bgr-admin-criteria-settings-spinner" alt="" />
	</div>
</div>
