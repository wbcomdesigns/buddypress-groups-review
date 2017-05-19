<?php
/**
 * Plugin Name: Review BuddyPress Groups
 * Plugin URI: https://wbcomdesigns.com/contact/
 * Description: This plugin allows site visitors to add reviews to the groups on the site.
 * Version: 1.0.0
 * Author: Wbcom Designs
 * Author URI: http://wbcomdesigns.com
 * License: GPLv2+
 * Text Domain: bp-group-reviews
 * Domain Path: /languages
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Load plugin textdomain.
 * @since 1.0.0
 *  @since   1.0.0
 *  @author  Wbcom Designs
*/
add_action( 'init', 'bgr_load_textdomain' );
function bgr_load_textdomain() {
	$domain = "bp-group-reviews";
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	load_textdomain( $domain, 'languages/'.$domain.'-' . $locale . '.mo' );
	$var = load_plugin_textdomain( $domain, false, plugin_basename( dirname(__FILE__) ) . '/languages' );
        
}

/**
 * Constants used in the plugin
 *  @since   1.0.0
 *  @author  Wbcom Designs
*/
define( 'BGR_PLUGIN_PATH', plugin_dir_path(__FILE__) );
define( 'BGR_PLUGIN_URL', plugin_dir_url(__FILE__) );
define( 'BGR_DOMAIN','bp-group-reviews');

/**
 * Include needed files on init
 *  @since   1.0.0
 *  @author  Wbcom Designs
*/
add_action( 'plugins_loaded', 'bgr_plugins_files' );

    function bgr_plugins_files(){
        if (!in_array('buddypress/bp-loader.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            add_action( 'admin_notices', 'bgr_admin_notice' );            
        }else if( bp_is_active( 'groups' ) ){
            /**
            * Include needed files on init
            */
            $include_files = array(
                'includes/bgr-scripts.php',
                'admin/bgr-admin.php',
                'includes/bgr-filters.php',
                'includes/bgr-ajax.php',
                'includes/bgr-shortcodes.php',
                'includes/widgets/bgr-review.php',
                'includes/bgr-notifications.php'
            );
            foreach($include_files as $include_file) include $include_file;
        }else{
            add_action( 'admin_notices', 'bgr_admin_group_notice' ); 
        }
    }
    
    /**
     * Show admin notice when buddypress not active or install
     *  @since   1.0.0
     *  @author  Wbcom Designs
    */
    function bgr_admin_notice() {
        ?>
        <div class="error notice is-dismissible">
            <p><?php _e( 'The <b>Review BuddyPress Groups</b> plugin requires <b>Buddypress</b> plugin to be installed and active.', BGR_DOMAIN ); ?></p>
        </div>
        <?php
    }
    
    /**
     * Show admin notice when buddypress user groups component not active
     *  @since   1.0.0
     *  @author  Wbcom Designs
    */   
    function bgr_admin_group_notice(){
        ?>
        <div class="error notice is-dismissible">
            <p><?php _e( 'The <b>Review BuddyPress Groups</b> plugin requires <b>BuddyPress User Groups </b>Component to be active.', BGR_DOMAIN ); ?></p>
        </div>
        <?php  
    }

    /**
     * Plugin Activation
     *  @since   1.0.0
     *  @author  Wbcom Designs
    */
    register_activation_hook( __FILE__, 'bgr_plugin_activation' );
    function bgr_plugin_activation() {
            //Check if "Buddypress" plugin is active or not
            if (!in_array('buddypress/bp-loader.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                    //Buddypress Plugin is inactive, hence deactivate this plugin
                    deactivate_plugins( plugin_basename( __FILE__ ) );
            }
    }

    /**
     * Plugin Uninstall
     *  @since   1.0.0
     *  @author  Wbcom Designs
    */
    register_uninstall_hook(__FILE__, 'bgr_plugin_uninstall');
    function bgr_plugin_uninstall(){
    }

    /**
     * Settings link for this plugin
     *  @since   1.0.0
     *  @author  Wbcom Designs
    */
    add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'bgr_admin_page_link' );
    function bgr_admin_page_link( $links ) {
            $page_link = array('<a href="'.admin_url('admin.php?page=group-review-settings').'">Settings</a>');
            return array_merge( $links, $page_link );
    }
    
    add_action( 'bp_include', 'include_files_bp' );
    function include_files_bp() {
        include 'includes/bgr-grp-extn.php';
     }