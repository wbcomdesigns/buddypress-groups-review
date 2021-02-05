<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'BGRScriptsStyles' ) ) {
	// Class to add custom scripts and styles.
	class BGRScriptsStyles {
		/*
		Constructor
		 *  @since   1.0.0
		 *  @author  Wbcom Designs
		 */

		public function __construct() {

			// Add Scripts only on reviews tab.
			add_action( 'wp_enqueue_scripts', array( $this, 'bgr_custom_variables' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'bgr_admin_custom_variables' ) );
		}

		/*
		 Actions performed for enqueuing scripts and styles for site front
		*  @since   1.0.0
		*  @author  Wbcom Designs
		*/

		function bgr_custom_variables() {
			wp_enqueue_style( 'bgr-font-awesome', 'https://use.fontawesome.com/releases/v5.4.2/css/all.css' );
			wp_enqueue_style( 'bgr-reviews-css', BGR_PLUGIN_URL . 'assets/css/bgr-reviews.css' );
			wp_enqueue_style( 'bgr-front-css', BGR_PLUGIN_URL . 'assets/css/bgr-front.css' );
			wp_enqueue_script( 'bgr-front-js', BGR_PLUGIN_URL . 'assets/js/bgr-front.js', array( 'jquery' ), time() );
			wp_localize_script(
				'bgr-front-js',
				'bgr_front_js_object',
				array(
					'view_more_text' => esc_html__( 'View More..', 'bp-group-reviews' ),
					'view_less_text' => esc_html__( 'View Less..', 'bp-group-reviews' ),
				)
			);
			wp_enqueue_style( 'bgr-ratings-css', BGR_PLUGIN_URL . 'assets/css/bgr-ratings.css' );
			wp_enqueue_script( 'bgr-ratings-js', BGR_PLUGIN_URL . 'assets/js/bgr-ratings.js', array( 'jquery' ) );
		}

		/*
		  Actions performed for enqueuing scripts and styles for admin page
		*  	@since   1.0.0
		*  	@author  Wbcom Designs
		*/

		function bgr_admin_custom_variables() {
			$curr_url = $_SERVER['REQUEST_URI'];
			$screen   = get_current_screen();
			if ( ( strpos( $curr_url, 'review' ) == true ) || ( 'wb-plugins_page_group-review-settings' == $screen->base ) ) {
				wp_enqueue_script( 'bgr-js-admin', BGR_PLUGIN_URL . 'admin/assets/js/bgr-admin.js', array( 'jquery' ) );
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'bgr-colorpicker-handle', BGR_PLUGIN_URL . 'admin/assets/js/bgr-colorpicker.js', array( 'wp-color-picker' ), false, false );
				if ( ! wp_style_is( 'wbcom-selectize-css', 'enqueued' ) ) {
					wp_enqueue_style( 'wbcom-selectize-css', BGR_PLUGIN_URL . 'admin/assets/css/selectize.css' );
				}
				wp_enqueue_style( 'bgr-css-admin', BGR_PLUGIN_URL . 'admin/assets/css/bgr-admin.css' );
				if ( ! wp_script_is( 'wbcom-selectize-js', 'enqueued' ) ) {
					wp_enqueue_script( 'wbcom-selectize-js', BGR_PLUGIN_URL . 'admin/assets/js/selectize.min.js', array( 'jquery' ) );
				}
				if ( ! wp_script_is( 'jquery-ui-sortable', 'enqueued' ) ) {
					wp_enqueue_script( 'jquery-ui-sortable' );
				}
			}
		}
	}
	new BGRScriptsStyles();
}
