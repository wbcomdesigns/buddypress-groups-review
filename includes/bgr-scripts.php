<?php
/**
 * Class to add custom scripts and styles.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    BuddyPress_Group_Review
 * @subpackage BuddyPress_Group_Review/includes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'BGRScriptsStyles' ) ) {
	/**
	 * Class to add custom scripts and styles.
	 *
	 * @link       https://wbcomdesigns.com/
	 * @since      1.0.0
	 *
	 * @package    BuddyPress_Group_Review
	 * @subpackage BuddyPress_Group_Review/includes
	 */
	class BGRScriptsStyles {

		/**
		 * Constructor
		 *
		 * @since   1.0.0
		 * @author  Wbcom Designs
		 *
		 * @return void
		 */
		public function __construct() {

			// Add Scripts only on reviews tab.
			add_action( 'wp_enqueue_scripts', array( $this, 'bgr_custom_variables' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'bgr_admin_custom_variables' ) );
		}

		/**
		 * Actions performed for enqueuing scripts and styles for site front.
		 *
		 * @since   1.0.0
		 * @author  Wbcom Designs
		 *
		 * @return void
		 */
		public function bgr_custom_variables() {
			if ( function_exists( 'bp_is_activity_component' ) && bp_is_activity_component() || function_exists('bp_is_groups_component') && bp_is_groups_component() || function_exists('bp_is_group') && bp_is_group() ) {
				if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
					$script_extension = '.js';
					$script_path      = '';
				} else {
					$script_extension = '.min.js';
					$script_path      = '/min';
				}
				if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
					$style_extension = is_rtl() ? '.rtl.css' : '.css';
					$style_path      = is_rtl() ? '/rtl' : '';
				} else {
					$style_extension = is_rtl() ? '.rtl.css' : '.min.css';
					$style_path      = is_rtl() ? '/rtl' : '/min';
				}
				wp_enqueue_style( 'bgr-font-awesome', 'https://use.fontawesome.com/releases/v5.4.2/css/all.css', array(), BGR_PLUGIN_VERSION, 'all' );
				wp_enqueue_style( 'bgr-reviews-css', BGR_PLUGIN_URL . 'assets/css'.$style_path.'/bgr-reviews'.$style_extension, array(), BGR_PLUGIN_VERSION, 'all' );
				wp_enqueue_style( 'bgr-front-css', BGR_PLUGIN_URL . 'assets/css'.$style_path.'/bgr-front'.$style_extension, array(), BGR_PLUGIN_VERSION, 'all' );
				wp_enqueue_script( 'bgr-front-js', BGR_PLUGIN_URL . 'assets/js'.$script_path.'/bgr-front'.$script_extension, array( 'jquery' ), BGR_PLUGIN_VERSION, false );
				$bgr_admin_settings         = get_option( 'bgr_admin_general_settings' );
				$bgr_admin_display_settings = get_option( 'bgr_admin_display_settings' );
				$exclude_groups             = isset( $bgr_admin_settings['exclude_groups'] ) ? $bgr_admin_settings['exclude_groups'] : array();
				if ( groups_is_user_admin( bp_loggedin_user_id(), bp_get_current_group_id() ) ) {
					$group_admin = 'yes';
				} else {
					$group_admin = 'no';
				}
				if ( in_array( bp_get_current_group_id(), $exclude_groups ) ) {
					$exclude_groups = 'true';
				}
				$auto_approve_reviews = '';
				if ( isset( $bgr_admin_settings['auto_approve_reviews'] ) && 'yes' == $bgr_admin_settings['auto_approve_reviews'] ) {
					$auto_approve_reviews = $bgr_admin_settings['auto_approve_reviews']; // hide review-management tab when moderation in disbale.
				}
				wp_localize_script(
					'bgr-front-js',
					'bgr_front_js_object',
					array(
						'view_more_text'       => esc_html__( 'View More..', 'bp-group-reviews' ),
						'view_less_text'       => esc_html__( 'View Less..', 'bp-group-reviews' ),
						'wbcom_nonce'          => wp_create_nonce( 'ajax-nonce' ),
						'auto_approve_reviews' => $auto_approve_reviews,
						'exclude_groups'       => $exclude_groups,
						'review_label'         => isset( $bgr_admin_display_settings['review_label'] ) ? strtolower( $bgr_admin_display_settings['review_label'] ) : esc_html( strtolower( 'Review' ) ),
						'group_id'             => bp_get_current_group_id(),
						'check_group_admin'    => $group_admin,
					)
				);
				wp_enqueue_style( 'bgr-ratings-css', BGR_PLUGIN_URL . 'assets/css'.$style_path.'/bgr-ratings'.$style_extension, array(), BGR_PLUGIN_VERSION, 'all' );
				wp_enqueue_script( 'bgr-ratings-js', BGR_PLUGIN_URL . 'assets/js'.$script_path.'/bgr-ratings'.$script_extension, array( 'jquery' ), BGR_PLUGIN_VERSION, false );
			}
		}

		/*
		*  	@since   1.0.0
		*  	@author  Wbcom Designs
		*/

		/**
		 * Actions performed for enqueuing scripts and styles for admin page.
		 *
		 * @since 1.0.0
		 * @author Wbcom Designs
		 *
		 * @return void
		 */
		public function bgr_admin_custom_variables() {
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$script_extension = '.js';
				$script_path      = '';
			} else {
				$script_extension = '.min.js';
				$script_path      = '/min';
			}
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$style_extension = is_rtl() ? '.rtl.css' : '.css';
				$style_path      = is_rtl() ? '/rtl' : '';
			} else {
				$style_extension = is_rtl() ? '.rtl.css' : '.min.css';
				$style_path      = is_rtl() ? '/rtl' : '/min';
			}
			$curr_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$screen   = get_current_screen();
			if ( ( 'wb-plugins_page_group-review-settings' == $screen->base ) || 'toplevel_page_wbcomplugins'  == $screen->base ) {
				wp_enqueue_script( 'bgr-js-admin', BGR_PLUGIN_URL . 'admin/assets/js'.$script_path.'/bgr-admin'.$script_extension, array( 'jquery' ), BGR_PLUGIN_VERSION, false );
				wp_localize_script(
					'bgr-js-admin',
					'bgr_admin_js',
					array(
						'ajax_url'        => admin_url( 'admin-ajax.php' ),
						'activate_text'   => esc_html__( 'Activate', 'bp-group-reviews' ),
						'deactivate_text' => esc_html__( 'Deactivate', 'bp-group-reviews' ),
						'wbcom_nonce'     => wp_create_nonce( 'admin-ajax-nonce' ),
					)
				);
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'bgr-colorpicker-handle', BGR_PLUGIN_URL . 'admin/assets/js'.$script_path.'/bgr-colorpicker'.$script_extension, array( 'wp-color-picker' ), BGR_PLUGIN_VERSION, false );
				if ( ! wp_style_is( 'wbcom-selectize-css', 'enqueued' ) ) {
					wp_enqueue_style( 'wbcom-selectize-css', BGR_PLUGIN_URL . 'admin/assets/css/vendor/selectize.css', array(), BGR_PLUGIN_VERSION, 'all' );
				}
				wp_enqueue_style( 'bgr-css-admin', BGR_PLUGIN_URL . 'admin/assets/css'.$style_path.'/bgr-admin'.$style_extension, array(), BGR_PLUGIN_VERSION, 'all' );
				if ( ! wp_script_is( 'wbcom-selectize-js', 'enqueued' ) ) {
					wp_enqueue_script( 'wbcom-selectize-js', BGR_PLUGIN_URL . 'admin/assets/js/vendor/selectize.min.js', array( 'jquery' ), BGR_PLUGIN_VERSION, false );
				}
				if ( ! wp_script_is( 'jquery-ui-sortable', 'enqueued' ) ) {
					wp_enqueue_script( 'jquery-ui-sortable' );
				}
			}
		}
	}
	new BGRScriptsStyles();
}
