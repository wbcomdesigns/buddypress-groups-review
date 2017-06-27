<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

//Class to add custom scripts and styles
if( !class_exists( 'BGRScriptsStyles' ) ) {
	class BGRScriptsStyles{

		/*Constructor
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */
		 
		function __construct() {

			//Add Scripts only on reviews tab
			$curr_url = $_SERVER['REQUEST_URI'];
			add_action( 'wp_enqueue_scripts', array( $this, 'bgr_custom_variables' ) );
			
			if( strpos( $curr_url, 'review-settings' ) !== false ) {
				add_action( 'admin_init', array( $this, 'bgr_admin_custom_variables' ) );
			}
		}

		/* Actions performed for enqueuing scripts and styles for site front
		*  @since   1.0.0
		*  @author  Wbcom Designs
		*/
		
		function bgr_custom_variables() {			
                        wp_enqueue_style('bgr-reviews-css', BGR_PLUGIN_URL.'assets/css/bgr-reviews.css');
                        wp_enqueue_style('bgr-front-css', BGR_PLUGIN_URL.'assets/css/bgr-front.css');
                        wp_enqueue_script('bgr-front-js', BGR_PLUGIN_URL.'assets/js/bgr-front.js', array('jquery'));
                        wp_enqueue_style('bgr-front-css', BGR_PLUGIN_URL.'assets/css/bgr-front.css');
                        wp_enqueue_style('bgr-ratings-css', BGR_PLUGIN_URL.'assets/css/bgr-ratings.css');
						wp_enqueue_script('bgr-ratings-js', BGR_PLUGIN_URL. 'assets/js/bgr-ratings.js', array('jquery') ); 											
		}
		
		/*Actions performed for enqueuing scripts and styles for admin page
		*  @since   1.0.0
		*  @author  Wbcom Designs
		*/                 
		
		function bgr_admin_custom_variables() {
						wp_enqueue_script( 'bgr-js-admin', BGR_PLUGIN_URL.'admin/assets/js/bgr-admin.js', array('jquery') );
						wp_enqueue_style( 'bgr-css-admin', BGR_PLUGIN_URL.'admin/assets/css/bgr-admin.css' );
						wp_enqueue_style( 'wp-color-picker' );   
						wp_enqueue_script( 'bgr-colorpicker-handle', BGR_PLUGIN_URL. 'admin/assets/js/bgr-colorpicker.js', array( 'wp-color-picker' ), false, false ); 
						wp_enqueue_script( 'jquery-ui-sortable' );
						if ( !wp_script_is( 'jquery-ui-sortable', 'enqueued' ) ) {
							wp_enqueue_script( 'jquery-ui-sortable' );
						}
						wp_enqueue_style( 'bgr-select2-css', BGR_PLUGIN_URL.'admin/assets/css/select2.min.css');                        
						wp_enqueue_script( 'bgr-select2-js', BGR_PLUGIN_URL. 'admin/assets/js/select2.min.js', array('jquery') ); 	
						
		}
	}
	new BGRScriptsStyles();
}