<?php
/**
 *  * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wbcomdesigns.com/
 * @since             1.0.0
 * @package           Buddypress_groups-review
 *
 * @wordpress-plugin
 * Plugin Name: Wbcom Designs - BuddyPress Group Reviews
 * Plugin URI: https://wbcomdesigns.com/contact/
 * Description: This plugin allows the BuddyPress Members to give reviews to the BuddyPress groups on the site. The review form allows the users to give text review, even rate the group on the basis of multiple criterias.
 * Version: 2.8.0
 * Author: Wbcom Designs
 * Author URI: http://wbcomdesigns.com
 * License: GPLv2+
 * Text Domain: bp-group-reviews
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 *  @since   1.0.0
 *  @author  Wbcom Designs
*/

add_action( 'init', 'bgr_load_textdomain' );

/**
 *  Adding setting links
 *
 *  @since    1.0.0
 *  @author   Wbcom Designs
 */
function bgr_load_textdomain() {
	$domain = 'bp-group-reviews';
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	load_textdomain( $domain, 'languages/' . $domain . '-' . $locale . '.mo' );
	$var = load_plugin_textdomain( $domain, false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

}

/**
 * Constants used in the plugin
 *
 *  @since   1.0.0
 *  @author  Wbcom Designs
*/
define( 'BGR_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BGR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Include needed files on init
 *
 *  @since   1.0.0
 *  @author  Wbcom Designs
*/
add_action( 'plugins_loaded', 'bgr_plugin_init' );

/**
 *  Adding setting links
 *
 *  @since    1.0.0
 *  @author   Wbcom Designs
 */
function bgr_plugin_init() {
	$bp_active_components = get_option( 'bp-active-components', true );
	if ( ! class_exists( 'BuddyPress' ) && ! array_key_exists( 'groups', $bp_active_components ) ) {
		add_action( 'admin_notices', 'bgr_admin_group_notice' );
	} else {
		run_bp_group_reviews_plugin();
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bgr_admin_page_link' );
		add_action( 'bp_init', 'include_files_bp' );
	}
}
add_action( 'bp_init', 'bgr_bp_notifications_for_review', 12 );

/**
 *  Adding notifications
 *
 *  @since    1.0.0
 *  @author   Wbcom Designs
 */
function bgr_bp_notifications_for_review() {
	include 'includes/bgr-notifications.php';
	buddypress()->bgr_bp_review                        = new BGR_Notifications();
	buddypress()->bgr_bp_review->notification_callback = 'bgr_format_notifications';
}

/**
 *  Check if buddypress activate.
 */
function bgr_requires_buddypress() {
	if ( ! class_exists( 'BuddyPress' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'admin_notices', 'bgr_admin_notice' );
		unset( $_GET['activate'] );
	}
}
add_action( 'admin_init', 'bgr_requires_buddypress' );

/**
 * Show admin notice when buddypress not active or install
 *
 *  @since   1.0.0
 *  @author  Wbcom Designs
 */
function bgr_admin_notice() {
	$bpquotes_plugin = esc_html__( 'BuddyPress Group Reviews', 'bp-group-reviews' );
	$bp_plugin       = esc_html__( 'BuddyPress', 'bp-group-reviews' );
	echo '<div class="error"><p>';
	/* translators: %s: search term */
	echo sprintf( esc_html__( '%1$s is ineffective now as it requires %2$s to be installed and active.', 'bp-group-reviews' ), '<strong>' . esc_html( $bpquotes_plugin ) . '</strong>', '<strong>' . esc_html( $bp_plugin ) . '</strong>' );
	echo '</p></div>';
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}

}

/**
 * Show admin notice when buddypress user groups component not active
 *
 *  @since   1.0.0
 *  @author  Wbcom Designs
 */
function bgr_admin_group_notice() {
	?>
	<div class="error notice is-dismissible">
		<p><?php esc_html_e( 'The Review BuddyPress Groups plugin requires BuddyPress User Groups Component to be active.', 'bp-group-reviews' ); ?></p>
	</div>
	<?php
}

/**
 *  Adding setting links
 *
 *  @since    1.0.0
 *  @param    string $links for this plugin.
 *  @author   Wbcom Designs
 */
function bgr_admin_page_link( $links ) {
		$page_link = array( '<a href="' . admin_url( 'admin.php?page=group-review-settings' ) . '">' . esc_html__( 'Settings', 'bp-group-reviews' ) . '</a>' );
		return array_merge( $links, $page_link );
}

/**
 * Settings link for this plugin
 *
 *  @since   1.0.0
 *  @author  Wbcom Designs
 */
function include_files_bp() {
	include 'includes/bgr-grp-extn.php';
}

/**
 * Run the plugin, include the required files
 */
function run_bp_group_reviews_plugin() {
	$include_files = array(
		'includes/bgr-globals.php',
		'includes/bgr-scripts.php',
		'admin/wbcom/wbcom-admin-settings.php',
		'admin/bgr-admin.php',
		'admin/bgr-admin-feedback.php',
		'includes/bgr-dynamic-css.php',
		'includes/bgr-rating-display.php',
		'includes/bgr-filters.php',
		'includes/bgr-ajax.php',
		'includes/bgr-shortcodes.php',
		'includes/widgets/bgr-review.php',
		'includes/widgets/group-rating.php',
	);
	foreach ( $include_files as $include_file ) {
		if ( class_exists( 'BuddyPress' ) ) {
			include $include_file;
		}
	}
}


/**
 * redirect to plugin settings page after activated
 */

add_action( 'activated_plugin', 'bp_group_reviews_activation_redirect_settings' );
function bp_group_reviews_activation_redirect_settings( $plugin ) {
	if ( class_exists( 'BuddyPress' ) ) {
		if ( $plugin == plugin_basename( __FILE__ ) ) {
			wp_redirect( admin_url( 'admin.php?page=group-review-settings' ) );
			exit;
		}
	}
}
