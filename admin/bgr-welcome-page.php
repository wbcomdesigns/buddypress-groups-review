<?php
/**
 * This file is used for rendering and saving plugin welcome settings.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
	// Exit if accessed directly.
}
?>
<div class="wbcom-welcome-main-wrapper">
	<div class="wbcom-welcome-head">
		<p class="wbcom-welcome-description"><?php esc_html_e( 'BuddyPress Group Reviews enables members to add reviews for groups and provide multiple ratings based on predefined criteria. All submitted reviews appear in the group’s "Manage Review" section, where admins can approve or deny them. Approved reviews are published and displayed in the "Reviews" tab on the group page. Admins have the flexibility to add as many rating criteria as needed.', 'bp-group-reviews' ); ?></p>
	</div><!-- .wbcom-welcome-head -->

	<div class="wbcom-welcome-content">
		<div class="wbcom-welcome-support-info">
			<h3><?php esc_html_e( 'Help & Support Resources', 'bp-group-reviews' ); ?></h3>
			<p><?php esc_html_e( 'If you need assistance, here are some helpful resources. Our documentation is a great place to start, and our support team is available if you require further help.', 'bp-group-reviews' ); ?></p>

			<div class="wbcom-support-info-wrap">
				<div class="wbcom-support-info-widgets">
					<div class="wbcom-support-inner">
						<h3><span class="dashicons dashicons-book"></span><?php esc_html_e( 'Documentation', 'bp-group-reviews' ); ?></h3>
						<p><?php esc_html_e( 'Explore our detailed guide on BuddyPress Group Reviews to understand all the features and how to make the most of them.', 'bp-group-reviews' ); ?></p>
						<a href="<?php echo esc_url( 'https://docs.wbcomdesigns.com/doc_category/buddypress-group-review/' ); ?>" class="button button-primary button-welcome-support" target="_blank"><?php esc_html_e( 'Read Documentation', 'bp-group-reviews' ); ?></a>
					</div>
				</div>

				<div class="wbcom-support-info-widgets">
					<div class="wbcom-support-inner">
						<h3><span class="dashicons dashicons-sos"></span><?php esc_html_e( 'Support Center', 'bp-group-reviews' ); ?></h3>
						<p><?php esc_html_e( 'Our support team is here to assist you with any questions or issues. Feel free to contact us anytime through our support center.', 'bp-group-reviews' ); ?></p>
						<a href="<?php echo esc_url( 'https://wbcomdesigns.com/support/' ); ?>" class="button button-primary button-welcome-support" target="_blank"><?php esc_html_e( 'Get Support', 'bp-group-reviews' ); ?></a>
					</div>
				</div>

				<div class="wbcom-support-info-widgets">
					<div class="wbcom-support-inner">
						<h3><span class="dashicons dashicons-admin-comments"></span><?php esc_html_e( 'Share Your Feedback', 'bp-group-reviews' ); ?></h3>
						<p><?php esc_html_e( 'We’d love to hear about your experience with the plugin. Your feedback and suggestions help us improve future updates.', 'bp-group-reviews' ); ?></p>
						<a href="<?php echo esc_url( 'https://wbcomdesigns.com/submit-review/' ); ?>" class="button button-primary button-welcome-support" target="_blank"><?php esc_html_e( 'Send Feedback', 'bp-group-reviews' ); ?></a>
					</div>
				</div>

			</div>
		</div>
	</div><!-- .wbcom-welcome-content -->
</div><!-- .wbcom-welcome-main-wrapper -->
