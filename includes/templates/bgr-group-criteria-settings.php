<?php
/**
 * Group Criteria Settings Template
 *
 * Template for the group admin panel to manage review criteria settings.
 *
 * @since   3.7.0
 * @author  Wbcom Designs
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes/templates
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$group_id          = bp_get_current_group_id();
$criteria          = bgr_group_criteria();
$settings          = $criteria->get_group_settings( $group_id );
$bgr_criteria_mode = $settings['mode'];
$global_all        = $criteria->get_global_all_criteria();
$global_active     = $criteria->get_global_active_criteria();
$enabled_global    = $settings['enabled_global_criteria'];
$custom_criteria   = $settings['custom_criteria'];
$nonce             = wp_create_nonce( 'bgr_group_criteria_nonce' );
?>

<div id="bgr-group-criteria-settings" class="bgr-criteria-settings-wrap">
	<input type="hidden" id="bgr-group-id" value="<?php echo esc_attr( $group_id ); ?>">
	<input type="hidden" id="bgr-criteria-nonce" value="<?php echo esc_attr( $nonce ); ?>">

	<div class="bgr-criteria-mode-section">
		<h4><?php esc_html_e( 'Criteria Mode', 'bp-group-reviews' ); ?></h4>
		<p class="description"><?php esc_html_e( 'Choose whether to use site-wide criteria or customize criteria for this group.', 'bp-group-reviews' ); ?></p>

		<div class="bgr-mode-options">
			<label class="bgr-mode-option">
				<input type="radio" name="bgr_criteria_mode" value="inherit" <?php checked( $bgr_criteria_mode, 'inherit' ); ?>>
				<span class="bgr-mode-label">
					<strong><?php esc_html_e( 'Use site-wide criteria', 'bp-group-reviews' ); ?></strong>
					<span class="bgr-mode-desc"><?php esc_html_e( 'Reviews will use the default criteria set by the site administrator.', 'bp-group-reviews' ); ?></span>
				</span>
			</label>

			<label class="bgr-mode-option">
				<input type="radio" name="bgr_criteria_mode" value="override" <?php checked( $bgr_criteria_mode, 'override' ); ?>>
				<span class="bgr-mode-label">
					<strong><?php esc_html_e( 'Customize criteria for this group', 'bp-group-reviews' ); ?></strong>
					<span class="bgr-mode-desc"><?php esc_html_e( 'Choose which criteria to enable and add custom criteria for this group.', 'bp-group-reviews' ); ?></span>
				</span>
			</label>
		</div>
	</div>

	<div id="bgr-override-settings" class="bgr-override-section" style="<?php echo 'override' === $bgr_criteria_mode ? '' : 'display:none;'; ?>">
		<hr>

		<div class="bgr-global-criteria-section">
			<h4><?php esc_html_e( 'Site-Wide Criteria', 'bp-group-reviews' ); ?></h4>
			<p class="description"><?php esc_html_e( 'Select which site-wide criteria to enable for this group.', 'bp-group-reviews' ); ?></p>

			<div class="bgr-criteria-list" id="bgr-global-criteria-list">
				<?php if ( ! empty( $global_all ) ) : ?>
					<?php foreach ( $global_all as $criterion_name ) : ?>
						<?php
						$is_enabled       = empty( $enabled_global ) ? in_array( $criterion_name, $global_active, true ) : in_array( $criterion_name, $enabled_global, true );
						$is_active_global = in_array( $criterion_name, $global_active, true );
						?>
						<label class="bgr-criteria-item <?php echo $is_active_global ? '' : 'bgr-inactive-global'; ?>">
							<input type="checkbox" name="bgr_enabled_global[]" value="<?php echo esc_attr( $criterion_name ); ?>" <?php checked( $is_enabled ); ?>>
							<span class="bgr-criteria-name"><?php echo esc_html( $criterion_name ); ?></span>
							<?php if ( ! $is_active_global ) : ?>
								<span class="bgr-criteria-badge bgr-badge-inactive"><?php esc_html_e( 'Inactive globally', 'bp-group-reviews' ); ?></span>
							<?php endif; ?>
						</label>
					<?php endforeach; ?>
				<?php else : ?>
					<p class="bgr-no-criteria"><?php esc_html_e( 'No site-wide criteria defined.', 'bp-group-reviews' ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<hr>

		<div class="bgr-custom-criteria-section">
			<h4><?php esc_html_e( 'Custom Criteria', 'bp-group-reviews' ); ?></h4>
			<p class="description"><?php esc_html_e( 'Add custom criteria specific to this group.', 'bp-group-reviews' ); ?></p>

			<div class="bgr-criteria-list" id="bgr-custom-criteria-list">
				<?php if ( ! empty( $custom_criteria ) ) : ?>
					<?php foreach ( $custom_criteria as $custom ) : ?>
						<?php if ( ! empty( $custom['active'] ) ) : ?>
							<div class="bgr-custom-criteria-item" data-name="<?php echo esc_attr( $custom['name'] ); ?>">
								<span class="bgr-criteria-name"><?php echo esc_html( $custom['name'] ); ?></span>
								<button type="button" class="bgr-archive-custom-criteria button-link" data-name="<?php echo esc_attr( $custom['name'] ); ?>">
									<?php esc_html_e( 'Archive', 'bp-group-reviews' ); ?>
								</button>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

			<div class="bgr-add-custom-criteria">
				<input type="text" id="bgr-new-criteria-name" placeholder="<?php esc_attr_e( 'Enter criteria name...', 'bp-group-reviews' ); ?>" maxlength="50">
				<button type="button" id="bgr-add-custom-criteria" class="button">
					<?php esc_html_e( 'Add Criteria', 'bp-group-reviews' ); ?>
				</button>
			</div>

			<?php
			// Show archived criteria if any exist.
			$archived = array_filter(
				$custom_criteria,
				function ( $c ) {
					return empty( $c['active'] );
				}
			);
			?>
			<?php if ( ! empty( $archived ) || ! empty( $settings['archived_criteria'] ) ) : ?>
				<div class="bgr-archived-criteria">
					<h5><?php esc_html_e( 'Archived Criteria', 'bp-group-reviews' ); ?></h5>
					<p class="description"><?php esc_html_e( 'These criteria are no longer active but may have historical reviews.', 'bp-group-reviews' ); ?></p>
					<ul>
						<?php foreach ( $archived as $arch ) : ?>
							<li>
								<span class="bgr-criteria-name"><?php echo esc_html( $arch['name'] ); ?></span>
								<span class="bgr-criteria-badge bgr-badge-archived"><?php esc_html_e( 'Archived', 'bp-group-reviews' ); ?></span>
							</li>
						<?php endforeach; ?>
						<?php foreach ( $settings['archived_criteria'] as $name => $info ) : ?>
							<?php if ( ! in_array( $name, array_column( $archived, 'name' ), true ) ) : ?>
								<li>
									<span class="bgr-criteria-name"><?php echo esc_html( $name ); ?></span>
									<span class="bgr-criteria-badge bgr-badge-archived">
										<?php
										if ( 'global_criteria_deleted' === $info['reason'] ) {
											esc_html_e( 'Global criteria removed', 'bp-group-reviews' );
										} else {
											esc_html_e( 'Archived', 'bp-group-reviews' );
										}
										?>
									</span>
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="bgr-criteria-actions">
		<button type="button" id="bgr-save-criteria-settings" class="button button-primary">
			<?php esc_html_e( 'Save Criteria Settings', 'bp-group-reviews' ); ?>
		</button>
		<span id="bgr-save-status" class="bgr-save-status"></span>
	</div>

	<div id="bgr-criteria-preview" class="bgr-preview-section">
		<h4><?php esc_html_e( 'Current Active Criteria', 'bp-group-reviews' ); ?></h4>
		<p class="description"><?php esc_html_e( 'These criteria will be shown on the review form for this group.', 'bp-group-reviews' ); ?></p>
		<ul id="bgr-active-criteria-preview">
			<?php
			$effective = $criteria->get_effective_criteria( $group_id );
			foreach ( $effective as $name ) :
				?>
				<li><?php echo esc_html( $name ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>

<style>
.bgr-criteria-settings-wrap {
	max-width: 800px;
}
.bgr-mode-options {
	margin: 15px 0;
}
.bgr-mode-option {
	display: block;
	padding: 15px;
	margin-bottom: 10px;
	border: 1px solid #ddd;
	border-radius: 4px;
	cursor: pointer;
	transition: border-color 0.2s;
}
.bgr-mode-option:hover {
	border-color: #2271b1;
}
.bgr-mode-option input[type="radio"] {
	margin-right: 10px;
}
.bgr-mode-label {
	display: inline-block;
}
.bgr-mode-desc {
	display: block;
	margin-top: 5px;
	color: #666;
	font-size: 13px;
	margin-left: 24px;
}
.bgr-criteria-list {
	margin: 15px 0;
}
.bgr-criteria-item {
	display: block;
	padding: 10px;
	margin-bottom: 5px;
	background: #f9f9f9;
	border-radius: 4px;
}
.bgr-criteria-item.bgr-inactive-global {
	opacity: 0.7;
}
.bgr-criteria-name {
	font-weight: 500;
}
.bgr-criteria-badge {
	display: inline-block;
	padding: 2px 8px;
	font-size: 11px;
	border-radius: 3px;
	margin-left: 10px;
}
.bgr-badge-inactive {
	background: #ffeeba;
	color: #856404;
}
.bgr-badge-archived {
	background: #d6d8db;
	color: #383d41;
}
.bgr-custom-criteria-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 10px;
	margin-bottom: 5px;
	background: #f0f7ff;
	border-radius: 4px;
}
.bgr-add-custom-criteria {
	display: flex;
	gap: 10px;
	margin-top: 15px;
}
.bgr-add-custom-criteria input[type="text"] {
	flex: 1;
	max-width: 300px;
}
.bgr-archived-criteria {
	margin-top: 20px;
	padding: 15px;
	background: #f5f5f5;
	border-radius: 4px;
}
.bgr-archived-criteria h5 {
	margin-top: 0;
}
.bgr-archived-criteria ul {
	margin: 10px 0 0;
	padding-left: 20px;
}
.bgr-criteria-actions {
	margin-top: 20px;
	padding-top: 20px;
	border-top: 1px solid #ddd;
}
.bgr-save-status {
	margin-left: 15px;
	font-style: italic;
}
.bgr-save-status.success {
	color: #46b450;
}
.bgr-save-status.error {
	color: #dc3232;
}
.bgr-preview-section {
	margin-top: 30px;
	padding: 15px;
	background: #e7f3ff;
	border-radius: 4px;
}
.bgr-preview-section h4 {
	margin-top: 0;
}
#bgr-active-criteria-preview {
	margin: 10px 0 0;
	padding-left: 20px;
}
#bgr-active-criteria-preview li {
	padding: 5px 0;
}
</style>
