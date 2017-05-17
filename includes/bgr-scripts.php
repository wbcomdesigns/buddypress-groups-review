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
		}
		
		/*Actions performed for enqueuing scripts and styles for admin page
                 *  @since   1.0.0
                 *  @author  Wbcom Designs
                 */                 
		function bgr_admin_custom_variables() {
			wp_enqueue_script('bgr-js-admin',BGR_PLUGIN_URL.'admin/assets/js/bgr-admin.js', array('jquery'));
			wp_enqueue_style('bgr-css-admin', BGR_PLUGIN_URL.'admin/assets/css/bgr-admin.css');
                        wp_enqueue_script('bgr-ui-admin','https://code.jquery.com/ui/1.12.1/jquery-ui.js', array('jquery'));
                        wp_enqueue_style('bgr-font-awesome-admin','https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
                       // wp_enqueue_style('select2-css','https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css');
                     //   wp_enqueue_script('select2-js','https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery')); 

		}
	}
	new BGRScriptsStyles();
}