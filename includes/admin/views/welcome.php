<?php
/**
 * Welcome tab — overview + help/support resources.
 *
 * Ported from the legacy admin/bgr-welcome-page.php into the
 * card-panel vocabulary.
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/admin
 * @since      3.9.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="bgr-card">
	<div class="bgr-card__head">
		<p class="bgr-card__title"><?php esc_html_e( 'About BuddyPress Group Reviews', 'bp-group-reviews' ); ?></p>
	</div>
	<div class="bgr-card__body">
		<p class="bgr-card__body-text">
			<?php esc_html_e( 'BuddyPress Group Reviews lets members add reviews for groups and rate them across multiple criteria. Submitted reviews appear in the group\'s "Manage Reviews" section, where admins approve or deny them. Approved reviews show in the group\'s "Reviews" tab. Add as many rating criteria as you need.', 'bp-group-reviews' ); ?>
		</p>
	</div>
</div>

<div class="bgr-card">
	<div class="bgr-card__head">
		<p class="bgr-card__title"><?php esc_html_e( 'Help & Support Resources', 'bp-group-reviews' ); ?></p>
		<p class="bgr-card__desc"><?php esc_html_e( 'If you need assistance, here are some helpful resources. Our documentation is a great place to start, and our support team is available if you require further help.', 'bp-group-reviews' ); ?></p>
	</div>
	<div class="bgr-card__body">
		<div class="bgr-resource-grid">
			<div class="bgr-resource">
				<h3 class="bgr-resource__title"><span class="dashicons dashicons-book" aria-hidden="true"></span><?php esc_html_e( 'Documentation', 'bp-group-reviews' ); ?></h3>
				<p class="bgr-resource__desc"><?php esc_html_e( 'Explore our detailed guide to understand all the features and how to make the most of them.', 'bp-group-reviews' ); ?></p>
				<a href="https://docs.wbcomdesigns.com/bp-family/buddypress-group-reviews/" class="bgr-btn bgr-btn-secondary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Read Documentation', 'bp-group-reviews' ); ?></a>
			</div>
			<div class="bgr-resource">
				<h3 class="bgr-resource__title"><span class="dashicons dashicons-sos" aria-hidden="true"></span><?php esc_html_e( 'Support Center', 'bp-group-reviews' ); ?></h3>
				<p class="bgr-resource__desc"><?php esc_html_e( 'Our support team is here to assist you with any questions or issues. Contact us anytime through our support center.', 'bp-group-reviews' ); ?></p>
				<a href="https://wbcomdesigns.com/support/" class="bgr-btn bgr-btn-secondary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get Support', 'bp-group-reviews' ); ?></a>
			</div>
			<div class="bgr-resource">
				<h3 class="bgr-resource__title"><span class="dashicons dashicons-admin-comments" aria-hidden="true"></span><?php esc_html_e( 'Share Your Feedback', 'bp-group-reviews' ); ?></h3>
				<p class="bgr-resource__desc"><?php esc_html_e( 'We\'d love to hear about your experience with the plugin. Your feedback helps us improve future updates.', 'bp-group-reviews' ); ?></p>
				<a href="https://wbcomdesigns.com/submit-review/" class="bgr-btn bgr-btn-secondary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Send Feedback', 'bp-group-reviews' ); ?></a>
			</div>
		</div>
	</div>
</div>
