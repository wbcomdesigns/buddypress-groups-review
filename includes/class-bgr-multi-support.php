<?php
/**
 * The frontend-specific functionality of the plugin.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    Buddypress_Group_Review
 * @subpackage Buddypress_Group_Review/includes
 */

/**
 * The frontend-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Buddypress_Group_Review
 * @subpackage Buddypress_Group_Review/includes
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Buddypress_Group_Review_Multi_Support {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The single instance of the class.
	 *
	 * @var Buddypress_Group_Review_Multi_Support
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * Main Buddypress_Group_Review_Multi_Support Instance.
	 *
	 * Ensures only one instance of Buddypress_Group_Review_Multi_Support is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see instantiateb_Buddypress_Group_Review()
	 * @return Buddypress_Group_Review_Multi_Support - Main instance.
	 */
	public static function instance() {
        
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Buddypress_Group_Review Constructor.
	 *
	 * @since  1.0.0
	 */
	public function __construct() { 
		$this->init_hooks();
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since  1.0.0
	 */
	private function init_hooks() {         
        add_action( 'admin_init', array( $this, 'bp_group_review_multisite_activation' ) );
	}

    /**
     * Check if the BuddyPress Group Reviews plugin should be active on the current subsite.
     */
    public function bp_group_review_multisite_activation() {        
        // Ensure this is a multisite installation
        if ( ! is_multisite() ) {
            return;
        }
        
        $deactivate = false;
        // If we're in the network admin, check for network-wide BuddyPress activation
        if ( is_network_admin() ) {
            if ( ! is_plugin_active_for_network( 'buddypress/bp-loader.php' ) ) {
                // BuddyPress is not network-activated, deactivate the plugin
                $deactivate = true;
                add_action( 'network_admin_notices',  array( $this, 'bp_group_review_show_buddypress_root_blog_notice' ) );
            }
        } else {
            // Check if BP_ROOT_BLOG is defined
            if ( defined( 'BP_ROOT_BLOG' ) && get_current_blog_id() != BP_ROOT_BLOG ) {			
                    // This subsite is not the one defined by BP_ROOT_BLOG, deactivate plugin
                    $deactivate = true;
                    add_action( 'admin_notices',  array( $this, 'bp_group_review_show_bp_root_blog_notice' ) );
            } else {			
                // BP_ROOT_BLOG is not defined, so only activate on the main site
                if ( ! is_main_site() && ! is_plugin_active( 'buddypress/bp-loader.php' ) ) {
                    // BuddyPress is not active on this subsite, deactivate the plugin
                    $deactivate = true;
                    add_action( 'admin_notices',  array( $this, 'bp_group_review_show_buddypress_required_notice' ) );
                }
            }
        }

        // Deactivate the plugin if necessary
        if ( $deactivate ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            if ( isset( $_GET['activate'] ) ) {
                unset( $_GET['activate'] );
            }
        }
    }

    /**
     * Admin notice when BP_ROOT_BLOG is defined and the plugin is activated on the wrong subsite.
     */
   public function bp_group_review_show_bp_root_blog_notice() {    
        echo '<div class="error"><p>';
        printf(
            // Translators: %s.
            esc_html__( '%1$s is only active on the subsite defined by BP_ROOT_BLOG.', 'bp-group-reviews' ),
            '<strong>' . esc_html__( 'BuddyPress Group Reviews', 'bp-group-reviews' ) . '</strong>'
        );
        echo '</p></div>';
    }

    /**
     * Show admin notice to inform the user that BuddyPress Group Reviews can only be activated on the BuddyPress root blog.
     */
    public function bp_group_review_show_buddypress_root_blog_notice() {   
        echo '<div class="error"><p>';
        printf(
            // Translators: %s.
            esc_html__( '%1$s can only be activated on the BuddyPress root blog.', 'bp-group-reviews' ),
            '<strong>' . esc_html__( 'BuddyPress Group Reviews', 'bp-group-reviews' ) . '</strong>'
        );
        echo '</p></div>';
    }

    /**
     * Show admin notice to inform the user that BuddyPress is required for the BuddyPress Group Reviews plugin.
     */
    public function bp_group_review_show_buddypress_required_notice() {    
        echo '<div class="error"><p>';
        printf(
            // Translators: %s.
            esc_html__( '%1$s requires BuddyPress to be active on this subsite. The plugin has been deactivated.', 'bp-group-reviews' ),
            '<strong>' . esc_html__( 'BuddyPress Group Reviews', 'bp-group-reviews' ) . '</strong>'
        );
        echo '</p></div>';
    }

}

/**
 * Main instance of Buddypress_Group_Review_Multi_Support.
 *
 * Returns the main instance of Buddypress_Group_Review_Multi_Support to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return Buddypress_Group_Review_Multi_Support
 */
Buddypress_Group_Review_Multi_Support::instance();