<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

//Class to add custom scripts and styles
if( !class_exists( 'BGRScriptsStyles' ) ) {
	class BGRScriptsStyles{

		/** Constructor
                 *  @since   1.0.0
                 *  @access   public   
                 *  @author  Wbcom Designs
                 */
            
		public function __construct() {

			//Add Scripts only on reviews tab
			$curr_url = $_SERVER['REQUEST_URI'];
			add_action( 'wp_enqueue_scripts', array( $this, 'bgr_custom_variables' ) );
			
			if( strpos( $curr_url, 'review-settings' ) !== false ) {
				add_action( 'admin_init', array( $this, 'bgr_admin_custom_variables' ) );
			}
		}

		/** Actions performed for enqueuing scripts and styles for site front
                 *  @since   1.0.0
                 *  @access   public   
                 *  @author  Wbcom Designs
                 */
                
		public function bgr_custom_variables() {			
                        wp_enqueue_style('bgr-reviews-css', BGR_PLUGIN_URL.'assets/css/bgr-reviews.css');
                        wp_enqueue_style('bgr-front-css', BGR_PLUGIN_URL.'assets/css/bgr-front.css');
                        wp_enqueue_script('bgr-front-js', BGR_PLUGIN_URL.'assets/js/bgr-front.js', array('jquery'));
                        wp_enqueue_style('bgr-front-css', BGR_PLUGIN_URL.'assets/css/bgr-front.css');
		}
		
		/** Actions performed for enqueuing scripts and styles for admin page
                 *  @since   1.0.0
                 *  @access  public   
                 *  @author  Wbcom Designs
                 */ 
                
		public function bgr_admin_custom_variables() {
			wp_enqueue_script('bgr-js-admin',BGR_PLUGIN_URL.'admin/assets/js/bgr-admin.js', array('jquery'));
			wp_enqueue_style('bgr-css-admin', BGR_PLUGIN_URL.'admin/assets/css/bgr-admin.css');
                        wp_enqueue_script( 'jquery-ui-sortable' );

		}
	}
	new BGRScriptsStyles();
}