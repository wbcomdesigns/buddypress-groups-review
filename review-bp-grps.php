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
 * Description: This plugin allows BuddyPress Members to give reviews to BuddyPress groups on the site. The review form allows users to give text reviews and even rate the group based on multiple criteria.
 * Version: 3.9.1
 * Requires at least: 6.5
 * Requires PHP: 8.0
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
 *
 * @author Wbcom Designs
 */

add_action( 'init', 'bp_group_review_load_textdomain' );

/**
 *  Adding setting links
 *
 *  @since    1.0.0
 *  @author   Wbcom Designs
 */
function bp_group_review_load_textdomain() {
	$domain = 'bp-group-reviews';
	load_plugin_textdomain( $domain, false, plugin_basename( __DIR__ ) . '/languages' );
}

/**
 * Constants used in the plugin
 *
 *  @since   1.0.0
 *  @author  Wbcom Designs
*/
define( 'BGR_PLUGIN_VERSION', '3.9.1' );
define( 'BGR_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BGR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'BP_ROOT_BLOG' ) ) {
	define( 'BP_ROOT_BLOG', 1 );
}

/**
 * Include needed files on init
 *
 *  @since   1.0.0
 *  @author  Wbcom Designs
*/
add_action( 'bp_loaded', 'bp_group_review_plugin_execute' );

/**
 *  Adding setting links
 *
 *  @since    1.0.0
 *  @author   Wbcom Designs
 */
function bp_group_review_plugin_execute() {
	// bp_is_active() is the authority on component state - the raw option can be
	// stale or filtered. Guard with || (either condition is disqualifying); the
	// old && meant a BP-active/Groups-off site fell through to the else branch
	// and loaded Groups-bound code anyway.
	if ( ! class_exists( 'BuddyPress' ) || ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'groups' ) ) {
		add_action( 'admin_notices', 'bp_group_review_admin_group_notice' );
	} else {
		run_bp_group_reviews_plugin();
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bp_group_review_admin_page_link' );
		add_action( 'bp_init', 'bp_group_review_include_files_bp' );
	}
}
add_action( 'bp_init', 'bp_group_review_notifications', 12 );

/**
 *  Adding notifications
 *
 *  @since    1.0.0
 *  @author   Wbcom Designs
 */
function bp_group_review_notifications() {
	// The whole notification component is Groups-bound: every one of its
	// callbacks reads Groups APIs that only exist while the Groups component is
	// active. Registering it with Groups off is what used to fatal the front end
	// on every page load. Do not lean on the admin_init self-deactivate here -
	// admin_init never runs for a front-end request.
	if ( ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'groups' ) ) {
		return;
	}
	include_once 'includes/bgr-notifications.php';
	buddypress()->bgr_bp_review                        = new BGR_Notifications();
	buddypress()->bgr_bp_review->notification_callback = 'bp_group_review_format_notifications';
}

/**
 *  Check if buddypress activate.
 */
function bp_group_review_requires_buddypress() {
	if ( ! class_exists( 'BuddyPress' ) || ! bp_is_active( 'groups' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			add_action( 'admin_notices', 'bp_group_review_admin_notice' );
	}
}
add_action( 'admin_init', 'bp_group_review_requires_buddypress' );

/**
 * Show admin notice when buddypress not active or install
 *
 *  @since   1.0.0
 *  @author  Wbcom Designs
 */
function bp_group_review_admin_notice() {
	if ( is_multisite() ) {
		return;
	}
	// Product and component names are brands, not copy - kept as literals so no
	// locale can rewrite them (matching the $bp_plugin literal already below).
	$bpquotes_plugin = 'BuddyPress Group Reviews';
	$bp_plugin       = 'BuddyPress';
	echo '<div class="error"><p>';
	if ( class_exists( 'BuddyPress' ) ) {
		if ( ! bp_is_active( 'groups' ) ) {
			$bp_gp_component = 'BuddyPress Groups Component';
			/* translators: %1$s: this plugin's name, %2$s: BuddyPress, %3$s: BuddyPress Groups Component. All are wrapped in <strong> tags. */
			printf( esc_html__( '%1$s requires %2$s to be installed and active, and the %3$s to be enabled.', 'bp-group-reviews' ), '<strong>' . esc_html( $bpquotes_plugin ) . '</strong>', '<strong>' . esc_html( $bp_plugin ) . '</strong>', '<strong>' . esc_html( $bp_gp_component ) . '</strong>' );
		}
	} else {
		/* translators: %1$s: this plugin's name, %2$s: BuddyPress. Both are wrapped in <strong> tags. */
			printf( esc_html__( '%1$s requires %2$s to be installed and active.', 'bp-group-reviews' ), '<strong>' . esc_html( $bpquotes_plugin ) . '</strong>', '<strong>' . esc_html( $bp_plugin ) . '</strong>' );
	}
	echo '</p></div>';
}

/**
 * Show admin notice when buddypress user groups component not active
 *
 *  @since   1.0.0
 *  @author  Wbcom Designs
 */
function bp_group_review_admin_group_notice() {
	?>
	<div class="error notice is-dismissible">
		<p><?php esc_html_e( 'BuddyPress Group Reviews requires the BuddyPress Groups Component to be active.', 'bp-group-reviews' ); ?></p>
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
function bp_group_review_admin_page_link( $links ) {
		$page_link = array( '<a href="' . admin_url( 'admin.php?page=group-review-settings' ) . '">' . esc_html__( 'Settings', 'bp-group-reviews' ) . '</a>' );
		return array_merge( $links, $page_link );
}

/**
 * Settings link for this plugin
 *
 *  @since   1.0.0
 *  @author  Wbcom Designs
 */
function bp_group_review_include_files_bp() {
	include 'includes/bgr-grp-extn.php';
}

/**
 * Run the plugin, include the required files
 */
function run_bp_group_reviews_plugin() {
	$include_files = array(
		'includes/bgr-globals.php',
		'includes/class-bgr-theme-integration.php',
		'includes/bgr-scripts.php',
		'admin/bgr-admin.php',
		'admin/bgr-admin-feedback.php',
		'includes/bgr-dynamic-css.php',
		'includes/bgr-rating-display.php',
		'includes/bgr-filters.php',
		'includes/bgr-ajax.php',
		'includes/bgr-bp-rest-integration.php',
		'includes/bgr-schema.php',
		'includes/bgr-shortcodes.php',
		'includes/bgr-activity.php',
		'includes/widgets/bgr-review.php',
		'includes/widgets/group-rating.php',
		'includes/bgr-functions.php',
		'includes/class-bgr-group-criteria.php',
		'includes/bgr-group-criteria-functions.php',
		'includes/bgr-group-criteria-ajax.php',
	);
	foreach ( $include_files as $include_file ) {
		if ( class_exists( 'BuddyPress' ) && bp_is_active( 'groups' ) ) {
			include $include_file;
		}
	}

	// Initialize the group criteria singleton to register its hooks.
	if ( class_exists( 'BGR_Group_Criteria' ) ) {
		BGR_Group_Criteria::get_instance();
	}

	// Initialize the theme-integration helper (adds bgr-theme-{slug} body class,
	// centralizes theme token mapping). See UI-013.
	if ( class_exists( 'BGR_Theme_Integration' ) ) {
		BGR_Theme_Integration::get_instance();
	}
}

/**
 * Add multisite support.
 *
 * @return void
 */
function bp_group_review_add_multi_support() {
	include 'includes/class-bgr-multi-support.php';
}
add_action( 'init', 'bp_group_review_add_multi_support' );


/**
 * Redirect to plugin settings page after activated.
 *
 * @since  1.0.0
 *
 * @param string $plugin Path to the plugin file relative to the plugins directory.
 */
function bp_group_review_activation_redirect_settings( $plugin ) {

	if ( plugin_basename( __FILE__ ) === $plugin && class_exists( 'BuddyPress' ) && bp_is_active( 'groups' ) ) {
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action']  == 'activate' && isset( $_REQUEST['plugin'] ) && $_REQUEST['plugin'] == $plugin) { //phpcs:ignore
			wp_safe_redirect( admin_url( 'admin.php?page=group-review-settings' ) );
			exit;
		}
	}
}
add_action( 'activated_plugin', 'bp_group_review_activation_redirect_settings' );


/**
 * Sanitize email settings array.
 *
 * @param array $input The input array to sanitize.
 * @return array The sanitized array.
 */
function bp_group_review_sanitize_email_settings( $input ) {
	$sanitized = array();

	if ( ! is_array( $input ) ) {
		return $sanitized;
	}

	foreach ( $input as $key => $value ) {
		if ( is_array( $value ) ) {
			$sanitized[ sanitize_key( $key ) ] = array_map( 'sanitize_text_field', $value );
		} else {
			$sanitized[ sanitize_key( $key ) ] = sanitize_text_field( $value );
		}
	}

	return $sanitized;
}

/*
 * The Emails tab's register_setting() lives in BGR_Admin_Panel::register_settings()
 * (admin_init) as of the 3.9.0 card-panel migration. The duplicate registration that
 * used to sit here on admin_menu was a leftover from that migration and has been
 * removed. bp_group_review_sanitize_email_settings() above is still the sanitize
 * callback the panel points at, so the save contract is unchanged.
 */
